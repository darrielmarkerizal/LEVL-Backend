<?php

namespace Modules\Forums\DTOs;

use App\Support\BaseDTO;

final class CreateThreadDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly ?int $courseId = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            courseId: $data['course_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'course_id' => $this->courseId,
        ];
    }
}
