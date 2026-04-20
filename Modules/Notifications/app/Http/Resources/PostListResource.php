<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Notifications\Enums\PostCategory;
use Modules\Notifications\Enums\PostStatus;

class PostListResource extends JsonResource
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
            'author_name' => $this->whenLoaded('author', fn () => $this->author->name),
            'last_editor' => $this->whenLoaded('lastEditor', function () {
                return $this->lastEditor ? [
                    'id' => $this->lastEditor->id,
                    'name' => $this->lastEditor->name,
                ] : null;
            }),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
