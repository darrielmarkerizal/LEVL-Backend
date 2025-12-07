<?php

namespace Modules\Schemes\DTOs;

use App\Support\BaseDTO;

final class CreateLessonDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $contentType = null,
        public readonly ?int $order = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            contentType: $data['content_type'] ?? null,
            order: $data['order'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->contentType,
            'order' => $this->order,
        ];
    }
}
