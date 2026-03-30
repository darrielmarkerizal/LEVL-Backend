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

        // Get enrollment for this user and course
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $lesson->unit->course_id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->first();

        if (!$enrollment) {
            throw LessonCompletionException::lessonLocked(
                __('messages.lessons.not_enrolled')
            );
        }

        // Check if already completed
        $existing = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->where('status', 'completed')
            ->first();

        if ($existing) {
            throw LessonCompletionException::alreadyCompleted(
                __('messages.lessons.already_completed')
            );
        }

        // Mark as completed in lesson_progress
        $progress = LessonProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => 'completed',
                'progress_percent' => 100,
                'completed_at' => now(),
            ]
        );

        // Update progression
        $this->progressionService->markLessonCompleted($lesson, $enrollment);

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

        // Get enrollment for this user and course
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $lesson->unit->course_id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->first();

        if (!$enrollment) {
            return false;
        }

        // Update lesson_progress to not_started
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

        // Update progression
        $this->progressionService->markLessonUncompleted($lesson, $enrollment);

        return true;
    }

    public function isCompleted(Lesson $lesson, int $userId): bool
    {
        return $lesson->isCompletedBy($userId);
    }

    public function getUserCompletions(int $userId, int $unitId): array
    {
        // Get all lessons in the unit
        $lessons = Lesson::where('unit_id', $unitId)->get();
        
        if ($lessons->isEmpty()) {
            return [];
        }

        // Get course_id from first lesson
        $courseId = $lessons->first()->unit->course_id;

        // Get enrollment
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return [];
        }

        // Get completed lessons from lesson_progress
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
