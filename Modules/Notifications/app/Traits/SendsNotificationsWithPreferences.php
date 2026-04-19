<?php

namespace Modules\Notifications\Traits;

use Modules\Auth\Models\User;
use Modules\Notifications\Models\NotificationPreference;
use Modules\Notifications\Services\NotificationService;

trait SendsNotificationsWithPreferences
{
    
    protected function notifyUser(
        User $user,
        string $category,
        string $title,
        string $message,
        ?array $data = null,
        bool $isCritical = false
    ): void {
        $notificationService = app(NotificationService::class);
        $channels = NotificationPreference::getChannels();

        foreach ($channels as $channel) {
            $notificationService->sendWithPreferences(
                $user,
                $category,
                $channel,
                $title,
                $message,
                $data,
                $isCritical
            );
        }
    }

    
    protected function notifyUserCritical(
        User $user,
        string $category,
        string $title,
        string $message,
        ?array $data = null
    ): void {
        $this->notifyUser($user, $category, $title, $message, $data, true);
    }
}
