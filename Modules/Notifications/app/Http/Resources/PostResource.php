<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Notifications\Enums\PostCategory;
use Modules\Notifications\Enums\PostStatus;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = $this->category instanceof PostCategory
            ? $this->category
            : PostCategory::tryFrom((string) $this->category);

        $status = $this->status instanceof PostStatus
            ? $this->status
            : PostStatus::tryFrom((string) $this->status);

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'category' => [
                'value' => $category?->value ?? (string) $this->category,
                'label' => $category?->label() ?? (string) $this->category,
                'icon' => $category?->icon() ?? '',
            ],
            'status' => [
                'value' => $status?->value ?? (string) $this->status,
                'label' => $status?->label() ?? (string) $this->status,
            ],
            'is_pinned' => $this->is_pinned,
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                ];
            }),
            'last_editor' => $this->whenLoaded('lastEditor', function () {
                return $this->lastEditor ? [
                    'id' => $this->lastEditor->id,
                    'name' => $this->lastEditor->name,
                ] : null;
            }),
            'audiences' => $this->whenLoaded('audiences', function () {
                return $this->audiences->map(function ($audience) {
                    return [
                        'role' => $audience->role->value,
                        'label' => $audience->role->label(),
                    ];
                });
            }),
            'notification_channels' => $this->whenLoaded('notifications', function () {
                return $this->notifications->map(function ($notification) {
                    return [
                        'channel' => $notification->channel,
                        'sent_at' => $notification->sent_at?->toIso8601String(),
                    ];
                });
            }),
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
