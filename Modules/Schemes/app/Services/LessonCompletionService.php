<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\LessonProgress;
use Modules\Schemes\Exceptions\LessonCompletionException;
use Modules\Schemes\Models\Lesson;

class LessonCompletionService
{
    public function __construct(
        private readonly PrerequisiteService $prerequisiteService,
        private readonly ProgressionService $progressionService
    ) {}

    public function markAsCompleted(Lesson $lesson, int $userId): LessonProgress
    {
        $accessCheck = $this->prerequisiteService->checkLessonAccess($lesson, $userId);

        if (! $accessCheck['accessible']) {
            throw LessonCompletionException::lessonLocked(
                __('messages.lessons.locked_cannot_complete')
            );
        }

        
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $lesson->unit->course_id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->first();

        if (!$enrollment) {
            throw LessonCompletionException::lessonLocked(
                __('messages.lessons.not_enrolled')
            );
        }

        $this->progressionService->markLessonCompleted($lesson, $enrollment);

        $progress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->where('status', 'completed')
            ->first();

        if (! $progress) {
            throw LessonCompletionException::notCompleted(
                __('messages.lessons.not_completed')
            );
        }

        return $progress;
    }

    public function unmarkAsCompleted(Lesson $lesson, int $userId): bool
    {
        $accessCheck = $this->prerequisiteService->checkLessonAccess($lesson, $userId);

        if (! $accessCheck['accessible']) {
            throw LessonCompletionException::lessonLocked(
                __('messages.lessons.locked_cannot_uncomplete')
            );
        }

        
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $lesson->unit->course_id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->first();

        if (!$enrollment) {
            return false;
        }

        
        $updated = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->update([
                'status' => 'not_started',
                'progress_percent' => 0,
                'completed_at' => null,
            ]);

        if ($updated === 0) {
            throw LessonCompletionException::notCompleted(
                __('messages.lessons.not_completed')
            );
        }

        
        $this->progressionService->markLessonUncompleted($lesson, $enrollment);

        return true;
    }

    public function isCompleted(Lesson $lesson, int $userId): bool
    {
        return $lesson->isCompletedBy($userId);
    }

    public function getUserCompletions(int $userId, int $unitId): array
    {
        
        $lessons = Lesson::where('unit_id', $unitId)->get();
        
        if ($lessons->isEmpty()) {
            return [];
        }

        
        $courseId = $lessons->first()->unit->course_id;

        
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return [];
        }

        
        return LessonProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->where('status', 'completed')
            ->with('lesson')
            ->get()
            ->map(fn ($progress) => [
                'lesson_id' => $progress->lesson_id,
                'lesson_title' => $progress->lesson->title,
                'completed_at' => $progress->completed_at,
            ])
            ->toArray();
    }
}
