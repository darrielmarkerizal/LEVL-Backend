<?php

declare(strict_types=1);

namespace Modules\Gamification\Traits;

use Illuminate\Support\Facades\Cache;
use Modules\Auth\Models\User;

/**
 * Trait for caching user lookups to reduce database queries
 * FIX: Prevents repeated User::find() calls in listeners
 */
trait CachesUsers
{
    /**
     * Get user from cache or database
     * Cache for 5 minutes to reduce queries
     */
    protected function getCachedUser(int $userId): ?User
    {
        return Cache::remember(
            "user.{$userId}.basic",
            now()->addMinutes(5),
            fn() => User::select('id', 'name', 'email')->find($userId)
        );
    }

    /**
     * Clear user cache (call when user data changes)
     */
    protected function clearUserCache(int $userId): void
    {
        Cache::forget("user.{$userId}.basic");
    }
}
