<?php

declare(strict_types=1);

namespace Modules\Schemes\DTOs;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateUnitDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional|null $title,

        public string|Optional|null $description,

        #[Min(0)]
        public int|Optional|null $order,
    ) {}

    public function toModelArray(): array
    {
        $data = [];

        if (! $this->title instanceof Optional) {
            $data['title'] = $this->title;
        }
        if (! $this->description instanceof Optional) {
            $data['description'] = $this->description;
        }
        if (! $this->order instanceof Optional) {
            $data['order'] = $this->order;
        }

        return $data;
    }
}
