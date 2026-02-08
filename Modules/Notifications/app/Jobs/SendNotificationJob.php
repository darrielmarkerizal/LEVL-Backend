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
            Log::info('SendNotificationJob: No user IDs provided, skipping');
            return;
        }

        if (! class_exists($this->notificationClass)) {
            Log::error('SendNotificationJob: Notification class does not exist', [
                'notification_class' => $this->notificationClass,
            ]);
            return;
        }

        Log::info('SendNotificationJob: Starting notification dispatch', [
            'user_count' => count($this->userIds),
            'notification_class' => $this->notificationClass,
        ]);

        try {
            $users = User::whereIn('id', $this->userIds)->get();

            if ($users->isEmpty()) {
                Log::warning('SendNotificationJob: No users found for provided IDs', [
                    'user_ids' => $this->userIds,
                ]);
                return;
            }

            $notification = new $this->notificationClass(...array_values($this->notificationData));

            Notification::send($users, $notification);

            Log::info('SendNotificationJob: Completed notification dispatch', [
                'users_notified' => $users->count(),
                'notification_class' => $this->notificationClass,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendNotificationJob: Failed to send notification', [
                'user_ids' => $this->userIds,
                'notification_class' => $this->notificationClass,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendNotificationJob: Job failed after all retries', [
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
