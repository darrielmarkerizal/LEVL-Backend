<?php

declare(strict_types=1);

namespace Modules\Schemes\Traits;

use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Schemes\Models\Course;

trait ValidatesEnrollment
{
    /**
     * Get active enrollment for the authenticated student
     */
    protected function getActiveEnrollment(Course $course): ?Enrollment
    {
        $user = auth('api')->user();
        
        if (!$user || !$user->hasRole('Student')) {
            return null;
        }

        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->first();
    }

    /**
     * Check if student is enrolled in the course
     */
    protected function isEnrolled(Course $course): bool
    {
        return $this->getActiveEnrollment($course) !== null;
    }

    /**
     * Require enrollment for students, return error response if not enrolled
     */
    protected function requireEnrollment(Course $course): ?object
    {
        $user = auth('api')->user();
        
        // Non-students can access (admin, instructor, etc)
        if (!$user || !$user->hasRole('Student')) {
            return null;
        }

        // Check enrollment
        if (!$this->isEnrolled($course)) {
            return $this->forbidden(__('messages.enrollments.not_enrolled'));
        }

        return null;
    }
}
