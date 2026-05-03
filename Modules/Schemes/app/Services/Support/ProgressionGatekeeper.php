<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Auth\Models\User;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Services\PrerequisiteService;

class ProgressionGatekeeper
{
    public function __construct(
        private readonly ProgressionFinder $finder,
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function validateLessonAccess(Course $course, Unit $unit, Lesson $lesson, int $userId): Enrollment
    {
        if ($unit->course_id !== $course->id || $lesson->unit_id !== $unit->id) {
            throw new ModelNotFoundException(__('messages.progress.lesson_not_in_unit'));
        }

        $user = User::find($userId);

        if ($user && ($user->hasRole(['Superadmin', 'Admin', 'Instructor']))) {
            $enrollment = $this->finder->getEnrollmentForCourse($course->id, $userId);
            if (! $enrollment) {
                
                $enrollment = Enrollment::create([
                    'user_id' => $userId,
                    'course_id' => $course->id,
                    'status' => EnrollmentStatus::Active,
                    'enrolled_at' => now(),
                ]);
            }

            return $enrollment;
        }

        $enrollment = $this->finder->getEnrollmentForCourse($course->id, $userId);

        if (! $enrollment || ! $this->canAccessLesson($lesson, $enrollment)) {
            throw new \App\Exceptions\BusinessException(__('messages.progress.locked_prerequisite'), [], 403);
        }

        return $enrollment;
    }

    public function canAccessLesson(Lesson $lesson, Enrollment $enrollment): bool
    {
        
        if ($lesson->isCompletedBy($enrollment->user_id)) {
            return true;
        }

        $lessonModel = $lesson->fresh([
            'unit.course',
        ]);

        if (! $lessonModel || ! $lessonModel->unit || ! $lessonModel->unit->course) {
            return false;
        }

        $unitAccess = $this->prerequisiteService->checkUnitAccess($lessonModel->unit, $enrollment->user_id);
        if (! ($unitAccess['accessible'] ?? false)) {
            return false;
        }

        $lessonAccess = $this->prerequisiteService->checkLessonAccess($lessonModel, $enrollment->user_id);

        return (bool) ($lessonAccess['accessible'] ?? false);
    }
}
