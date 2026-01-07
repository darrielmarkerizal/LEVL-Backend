<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts;

use Modules\Auth\Models\User;

interface UserAccessPolicyInterface
{
    public function canAdminViewUser(User $admin, User $target): bool;
}
