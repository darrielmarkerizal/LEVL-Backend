<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Carbon\Carbon;
use Modules\Gamification\Events\UserLoggedIn;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Traits\CachesUsers;

class AwardXpForDailyLogin
{
    use CachesUsers; // FIX: Use cached user lookups

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $userId = $event->userId;

        // Check if already logged in today
        $cacheKey = "gamification.daily_login.{$userId}.".Carbon::today()->format('Y-m-d');

        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            // Already awarded XP for today
            return;
        }

        // 1. Award XP for daily login (10 XP from xp_sources)
        $this->gamification->awardXp(
            $userId,
            0, // Will use xp_sources config (daily_login = 10 XP)
            'daily_login',
            'system',
            null,
            [
                'description' => 'Daily login bonus',
            ]
        );

        // 2. Update streak
        $stats = UserGamificationStat::where('user_id', $userId)->first();
        if ($stats) {
            $currentStreak = $stats->current_streak;

            // Check for streak milestones
            if ($currentStreak == 7) {
                $this->gamification->awardXp(
                    $userId,
                    0, // Will use xp_sources config (streak_7_days = 200 XP)
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
                    0, // Will use xp_sources config (streak_30_days = 1000 XP)
                    'streak_30_days',
                    'system',
                    null,
                    [
                        'description' => '30-day login streak bonus!',
                    ]
                );
            }
        }

        // 3. Log event
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

        // 4. Increment counters
        $this->counterService->increment($userId, 'daily_login', 'global', null, 'lifetime');

        // 5. Evaluate Dynamic Badge Rules
        // FIX: Use cached user lookup
        $user = $this->getCachedUser($userId);
        if ($user) {
            $payload = [
                'login_date' => Carbon::today()->toDateString(),
                'current_streak' => $stats?->current_streak ?? 0,
            ];
            $this->evaluator->evaluate($user, 'daily_login', $payload);
        }

        // Cache for 24 hours
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, Carbon::tomorrow()->addHour());
    }
}
