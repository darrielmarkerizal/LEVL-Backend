<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateReplyDTO extends Data
{
    public function __construct(
        public string|Optional|null $content,
    ) {}

    public function toModelArray(): array
    {
        $data = [];

        if (! $this->content instanceof Optional) {
            $data['content'] = $this->content;
        }

        return $data;
    }
}
