<?php

namespace App\Traits;

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;

/**
 * Trait ManagesCourse
 *
 * Provides shared authorization methods for course management operations.
 * This trait consolidates duplicate authorization logic from multiple controllers
 * (AssignmentController, EnrollmentsController, ReportController) into a single location.
 */
trait ManagesCourse
{
    /**
     * Check if user can manage the course.
     *
     * A user can manage a course if they are:
     * - A Superadmin (can manage all courses)
     * - An Admin (can manage all courses)
     * - The course instructor (instructor_id matches user id)
     * - A course admin (assigned via course admins relationship)
     *
     * @param  User  $user  The user to check authorization for
     * @param  Course  $course  The course to check management access for
     * @return bool True if user can manage the course, false otherwise
     */
    protected function userCanManageCourse(User $user, Course $course): bool
    {
        // Superadmin can manage all courses
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can manage all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Check if user is an Instructor and is the course instructor or course admin
        if ($user->hasRole('Instructor')) {
            // Check if user is the course instructor
            if ((int) $course->instructor_id === (int) $user->id) {
                return true;
            }

            // Check if user is a course admin
            if (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can modify an enrollment.
     *
     * A user can modify an enrollment if they are:
     * - A Superadmin (can modify all enrollments)
     * - The owner of the enrollment (user_id matches)
     *
     * @param  User  $user  The user to check authorization for
     * @param  Enrollment  $enrollment  The enrollment to check modification access for
     * @return bool True if user can modify the enrollment, false otherwise
     */
    protected function canModifyEnrollment(User $user, Enrollment $enrollment): bool
    {
        // Superadmin can modify all enrollments
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // User can modify their own enrollment
        return (int) $enrollment->user_id === (int) $user->id;
    }

    /**
     * Check if user is a system administrator (Admin or Superadmin).
     *
     * @param  User  $user  The user to check
     * @return bool True if user is Admin or Superadmin, false otherwise
     */
    protected function isSystemAdmin(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Superadmin']);
    }

    /**
     * Check if user is the instructor of the course.
     *
     * @param  User  $user  The user to check
     * @param  Course  $course  The course to check instructor status for
     * @return bool True if user is the course instructor, false otherwise
     */
    protected function isInstructorOfCourse(User $user, Course $course): bool
    {
        return $course->hasInstructor($user);
    }

    /**
     * Check if user is a course admin.
     *
     * @param  User  $user  The user to check
     * @param  Course  $course  The course to check admin status for
     * @return bool True if user is a course admin, false otherwise
     */
    protected function isCourseAdmin(User $user, Course $course): bool
    {
        if (method_exists($course, 'hasAdmin')) {
            return $course->hasAdmin($user);
        }

        return false;
    }
}
