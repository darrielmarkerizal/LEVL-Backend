<?php

namespace App\Support;

use App\Contracts\EnrollmentKeyHasherInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Service for secure enrollment key hashing operations.
 *
 * Uses Laravel's Hash facade (bcrypt by default) to securely hash
 * enrollment keys before storage and verify them during enrollment.
 */
class EnrollmentKeyHasher implements EnrollmentKeyHasherInterface
{
    /**
     * Hash a plain text enrollment key.
     *
     * @param  string  $plainKey  The plain text enrollment key to hash
     * @return string The hashed enrollment key
     */
    public function hash(string $plainKey): string
    {
        return Hash::make($plainKey);
    }

    /**
     * Verify a plain text key against a hashed key.
     *
     * @param  string  $plainKey  The plain text enrollment key to verify
     * @param  string  $hashedKey  The hashed enrollment key to compare against
     * @return bool True if the plain key matches the hash, false otherwise
     */
    public function verify(string $plainKey, string $hashedKey): bool
    {
        return Hash::check($plainKey, $hashedKey);
    }

    /**
     * Generate a new random enrollment key.
     *
     * Generates an uppercase alphanumeric key suitable for course enrollment.
     *
     * @param  int  $length  The length of the key to generate (default: 12)
     * @return string The generated plain text enrollment key
     */
    public function generate(int $length = 12): string
    {
        return strtoupper(Str::random($length));
    }
}
