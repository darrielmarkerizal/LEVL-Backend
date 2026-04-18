<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\UserLoggedIn;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyUserOnLoggedIn
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $this->notificationService->notifyByPreferences(
            $event->user,
            NotificationType::System->value,
            'Login berhasil',
            "Login terdeteksi via {$event->loginType} dari {$event->ip}.",
            [
                'ip' => $event->ip,
                'login_type' => $event->loginType,
            ],
            channels: ['in_app'],
            isCritical: true
        );
    }
}
