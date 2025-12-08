<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateThreadDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional|null $title,

        public string|Optional|null $content,
    ) {}

    public function toModelArray(): array
    {
        $data = [];

        if (! $this->title instanceof Optional) {
            $data['title'] = $this->title;
        }
        if (! $this->content instanceof Optional) {
            $data['content'] = $this->content;
        }

        return $data;
    }
}
