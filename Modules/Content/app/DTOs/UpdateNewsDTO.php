<?php

namespace Modules\Content\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateNewsDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional|null $title,

        public string|Optional|null $content,

        public string|Optional|null $excerpt,

        #[MapInputName('is_featured')]
        public bool|Optional|null $isFeatured,

        #[MapInputName('category_ids')]
        public array|Optional|null $categoryIds,

        #[MapInputName('tag_ids')]
        public array|Optional|null $tagIds,
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
        if (! $this->excerpt instanceof Optional) {
            $data['excerpt'] = $this->excerpt;
        }
        if (! $this->isFeatured instanceof Optional) {
            $data['is_featured'] = $this->isFeatured;
        }
        if (! $this->categoryIds instanceof Optional) {
            $data['category_ids'] = $this->categoryIds;
        }
        if (! $this->tagIds instanceof Optional) {
            $data['tag_ids'] = $this->tagIds;
        }

        return $data;
    }
}
