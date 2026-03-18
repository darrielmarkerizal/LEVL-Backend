<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Illuminate\Support\Collection;
use Modules\Gamification\Contracts\Repositories\GamificationEventLogRepositoryInterface;
use Modules\Gamification\Models\GamificationEventLog;

class EventLoggerService
{
    /**
     * Important events that should always be logged
     * This prevents event log table from growing to 86M rows/day
     */
    private const IMPORTANT_EVENTS = [
        'badge_awarded',
        'level_up',
        'course_completed',
        'milestone_reached',
        'streak_milestone',
        'leaderboard_rank_change',
    ];

    public function __construct(
        private readonly GamificationEventLogRepositoryInterface $repository
    ) {}

    public function log(
        int $userId,
        string $eventType,
        ?string $sourceType = null,
        ?int $sourceId = null,
        array $payload = []
    ): ?GamificationEventLog {
        // Selective logging: only log important events
        if (! $this->shouldLog($eventType)) {
            return null;
        }

        // Limit payload size to prevent bloat
        $limitedPayload = $this->limitPayload($payload);

        return $this->repository->create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'payload' => $limitedPayload,
            'created_at' => now(),
        ]);
    }

    /**
     * Determine if event should be logged
     * Only important events are logged to prevent table bloat
     */
    private function shouldLog(string $eventType): bool
    {
        return in_array($eventType, self::IMPORTANT_EVENTS, true);
    }

    private function limitPayload(array $payload): array
    {
        // Only keep essential fields (max 10 fields)
        $allowedFields = [
            'id',
            'score',
            'attempt',
            'duration',
            'is_weekend',
            'time',
            'course_id',
            'unit_id',
            'lesson_id',
            'assignment_id',
        ];

        return array_intersect_key($payload, array_flip($allowedFields));
    }

    public function getUserEventHistory(
        int $userId,
        string $eventType,
        int $limit = 100
    ): Collection {
        return $this->repository->getUserEventHistory($userId, $eventType, $limit);
    }

    public function getRecentEvents(int $userId, int $days = 7): Collection
    {
        return $this->repository->getRecentEvents($userId, $days);
    }

    public function cleanupOldLogs(int $days = 90): int
    {
        return $this->repository->deleteOlderThan($days);
    }
}
