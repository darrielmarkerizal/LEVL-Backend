<?php

declare(strict_types=1);

namespace Modules\Schemes\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateLessonDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional|null $title,

        public string|Optional|null $description,

        #[MapInputName('content_type')]
        public string|Optional|null $contentType,

        #[Min(0)]
        public int|Optional|null $order,
    ) {}

    public function toModelArray(): array
    {
        $data = [];

        if (! $this->title instanceof Optional) {
            $data['title'] = $this->title;
        }
        if (! $this->description instanceof Optional) {
            $data['description'] = $this->description;
        }
        if (! $this->contentType instanceof Optional) {
            $data['content_type'] = $this->contentType;
        }
        if (! $this->order instanceof Optional) {
            $data['order'] = $this->order;
        }

        return $data;
    }
}
