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

        public ?string $target_type = 'all',

        public ?string $target_value = null,

        public ?int $course_id = null,

        public ?string $scheduled_at = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'priority' => $this->priority,
            'target_type' => $this->target_type,
            'target_value' => $this->target_value,
            'course_id' => $this->course_id,
            'scheduled_at' => $this->scheduled_at,
        ];
    }
}
