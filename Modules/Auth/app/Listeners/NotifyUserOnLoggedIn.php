<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Auth\Events\UserLoggedIn;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyUserOnLoggedIn implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $this->notificationService->notifyByPreferences(
            $event->user,
            NotificationType::System->value,
            __('notifications.auth.login_title'),
            __('notifications.auth.login_message', [
                'login_type' => $event->loginType,
                'ip' => $event->ip,
            ]),
            [
                'ip' => $event->ip,
                'login_type' => $event->loginType,
            ],
            channels: ['in_app'],
            isCritical: true
        );
    }
}
