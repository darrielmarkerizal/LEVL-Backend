<?php

declare(strict_types=1);

namespace Modules\Auth\Services\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Auth\DTOs\RegisterDTO;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;
use Modules\Auth\Services\EmailVerificationService;
use Modules\Auth\Support\TokenPairDTO;
use Tymon\JWTAuth\JWTAuth;

class AuthRegistrationProcessor
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
        private readonly JWTAuth $jwt,
        private readonly EmailVerificationService $emailVerification
    ) {}

    public function register(RegisterDTO|array $data, string $ip, ?string $userAgent): array
    {
        return DB::transaction(function () use ($data, $ip, $userAgent) {
            $validated = $data instanceof RegisterDTO ? $data->toArray() : $data;
            $validated['password'] = Hash::make($validated['password']);
            $user = $this->authRepository->createUser($validated);

            $user->assignRole(config('auth.default_role', 'Student'));

            $token = $this->jwt->fromUser($user);

            $deviceId = hash('sha256', ($ip ?? '').($userAgent ?? '').$user->id);
            $refresh = $this->authRepository->createRefreshToken(
                userId: $user->id,
                ip: $ip,
                userAgent: $userAgent,
                deviceId: $deviceId,
            );

            $pair = new TokenPairDTO(
                accessToken: $token,
                expiresIn: $this->jwt->factory()->getTTL() * 60,
                refreshToken: $refresh->getAttribute('plain_token'),
            );

            $verificationUuid = $this->emailVerification->sendVerificationLink($user);

            $userArray = $user->toArray();
            $userArray['roles'] = $user->getRoleNames()->values();
            $userArray['avatar_url'] = $user->avatar_url;

            $response = ['user' => $userArray] + $pair->toArray();

            if ($verificationUuid) {
                $response['verification_uuid'] = $verificationUuid;
            }

            return $response;
        });
    }

    public function createUserFromGoogle($googleUser): User
    {
        $user = $this->authRepository->createUser([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'username' => null,
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
            'status' => UserStatus::Active,
        ]);

        $user->assignRole(config('auth.default_role', 'Student'));

        return $user;
    }

    public function generateDevTokens(string $ip, ?string $userAgent, ?int $userId = null): array
    {
        if ($userId) {
            $user = User::find($userId);
            if (! $user) {
                return ['error' => 'User not found'];
            }
            return ['specific_user' => $this->formatUserToken($user, $ip, $userAgent)];
        }

        $roles = ['Student', 'Instructor', 'Admin', 'Superadmin'];
        $tokens = [];

        foreach ($roles as $role) {
            $user = User::where('email', strtolower($role).'@example.com')->first();

            if (! $user) {
                $user = $this->authRepository->createUser([
                    'name' => $role,
                    'email' => strtolower($role).'@example.com',
                    'username' => strtolower($role),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => UserStatus::Active,
                ]);

                $user->assignRole($role);
            }

            $tokens[$role] = $this->formatUserToken($user, $ip, $userAgent, $role);
        }

        return $tokens;
    }

    private function formatUserToken(User $user, string $ip, ?string $userAgent, ?string $role = null): array
    {
        $originalTTL = $this->jwt->factory()->getTTL();
        $this->jwt->factory()->setTTL(525600);

        $token = $this->jwt->fromUser($user);
        $deviceId = hash('sha256', ($ip ?? '').($userAgent ?? '').$user->id);
        $refresh = $this->authRepository->createRefreshToken(
            userId: $user->id,
            ip: $ip,
            userAgent: $userAgent,
            deviceId: $deviceId,
        );

        $this->jwt->factory()->setTTL($originalTTL);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'role' => $role ?? $user->getRoleNames()->first(),
            ],
            'access_token' => $token,
            'refresh_token' => $refresh->getAttribute('plain_token'),
            'expires_in' => 525600 * 60,
        ];
    }
}
