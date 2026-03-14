<?php

declare(strict_types=1);

namespace Modules\Gamification\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;
use Modules\Gamification\Contracts\Repositories\GamificationEventLogRepositoryInterface;
use Modules\Gamification\Models\GamificationEventLog;

class GamificationEventLogRepository extends BaseRepository implements GamificationEventLogRepositoryInterface
{
    protected function model(): string
    {
        return GamificationEventLog::class;
    }

    public function create(array $data): GamificationEventLog
    {
        return GamificationEventLog::create($data);
    }

    public function getUserEventHistory(int $userId, string $eventType, int $limit = 100): Collection
    {
        return GamificationEventLog::where('user_id', $userId)
            ->where('event_type', $eventType)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentEvents(int $userId, int $days = 7): Collection
    {
        return GamificationEventLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function deleteOlderThan(int $days): int
    {
        $cutoff = now()->subDays($days);

        return GamificationEventLog::where('created_at', '<', $cutoff)->delete();
    }
}
