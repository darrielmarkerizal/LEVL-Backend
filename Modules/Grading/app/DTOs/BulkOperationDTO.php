<?php

declare(strict_types=1);

namespace Modules\Grading\DTOs;

use Spatie\LaravelData\Data;

class BulkOperationDTO extends Data
{
    public function __construct(
        public array $submissionIds,
        public ?string $feedback = null,
        public ?int $performerId = null,
        public bool $async = false
    ) {}
}
