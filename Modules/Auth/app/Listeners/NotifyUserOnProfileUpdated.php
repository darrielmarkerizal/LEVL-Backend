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
            ? 'Profil diperbarui dan email Anda berubah. Silakan verifikasi ulang email.'
            : 'Profil Anda berhasil diperbarui.';

        $this->notificationService->notifyByPreferences(
            $event->user,
            NotificationType::System->value,
            'Profil diperbarui',
            $message,
            [
                'email_changed' => $event->emailChanged,
            ],
            channels: ['in_app']
        );
    }
}
