<?php

namespace App\Support;

use App\Contracts\EnrollmentKeyHasherInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnrollmentKeyHasher implements EnrollmentKeyHasherInterface
{
    public function hash(string $plainKey): string
    {
        return Hash::make($plainKey);
    }

    public function verify(string $plainKey, string $hashedKey): bool
    {
        return Hash::check($plainKey, $hashedKey);
    }

    public function generate(int $length = 12): string
    {
        return strtoupper(Str::random($length));
    }
}
