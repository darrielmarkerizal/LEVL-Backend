<?php

declare(strict_types=1);

namespace Modules\Gamification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\UserBadge;

class BadgeEarned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public UserBadge $userBadge,
        public Badge $badge
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("user.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'badge.earned';
    }

    public function broadcastWith(): array
    {
        return [
            'event' => 'badge_earned',
            'user_id' => $this->userId,
            'badge' => [
                'id' => $this->badge->id,
                'code' => $this->badge->code,
                'name' => $this->badge->name,
                'description' => $this->badge->description,
                'type' => $this->badge->type?->value,
                'rarity' => $this->badge->rarity?->value,
                'xp_reward' => $this->badge->xp_reward,
                'icon_url' => $this->badge->icon_url,
            ],
            'earned_at' => $this->userBadge->earned_at?->toIso8601String(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
