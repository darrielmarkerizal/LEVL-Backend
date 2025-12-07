<?php

namespace Modules\Forums\DTOs;

use App\Support\BaseDTO;

final class UpdateThreadDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $content = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}
