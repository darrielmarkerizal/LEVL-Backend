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
        // Published courses are accessible to everyone
        if ($course->status === 'published') {
            return true;
        }

        // Draft/archived courses require authentication
        if (! $user) {
            return false;
        }

        // Students cannot access draft/archived courses
        if ($user->hasRole('Student')) {
            return false;
        }

        // Admin and Superadmin can view all courses (published and draft)
        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        // Instructors can only view courses they are assigned to
        if ($user->hasRole('Instructor')) {
            return $course->instructors()->where('user_id', $user->id)->exists();
        }

        return false;
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

        // Instructor can only update courses they are assigned to
        if ($user->hasRole('Instructor')) {
            return $course->instructors()->where('user_id', $user->id)->exists();
        }

        return false;
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

        // Instructor can only delete courses they are assigned to
        if ($user->hasRole('Instructor')) {
            return $course->instructors()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function publish(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        // Allow instructor to publish courses they are assigned to
        if ($user->hasRole('Instructor')) {
            return $course->instructors()->where('user_id', $user->id)->exists();
        }

        return false;
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

        // Instructor can view assignments for courses they are assigned to
        if ($user->hasRole('Instructor')) {
            return $course->instructors()->where('user_id', $user->id)->exists();
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
