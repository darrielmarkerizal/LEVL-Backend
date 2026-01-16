<?php

declare(strict_types=1);

namespace Modules\Schemes\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateCourseDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        public ?string $description = null,

        #[MapInputName('category_id')]
        public ?int $categoryId = null,

        #[MapInputName('level_tag')]
        public ?string $levelTag = null,

        public ?string $type = null,

        public ?array $tags = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'level_tag' => $this->levelTag,
            'type' => $this->type,
            'tags' => $this->tags,
        ];
    }
}
