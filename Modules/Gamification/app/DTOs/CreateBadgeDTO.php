<?php

namespace Modules\Gamification\DTOs;

use App\Support\BaseDTO;

final class CreateBadgeDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $type,
        public readonly ?int $requiredValue = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            name: $data['name'],
            description: $data['description'],
            type: $data['type'],
            requiredValue: isset($data['required_value']) ? (int) $data['required_value'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'required_value' => $this->requiredValue,
        ];
    }
}
