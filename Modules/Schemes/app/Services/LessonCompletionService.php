<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Modules\Schemes\Exceptions\LessonCompletionException;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\LessonCompletion;

class LessonCompletionService
{
    public function __construct(
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function markAsCompleted(Lesson $lesson, int $userId): LessonCompletion
    {
        $accessCheck = $this->prerequisiteService->checkLessonAccess($lesson, $userId);

        if (! $accessCheck['accessible']) {
            throw LessonCompletionException::lessonLocked(
                __('messages.lessons.locked_cannot_complete')
            );
        }

        $existing = LessonCompletion::where('lesson_id', $lesson->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            throw LessonCompletionException::alreadyCompleted(
                __('messages.lessons.already_completed')
            );
        }

        $completion = LessonCompletion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $userId,
            'completed_at' => now(),
        ]);

        // Also update lesson_progress for progression tracking
        $enrollment = \Modules\Enrollments\Models\Enrollment::where('user_id', $userId)
            ->where('course_id', $lesson->unit->course_id)
            ->whereIn('status', [\Modules\Enrollments\Enums\EnrollmentStatus::Active, \Modules\Enrollments\Enums\EnrollmentStatus::Completed])
            ->first();

        if ($enrollment) {
            // Update or create lesson progress
            \Modules\Enrollments\Models\LessonProgress::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'status' => \Modules\Enrollments\Enums\ProgressStatus::Completed,
                    'progress_percent' => 100,
                    'completed_at' => now(),
                ]
            );

            // Dispatch event for XP and progress tracking
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($lesson, $userId, $enrollment) {
                \Modules\Schemes\Events\LessonCompleted::dispatch($lesson, $userId, $enrollment->id);
            });
        }

        return $completion;
    }

    public function unmarkAsCompleted(Lesson $lesson, int $userId): bool
    {
        $accessCheck = $this->prerequisiteService->checkLessonAccess($lesson, $userId);

        if (! $accessCheck['accessible']) {
            throw LessonCompletionException::lessonLocked(
                __('messages.lessons.locked_cannot_uncomplete')
            );
        }

        $deleted = LessonCompletion::where('lesson_id', $lesson->id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted === 0) {
            throw LessonCompletionException::notCompleted(
                __('messages.lessons.not_completed')
            );
        }

        return true;
    }

    public function isCompleted(Lesson $lesson, int $userId): bool
    {
        return $lesson->isCompletedBy($userId);
    }

    public function getUserCompletions(int $userId, int $unitId): array
    {
        return LessonCompletion::whereHas('lesson', function ($query) use ($unitId) {
            $query->where('unit_id', $unitId);
        })
            ->where('user_id', $userId)
            ->with('lesson')
            ->get()
            ->map(fn ($completion) => [
                'lesson_id' => $completion->lesson_id,
                'lesson_title' => $completion->lesson->title,
                'completed_at' => $completion->completed_at,
            ])
            ->toArray();
    }
}
