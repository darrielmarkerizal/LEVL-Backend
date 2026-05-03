<?php

declare(strict_types=1);

namespace Modules\Auth\Observers;

use Modules\Auth\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        // Privacy settings removed
    }
}
