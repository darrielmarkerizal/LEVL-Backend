<?php

declare(strict_types=1);

namespace Modules\Notifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\Auth\Models\User;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 10;

    public function __construct(
        public array $userIds,
        public string $notificationClass,
        public array $notificationData = []
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        if (empty($this->userIds)) {
            Log::info(__('notifications.jobs.send_notification.no_user_ids'));

            return;
        }

        if (! class_exists($this->notificationClass)) {
            Log::error(__('notifications.jobs.send_notification.class_missing'), [
                'notification_class' => $this->notificationClass,
            ]);

            return;
        }

        Log::info(__('notifications.jobs.send_notification.dispatch_start'), [
            'user_count' => count($this->userIds),
            'notification_class' => $this->notificationClass,
        ]);

        try {
            $users = User::whereIn('id', $this->userIds)->get();

            if ($users->isEmpty()) {
                Log::warning(__('notifications.jobs.send_notification.no_users_found'), [
                    'user_ids' => $this->userIds,
                ]);

                return;
            }

            $notification = new $this->notificationClass(...array_values($this->notificationData));

            Notification::send($users, $notification);

            Log::info(__('notifications.jobs.send_notification.dispatch_complete'), [
                'users_notified' => $users->count(),
                'notification_class' => $this->notificationClass,
            ]);
        } catch (\Throwable $e) {
            Log::error(__('notifications.jobs.send_notification.failed'), [
                'user_ids' => $this->userIds,
                'notification_class' => $this->notificationClass,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(__('notifications.jobs.send_notification.job_failed'), [
            'user_ids' => $this->userIds,
            'notification_class' => $this->notificationClass,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    public function tags(): array
    {
        $className = class_basename($this->notificationClass);

        return [
            'send-notification',
            'notification:'.$className,
            'users:'.count($this->userIds),
        ];
    }
}
