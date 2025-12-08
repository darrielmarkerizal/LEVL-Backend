<?php

namespace Modules\Schemes\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateCourseDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional|null $title,

        public string|Optional|null $description,

        #[MapInputName('category_id')]
        public int|Optional|null $categoryId,

        #[MapInputName('level_tag')]
        public string|Optional|null $levelTag,

        public string|Optional|null $type,

        public array|Optional|null $tags,
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
        if (! $this->categoryId instanceof Optional) {
            $data['category_id'] = $this->categoryId;
        }
        if (! $this->levelTag instanceof Optional) {
            $data['level_tag'] = $this->levelTag;
        }
        if (! $this->type instanceof Optional) {
            $data['type'] = $this->type;
        }
        if (! $this->tags instanceof Optional) {
            $data['tags'] = $this->tags;
        }

        return $data;
    }
}
