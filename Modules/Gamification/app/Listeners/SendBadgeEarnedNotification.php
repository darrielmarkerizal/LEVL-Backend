<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Events\BadgeEarned;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class SendBadgeEarnedNotification extends GamificationListener
{

    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(BadgeEarned $event): void
    {
        $user = $this->getCachedUser($event->userId);
        if (! $user) {
            return;
        }

        $this->notificationService->notifyByPreferences(
            $user,
            NotificationType::Achievements->value,
            __('gamification::notifications.badge_earned_title', ['badge' => $event->badge->name]),
            __('gamification::notifications.badge_earned_message', [
                'badge' => $event->badge->name,
                'xp' => $event->badge->xp_reward ?? 0,
            ]),
            [
                'badge_id' => $event->badge->id,
                'badge_code' => $event->badge->code,
                'badge_name' => $event->badge->name,
                'badge_rarity' => $event->badge->rarity?->value,
                'badge_icon_url' => $event->badge->icon_url,
                'xp_reward' => $event->badge->xp_reward,
                'earned_at' => $event->userBadge->earned_at?->toIso8601String(),
            ]
        );
    }
}
