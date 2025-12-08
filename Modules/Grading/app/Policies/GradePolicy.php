<?php

namespace Modules\Grading\Policies;

use Modules\Auth\Models\User;
use Modules\Grading\Models\Grade;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Grade $grade): bool
    {
        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        if ($grade->submission?->user_id === $user->id) {
            return true;
        }

        return $user->hasRole('Instructor') && $grade->graded_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Instructor');
    }

    public function update(User $user, Grade $grade): bool
    {
        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $grade->graded_by === $user->id;
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->hasRole('Superadmin');
    }
}
