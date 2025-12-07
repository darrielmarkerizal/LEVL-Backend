<?php

namespace Modules\Learning\DTOs;

use App\Support\BaseDTO;

final class CreateSubmissionDTO extends BaseDTO
{
    public function __construct(
        public readonly int $assignmentId,
        public readonly ?string $content = null,
        public readonly ?array $files = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            assignmentId: (int) $data['assignment_id'],
            content: $data['content'] ?? null,
            files: $data['files'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'assignment_id' => $this->assignmentId,
            'content' => $this->content,
            'files' => $this->files,
        ];
    }
}
