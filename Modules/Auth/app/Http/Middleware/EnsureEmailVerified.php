<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \Modules\Auth\Models\User|null $user */
        $user = auth('api')->user();

        if ($user && $user->email_verified_at) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.auth.email_not_verified'),
            ], 403);
        }

        return redirect()->away(rtrim(config('app.url'), '/'));
    }
}


