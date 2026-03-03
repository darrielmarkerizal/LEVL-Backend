<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Data;

final class CreateThreadDTO extends Data
{
    public function __construct(

        public string $title,

        public string $content,

        public ?int $courseId = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'course_id' => $this->courseId,
        ];
    }
}
