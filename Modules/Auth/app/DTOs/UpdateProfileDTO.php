<?php

namespace Modules\Auth\DTOs;

use App\Support\BaseDTO;
use Illuminate\Http\UploadedFile;

final class UpdateProfileDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $username = null,
        public readonly ?UploadedFile $avatar = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            name: $data['name'] ?? null,
            username: $data['username'] ?? null,
            avatar: $data['avatar'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'avatar' => $this->avatar,
        ];
    }
}
