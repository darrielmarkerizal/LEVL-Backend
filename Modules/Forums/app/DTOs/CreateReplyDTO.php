<?php

namespace Modules\Forums\DTOs;

use App\Support\BaseDTO;

final class CreateReplyDTO extends BaseDTO
{
    public function __construct(
        public readonly string $content,
        public readonly int $threadId,
        public readonly ?int $parentId = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            content: $data['content'],
            threadId: $data['thread_id'],
            parentId: $data['parent_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'thread_id' => $this->threadId,
            'parent_id' => $this->parentId,
        ];
    }
}
