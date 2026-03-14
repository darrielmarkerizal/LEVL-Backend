<?php

declare(strict_types=1);

namespace Modules\Schemes\Policies;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;

class CoursePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Course $course): bool
    {
        if ($course->status === 'published') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        return $course->instructor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Instructor');
    }

    public function update(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can update all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can only update their own courses
        return $user->hasRole('Instructor') && $course->instructor_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can delete all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can only delete their own courses
        return $user->hasRole('Instructor') && $course->instructor_id === $user->id;
    }

    public function publish(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        // Allow instructor to publish their own course
        return $user->hasRole('Instructor') && $course->instructor_id === $user->id;
    }

    public function viewAssignments(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can view all course assignments
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can view their course assignments
        if ($user->hasRole('Instructor')) {
            return $course->instructor_id === $user->id;
        }

        // Student can view if enrolled
        if ($user->hasRole('Student')) {
            return \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereIn('status', ['active', 'completed'])
                ->exists();
        }

        return false;
    }

    public function manageContent(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    public function viewUnits(?User $user, Course $course): bool
    {
        return $this->view($user, $course);
    }
}
