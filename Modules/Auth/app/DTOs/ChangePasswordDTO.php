<?php

namespace Modules\Auth\DTOs;

use App\Support\BaseDTO;

final class ChangePasswordDTO extends BaseDTO
{
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            currentPassword: $data['current_password'],
            newPassword: $data['new_password'],
        );
    }

    public function toArray(): array
    {
        return [
            'current_password' => $this->currentPassword,
            'new_password' => $this->newPassword,
        ];
    }
}
