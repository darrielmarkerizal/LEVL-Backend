<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\UserRegistered;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyUserOnRegistered
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(UserRegistered $event): void
    {
        $this->notificationService->notifyByPreferences(
            $event->user,
            NotificationType::System->value,
            __('notifications.auth.registered_title'),
            __('notifications.auth.registered_message'),
            [
                'user_id' => $event->user->id,
            ],
            channels: ['in_app'],
            isCritical: true
        );
    }
}
