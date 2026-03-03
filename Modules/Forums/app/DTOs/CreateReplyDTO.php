<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Data;

final class CreateReplyDTO extends Data
{
    public function __construct(

        public string $content,

        public int $threadId,

        public ?int $parentId = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'content' => $this->content,
            'thread_id' => $this->threadId,
            'parent_id' => $this->parentId,
        ];
    }
}
