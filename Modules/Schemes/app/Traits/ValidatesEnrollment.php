<?php

declare(strict_types=1);

namespace Modules\Schemes\Traits;

use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;

trait ValidatesEnrollment
{
    
    protected function getActiveEnrollment(Course $course): ?Enrollment
    {
        $user = auth('api')->user();

        if (! $user || ! $user->hasRole('Student')) {
            return null;
        }

        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->first();
    }

    
    protected function isEnrolled(Course $course): bool
    {
        return $this->getActiveEnrollment($course) !== null;
    }

    
    protected function requireEnrollment(Course $course): ?object
    {
        $user = auth('api')->user();

        
        if (! $user || ! $user->hasRole('Student')) {
            return null;
        }

        
        if (! $this->isEnrolled($course)) {
            return $this->forbidden(__('messages.enrollments.not_enrolled'));
        }

        return null;
    }
}
