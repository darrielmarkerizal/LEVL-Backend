<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\PasswordChanged;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyUserOnPasswordChanged
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(PasswordChanged $event): void
    {
        $this->notificationService->notifyByPreferences(
            $event->user,
            NotificationType::System->value,
            __('notifications.auth.password_changed_title'),
            __('notifications.auth.password_changed_message'),
            [
                'user_id' => $event->user->id,
            ],
            channels: ['in_app', 'email'],
            isCritical: true
        );
    }
}
