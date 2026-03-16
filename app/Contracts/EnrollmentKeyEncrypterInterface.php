<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface for enrollment key encryption/decryption
 * 
 * This interface defines methods for encrypting and decrypting enrollment keys.
 * Unlike hashing, encryption is reversible, allowing authorized users to view the original key.
 */
interface EnrollmentKeyEncrypterInterface
{
    /**
     * Encrypt an enrollment key
     *
     * @param string $plainKey The plain text enrollment key
     * @return string The encrypted key
     */
    public function encrypt(string $plainKey): string;

    /**
     * Decrypt an enrollment key
     *
     * @param string $encryptedKey The encrypted enrollment key
     * @return string The decrypted plain text key
     * @throws \Illuminate\Contracts\Encryption\DecryptException
     */
    public function decrypt(string $encryptedKey): string;

    /**
     * Verify if a plain key matches an encrypted key
     *
     * @param string $plainKey The plain text key to verify
     * @param string $encryptedKey The encrypted key to compare against
     * @return bool True if the keys match, false otherwise
     */
    public function verify(string $plainKey, string $encryptedKey): bool;
}
