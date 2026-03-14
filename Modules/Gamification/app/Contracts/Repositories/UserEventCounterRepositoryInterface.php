<?php

declare(strict_types=1);

namespace Modules\Gamification\Contracts\Repositories;

use Illuminate\Support\Collection;
use Modules\Gamification\Models\UserEventCounter;

interface UserEventCounterRepositoryInterface
{
    public function findOrCreate(
        int $userId,
        string $eventType,
        ?string $scopeType,
        ?int $scopeId,
        string $window,
        ?\Carbon\Carbon $windowStart
    ): UserEventCounter;

    public function findByUserAndEvent(
        int $userId,
        string $eventType,
        ?string $scopeType,
        ?int $scopeId,
        string $window,
        ?\Carbon\Carbon $windowStart
    ): ?UserEventCounter;

    public function getUserCounters(int $userId, ?string $eventType = null): Collection;

    public function deleteExpired(): int;
}
