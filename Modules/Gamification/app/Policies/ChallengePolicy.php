<?php

namespace Modules\Gamification\Policies;

use Modules\Auth\Models\User;
use Modules\Gamification\Models\Challenge;

class ChallengePolicy
{
    /**
     * Determine whether the user can view any challenges.
     */
    public function viewAny(?User $user): bool
    {
        return true; // All users (including guests) can view challenges
    }

    /**
     * Determine whether the user can view the challenge.
     */
    public function view(?User $user, Challenge $challenge): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create challenges.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can update the challenge.
     */
    public function update(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can delete the challenge.
     */
    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('Superadmin');
    }

    /**
     * Determine whether the user can claim the challenge reward.
     */
    public function claim(User $user, Challenge $challenge): bool
    {
        return true; // Authenticated users can claim their completed challenges
    }
}
