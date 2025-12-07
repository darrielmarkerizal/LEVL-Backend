<?php

namespace Modules\Gamification\DTOs;

use App\Support\BaseDTO;

final class CreateChallengeDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $type,
        public readonly int $xpReward,
        public readonly ?int $courseId = null,
        public readonly ?string $criteriaType = null,
        public readonly ?int $criteriaValue = null,
        public readonly ?\DateTimeInterface $startDate = null,
        public readonly ?\DateTimeInterface $endDate = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            type: $data['type'],
            xpReward: (int) $data['xp_reward'],
            courseId: isset($data['course_id']) ? (int) $data['course_id'] : null,
            criteriaType: $data['criteria_type'] ?? null,
            criteriaValue: isset($data['criteria_value']) ? (int) $data['criteria_value'] : null,
            startDate: isset($data['start_date']) ? new \DateTime($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new \DateTime($data['end_date']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'xp_reward' => $this->xpReward,
            'course_id' => $this->courseId,
            'criteria_type' => $this->criteriaType,
            'criteria_value' => $this->criteriaValue,
            'start_date' => $this->startDate?->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate?->format('Y-m-d H:i:s'),
        ];
    }
}
