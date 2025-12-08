<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateThreadDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        #[Required]
        public string $content,

        #[MapInputName('course_id')]
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
