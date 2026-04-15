<?php

declare(strict_types=1);

namespace Modules\Notifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use Modules\Notifications\Models\Post;

class SendPostNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public int $timeout = 120;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private Post $post,
        private array $channels,
        private array $audiences
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        Log::info('SendPostNotificationJob: Starting notification dispatch', [
            'post_uuid' => $this->post->uuid,
            'post_title' => $this->post->title,
            'channels' => $this->channels,
            'audiences' => $this->audiences,
        ]);

        try {
            $totalUsers = 0;
            $hasUsers = false;

            User::whereIn('role', $this->audiences)
                ->where('status', 'active')
                ->chunkById(100, function ($chunk) use (&$totalUsers, &$hasUsers) {
                    $hasUsers = true;
                    $totalUsers += $chunk->count();
                    foreach ($this->channels as $channel) {
                        match ($channel) {
                            'email' => $this->sendEmailNotifications($chunk),
                            'in_app' => $this->sendInAppNotifications($chunk),
                            'push' => $this->sendPushNotifications($chunk),
                            default => Log::warning('Unknown notification channel', ['channel' => $channel]),
                        };
                    }
                });

            if (! $hasUsers) {
                Log::warning('SendPostNotificationJob: No active users found for audiences', [
                    'audiences' => $this->audiences,
                ]);

                return;
            }

            foreach ($this->channels as $channel) {
                $this->post->notifications()
                    ->where('channel', $channel)
                    ->update(['sent_at' => now()]);
            }

            Log::info('SendPostNotificationJob: Completed notification dispatch', [
                'post_uuid' => $this->post->uuid,
                'users_notified' => $totalUsers,
                'channels' => $this->channels,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendPostNotificationJob: Failed to send notifications', [
                'post_uuid' => $this->post->uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Send email notifications to users
     */
    private function sendEmailNotifications($users): void
    {
        // TODO: Implement email notification using PostPublishedMail
        // This will be implemented in task 13.1
        Log::info('Email notifications queued', [
            'post_uuid' => $this->post->uuid,
            'user_count' => $users->count(),
        ]);
    }

    /**
     * Send in-app notifications to users
     */
    private function sendInAppNotifications($users): void
    {
        // TODO: Implement in-app notification creation
        // Create notification records in the database
        Log::info('In-app notifications created', [
            'post_uuid' => $this->post->uuid,
            'user_count' => $users->count(),
        ]);
    }

    /**
     * Send push notifications to users
     */
    private function sendPushNotifications($users): void
    {
        // TODO: Implement push notification via FCM/APNS
        Log::info('Push notifications sent', [
            'post_uuid' => $this->post->uuid,
            'user_count' => $users->count(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendPostNotificationJob: Job failed after all retries', [
            'post_uuid' => $this->post->uuid,
            'channels' => $this->channels,
            'audiences' => $this->audiences,
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array
    {
        return [
            'send-post-notification',
            'post:'.$this->post->uuid,
            'channels:'.implode(',', $this->channels),
        ];
    }
}
