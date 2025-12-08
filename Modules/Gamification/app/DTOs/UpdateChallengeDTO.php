<?php

namespace Modules\Gamification\DTOs;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateChallengeDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional $title,

        public string|Optional $description,

        public string|Optional $type,

        #[Min(0)]
        public int|Optional $xpReward,

        public ?int $courseId = null,

        public ?string $criteriaType = null,

        public ?int $criteriaValue = null,

        public ?\DateTimeInterface $startDate = null,

        public ?\DateTimeInterface $endDate = null,
    ) {}
}
