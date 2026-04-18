<?php

declare(strict_types=1);

namespace Modules\Notifications\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Boolean;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreatePostDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        #[Required]
        public string $content,

        #[Required]
        public string $category,

        #[Required]
        public string $status,

        #[Required, ArrayType]
        public array $audiences,

        #[ArrayType]
        public array $notificationChannels = [],

        #[Boolean]
        #[MapInputName('is_pinned')]
        public bool $isPinned = false,

        #[Date]
        #[MapInputName('scheduled_at')]
        public ?string $scheduledAt = null,
    ) {}
}
