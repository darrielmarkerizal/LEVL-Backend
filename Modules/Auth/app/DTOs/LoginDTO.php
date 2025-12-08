<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class LoginDTO extends Data
{
    public function __construct(
        #[Required]
        public string $login,

        #[Required]
        public string $password,
    ) {}
}
