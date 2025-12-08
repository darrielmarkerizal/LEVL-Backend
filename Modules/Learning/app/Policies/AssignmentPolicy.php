<?php

namespace Modules\Learning\Policies;

use Modules\Auth\Models\User;
use Modules\Learning\Models\Assignment;

class AssignmentPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Assignment $assignment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Instructor');
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $assignment->lesson?->unit?->course?->instructor_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return $user->hasRole('Admin') || ($user->hasRole('Instructor') && $assignment->lesson?->unit?->course?->instructor_id === $user->id);
    }

    public function publish(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}
