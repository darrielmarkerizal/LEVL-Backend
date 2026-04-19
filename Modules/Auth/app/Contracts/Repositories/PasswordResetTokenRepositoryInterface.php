<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts\Repositories;

use Illuminate\Support\Collection;
use Modules\Auth\Models\PasswordResetToken;

interface PasswordResetTokenRepositoryInterface
{
    
    public function create(array $data): PasswordResetToken;

    
    public function findByEmail(string $email): Collection;

    
    public function deleteByEmail(string $email): int;

    
    public function findValidTokens(int $ttlMinutes, int $limit = 100): Collection;

    
    public function findAll(): Collection;

    
    public function delete(PasswordResetToken $token): bool;
}
