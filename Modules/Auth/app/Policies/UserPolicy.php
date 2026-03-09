<?php

declare(strict_types=1);

namespace Modules\Auth\Policies;

use Modules\Auth\Models\User;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\CourseAdmin;
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

        // Admin can view other Admins (but not Superadmin)
        if ($targetUser->hasRole('Admin') && ! $targetUser->hasRole('Superadmin')) {
            return true;
        }

        // Admin can view Students/Instructors in managed courses OR users they created
        if ($targetUser->hasRole(['Instructor', 'Student'])) {
            $managedCourseIds = CourseAdmin::where('user_id', $authUser->id)
                ->pluck('course_id')
                ->unique();

            // Check if user was created by this admin
            $wasCreatedByAdmin = Activity::query()
                ->where('event', 'created')
                ->where('subject_type', User::class)
                ->where('subject_id', $targetUser->id)
                ->where('causer_type', User::class)
                ->where('causer_id', $authUser->id)
                ->exists();

            if ($wasCreatedByAdmin) {
                return true;
            }

            // For Students: check active/completed enrollment in managed courses
            if ($targetUser->hasRole('Student')) {
                return Enrollment::where('user_id', $targetUser->id)
                    ->whereIn('course_id', $managedCourseIds)
                    ->whereIn('status', [
                        EnrollmentStatus::Active->value,
                        EnrollmentStatus::Completed->value,
                    ])
                    ->exists();
            }

            // For Instructors: check if teaching in managed courses
            if ($targetUser->hasRole('Instructor')) {
                return Course::query()
                    ->whereIn('id', $managedCourseIds)
                    ->where('instructor_id', $targetUser->id)
                    ->exists();
            }
        }

        return false;
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole(['Superadmin', 'Admin']);
    }

    public function update(User $authUser, User $targetUser): bool
    {
        return $this->view($authUser, $targetUser);
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        if (! $authUser->hasRole('Superadmin')) {
            return false;
        }

        // Cannot delete self
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        return true;
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

        // Only Admin role can reset passwords
        if (! $authUser->hasRole('Admin')) {
            return false;
        }

        // Admin cannot reset password for other Admins or Superadmins
        if ($targetUser->hasRole(['Admin', 'Superadmin'])) {
            return false;
        }

        // Admin can reset password for Students/Instructors in courses they manage
        if ($targetUser->hasRole(['Instructor', 'Student'])) {
            $managedCourseIds = CourseAdmin::where('user_id', $authUser->id)
                ->pluck('course_id')
                ->unique();

            return Enrollment::where('user_id', $targetUser->id)
                ->whereIn('course_id', $managedCourseIds)
                ->exists();
        }

        return false;
    }
}
