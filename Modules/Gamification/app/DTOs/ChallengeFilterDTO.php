<?php

namespace Modules\Gamification\DTOs;

use App\Support\BaseDTO;

final class ChallengeFilterDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?int $courseId = null,
        public readonly ?bool $active = null,
        public readonly ?string $sort = null,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            type: $data['type'] ?? $data['filter']['type'] ?? null,
            courseId: isset($data['course_id']) ? (int) $data['course_id'] : (isset($data['filter']['course_id']) ? (int) $data['filter']['course_id'] : null),
            active: isset($data['active']) ? (bool) $data['active'] : null,
            sort: $data['sort'] ?? null,
            perPage: max(1, min(100, (int) ($data['per_page'] ?? 15))),
            page: max(1, (int) ($data['page'] ?? 1)),
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'course_id' => $this->courseId,
            'active' => $this->active,
            'sort' => $this->sort,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }
}
