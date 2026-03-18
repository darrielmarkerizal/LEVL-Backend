<?php

declare(strict_types=1);

namespace Modules\Enrollments\Policies;

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;

class EnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Superadmin');
    }

    public function viewByCourse(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can manage all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can only manage their assigned courses
        if ($user->hasRole('Instructor')) {
            return $course->hasInstructor($user);
        }

        return false;
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ((int) $enrollment->user_id === (int) $user->id) {
            return true;
        }

        if ($enrollment->course) {
            // Admin can view all enrollments
            if ($user->hasRole('Admin')) {
                return true;
            }

            // Instructor can only view enrollments in their courses
            if ($user->hasRole('Instructor')) {
                return $enrollment->course->hasInstructor($user);
            }
        }

        return false;
    }

    public function modify(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ((int) $enrollment->user_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, Enrollment $enrollment): bool
    {
        return $this->modify($user, $enrollment);
    }

    public function withdraw(User $user, Enrollment $enrollment): bool
    {
        return $this->modify($user, $enrollment);
    }

    public function approve(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return $this->isCourseManager($user, $enrollment);
    }

    public function decline(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return $this->isCourseManager($user, $enrollment);
    }

    public function remove(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return $this->isCourseManager($user, $enrollment);
    }

    private function isCourseManager(User $user, Enrollment $enrollment): bool
    {
        if (! $enrollment->course) {
            return false;
        }

        // Admin can manage all enrollments
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can only manage enrollments in their courses
        if ($user->hasRole('Instructor')) {
            return $enrollment->course->hasInstructor($user);
        }

        return false;
    }
}
