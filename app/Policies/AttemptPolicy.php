<?php

namespace App\Policies;

use Modules\Auth\Models\User;
use Modules\Assessments\Models\Attempt;

class AttemptPolicy
{
    /**
     * Determine if user can view attempt
     */
    public function view(User $user, Attempt $attempt): bool
    {
        // User can view their own attempt
        if ($user->id === $attempt->user_id) {
            return true;
        }

        // Instructor/Admin can view attempts for their exercises
        if ($user->hasAnyRole(['Admin', 'Instructor', 'Superadmin'])) {
            $exercise = $attempt->exercise;

            // Creator of exercise can view
            if ($user->id === $exercise->created_by) {
                return true;
            }

            // Superadmin can view all
            if ($user->hasRole('Superadmin')) {
                return true;
            }

            // Admin managing courses can view
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
        }

        return false;
    }
}
