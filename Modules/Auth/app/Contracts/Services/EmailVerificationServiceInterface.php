<?php

declare(strict_types=1);


namespace Modules\Auth\Contracts\Services;

use Modules\Auth\Models\User;

interface EmailVerificationServiceInterface
{
    public function sendVerificationLink(User $user): ?string;

    public function verifyByCode(string $uuidOrToken, string $code): array;

    public function verifyByToken(string $token, string $uuid): array;

    public function sendChangeEmailLink(User $user, string $newEmail): ?string;

    public function verifyChangeByToken(string $token, string $uuid): array;
}
