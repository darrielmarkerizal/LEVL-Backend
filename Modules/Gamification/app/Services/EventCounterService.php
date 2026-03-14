<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Gamification\Contracts\Repositories\UserEventCounterRepositoryInterface;
use Modules\Gamification\Models\UserEventCounter;

class EventCounterService
{
    public function __construct(
        private readonly UserEventCounterRepositoryInterface $repository
    ) {}

    public function increment(
        int $userId,
        string $eventType,
        ?string $scopeType = null,
        ?int $scopeId = null,
        string $window = 'lifetime'
    ): UserEventCounter {
        $bounds = $this->getWindowBounds($window);

        // FIX: Use single UPSERT query instead of transaction with multiple queries
        DB::statement('
            INSERT INTO user_event_counters 
                (user_id, event_type, scope_type, scope_id, window, counter, 
                 window_start, window_end, last_increment_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 1, ?, ?, NOW(), NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                counter = IF(window_end IS NOT NULL AND window_end < NOW(), 1, counter + 1),
                window_start = IF(window_end IS NOT NULL AND window_end < NOW(), VALUES(window_start), window_start),
                window_end = IF(window_end IS NOT NULL AND window_end < NOW(), VALUES(window_end), window_end),
                last_increment_at = NOW(),
                updated_at = NOW()
        ', [
            $userId,
            $eventType,
            $scopeType,
            $scopeId,
            $window,
            $bounds['start'],
            $bounds['end']
        ]);

        // Return the updated counter
        return $this->repository->findByUserAndEvent(
            $userId,
            $eventType,
            $scopeType,
            $scopeId,
            $window,
            $bounds['start']
        ) ?? new UserEventCounter();
    }

    public function getCounter(
        int $userId,
        string $eventType,
        ?string $scopeType = null,
        ?int $scopeId = null,
        string $window = 'lifetime'
    ): int {
        $bounds = $this->getWindowBounds($window);

        $counter = $this->repository->findByUserAndEvent(
            $userId,
            $eventType,
            $scopeType,
            $scopeId,
            $window,
            $bounds['start']
        );

        return $counter?->counter ?? 0;
    }

    public function getUserCounters(int $userId, ?string $eventType = null): array
    {
        $counters = $this->repository->getUserCounters($userId, $eventType);

        return $counters->groupBy('event_type')
            ->map(function ($counters, $event) {
                return [
                    'event_type' => $event,
                    'lifetime' => $counters->where('window', 'lifetime')->first()?->counter ?? 0,
                    'daily' => $counters->where('window', 'daily')->first()?->counter ?? 0,
                    'weekly' => $counters->where('window', 'weekly')->first()?->counter ?? 0,
                    'monthly' => $counters->where('window', 'monthly')->first()?->counter ?? 0,
                ];
            })
            ->values()
            ->toArray();
    }

    public function cleanupExpiredCounters(): int
    {
        return $this->repository->deleteExpired();
    }

    private function getWindowBounds(string $window): array
    {
        return match ($window) {
            'daily' => [
                'start' => Carbon::today(),
                'end' => Carbon::tomorrow(),
            ],
            'weekly' => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek(),
            ],
            'monthly' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
            ],
            'lifetime' => [
                'start' => null,
                'end' => null,
            ],
            default => [
                'start' => null,
                'end' => null,
            ],
        };
    }
}
