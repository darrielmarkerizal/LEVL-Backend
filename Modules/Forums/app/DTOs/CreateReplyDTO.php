<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateReplyDTO extends Data
{
    public function __construct(
        #[Required]
        public string $content,

        #[Required]
        #[MapInputName('thread_id')]
        public int $threadId,

        #[MapInputName('parent_id')]
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
