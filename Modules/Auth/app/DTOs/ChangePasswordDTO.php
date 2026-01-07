<?php

declare(strict_types=1);


namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class ChangePasswordDTO extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('current_password')]
        public string $currentPassword,

        #[Required, Min(8)]
        #[MapInputName('new_password')]
        public string $newPassword,
    ) {}
}
