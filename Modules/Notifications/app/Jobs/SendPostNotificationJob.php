<?php

declare(strict_types=1);

namespace Modules\Notifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Auth\app\Models\User;
use Modules\Notifications\Models\Post;

class SendPostNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 10;

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
            // Query target users based on audiences
            $users = User::whereIn('role', $this->audiences)
                ->where('status', 'active')
                ->get();

            if ($users->isEmpty()) {
                Log::warning('SendPostNotificationJob: No active users found for audiences', [
                    'audiences' => $this->audiences,
                ]);
                return;
            }

            // Send notifications through each channel
            foreach ($this->channels as $channel) {
                match ($channel) {
                    'email' => $this->sendEmailNotifications($users),
                    'in_app' => $this->sendInAppNotifications($users),
                    'push' => $this->sendPushNotifications($users),
                    default => Log::warning('Unknown notification channel', ['channel' => $channel]),
                };

                // Update sent_at timestamp for this channel
                $this->post->notifications()
                    ->where('channel', $channel)
                    ->update(['sent_at' => now()]);
            }

            Log::info('SendPostNotificationJob: Completed notification dispatch', [
                'post_uuid' => $this->post->uuid,
                'users_notified' => $users->count(),
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

