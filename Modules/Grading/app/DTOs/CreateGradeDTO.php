<?php

namespace Modules\Grading\DTOs;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class CreateGradeDTO extends Data
{
    public function __construct(
        #[Required]
        public int $submissionId,

        #[Required, Min(0), Max(100)]
        public float $score,

        public ?string $feedback = null,

        public ?string $status = 'graded',
    ) {}
}
