<?php

namespace Modules\Schemes\DTOs;

use App\Support\BaseDTO;

final class UpdateUnitDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $order = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            order: $data['order'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'order' => $this->order,
        ];
    }
}
