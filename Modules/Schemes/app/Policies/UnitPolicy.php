<?php

declare(strict_types=1);

namespace Modules\Schemes\Policies;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Unit;

class UnitPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Unit $unit): bool
    {
        $course = $unit->course;
        if (! $course) {
            return false;
        }

        if ($unit->status === 'published' && $course->status === 'published') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can view all units
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can view units in their assigned courses
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

    public function create(User $user, \Modules\Schemes\Models\Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can create units in all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can create units in their assigned courses
        return $user->hasRole('Instructor') && $course->instructors()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Unit $unit): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $unit->course;
        if (! $course) {
            return false;
        }

        // Admin can update all units
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can update units in their assigned courses
        return $user->hasRole('Instructor') && $course->instructors()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Unit $unit): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $unit->course;
        if (! $course) {
            return false;
        }

        // Admin can delete all units
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can delete units in their assigned courses
        return $user->hasRole('Instructor') && $course->instructors()->where('user_id', $user->id)->exists();
    }

    public function reorder(User $user, Unit $unit): bool
    {
        return $this->update($user, $unit);
    }
}
