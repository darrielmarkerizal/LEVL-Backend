<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;


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
