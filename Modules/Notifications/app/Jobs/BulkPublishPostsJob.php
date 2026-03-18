<?php

declare(strict_types=1);

namespace Modules\Notifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Notifications\Enums\PostStatus;
use Modules\Notifications\Models\Post;
use Modules\Notifications\Services\PostService;

class BulkPublishPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public int $timeout = 300;

    public array $backoff = [5, 30, 120];

    private const BATCH_SIZE = 10;

    public function __construct(
        private array $postUuids
    ) {
        $this->onQueue('notifications');
    }

    public function handle(PostService $service): void
    {
        $startTime = now();
        $successCount = 0;
        $failureCount = 0;

        Log::info('BulkPublishPostsJob: Starting bulk publish', [
            'post_count' => count($this->postUuids),
            'batch_size' => self::BATCH_SIZE,
        ]);

        // Process posts in batches
        $batches = array_chunk($this->postUuids, self::BATCH_SIZE);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $uuid) {
                try {
                    $post = Post::where('uuid', $uuid)->first();

                    if (! $post) {
                        Log::warning('BulkPublishPostsJob: Post not found', ['uuid' => $uuid]);
                        $failureCount++;

                        continue;
                    }

                    // Only publish if not already published
                    if ($post->status === PostStatus::PUBLISHED) {
                        Log::debug('BulkPublishPostsJob: Post already published', [
                            'uuid' => $uuid,
                            'title' => $post->title,
                        ]);
                        $successCount++;

                        continue;
                    }

                    $service->publishPost($post);
                    $successCount++;

                    Log::debug('BulkPublishPostsJob: Post published', [
                        'uuid' => $uuid,
                        'title' => $post->title,
                    ]);
                } catch (\Throwable $e) {
                    $failureCount++;
                    Log::error('BulkPublishPostsJob: Failed to publish post', [
                        'uuid' => $uuid,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue processing remaining posts
                }
            }

        }

        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        Log::info('BulkPublishPostsJob: Completed bulk publish', [
            'total_posts' => count($this->postUuids),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'duration_seconds' => $duration,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BulkPublishPostsJob: Job failed after all retries', [
            'post_count' => count($this->postUuids),
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array
    {
        return [
            'bulk-publish-posts',
            'posts:'.count($this->postUuids),
        ];
    }
}
