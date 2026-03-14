<?php

declare(strict_types=1);

namespace Modules\Gamification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeveledUp implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public int $oldLevel,
        public int $newLevel,
        public int $totalXp,
        public array $rewards = []
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("user.{$this->userId}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'level.up';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event' => 'level_up',
            'user_id' => $this->userId,
            'old_level' => $this->oldLevel,
            'new_level' => $this->newLevel,
            'total_xp' => $this->totalXp,
            'rewards' => $this->rewards,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
