<?php

namespace Modules\Grading\DTOs;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateGradeDTO extends Data
{
    public function __construct(
        #[Min(0), Max(100)]
        public float|Optional $score,

        public string|Optional $feedback,

        public string|Optional $status,
    ) {}
}
