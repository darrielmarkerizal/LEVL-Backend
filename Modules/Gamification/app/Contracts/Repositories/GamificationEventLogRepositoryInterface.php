<?php

declare(strict_types=1);

namespace Modules\Gamification\Contracts\Repositories;

use Illuminate\Support\Collection;
use Modules\Gamification\Models\GamificationEventLog;

interface GamificationEventLogRepositoryInterface
{
    public function create(array $data): GamificationEventLog;

    public function getUserEventHistory(int $userId, string $eventType, int $limit = 100): Collection;

    public function getRecentEvents(int $userId, int $days = 7): Collection;

    public function deleteOlderThan(int $days): int;
}
