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

    public function __construct(
        private readonly BadgeManager $badgeManager,
        private readonly PointManager $pointManager,
        private readonly BadgeRuleEvaluator $evaluator
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserLeveledUp $event): void
    {
        try {
            // Award milestone badge if this is a milestone level
            $this->awardMilestoneBadge($event);

            // Evaluate badge rules triggered by level_reached
            $user = User::find($event->userId);
            if ($user) {
                $this->evaluator->evaluate($user, 'level_reached', [
                    'level' => $event->newLevel,
                    'old_level' => $event->oldLevel,
                    'total_xp' => $event->totalXp,
                ]);
            }

            // Award additional rewards if any
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

    /**
     * Award milestone badge if this level has one
     */
    private function awardMilestoneBadge(UserLeveledUp $event): void
    {
        // Get level config to check for milestone badge
        $levelConfig = \Modules\Common\Models\LevelConfig::where('level', $event->newLevel)
            ->with('milestoneBadge')
            ->first();

        if ($levelConfig && $levelConfig->milestone_badge_id) {
            $badge = $levelConfig->milestoneBadge;

            if ($badge) {
                $this->badgeManager->awardBadge(
                    $event->userId,
                    $badge->code,
                    "Mencapai level {$event->newLevel}"
                );

                Log::info('Milestone badge awarded', [
                    'user_id' => $event->userId,
                    'level' => $event->newLevel,
                    'badge_code' => $badge->code,
                    'badge_name' => $badge->name,
                ]);
            }
        }
    }

    private function awardRewards(UserLeveledUp $event): void
    {
        // Additional reward logic can be added here
        // For example: unlock features, send notifications, etc.
    }
}
