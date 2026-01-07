<?php

declare(strict_types=1);

namespace Modules\Auth\Policies;

use Modules\Auth\Contracts\UserAccessPolicyInterface;
use Modules\Auth\Models\User;

class UserAccessPolicy implements UserAccessPolicyInterface
{
    public function canAdminViewUser(User $admin, User $target): bool
    {
        if ($admin->hasRole('Superadmin')) {
            return true;
        }

        return $admin->hasRole('Admin') || $admin->hasRole('Instructor');
    }
}
