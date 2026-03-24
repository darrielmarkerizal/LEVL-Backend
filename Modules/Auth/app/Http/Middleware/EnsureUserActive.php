<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Enums\UserStatus;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure only active users can access protected routes.
 *
 * This middleware checks if the authenticated user has an Active status.
 * Users with Pending, Inactive, or Banned status will be blocked with appropriate error messages.
 *
 * Usage:
 * Route::middleware(['auth:api', 'ensure.user.active'])->group(function() {
 *     // Protected routes
 * });
 */
class EnsureUserActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Modules\Auth\Models\User|null $user */
        $user = auth('api')->user();

        // If no user is authenticated, let the auth middleware handle it
        if (! $user) {
            return $next($request);
        }

        // Allow pending users to access email verification endpoints
        if ($user->status === UserStatus::Pending) {
            $allowedPendingRoutes = [
                'api/v1/auth/email/verify/send',
                'api/v1/auth/email/verify',
                'api/v1/auth/logout',
                'api/v1/profile', // Allow GET profile to check status
            ];

            foreach ($allowedPendingRoutes as $route) {
                if ($request->is($route)) {
                    return $next($request);
                }
            }
        }

        // Check if user status is Active
        if ($user->status !== UserStatus::Active) {
            $message = match ($user->status) {
                UserStatus::Pending => __('messages.auth.email_not_verified'),
                UserStatus::Inactive => __('messages.auth.account_not_active_contact_admin'),
                UserStatus::Banned => __('messages.auth.account_banned_contact_admin'),
            };

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'user_status' => $user->status->value,
            ], 403);
        }

        return $next($request);
    }
}
