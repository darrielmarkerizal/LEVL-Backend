<?php

namespace Modules\Enrollments\DTOs;

use App\Support\BaseDTO;

final class EnrollmentFilterDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?int $courseId = null,
        public readonly ?int $userId = null,
        public readonly ?string $courseSlug = null,
        public readonly ?string $sort = null,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            status: $data['status'] ?? $data['filter']['status'] ?? null,
            courseId: isset($data['course_id']) ? (int) $data['course_id'] : (isset($data['filter']['course_id']) ? (int) $data['filter']['course_id'] : null),
            userId: isset($data['user_id']) ? (int) $data['user_id'] : (isset($data['filter']['user_id']) ? (int) $data['filter']['user_id'] : null),
            courseSlug: $data['course_slug'] ?? null,
            sort: $data['sort'] ?? null,
            perPage: max(1, min(100, (int) ($data['per_page'] ?? 15))),
            page: max(1, (int) ($data['page'] ?? 1)),
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'course_id' => $this->courseId,
            'user_id' => $this->userId,
            'course_slug' => $this->courseSlug,
            'sort' => $this->sort,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }

    public function toFilterArray(): array
    {
        return array_filter([
            'filter' => array_filter([
                'status' => $this->status,
                'course_id' => $this->courseId,
                'user_id' => $this->userId,
            ], fn ($v) => $v !== null),
            'sort' => $this->sort,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ], fn ($v) => $v !== null && $v !== []);
    }
}
