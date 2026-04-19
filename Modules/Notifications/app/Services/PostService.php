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
        public readonly PostRepository $repository
    ) {}

    
    public function createPost(CreatePostDTO $dto, int $authorId): Post
    {
        return DB::transaction(function () use ($dto, $authorId) {
            
            $uuid = (string) Str::uuid();
            $slug = Str::slug($dto->title);

            
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

            
            foreach ($dto->audiences as $role) {
                $post->audiences()->create(['role' => $role]);
            }

            
            foreach ($dto->notificationChannels as $channel) {
                $post->notifications()->create(['channel' => $channel]);
            }

            
            if ($dto->status === PostStatus::PUBLISHED->value && ! empty($dto->notificationChannels)) {
                $this->sendNotifications($post);
            }

            return $post->load(['author', 'audiences', 'notifications']);
        });
    }

    
    public function updatePost(Post $post, UpdatePostDTO $dto, int $editorId): Post
    {
        return DB::transaction(function () use ($post, $dto, $editorId) {
            $updateData = ['last_editor_id' => $editorId];

            
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

            
            $this->repository->update($post, $updateData);

            
            if (! ($dto->audiences instanceof \Spatie\LaravelData\Optional)) {
                $post->audiences()->delete();
                foreach ($dto->audiences as $role) {
                    $post->audiences()->create(['role' => $role]);
                }
            }

            
            if (! ($dto->notificationChannels instanceof \Spatie\LaravelData\Optional)) {
                $post->notifications()->delete();
                foreach ($dto->notificationChannels as $channel) {
                    $post->notifications()->create(['channel' => $channel]);
                }
            }

            
            if (! empty($dto->resendNotificationChannels) && $post->status === PostStatus::PUBLISHED) {
                $this->sendNotifications($post, $dto->resendNotificationChannels);
            }

            return $post->fresh(['author', 'lastEditor', 'audiences', 'notifications']);
        });
    }

    
    public function deletePost(Post $post): bool
    {
        return $this->repository->delete($post);
    }

    
    public function restorePost(Post $post): bool
    {
        return $this->repository->restore($post);
    }

    
    public function forceDeletePost(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            
            $post->clearMediaCollection('images');

            return $this->repository->forceDelete($post);
        });
    }

    
    public function publishPost(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $this->repository->update($post, [
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now(),
                'scheduled_at' => null,
            ]);

            
            $this->sendNotifications($post);

            return $post->fresh(['author', 'audiences', 'notifications']);
        });
    }

    
    public function unpublishPost(Post $post): Post
    {
        $this->repository->update($post, [
            'status' => PostStatus::DRAFT->value,
            'published_at' => null,
        ]);

        return $post->fresh();
    }

    
    public function schedulePost(Post $post, string $scheduledAt): Post
    {
        $this->repository->update($post, [
            'status' => PostStatus::SCHEDULED->value,
            'scheduled_at' => $scheduledAt,
            'published_at' => null,
        ]);

        return $post->fresh();
    }

    
    public function publishScheduledPost(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $this->repository->update($post, [
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now(),
            ]);

            
            $this->sendNotifications($post);

            Log::info('Published scheduled post', [
                'post_uuid' => $post->uuid,
                'post_title' => $post->title,
            ]);

            return $post->fresh(['author', 'audiences', 'notifications']);
        });
    }

    
    public function cancelSchedule(Post $post): Post
    {
        $this->repository->update($post, [
            'status' => PostStatus::DRAFT->value,
            'scheduled_at' => null,
        ]);

        return $post->fresh();
    }

    
    public function togglePin(Post $post): Post
    {
        $this->repository->update($post, [
            'is_pinned' => ! $post->is_pinned,
        ]);

        return $post->fresh();
    }

    
    public function markAsViewed(Post $post, int $userId): void
    {
        try {
            
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
            
            
            Log::debug('View already recorded', [
                'post_id' => $post->id,
                'user_id' => $userId,
            ]);
        }
    }

    
    public function uploadImage(Post $post, UploadedFile $file): string
    {
        $media = $post->addMedia($file)
            ->toMediaCollection('images');

        return $media->getUrl();
    }

    
    public function bulkDelete(array $postUuids): void
    {
        BulkDeletePostsJob::dispatch($postUuids);

        Log::info('Bulk delete job dispatched', [
            'post_count' => count($postUuids),
        ]);
    }

    
    public function bulkPublish(array $postUuids): void
    {
        BulkPublishPostsJob::dispatch($postUuids);

        Log::info('Bulk publish job dispatched', [
            'post_count' => count($postUuids),
        ]);
    }

    
    private function sendNotifications(Post $post, ?array $specificChannels = null): void
    {
        
        $channels = $specificChannels ?? $post->notifications->pluck('channel')->toArray();

        if (empty($channels)) {
            return;
        }

        
        $audiences = $post->audiences->pluck('role')->toArray();

        if (empty($audiences)) {
            return;
        }

        
        SendPostNotificationJob::dispatch($post, $channels, $audiences);

        Log::info('Post notification job dispatched', [
            'post_uuid' => $post->uuid,
            'channels' => $channels,
            'audiences' => $audiences,
        ]);
    }
}
