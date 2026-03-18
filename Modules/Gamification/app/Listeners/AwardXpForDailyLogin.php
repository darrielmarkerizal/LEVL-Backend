<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Modules\Gamification\Events\UserLoggedIn;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Traits\CachesUsers;

class AwardXpForDailyLogin implements ShouldQueue
{
    use CachesUsers;
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $userId = $event->userId;

        $cacheKey = "gamification.daily_login.{$userId}.".Carbon::today()->format('Y-m-d');

        if (Cache::has($cacheKey)) {
            return;
        }

        $this->gamification->awardXp(
            $userId,
            0,
            'daily_login',
            'system',
            null,
            [
                'description' => 'Daily login bonus',
            ]
        );

        $stats = UserGamificationStat::where('user_id', $userId)->first();
        if ($stats) {
            $currentStreak = $stats->current_streak;

            if ($currentStreak == 7) {
                $this->gamification->awardXp(
                    $userId,
                    0,
                    'streak_7_days',
                    'system',
                    null,
                    [
                        'description' => '7-day login streak bonus!',
                    ]
                );
            } elseif ($currentStreak == 30) {
                $this->gamification->awardXp(
                    $userId,
                    0,
                    'streak_30_days',
                    'system',
                    null,
                    [
                        'description' => '30-day login streak bonus!',
                    ]
                );
            }
        }

        $this->loggerService->log(
            $userId,
            'daily_login',
            'system',
            null,
            [
                'login_date' => Carbon::today()->toDateString(),
                'current_streak' => $stats?->current_streak ?? 0,
            ]
        );

        $this->counterService->increment($userId, 'daily_login', 'global', null, 'lifetime');

        $user = $this->getCachedUser($userId);
        if ($user) {
            $payload = [
                'login_date' => Carbon::today()->toDateString(),
                'current_streak' => $stats?->current_streak ?? 0,
            ];
            $this->evaluator->evaluate($user, 'daily_login', $payload);
        }

        Cache::put($cacheKey, true, Carbon::tomorrow()->addHour());
    }
}
