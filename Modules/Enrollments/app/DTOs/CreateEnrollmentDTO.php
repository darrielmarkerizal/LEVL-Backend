<?php

namespace Modules\Enrollments\DTOs;

use App\Support\BaseDTO;

final class CreateEnrollmentDTO extends BaseDTO
{
    public function __construct(
        public readonly int $courseId,
        public readonly ?string $enrollmentKey = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            courseId: $data['course_id'],
            enrollmentKey: $data['enrollment_key'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'course_id' => $this->courseId,
            'enrollment_key' => $this->enrollmentKey,
        ];
    }
}
