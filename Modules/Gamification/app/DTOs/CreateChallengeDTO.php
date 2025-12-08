<?php

namespace Modules\Gamification\DTOs;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateChallengeDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        #[Required]
        public string $description,

        #[Required]
        public string $type,

        #[Required, Min(0)]
        #[MapInputName('xp_reward')]
        public int $xpReward,

        #[MapInputName('course_id')]
        public ?int $courseId = null,

        #[MapInputName('criteria_type')]
        public ?string $criteriaType = null,

        #[MapInputName('criteria_value')]
        public ?int $criteriaValue = null,

        #[MapInputName('start_date')]
        public ?Carbon $startDate = null,

        #[MapInputName('end_date')]
        public ?Carbon $endDate = null,
    ) {}

    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'xp_reward' => $this->xpReward,
            'course_id' => $this->courseId,
            'criteria_type' => $this->criteriaType,
            'criteria_value' => $this->criteriaValue,
            'start_date' => $this->startDate?->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate?->format('Y-m-d H:i:s'),
        ];
    }
}
