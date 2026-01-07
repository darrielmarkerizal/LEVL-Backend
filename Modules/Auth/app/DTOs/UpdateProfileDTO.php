<?php

declare(strict_types=1);


namespace Modules\Auth\DTOs;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateProfileDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional|null $name,

        #[Max(255)]
        public string|Optional|null $username,

        public UploadedFile|Optional|null $avatar,
    ) {}

    public function toModelArray(): array
    {
        $data = [];

        if (! $this->name instanceof Optional && $this->name !== null) {
            $data['name'] = $this->name;
        }
        if (! $this->username instanceof Optional && $this->username !== null) {
            $data['username'] = $this->username;
        }

        return $data;
    }
}
