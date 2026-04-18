<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\ProfileUpdated;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyUserOnProfileUpdated
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(ProfileUpdated $event): void
    {
        $message = $event->emailChanged
            ? __('notifications.auth.profile_updated_email_changed_message')
            : __('notifications.auth.profile_updated_message');

        $this->notificationService->notifyByPreferences(
            $event->user,
            NotificationType::System->value,
            __('notifications.auth.profile_updated_title'),
            $message,
            [
                'email_changed' => $event->emailChanged,
            ],
            channels: ['in_app']
        );
    }
}
