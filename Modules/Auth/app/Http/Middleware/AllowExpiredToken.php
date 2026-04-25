<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowExpiredToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/v1/auth/refresh') && ! $request->routeIs('auth.refresh')) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => __('messages.auth.middleware_refresh_only'),
                ],
                403,
            );
        }

        $refreshToken =
          $request->cookie('refresh_token') ??
          ($request->header('X-Refresh-Token') ?? $request->input('refresh_token'));

        if (empty($refreshToken)) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => __('messages.auth.refresh_token_required'),
                ],
                400,
            );
        }

        $request->merge(['refresh_token' => $refreshToken]);

        return $next($request);
    }
}
