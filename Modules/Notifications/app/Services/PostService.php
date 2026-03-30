<?php

declare(strict_types=1);

namespace Modules\Notifications\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Notifications\DTOs\CreatePostDTO;
use Modules\Notifications\DTOs\UpdatePostDTO;
use Modules\Notifications\Enums\PostStatus;
use Modules\Notifications\Jobs\BulkDeletePostsJob;
use Modules\Notifications\Jobs\BulkPublishPostsJob;
use Modules\Notifications\Jobs\SendPostNotificationJob;
use Modules\Notifications\Models\Post;
use Modules\Notifications\Models\PostView;
use Modules\Notifications\Repositories\PostRepository;

class PostService
{
    public function __construct(
        private PostRepository $repository
    ) {}

    /**
     * Create a new post with transaction support
     */
    public function createPost(CreatePostDTO $dto, int $authorId): Post
    {
        return DB::transaction(function () use ($dto, $authorId) {
            // Generate UUID and slug
            $uuid = (string) Str::uuid();
            $slug = Str::slug($dto->title);

            // Create post
            $post = $this->repository->create([
                'uuid' => $uuid,
                'title' => $dto->title,
                'slug' => $slug,
                'content' => $dto->content,
                'category' => $dto->category,
                'status' => $dto->status,
                'is_pinned' => $dto->isPinned,
                'author_id' => $authorId,
                'scheduled_at' => $dto->scheduledAt,
                'published_at' => $dto->status === PostStatus::PUBLISHED->value ? now() : null,
            ]);

            // Attach audiences
            foreach ($dto->audiences as $role) {
                $post->audiences()->create(['role' => $role]);
            }

            // Store notification channel preferences
            foreach ($dto->notificationChannels as $channel) {
                $post->notifications()->create(['channel' => $channel]);
            }

            // Send notifications if published immediately
            if ($dto->status === PostStatus::PUBLISHED->value && ! empty($dto->notificationChannels)) {
                $this->sendNotifications($post);
            }

            return $post->load(['author', 'audiences', 'notifications']);
        });
    }

    /**
     * Update an existing post with last_editor_id tracking
     */
    public function updatePost(Post $post, UpdatePostDTO $dto, int $editorId): Post
    {
        return DB::transaction(function () use ($post, $dto, $editorId) {
            $updateData = ['last_editor_id' => $editorId];

            // Build update data from DTO
            if (! ($dto->title instanceof \Spatie\LaravelData\Optional)) {
                $updateData['title'] = $dto->title;
                $updateData['slug'] = Str::slug($dto->title);
            }

            if (! ($dto->content instanceof \Spatie\LaravelData\Optional)) {
                $updateData['content'] = $dto->content;
            }

            if (! ($dto->category instanceof \Spatie\LaravelData\Optional)) {
                $updateData['category'] = $dto->category;
            }

            if (! ($dto->status instanceof \Spatie\LaravelData\Optional)) {
                $updateData['status'] = $dto->status;
            }

            if (! ($dto->isPinned instanceof \Spatie\LaravelData\Optional)) {
                $updateData['is_pinned'] = $dto->isPinned;
            }

            // Update post
            $this->repository->update($post, $updateData);

            // Update audiences if provided
            if (! ($dto->audiences instanceof \Spatie\LaravelData\Optional)) {
                $post->audiences()->delete();
                foreach ($dto->audiences as $role) {
                    $post->audiences()->create(['role' => $role]);
                }
            }

            // Update notification channels if provided
            if (! ($dto->notificationChannels instanceof \Spatie\LaravelData\Optional)) {
                $post->notifications()->delete();
                foreach ($dto->notificationChannels as $channel) {
                    $post->notifications()->create(['channel' => $channel]);
                }
            }

            // Resend notifications if requested
            if (! empty($dto->resendNotificationChannels) && $post->status === PostStatus::PUBLISHED) {
                $this->sendNotifications($post, $dto->resendNotificationChannels);
            }

            return $post->fresh(['author', 'lastEditor', 'audiences', 'notifications']);
        });
    }

    /**
     * Soft delete a post
     */
    public function deletePost(Post $post): bool
    {
        return $this->repository->delete($post);
    }

