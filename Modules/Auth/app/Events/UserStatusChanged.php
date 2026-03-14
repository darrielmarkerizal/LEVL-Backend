<?php

declare(strict_types=1);

namespace Modules\Auth\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;

/**
 * Event fired when a user's status is changed.
 * 
 * This event is dispatched whenever a user's status is updated,
 * allowing listeners to perform actions like logging, notifications, etc.
 */
class UserStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \Modules\Auth\Models\User  $user  The user whose status changed
     * @param  \Modules\Auth\Enums\UserStatus  $oldStatus  The previous status
     * @param  \Modules\Auth\Enums\UserStatus  $newStatus  The new status
     * @param  \Modules\Auth\Models\User|null  $changedBy  The user who made the change (null for system changes)
     * @param  string|null  $reason  Optional reason for the status change
     */
    public function __construct(
        public User $user,
        public UserStatus $oldStatus,
        public UserStatus $newStatus,
        public ?User $changedBy = null,
        public ?string $reason = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
