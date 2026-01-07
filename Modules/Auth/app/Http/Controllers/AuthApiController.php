<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Contracts\Services\AuthServiceInterface;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\LogoutRequest;
use Modules\Auth\Http\Requests\RefreshTokenRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\SetUsernameRequest;
use Modules\Auth\Http\Requests\UpdateProfileRequest;
use Modules\Auth\Http\Requests\UpdateUserStatusRequest;
use Modules\Auth\Http\Requests\CreateUserRequest;
use Modules\Auth\Http\Requests\VerifyEmailByTokenRequest;
use Modules\Auth\Http\Resources\LoginResource;
use Modules\Auth\Http\Resources\RegisterResource;
use Modules\Auth\Http\Resources\UserResource;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuthenticationService;

class AuthApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuthServiceInterface $authService,
        private AuthenticationService $authenticationService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success(new RegisterResource($result), __('messages.auth.register_success'));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('login'),
            $request->input('password'),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success(new LoginResource($result), $result['message'] ?? __('messages.auth.login_success'));
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authenticationService->logout(
            $user,
            $request->bearerToken(),
            $request->input('refresh_token')
        );

        return $this->success([], __('messages.auth.logout_success'));
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $result = $this->authenticationService->refresh(
            $request->input('refresh_token'),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success($result, __('messages.auth.refresh_success'));
    }

    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return $this->success(new UserResource($user), __('messages.auth.profile_retrieved'));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updatedUser = $this->authService->updateProfile($user, $request->validated());

        return $this->success(new UserResource($updatedUser), __('messages.auth.profile_updated'));
    }

    public function updateUserStatus(UpdateUserStatusRequest $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $updatedUser = $this->authService->updateUserStatus($user, $request->input('status'));

        return $this->success(new UserResource($updatedUser), __('messages.auth.status_updated'));
    }

    public function createUser(CreateUserRequest $request): JsonResponse
    {
        $role = $request->input('role');
        
        $method = 'create' . $role;
        if (!method_exists($this->authService, $method)) {
            return $this->error('Invalid role specified.', [], 422);
        }

        $user = $this->authService->$method($request->validated());

        return $this->success(new UserResource($user), __('messages.auth.user_created_success'));
    }

    public function verifyEmail(VerifyEmailByTokenRequest $request): JsonResponse
    {
        $result = $this->authService->verifyEmail($request->input('token'), $request->input('uuid'));

        if ($result['status'] !== 'ok') {
            return $this->error(__('messages.auth.verification_' . $result['status']), [], 422);
        }

        return $this->success([], __('messages.auth.email_verified'));
    }

    public function sendEmailVerification(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $uuid = $this->authService->sendEmailVerificationLink($user);

        if (!$uuid) {
            return $this->error(__('messages.auth.email_already_verified'), [], 422);
        }

        return $this->success(['uuid' => $uuid], __('messages.auth.verification_link_sent'));
    }

    public function listUsers(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $users = $this->authService->listUsers($user);

        return $this->success($users, __('messages.auth.users_retrieved'));
    }

    public function showUser(int $userId): JsonResponse
    {
        /** @var User $authUser */
        $authUser = auth()->user();
        $target = User::findOrFail($userId);

        $user = $this->authService->showUser($authUser, $target);

        return $this->success(new UserResource($user), __('messages.auth.user_retrieved'));
    }

    public function googleRedirect(): JsonResponse
    {
        try {
            $redirectUrl = Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return $this->success(['redirect_url' => $redirectUrl], 'Google OAuth redirect URL generated.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), [], 500);
        }
    }

    public function googleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = $this->authService->createUserFromGoogle($googleUser);
            }

            $tokens = $this->authenticationService->generateTokens($user, request()->ip(), request()->userAgent());

            return $this->success(new LoginResource($tokens), 'Google login successful.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), [], 500);
        }
    }

    public function setUsername(SetUsernameRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->authService->setUsername($user, $request->input('username'));

        return $this->success(new UserResource($result['user']), __('messages.auth.username_set_success'));
    }

    public function generateDevTokens(): JsonResponse
    {
        $tokens = $this->authService->generateDevTokens(request()->ip(), request()->userAgent());

        return $this->success($tokens, 'Dev tokens generated successfully for all roles.');
    }
}
