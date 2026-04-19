<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts\Services;

use Modules\Auth\Models\User;

interface AuthServiceInterface
{
    
    public function register(array $validated, string $ip, ?string $userAgent): array;

    
    public function login(string $login, string $password, string $ip, ?string $userAgent): array;

    
    public function refresh(string $refreshToken, string $ip, ?string $userAgent): array;

    public function logout(User $user, string $currentJwt, ?string $refreshToken = null): void;

    public function me(User $user): User;

    
    public function logProfileUpdate(User $user, array $changes, ?string $ip, ?string $userAgent): void;

    
    public function logEmailChangeRequest(User $user, string $newEmail, string $uuid, ?string $ip, ?string $userAgent): void;

    public function createUserFromGoogle($googleUser): User;

    
    public function generateDevTokens(string $ip, ?string $userAgent, ?int $userId = null): array;

    
    public function setUsername(User $user, string $username): array;

    
    public function verifyEmail(string $token, string $uuid): array;

    public function sendEmailVerificationLink(User $user): ?string;

    
    public function setPassword(User $user, string $password): array;
}
