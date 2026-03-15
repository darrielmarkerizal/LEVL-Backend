<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict soft-deleted users to only access restore endpoint
 */
class RestrictDeletedUserAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated or not soft-deleted, allow access
        if (! $user || ! $user->trashed()) {
            return $next($request);
        }

        // Allow access to restore endpoint
        if ($request->is('api/v1/profile/account/restore')) {
            return $next($request);
        }

        // Allow access to logout endpoint
        if ($request->is('api/v1/auth/logout')) {
            return $next($request);
        }

        // Allow access to profile endpoint to check status
        if ($request->is('api/v1/profile') && $request->isMethod('GET')) {
            return $next($request);
        }

        // Block all other endpoints
        return response()->json([
            'success' => false,
            'message' => __('messages.account.deleted_restricted_access'),
            'data' => [
                'account_status' => 'deleted',
                'deleted_at' => $user->deleted_at,
                'restore_deadline' => $user->deleted_at->copy()->addDays(30),
                'days_remaining' => max(0, 30 - $user->deleted_at->diffInDays(now())),
            ],
            'meta' => null,
            'errors' => null,
        ], 403);
    }
}
