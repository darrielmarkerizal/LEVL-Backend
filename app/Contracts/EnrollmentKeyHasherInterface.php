<?php

namespace App\Contracts;


interface EnrollmentKeyHasherInterface
{
    
    public function hash(string $plainKey): string;

    
    public function verify(string $plainKey, string $hashedKey): bool;

    
    public function generate(int $length = 12): string;
}
