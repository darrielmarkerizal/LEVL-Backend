<?php

namespace Modules\Schemes\DTOs;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class CreateUnitDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        public ?string $description = null,

        #[Min(0)]
        public ?int $order = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'order' => $this->order,
        ];
    }
}
