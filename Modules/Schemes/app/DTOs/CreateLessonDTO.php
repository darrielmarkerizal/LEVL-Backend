<?php

declare(strict_types=1);

namespace Modules\Schemes\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateLessonDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        public ?string $description = null,

        #[MapInputName('content_type')]
        public ?string $contentType = null,

        #[Min(0)]
        public ?int $order = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->contentType,
            'order' => $this->order,
        ];
    }
}
