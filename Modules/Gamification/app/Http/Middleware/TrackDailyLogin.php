<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Gamification\Events\UserLoggedIn;

class TrackDailyLogin
{
    
    public function handle(Request $request, Closure $next)
    {
        
        if (auth()->check()) {
            $userId = auth()->id();

            
            
            event(new UserLoggedIn($userId));
        }

        return $next($request);
    }
}
