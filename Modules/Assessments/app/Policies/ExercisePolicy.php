<?php

namespace Modules\Assessments\Policies;

use Modules\Auth\Models\User;
use Modules\Assessments\Models\Exercise;

class ExercisePolicy
{
    /**
     * Determine if the user can create exercises
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Admin', 'Instructor', 'Superadmin']);
    }

    /**
     * Determine if the user can view the exercise
     */
    public function view(User $user, Exercise $exercise): bool
    {
        // User is the creator
        if ($exercise->created_by === $user->id) {
            return true;
        }

        // Superadmin can view all
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Admin can view if they manage the course
        if ($user->hasRole('Admin') && $exercise->scope_type === 'course') {
            return $user->managedCourses()->where('courses.id', $exercise->scope_id)->exists();
        }

        return false;
    }

    /**
     * Determine if the user can update the exercise
     */
    public function update(User $user, Exercise $exercise): bool
    {
        // Can only update draft exercises
        if ($exercise->status !== 'draft') {
            return false;
        }

        // Must be creator or superadmin
        return $exercise->created_by === $user->id || $user->hasRole('Superadmin');
    }

    /**
     * Determine if the user can delete the exercise
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        // Can only delete draft exercises
        if ($exercise->status !== 'draft') {
            return false;
        }

        // Must be creator or superadmin
        return $exercise->created_by === $user->id || $user->hasRole('Superadmin');
    }
}
