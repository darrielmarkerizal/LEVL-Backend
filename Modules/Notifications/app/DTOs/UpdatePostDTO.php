<?php

declare(strict_types=1);

namespace Modules\Notifications\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Boolean;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdatePostDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional $title = new Optional,

        public string|Optional $content = new Optional,

        public string|Optional $category = new Optional,

        public string|Optional $status = new Optional,

        #[ArrayType]
        public array|Optional $audiences = new Optional,

        #[ArrayType]
        public array|Optional $notificationChannels = new Optional,

        #[Boolean]
        #[MapInputName('is_pinned')]
        public bool|Optional $isPinned = new Optional,

        #[ArrayType]
        #[MapInputName('resend_notification_channels')]
        public array $resendNotificationChannels = [],
    ) {}
}
