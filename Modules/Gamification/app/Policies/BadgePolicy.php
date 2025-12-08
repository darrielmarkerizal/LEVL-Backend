<?php

namespace Modules\Gamification\Policies;

use Modules\Auth\Models\User;
use Modules\Gamification\Models\Badge;

class BadgePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Badge $badge): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin');
    }

    public function update(User $user, Badge $badge): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin');
    }

    public function delete(User $user, Badge $badge): bool
    {
        return $user->hasRole('Superadmin');
    }
}
