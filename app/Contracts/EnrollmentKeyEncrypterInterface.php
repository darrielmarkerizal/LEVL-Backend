<?php

declare(strict_types=1);

namespace App\Contracts;


interface EnrollmentKeyEncrypterInterface
{
    
    public function encrypt(string $plainKey): string;

    
    public function decrypt(string $encryptedKey): string;

    
    public function verify(string $plainKey, string $encryptedKey): bool;
}
