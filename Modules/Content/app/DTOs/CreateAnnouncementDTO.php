<?php

namespace Modules\Content\DTOs;

use App\Support\BaseDTO;

final class CreateAnnouncementDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $status = 'draft',
        public readonly ?string $priority = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            status: $data['status'] ?? 'draft',
            priority: $data['priority'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'priority' => $this->priority,
        ];
    }
}
