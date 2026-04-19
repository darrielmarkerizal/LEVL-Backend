<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;

class ProgressionFinder
{
    public function getEnrollmentForCourse(int $courseId, int $userId): ?Enrollment
    {
        return Enrollment::query()
            ->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->whereIn('status', [
                EnrollmentStatus::Active,
                EnrollmentStatus::Completed,
            ])
            ->first();
    }

    public function validateAndGetProgress(Course $course, int $targetUserId, int $requestingUserId): array
    {
        $targetUser = \Modules\Auth\Models\User::find($targetUserId);
        if (! $targetUser) {
            throw new ModelNotFoundException(__('messages.users.not_found'));
        }

        $enrollment = Enrollment::where('user_id', $targetUserId)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (! $enrollment) {
            throw new ModelNotFoundException(__('messages.progress.enrollment_not_found'));
        }

        return $this->getCourseProgressData($course, $enrollment);
    }

    public function getProgressForUser(Course $course, int $userId): array
    {
        $enrollment = $this->getEnrollmentForCourse($course->id, $userId);
        if (! $enrollment) {
            throw new ModelNotFoundException(__('messages.progress.enrollment_not_found'));
        }

        return $this->getCourseProgressData($course, $enrollment);
    }

    public function getCourseProgressData(Course $course, Enrollment $enrollment): array
    {
        $courseModel = $course->fresh([
            'units' => function ($query) {
                $query->where('status', 'published')
                    ->orderBy('order')
                    ->with(['lessons' => function ($lessonQuery) {
                        $lessonQuery->where('status', 'published')->orderBy('order');
                    }]);
            },
        ]);

        if (! $courseModel) {
            return [];
        }

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        

        
        
        
        
        
        
        
        
        

        return []; 
    }
}
