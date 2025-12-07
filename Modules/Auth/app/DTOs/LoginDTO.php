<?php

namespace Modules\Auth\DTOs;

use App\Support\BaseDTO;

final class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $login,
        public readonly string $password,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            login: $data['login'],
            password: $data['password'],
        );
    }

    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
        ];
    }
}
