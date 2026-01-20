<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts\Services;

use Modules\Auth\DTOs\LoginDTO;
use Modules\Auth\Models\User;
use Tymon\JWTAuth\JWTAuth;

interface AuthenticationServiceInterface
{
    public function login(
        LoginDTO|string $loginOrDto,
        ?string $password,
        string $ip,
        ?string $userAgent
    ): array;

    public function generateTokens(User $user, string $ip, ?string $userAgent, bool $wasAutoVerified = false): array;

    public function logout(User $user, string $currentJwt, ?string $refreshToken = null): void;

    public function refresh(string $refreshToken, string $ip, ?string $userAgent): array;

    public function getJwt(): JWTAuth;
}
