<?php

declare(strict_types=1);

namespace Modules\Auth\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;


class UserStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    
    public function __construct(
        public User $user,
        public UserStatus $oldStatus,
        public UserStatus $newStatus,
        public ?User $changedBy = null,
        public ?string $reason = null
    ) {}

    
    public function broadcastOn(): array
    {
        return [];
    }
}
