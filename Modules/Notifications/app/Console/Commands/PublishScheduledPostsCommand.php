<?php

declare(strict_types=1);

namespace Modules\Notifications\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Notifications\Repositories\PostRepository;
use Modules\Notifications\Services\PostService;

class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish posts that are scheduled for the current time';

    public function __construct(
        private PostRepository $repository,
        private PostService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking for scheduled posts to publish...');
        Log::info('PublishScheduledPostsCommand: Starting scheduled post publication check');

        // Get all posts that are scheduled and ready to publish
        $pendingPosts = $this->repository->getPendingScheduledPosts();

        if ($pendingPosts->isEmpty()) {
            $this->info('No scheduled posts to publish.');
            Log::info('PublishScheduledPostsCommand: No scheduled posts found');
            return self::SUCCESS;
        }

        $published = 0;
        $failed = 0;

        foreach ($pendingPosts as $post) {
            try {
                $this->service->publishScheduledPost($post);
                $published++;
                $this->info("✓ Published post: {$post->title} (UUID: {$post->uuid})");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to publish post: {$post->title} (UUID: {$post->uuid})");
                Log::error('PublishScheduledPostsCommand: Failed to publish post', [
                    'post_uuid' => $post->uuid,
                    'post_title' => $post->title,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("\nSummary:");
        $this->info("Published: {$published}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }

        Log::info('PublishScheduledPostsCommand: Completed scheduled post publication', [
            'published_count' => $published,
            'failed_count' => $failed,
        ]);

        return self::SUCCESS;
    }
}
