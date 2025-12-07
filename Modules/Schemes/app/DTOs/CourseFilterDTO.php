<?php

namespace Modules\Schemes\DTOs;

use App\Support\BaseDTO;

final class CourseFilterDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $levelTag = null,
        public readonly ?string $type = null,
        public readonly ?int $categoryId = null,
        public readonly ?string $tag = null,
        public readonly ?string $search = null,
        public readonly ?string $sort = null,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
    ) {}

    public static function fromRequest(array $data): static
    {
        $filter = $data['filter'] ?? [];

        return new self(
            status: $filter['status'] ?? null,
            levelTag: $filter['level_tag'] ?? $filter['level'] ?? null,
            type: $filter['type'] ?? null,
            categoryId: isset($filter['category_id']) ? (int) $filter['category_id'] : null,
            tag: $filter['tag'] ?? null,
            search: $data['search'] ?? null,
            sort: $data['sort'] ?? null,
            perPage: (int) ($data['per_page'] ?? 15),
            page: (int) ($data['page'] ?? 1),
        );
    }

    public function toArray(): array
    {
        return [
            'filter' => array_filter([
                'status' => $this->status,
                'level_tag' => $this->levelTag,
                'type' => $this->type,
                'category_id' => $this->categoryId,
                'tag' => $this->tag,
            ], fn ($v) => $v !== null),
            'search' => $this->search,
            'sort' => $this->sort,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }
}
