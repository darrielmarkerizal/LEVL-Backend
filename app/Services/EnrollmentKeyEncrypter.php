<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EnrollmentKeyEncrypterInterface;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;


class EnrollmentKeyEncrypter implements EnrollmentKeyEncrypterInterface
{
    
    public function encrypt(string $plainKey): string
    {
        return Crypt::encryptString($plainKey);
    }

    
    public function decrypt(string $encryptedKey): string
    {
        try {
            return Crypt::decryptString($encryptedKey);
        } catch (DecryptException $e) {
            throw $e;
        }
    }

    
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
