<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Events\UserStatusChanged;
use Modules\Auth\Notifications\UserStatusChangedNotification;

class NotifyUserStatusChange implements ShouldQueue
{
    
    public function handle(UserStatusChanged $event): void
    {
        
        if ($event->newStatus === UserStatus::Pending) {
            return;
        }

        
        if ($event->oldStatus === $event->newStatus) {
            return;
        }

        
        $event->user->notify(new UserStatusChangedNotification(
            $event->oldStatus,
            $event->newStatus,
            $event->changedBy,
            $event->reason
        ));
    }
}