    /**
     * Restore a soft-deleted post
     */
    public function restorePost(Post $post): bool
    {
        return $this->repository->restore($post);
    }

    /**
     * Permanently delete a post
     */
    public function forceDeletePost(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            // Delete all media files
            $post->clearMediaCollection('images');

            return $this->repository->forceDelete($post);
        });
    }

    /**
     * Publish a draft post
     */
    public function publishPost(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $this->repository->update($post, [
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now(),
                'scheduled_at' => null,
            ]);

            // Send notifications
            $this->sendNotifications($post);

            return $post->fresh(['author', 'audiences', 'notifications']);
        });
    }

    /**
     * Unpublish a post (revert to draft)
     */
    public function unpublishPost(Post $post): Post
    {
        $this->repository->update($post, [
            'status' => PostStatus::DRAFT->value,
            'published_at' => null,
        ]);

        return $post->fresh();
    }

    /**
     * Schedule a post for future publication
     */
    public function schedulePost(Post $post, string $scheduledAt): Post
    {
        $this->repository->update($post, [
            'status' => PostStatus::SCHEDULED->value,
            'scheduled_at' => $scheduledAt,
            'published_at' => null,
        ]);

        return $post->fresh();
    }

    /**
     * Publish a scheduled post (called by command)
     */
    public function publishScheduledPost(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $this->repository->update($post, [
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now(),
            ]);

            // Send notifications
            $this->sendNotifications($post);

            Log::info('Published scheduled post', [
                'post_uuid' => $post->uuid,
                'post_title' => $post->title,
            ]);

            return $post->fresh(['author', 'audiences', 'notifications']);
        });
    }

    /**
     * Cancel scheduling and revert to draft
     */
    public function cancelSchedule(Post $post): Post
    {
        $this->repository->update($post, [
            'status' => PostStatus::DRAFT->value,
            'scheduled_at' => null,
        ]);

        return $post->fresh();
    }

    /**
     * Toggle pin status of a post
     */
    public function togglePin(Post $post): Post
    {
        $this->repository->update($post, [
            'is_pinned' => ! $post->is_pinned,
        ]);

        return $post->fresh();
    }

    /**
     * Mark a post as viewed by a user (with unique constraint handling)
     */
    public function markAsViewed(Post $post, int $userId): void
    {
        try {
            // Use polymorphic content_reads instead of post_views
            \Modules\Common\Models\ContentRead::firstOrCreate(
                [
                    'readable_type' => 'Modules\\Notifications\\Models\\Post',
                    'readable_id' => $post->id,
                    'user_id' => $userId,
                ],
                [
                    'read_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            // Silently handle duplicate key violations
            // This can happen in race conditions
            Log::debug('View already recorded', [
                'post_id' => $post->id,
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * Upload an image for the rich text editor
     */
    public function uploadImage(Post $post, UploadedFile $file): string
    {
        $media = $post->addMedia($file)
            ->toMediaCollection('images');

        return $media->getUrl();
    }

    /**
     * Bulk delete posts (dispatches job)
     */
    public function bulkDelete(array $postUuids): void
    {
        BulkDeletePostsJob::dispatch($postUuids);

        Log::info('Bulk delete job dispatched', [
            'post_count' => count($postUuids),
        ]);
    }

    /**
     * Bulk publish posts (dispatches job)
     */
    public function bulkPublish(array $postUuids): void
    {
        BulkPublishPostsJob::dispatch($postUuids);

        Log::info('Bulk publish job dispatched', [
            'post_count' => count($postUuids),
        ]);
    }

    /**
     * Send notifications for a post through selected channels
     */
    private function sendNotifications(Post $post, ?array $specificChannels = null): void
    {
        // Get channels to send through
        $channels = $specificChannels ?? $post->notifications->pluck('channel')->toArray();

        if (empty($channels)) {
            return;
        }

        // Get target audiences
        $audiences = $post->audiences->pluck('role')->toArray();

        if (empty($audiences)) {
            return;
        }

        // Dispatch notification job
        SendPostNotificationJob::dispatch($post, $channels, $audiences);

        Log::info('Post notification job dispatched', [
            'post_uuid' => $post->uuid,
            'channels' => $channels,
            'audiences' => $audiences,
        ]);
    }
}
