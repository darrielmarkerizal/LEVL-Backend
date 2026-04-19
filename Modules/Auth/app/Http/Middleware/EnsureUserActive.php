<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Enums\UserStatus;
use Symfony\Component\HttpFoundation\Response;


class EnsureUserActive
{
    
    public function handle(Request $request, Closure $next): Response
    {
        
        $user = auth('api')->user();

        
        if (! $user) {
            return $next($request);
        }

        
        if ($user->status === UserStatus::Pending) {
            $allowedPendingRoutes = [
                'api/v1/auth/email/verify/send',
                'api/v1/auth/email/verify',
                'api/v1/auth/logout',
                'api/v1/profile', 
            ];

            foreach ($allowedPendingRoutes as $route) {
                if ($request->is($route)) {
                    return $next($request);
                }
            }
        }

        
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
