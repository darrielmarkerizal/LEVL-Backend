<?php

namespace Modules\Schemes\DTOs;

use App\Support\BaseDTO;

final class UpdateCourseDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $categoryId = null,
        public readonly ?string $levelTag = null,
        public readonly ?string $type = null,
        public readonly ?array $tags = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            categoryId: $data['category_id'] ?? null,
            levelTag: $data['level_tag'] ?? null,
            type: $data['type'] ?? null,
            tags: $data['tags'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'level_tag' => $this->levelTag,
            'type' => $this->type,
            'tags' => $this->tags,
        ];
    }
}
