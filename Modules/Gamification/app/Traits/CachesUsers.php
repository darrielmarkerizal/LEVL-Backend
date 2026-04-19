<?php

declare(strict_types=1);

namespace Modules\Gamification\Traits;

use Illuminate\Support\Facades\Cache;
use Modules\Auth\Models\User;


trait CachesUsers
{
    
    protected function getCachedUser(int $userId): ?User
    {
        return Cache::remember(
            "user.{$userId}.basic",
            now()->addMinutes(5),
            fn () => User::select('id', 'name', 'email')->find($userId)
        );
    }

    
    protected function clearUserCache(int $userId): void
    {
        Cache::forget("user.{$userId}.basic");
    }
}
