<?php

declare(strict_types=1);

namespace Modules\Schemes\Policies;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Tag;

class TagPolicy
{
    
    public function viewAny(?User $user): bool
    {
        return true;
    }

    
    public function view(?User $user, Tag $tag): bool
    {
        return true;
    }

    
    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin');
    }

    
    public function update(User $user, Tag $tag): bool
    {
        return $user->hasRole('Superadmin');
    }

    
    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasRole('Superadmin');
    }
}
