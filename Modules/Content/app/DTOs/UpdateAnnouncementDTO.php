<?php

namespace Modules\Content\DTOs;

use App\Support\BaseDTO;

final class UpdateAnnouncementDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $content = null,
        public readonly ?string $targetType = null,
        public readonly ?string $targetValue = null,
        public readonly ?string $priority = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            targetType: $data['target_type'] ?? null,
            targetValue: $data['target_value'] ?? null,
            priority: $data['priority'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'target_type' => $this->targetType,
            'target_value' => $this->targetValue,
            'priority' => $this->priority,
        ];
    }
}
