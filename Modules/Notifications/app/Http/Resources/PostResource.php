<?php

namespace Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'category' => [
                'value' => $this->category->value,
                'label' => $this->category->label(),
                'icon' => $this->category->icon(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
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
            'view_count' => $this->when(
                $this->relationLoaded('views'),
                fn () => $this->views->count()
            ),
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
