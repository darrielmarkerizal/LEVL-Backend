<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use Modules\Gamification\Events\UserLeveledUp;
use Modules\Gamification\Services\Support\BadgeManager;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;
use Modules\Gamification\Services\Support\PointManager;

class HandleLevelUp implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private readonly BadgeManager $badgeManager,
        private readonly PointManager $pointManager,
        private readonly BadgeRuleEvaluator $evaluator
    ) {}

    
    public function handle(UserLeveledUp $event): void
    {
        try {
            
            $user = User::find($event->userId);
            if ($user) {
                $this->evaluator->evaluate($user, 'level_reached', [
                    'level' => $event->newLevel,
                    'old_level' => $event->oldLevel,
                    'total_xp' => $event->totalXp,
                ]);
            }

            
            if (! empty($event->rewards)) {
                $this->awardRewards($event);
            }

            Log::info('User leveled up', [
                'user_id' => $event->userId,
                'old_level' => $event->oldLevel,
                'new_level' => $event->newLevel,
                'rewards' => $event->rewards,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to handle level up', [
                'user_id' => $event->userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function awardRewards(UserLeveledUp $event): void
    {
        foreach ($event->rewards as $reward) {
            $type = $reward['type'] ?? null;

            if ($type === 'badge' && ! empty($reward['badge_code'])) {
                $this->badgeManager->awardBadge(
                    $event->userId,
                    $reward['badge_code'],
                    $reward['badge_name'] ?? "Level {$event->newLevel} Badge",
                    $reward['badge_description'] ?? null,
                );
            } elseif ($type === 'xp' && ! empty($reward['amount'])) {
                $this->pointManager->awardXp(
                    $event->userId,
                    (int) $reward['amount'],
                    'level_up_reward',
                    'system',
                    null,
                    [
                        'description' => "Level {$event->newLevel} reward",
                        'metadata' => ['level' => $event->newLevel],
                    ]
                );
            }
        }
    }
}
