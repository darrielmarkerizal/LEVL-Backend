<?php

namespace Modules\Content\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateNewsDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        #[Required]
        public string $content,

        public ?string $slug = null,

        public ?string $excerpt = null,

        public ?string $status = 'draft',

        #[MapInputName('is_featured')]
        public bool $isFeatured = false,

        #[MapInputName('category_ids')]
        public array $categoryIds = [],

        #[MapInputName('tag_ids')]
        public array $tagIds = [],
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'category_ids' => $this->categoryIds,
            'tag_ids' => $this->tagIds,
        ];
    }
}
