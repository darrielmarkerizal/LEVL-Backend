<?php

namespace Modules\Gamification\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateBadgeDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional $name,

        public string|Optional $description,

        public string|Optional $type,

        #[MapInputName('required_value'), Min(0)]
        public int|Optional|null $requiredValue,
    ) {}

    public function toModelArray(): array
    {
        $data = [];

        if (! $this->name instanceof Optional) {
            $data['name'] = $this->name;
        }
        if (! $this->description instanceof Optional) {
            $data['description'] = $this->description;
        }
        if (! $this->type instanceof Optional) {
            $data['type'] = $this->type;
        }
        if (! $this->requiredValue instanceof Optional) {
            $data['required_value'] = $this->requiredValue;
        }

        return $data;
    }
}
