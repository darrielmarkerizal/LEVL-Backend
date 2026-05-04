<?php

declare(strict_types=1);

namespace Modules\Notifications\Services;

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
use Modules\Notifications\Models\Notification;
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
                $roleValue = is_string($role) ? $role : (is_object($role) && property_exists($role, 'value') ? $role->value : $role);
                $post->audiences()->create(['role' => $roleValue]);
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
                    $roleValue = is_string($role) ? $role : (is_object($role) && property_exists($role, 'value') ? $role->value : $role);
                    $post->audiences()->create(['role' => $roleValue]);
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
        $this->deletePostNotifications($post);

        return $this->repository->delete($post);
    }

    
    private function deletePostNotifications(Post $post): void
    {
        
        $notifications = Notification::query()
            ->whereJsonContains('data->post_id', $post->id)
            ->orWhereJsonContains('data->post_uuid', $post->uuid)
            ->get();

        foreach ($notifications as $notification) {
            
            $notification->users()->detach();
            
            $notification->delete();
        }

        Log::info('Deleted notifications for post', [
            'post_uuid' => $post->uuid,
            'post_id' => $post->id,
            'notifications_deleted' => $notifications->count(),
        ]);
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

        
        $audiences = $post->audiences->map(function ($audience) {
            $role = $audience->role;
            return is_object($role) && property_exists($role, 'value') ? $role->value : $role;
        })->toArray();

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
