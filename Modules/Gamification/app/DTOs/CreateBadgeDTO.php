<?php

namespace Modules\Gamification\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateBadgeDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,

        #[Required]
        public string $description,

        #[Required]
        public string $type,

        #[MapInputName('required_value'), Min(0)]
        public ?int $requiredValue = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'required_value' => $this->requiredValue,
        ];
    }
}
