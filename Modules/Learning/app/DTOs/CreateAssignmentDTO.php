<?php

namespace Modules\Learning\DTOs;

use App\Support\BaseDTO;

final class CreateAssignmentDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly int $lessonId,
        public readonly ?string $submissionType = 'file',
        public readonly ?int $maxScore = 100,
        public readonly ?\DateTimeInterface $dueDate = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            lessonId: (int) $data['lesson_id'],
            submissionType: $data['submission_type'] ?? 'file',
            maxScore: isset($data['max_score']) ? (int) $data['max_score'] : 100,
            dueDate: isset($data['due_date']) ? new \DateTime($data['due_date']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'lesson_id' => $this->lessonId,
            'submission_type' => $this->submissionType,
            'max_score' => $this->maxScore,
            'due_date' => $this->dueDate?->format('Y-m-d H:i:s'),
        ];
    }
}
