<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Auth\Enums\UserStatus;
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

        $authRepository = app(AuthRepositoryInterface::class);
        $refreshRecord = $authRepository->findValidRefreshRecord($refreshToken);

        if (! $refreshRecord) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => __('messages.auth.refresh_token_invalid'),
                ],
                401,
            );
        }

        $user = $refreshRecord->user;
        if (! $user) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => __('messages.user.not_found'),
                ],
                401,
            );
        }

        if ($user->status !== UserStatus::Active) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => __('messages.auth.account_not_active'),
                ],
                403,
            );
        }

        auth('api')->setUser($user);
        $request->merge(['refresh_token' => $refreshToken]);

        return $next($request);
    }
}
