<?php

namespace App\Contracts;

/**
 * Interface for enrollment key hashing operations.
 *
 * Provides secure hashing, verification, and generation of enrollment keys
 * to protect sensitive course access codes from plain text storage.
 */
interface EnrollmentKeyHasherInterface
{
    /**
     * Hash a plain text enrollment key.
     *
     * @param  string  $plainKey  The plain text enrollment key to hash
     * @return string The hashed enrollment key
     */
    public function hash(string $plainKey): string;

    /**
     * Verify a plain text key against a hashed key.
     *
     * @param  string  $plainKey  The plain text enrollment key to verify
     * @param  string  $hashedKey  The hashed enrollment key to compare against
     * @return bool True if the plain key matches the hash, false otherwise
     */
    public function verify(string $plainKey, string $hashedKey): bool;

    /**
     * Generate a new random enrollment key.
     *
     * @param  int  $length  The length of the key to generate (default: 12)
     * @return string The generated plain text enrollment key
     */
    public function generate(int $length = 12): string;
}
