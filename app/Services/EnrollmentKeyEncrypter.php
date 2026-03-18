<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EnrollmentKeyEncrypterInterface;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Service for encrypting and decrypting enrollment keys
 *
 * Uses Laravel's built-in encryption which uses AES-256-CBC cipher
 * with OpenSSL for secure encryption/decryption.
 */
class EnrollmentKeyEncrypter implements EnrollmentKeyEncrypterInterface
{
    /**
     * Encrypt an enrollment key
     */
    public function encrypt(string $plainKey): string
    {
        return Crypt::encryptString($plainKey);
    }

    /**
     * Decrypt an enrollment key
     *
     * @throws DecryptException
     */
    public function decrypt(string $encryptedKey): string
    {
        try {
            return Crypt::decryptString($encryptedKey);
        } catch (DecryptException $e) {
            throw $e;
        }
    }

    /**
     * Verify if a plain key matches an encrypted key
     */
    public function verify(string $plainKey, string $encryptedKey): bool
    {
        try {
            $decrypted = $this->decrypt($encryptedKey);

            return hash_equals($plainKey, $decrypted);
        } catch (DecryptException $e) {
            return false;
        }
    }
}
