<?php

declare(strict_types=1);

namespace Modules\Notifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Notifications\Models\Post;
use Modules\Notifications\Services\PostService;

class BulkDeletePostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public int $backoff = 30;

    private const BATCH_SIZE = 10;
    private const BATCH_DELAY_MS = 100;

    public function __construct(
        private array $postUuids
    ) {
        $this->onQueue('bulk-operations');
    }

    public function handle(PostService $service): void
    {
        $startTime = now();
        $successCount = 0;
        $failureCount = 0;

        Log::info('BulkDeletePostsJob: Starting bulk delete', [
            'post_count' => count($this->postUuids),
            'batch_size' => self::BATCH_SIZE,
        ]);

        // Process posts in batches
        $batches = array_chunk($this->postUuids, self::BATCH_SIZE);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $uuid) {
                try {
                    $post = Post::where('uuid', $uuid)->first();

                    if (!$post) {
                        Log::warning('BulkDeletePostsJob: Post not found', ['uuid' => $uuid]);
                        $failureCount++;
                        continue;
                    }

                    $service->deletePost($post);
                    $successCount++;

                    Log::debug('BulkDeletePostsJob: Post deleted', [
                        'uuid' => $uuid,
                        'title' => $post->title,
                    ]);
                } catch (\Throwable $e) {
                    $failureCount++;
                    Log::error('BulkDeletePostsJob: Failed to delete post', [
                        'uuid' => $uuid,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue processing remaining posts
                }
            }

            // Add delay between batches (except for the last batch)
            if ($batchIndex < count($batches) - 1) {
                usleep(self::BATCH_DELAY_MS * 1000);
            }
        }

        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        Log::info('BulkDeletePostsJob: Completed bulk delete', [
            'total_posts' => count($this->postUuids),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'duration_seconds' => $duration,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BulkDeletePostsJob: Job failed after all retries', [
            'post_count' => count($this->postUuids),
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array
    {
        return [
            'bulk-delete-posts',
            'posts:'.count($this->postUuids),
        ];
    }
}

