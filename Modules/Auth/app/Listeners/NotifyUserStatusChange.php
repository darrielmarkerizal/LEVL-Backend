<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Events\UserStatusChanged;
use Modules\Auth\Notifications\UserStatusChangedNotification;

/**
 * Listener to notify users when their status changes.
 */
class NotifyUserStatusChange
{
    /**
     * Handle the event.
     *
     * @param  \Modules\Auth\Events\UserStatusChanged  $event
     * @return void
     */
    public function handle(UserStatusChanged $event): void
    {
        // Don't notify for Pending status (handled by email verification)
        if ($event->newStatus === UserStatus::Pending) {
            return;
        }

        // Don't notify if status didn't actually change
        if ($event->oldStatus === $event->newStatus) {
            return;
        }

        // Send notification to the user
        $event->user->notify(new UserStatusChangedNotification(
            $event->oldStatus,
            $event->newStatus,
            $event->changedBy,
            $event->reason
        ));
    }
}
