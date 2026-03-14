<?php

declare(strict_types=1);

namespace Modules\Gamification\Repositories;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Gamification\Contracts\Repositories\UserEventCounterRepositoryInterface;
use Modules\Gamification\Models\UserEventCounter;

class UserEventCounterRepository extends BaseRepository implements UserEventCounterRepositoryInterface
{
    protected function model(): string
    {
        return UserEventCounter::class;
    }

    public function findOrCreate(
        int $userId,
        string $eventType,
        ?string $scopeType,
        ?int $scopeId,
        string $window,
        ?Carbon $windowStart
    ): UserEventCounter {
        return UserEventCounter::firstOrCreate(
            [
                'user_id' => $userId,
                'event_type' => $eventType,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'window' => $window,
                'window_start' => $windowStart,
            ],
            [
                'counter' => 0,
                'window_end' => $this->calculateWindowEnd($window, $windowStart),
            ]
        );
    }

    public function findByUserAndEvent(
        int $userId,
        string $eventType,
        ?string $scopeType,
        ?int $scopeId,
        string $window,
        ?Carbon $windowStart
    ): ?UserEventCounter {
        return UserEventCounter::where('user_id', $userId)
            ->where('event_type', $eventType)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->where('window', $window)
            ->where('window_start', $windowStart)
            ->active()
            ->first();
    }

    public function getUserCounters(int $userId, ?string $eventType = null): Collection
    {
        $query = UserEventCounter::where('user_id', $userId)->active();

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        return $query->get();
    }

    public function deleteExpired(): int
    {
        return UserEventCounter::where('window_end', '<', now())
            ->where('window', '!=', 'lifetime')
            ->delete();
    }

    private function calculateWindowEnd(string $window, ?Carbon $windowStart): ?Carbon
    {
        if ($window === 'lifetime' || ! $windowStart) {
            return null;
        }

        return match ($window) {
            'daily' => $windowStart->copy()->addDay(),
            'weekly' => $windowStart->copy()->addWeek(),
            'monthly' => $windowStart->copy()->addMonth(),
            default => null,
        };
    }
}
