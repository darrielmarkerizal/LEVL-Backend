<?php

namespace Modules\Content\DTOs;

use App\Support\BaseDTO;

final class UpdateNewsDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $content = null,
        public readonly ?string $excerpt = null,
        public readonly ?bool $isFeatured = null,
        public readonly ?array $categoryIds = null,
        public readonly ?array $tagIds = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            isFeatured: isset($data['is_featured']) ? (bool) $data['is_featured'] : null,
            categoryIds: $data['category_ids'] ?? null,
            tagIds: $data['tag_ids'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'is_featured' => $this->isFeatured,
            'category_ids' => $this->categoryIds,
            'tag_ids' => $this->tagIds,
        ];
    }
}
