<?php

declare(strict_types=1);

namespace Modules\Auth\Policies;

use Modules\Auth\Models\User;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;
use Spatie\Activitylog\Models\Activity;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole(['Superadmin', 'Admin', 'Instructor']);
    }

    public function view(User $authUser, User $targetUser): bool
    {
        if ($authUser->hasRole('Superadmin')) {
            return true;
        }

        if ($authUser->hasRole('Instructor')) {
            if (! $targetUser->hasRole('Student')) {
                return false;
            }

            $instructorCourseIds = Course::query()
                ->where('instructor_id', $authUser->id)
                ->pluck('id')
                ->unique();

            $hasActiveOrCompletedEnrollment = Enrollment::where('user_id', $targetUser->id)
                ->whereIn('course_id', $instructorCourseIds)
                ->whereIn('status', [
                    EnrollmentStatus::Active->value,
                    EnrollmentStatus::Completed->value,
                ])
                ->exists();

            if ($hasActiveOrCompletedEnrollment) {
                return true;
            }

            return Activity::query()
                ->where('event', 'created')
                ->where('subject_type', User::class)
                ->where('subject_id', $targetUser->id)
                ->where('causer_type', User::class)
                ->where('causer_id', $authUser->id)
                ->exists();
        }

        if (! $authUser->hasRole('Admin')) {
            return false;
        }

        // Admin CANNOT view Superadmins
        if ($targetUser->hasRole('Superadmin')) {
            return false;
        }

        // Admin can view all Students, Instructors, and other Admins
        return true;
    }

    public function create(User $authUser): bool
    {
        // Superadmin and Admin can create users
        // Admin cannot create Superadmin (enforced in UserLifecycleProcessor)
        return $authUser->hasRole(['Superadmin', 'Admin']);
    }

    public function update(User $authUser, User $targetUser): bool
    {
        return $this->view($authUser, $targetUser);
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        // Cannot delete self
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // Superadmin can delete anyone (except self)
        if ($authUser->hasRole('Superadmin')) {
            return true;
        }

        // Admin can delete users except Superadmins
        if ($authUser->hasRole('Admin')) {
            return ! $targetUser->hasRole('Superadmin');
        }

        return false;
    }

    public function updateStatus(User $authUser, User $targetUser): bool
    {
        return $this->view($authUser, $targetUser);
    }

    public function resetPassword(User $authUser, User $targetUser): bool
    {
        // Superadmin can reset password for anyone
        if ($authUser->hasRole('Superadmin')) {
            return true;
        }

        // Admin cannot reset passwords for other Admins or Superadmins
        if ($authUser->hasRole('Admin')) {
            if ($targetUser->hasRole(['Admin', 'Superadmin'])) {
                return false;
            }

            // Admin can reset password for Students and Instructors
            return $this->view($authUser, $targetUser);
        }

        // Instructor and other roles cannot reset passwords
        return false;
    }
}
