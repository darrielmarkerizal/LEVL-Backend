<?php

namespace Modules\Content\DTOs;

use App\Support\BaseDTO;

final class CreateNewsDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $slug = null,
        public readonly ?string $excerpt = null,
        public readonly ?string $featuredImagePath = null,
        public readonly ?string $status = 'draft',
        public readonly bool $isFeatured = false,
        public readonly array $categoryIds = [],
        public readonly array $tagIds = [],
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            slug: $data['slug'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            featuredImagePath: $data['featured_image_path'] ?? null,
            status: $data['status'] ?? 'draft',
            isFeatured: (bool) ($data['is_featured'] ?? false),
            categoryIds: $data['category_ids'] ?? [],
            tagIds: $data['tag_ids'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'featured_image_path' => $this->featuredImagePath,
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'category_ids' => $this->categoryIds,
            'tag_ids' => $this->tagIds,
        ];
    }
}
