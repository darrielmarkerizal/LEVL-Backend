<?php

declare(strict_types=1);


namespace Modules\Auth\Contracts\Services;

use Modules\Auth\Models\User;

interface AuthServiceInterface
{
    /** @return array<string,mixed> */
    public function register(array $validated, string $ip, ?string $userAgent): array;

    /** @return array<string,mixed> */
    public function login(string $login, string $password, string $ip, ?string $userAgent): array;

    /** @return array<string,mixed> */
    public function refresh(string $refreshToken, string $ip, ?string $userAgent): array;

    public function logout(User $user, string $currentJwt, ?string $refreshToken = null): void;

    public function me(User $user): User;

    /**
     * Log a profile update action to the audit trail.
     *
     * @param  array<string,array{0:mixed,1:mixed}>  $changes  Array of changed fields with [old, new] values
     */
    public function logProfileUpdate(User $user, array $changes, ?string $ip, ?string $userAgent): void;

    /**
     * Log an email change request action to the audit trail.
     */
    public function logEmailChangeRequest(User $user, string $newEmail, string $uuid, ?string $ip, ?string $userAgent): void;

    public function createUserFromGoogle($googleUser): User;

    /** @return array<string,mixed> */
    public function generateDevTokens(string $ip, ?string $userAgent): array;

    /** @return array<string,mixed> */
    public function setUsername(User $user, string $username): array;

    /** @return array<string,mixed> */
    public function verifyEmail(string $token, string $uuid): array;

    public function sendEmailVerificationLink(User $user): ?string;
    
    /** @return array<string,mixed> */
    public function setPassword(User $user, string $password): array;
}
