<?php

namespace Modules\Forums\DTOs;

use App\Support\BaseDTO;

final class UpdateReplyDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $content = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            content: $data['content'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
