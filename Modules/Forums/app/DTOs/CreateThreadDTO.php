<?php

namespace Modules\Forums\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;


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
