<?php

namespace Modules\Assessments\Policies;

use Modules\Auth\Models\User;
use Modules\Assessments\Models\Attempt;

class AttemptPolicy
{
    /**
     * Determine if the user can view the attempt
     */
    public function view(User $user, Attempt $attempt): bool
    {
        // User can view their own attempts
        if ($attempt->user_id === $user->id) {
            return true;
        }

        // Instructor/Admin can view attempts for their exercises
        if ($user->hasRole(['Instructor', 'Admin', 'Superadmin'])) {
            $exercise = $attempt->exercise;

            // Superadmin can view all
            if ($user->hasRole('Superadmin')) {
                return true;
            }

            // Creator of exercise can view
            if ($exercise->created_by === $user->id) {
                return true;
            }

            // Admin managing the course can view
            if ($user->hasRole('Admin') && $exercise->scope_type === 'course') {
                return $user->managedCourses()->where('courses.id', $exercise->scope_id)->exists();
            }
        }

        return false;
    }
}
