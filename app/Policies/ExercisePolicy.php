<?php

namespace App\Policies;

use Modules\Auth\Models\User;
use Modules\Assessments\Models\Exercise;

class ExercisePolicy
{
    /**
     * Determine if user can create exercise
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Instructor', 'Superadmin']);
    }

    /**
     * Determine if user can view exercise
     */
    public function view(User $user, Exercise $exercise): bool
    {
        // Creator can always view
        if ($user->id === $exercise->created_by) {
            return true;
        }

        // Superadmin can view all
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can view exercises in courses they manage
        if ($user->hasRole('Admin')) {
            return match ($exercise->scope_type) {
                'course' => $user->managedCourses()->where('id', $exercise->scope_id)->exists(),
                'unit' => $user->managedCourses()
                    ->whereHas('units', fn ($q) => $q->where('id', $exercise->scope_id))
                    ->exists(),
                'lesson' => $user->managedCourses()
                    ->whereHas('units.lessons', fn ($q) => $q->where('id', $exercise->scope_id))
                    ->exists(),
                default => false,
            };
        }

        // Instructors can view exercises they created or in their courses
        if ($user->hasRole('Instructor')) {
            return $user->id === $exercise->created_by;
        }

        return false;
    }

    /**
     * Determine if user can update exercise
     */
    public function update(User $user, Exercise $exercise): bool
    {
        // Creator can always update if not published
        if ($user->id === $exercise->created_by && $exercise->status === 'draft') {
            return true;
        }

        // Superadmin can update any draft exercise
        if ($user->hasRole('Superadmin') && $exercise->status === 'draft') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete exercise
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        // Can only delete draft exercises
        if ($exercise->status !== 'draft') {
            return false;
        }

        // Creator can delete
        if ($user->id === $exercise->created_by) {
            return true;
        }

        // Superadmin can delete
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return false;
    }
}
