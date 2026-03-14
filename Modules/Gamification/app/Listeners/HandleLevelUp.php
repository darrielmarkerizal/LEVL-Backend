<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Gamification\Events\UserLeveledUp;
use Modules\Gamification\Services\Support\BadgeManager;
use Modules\Gamification\Services\Support\PointManager;

class HandleLevelUp
{
    public function __construct(
        private readonly BadgeManager $badgeManager,
        private readonly PointManager $pointManager
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserLeveledUp $event): void
    {
        try {
            // Award milestone rewards if any
            if (!empty($event->rewards)) {
                $this->awardRewards($event);
            }

            // Award milestone badge if exists
            if (isset($event->rewards['badge'])) {
                $this->badgeManager->awardBadge(
                    $event->userId,
                    $event->rewards['badge'],
                    "Reached level {$event->newLevel}"
                );
            }

            // Award bonus XP if exists
            if (isset($event->rewards['bonus_xp']) && $event->rewards['bonus_xp'] > 0) {
                $this->pointManager->awardXp(
                    $event->userId,
                    $event->rewards['bonus_xp'],
                    'level_up_bonus',
                    'level',
                    $event->newLevel,
                    ['description' => "Bonus XP for reaching level {$event->newLevel}"]
                );
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
        // Additional reward logic can be added here
        // For example: unlock features, send notifications, etc.
    }
}
