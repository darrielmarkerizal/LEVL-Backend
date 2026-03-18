<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\UserStatusChanged;

/**
 * Listener to log user status changes to activity log.
 */
class LogUserStatusChange
{
    /**
     * Handle the event.
     */
    public function handle(UserStatusChanged $event): void
    {
        $properties = [
            'user_id' => $event->user->id,
            'user_name' => $event->user->name,
            'user_email' => $event->user->email,
            'old_status' => $event->oldStatus->value,
            'new_status' => $event->newStatus->value,
            'changed_by_id' => $event->changedBy?->id,
            'changed_by_name' => $event->changedBy?->name,
            'reason' => $event->reason,
        ];

        activity('user_status')
            ->causedBy($event->changedBy ?? $event->user)
            ->performedOn($event->user)
            ->withProperties($properties)
            ->log(sprintf(
                'User status changed from %s to %s',
                $event->oldStatus->label(),
                $event->newStatus->label()
            ));
    }
}
