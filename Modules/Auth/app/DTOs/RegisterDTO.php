<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class RegisterDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,

        #[Required, Max(255)]
        public string $username,

        #[Required, Email, Max(255)]
        public string $email,

        #[Required, Min(8)]
        public string $password,
    ) {}
}
