<?php

namespace Modules\Enrollments\Policies;

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;

class EnrollmentPolicy
{
    /**
     * Determine whether the user can view any enrollments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Superadmin');
    }

    /**
     * Determine whether the user can view enrollments for a specific course.
     */
    public function viewForCourse(User $user, int $courseId): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->hasRole('Admin') || $user->hasRole('Instructor')) {
            $course = \Modules\Schemes\Models\Course::find($courseId);
            if ($course) {
                return (int) $course->instructor_id === (int) $user->id
                    || (method_exists($course, 'hasAdmin') && $course->hasAdmin($user));
            }
        }

        return false;
    }

    /**
     * Determine whether the user can view the enrollment.
     */
    public function view(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // User can view their own enrollment
        if ((int) $enrollment->user_id === (int) $user->id) {
            return true;
        }

        // Course managers can view enrollments
        if ($user->hasRole('Admin') || $user->hasRole('Instructor')) {
            $course = $enrollment->course;
            if ($course) {
                return (int) $course->instructor_id === (int) $user->id
                    || (method_exists($course, 'hasAdmin') && $course->hasAdmin($user));
            }
        }

        return false;
    }

    /**
     * Determine whether the user can enroll (create enrollment).
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Student');
    }

    /**
     * Determine whether the user can cancel the enrollment.
     */
    public function cancel(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return (int) $enrollment->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can withdraw from the enrollment.
     */
    public function withdraw(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return (int) $enrollment->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can approve the enrollment.
     */
    public function approve(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->hasRole('Admin') || $user->hasRole('Instructor')) {
            $course = $enrollment->course;
            if ($course) {
                return (int) $course->instructor_id === (int) $user->id
                    || (method_exists($course, 'hasAdmin') && $course->hasAdmin($user));
            }
        }

        return false;
    }

    /**
     * Determine whether the user can decline the enrollment.
     */
    public function decline(User $user, Enrollment $enrollment): bool
    {
        return $this->approve($user, $enrollment);
    }

    /**
     * Determine whether the user can remove the enrollment.
     */
    public function remove(User $user, Enrollment $enrollment): bool
    {
        return $this->approve($user, $enrollment);
    }
}
