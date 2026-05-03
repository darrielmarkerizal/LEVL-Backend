<?php

declare(strict_types=1);

namespace Modules\Auth\Policies;

use Modules\Auth\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole(['Superadmin', 'Admin']);
    }

    public function view(User $authUser, User $targetUser): bool
    {
        if ($authUser->hasRole('Superadmin')) {
            return true;
        }

        if (! $authUser->hasRole('Admin')) {
            return false;
        }

        if ($targetUser->hasRole('Superadmin')) {
            return false;
        }

        return true;
    }

    public function create(User $authUser): bool
    {
        
        
        return $authUser->hasRole(['Superadmin', 'Admin']);
    }

    public function update(User $authUser, User $targetUser): bool
    {
        return $this->view($authUser, $targetUser);
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        
        if ($authUser->hasRole('Superadmin')) {
            return true;
        }

        
        if ($authUser->hasRole('Admin')) {
            return ! $targetUser->hasRole('Superadmin');
        }

        return false;
    }

    public function updateStatus(User $authUser, User $targetUser): bool
    {
        return $this->view($authUser, $targetUser);
    }

    public function resetPassword(User $authUser, User $targetUser): bool
    {
        
        if ($authUser->hasRole('Superadmin')) {
            return true;
        }

        
        if ($authUser->hasRole('Admin')) {
            if ($targetUser->hasRole(['Admin', 'Superadmin'])) {
                return false;
            }

            
            return $this->view($authUser, $targetUser);
        }

        
        return false;
    }
}
