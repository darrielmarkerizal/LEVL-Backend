<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Gamification\Events\UserLoggedIn;

class TrackDailyLogin
{
    /**
     * Handle an incoming request and track daily login
     */
    public function handle(Request $request, Closure $next)
    {
        // Only track for authenticated users
        if (auth()->check()) {
            $userId = auth()->id();

            // Dispatch UserLoggedIn event
            // The listener will handle checking if already logged in today
            event(new UserLoggedIn($userId));
        }

        return $next($request);
    }
}
