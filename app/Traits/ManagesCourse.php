<?php

namespace App\Traits;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;

trait ManagesCourse
{
    protected function userCanManageCourse(User $user, Course $course): bool
    {
        if ($user->hasAnyRole(['Admin', 'Superadmin'])) {
            return true;
        }

        return $course->isAssignedInstructor($user);
    }

    protected function isSystemAdmin(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Superadmin']);
    }

    protected function isInstructorOfCourse(User $user, Course $course): bool
    {
        return $course->isAssignedInstructor($user);
    }
}
