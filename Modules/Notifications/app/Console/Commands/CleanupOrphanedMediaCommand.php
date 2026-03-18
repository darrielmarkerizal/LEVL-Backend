<?php

declare(strict_types=1);

namespace Modules\Notifications\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Notifications\Models\Post;

class CleanupOrphanedMediaCommand extends Command
{
    protected $signature = 'posts:cleanup-orphaned-media';

    protected $description = 'Delete temporary posts and orphaned media older than 24 hours';

    public function handle(): int
    {
        $this->info('Checking for orphaned media to cleanup...');
        Log::info('CleanupOrphanedMediaCommand: Starting orphaned media cleanup');

        // Find temporary posts older than 24 hours
        $temporaryPosts = Post::where('title', 'temp_upload')
            ->where('created_at', '<', now()->subHours(24))
            ->withTrashed()
            ->get();

        if ($temporaryPosts->isEmpty()) {
            $this->info('No orphaned media to cleanup.');
            Log::info('CleanupOrphanedMediaCommand: No orphaned media found');

            return self::SUCCESS;
        }

        $deletedPosts = 0;
        $deletedMedia = 0;

        foreach ($temporaryPosts as $post) {
            try {
                // Count media files before deletion
                $mediaCount = $post->getMedia('images')->count();

                // Delete all media files using Spatie Media Library
                $post->clearMediaCollection('images');

                // Force delete the temporary post
                $post->forceDelete();

                $deletedPosts++;
                $deletedMedia += $mediaCount;

                $this->info("✓ Deleted temporary post (UUID: {$post->uuid}) with {$mediaCount} media file(s)");
            } catch (\Exception $e) {
                $this->error("✗ Failed to delete temporary post (UUID: {$post->uuid})");
                Log::error('CleanupOrphanedMediaCommand: Failed to delete temporary post', [
                    'post_uuid' => $post->uuid,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("\nSummary:");
        $this->info("Deleted posts: {$deletedPosts}");
        $this->info("Deleted media files: {$deletedMedia}");

        Log::info('CleanupOrphanedMediaCommand: Completed orphaned media cleanup', [
            'deleted_posts_count' => $deletedPosts,
            'deleted_media_count' => $deletedMedia,
        ]);

        return self::SUCCESS;
    }
}
