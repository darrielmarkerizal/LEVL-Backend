<?php

namespace Modules\Content\DTOs;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class CreateAnnouncementDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        #[Required]
        public string $content,

        public ?string $status = 'draft',

        public ?string $priority = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'priority' => $this->priority,
        ];
    }
}
