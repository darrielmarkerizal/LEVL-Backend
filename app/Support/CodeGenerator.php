<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CodeGenerator
{
    public static function generate(
        string $prefix,
        int $length,
        string $modelClass,
        string $column = 'code',
        int $maxAttempts = 10
    ): string {
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} does not exist.");
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("Class {$modelClass} must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        $attempts = 0;

        do {
            $code = $prefix.strtoupper(Str::random($length));
            $exists = $modelClass::where($column, $code)->exists();
            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException(
                    "Unable to generate unique code after {$maxAttempts} attempts. ".
                    'Consider increasing code length or reducing prefix length.'
                );
            }
        } while ($exists);

        return $code;
    }

    public static function generateNumeric(
        string $prefix,
        int $length,
        string $modelClass,
        string $column = 'code',
        int $maxAttempts = 10
    ): string {
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} does not exist.");
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("Class {$modelClass} must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        $attempts = 0;

        do {
            $randomNumber = str_pad(
                (string) random_int(0, (int) str_repeat('9', $length)),
                $length,
                '0',
                STR_PAD_LEFT
            );
            $code = $prefix.$randomNumber;
            $exists = $modelClass::where($column, $code)->exists();
            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException(
                    "Unable to generate unique numeric code after {$maxAttempts} attempts."
                );
            }
        } while ($exists);

        return $code;
    }

    
    public static function generateSequential(
        string $prefix,
        string $modelClass,
        int $padding = 6,
        string $column = 'code'
    ): string {
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} does not exist.");
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("Class {$modelClass} must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        
        $lastRecord = $modelClass::where($column, 'LIKE', $prefix.'%')
            ->orderBy($column, 'desc')
            ->first();

        if (! $lastRecord) {
            
            $number = 1;
        } else {
            
            $lastCode = $lastRecord->{$column};
            $lastNumber = (int) str_replace($prefix, '', $lastCode);
            $number = $lastNumber + 1;
        }

        return $prefix.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
    }

    public static function generateWithDate(
        string $prefix,
        string $dateFormat = 'Ymd',
        int $length = 4,
        ?string $modelClass = null,
        string $column = 'code'
    ): string {
        $datePart = date($dateFormat);
        $randomPart = strtoupper(Str::random($length));
        $code = $prefix.$datePart.'-'.$randomPart;

        
        if ($modelClass !== null) {
            if (! class_exists($modelClass)) {
                throw new \InvalidArgumentException("Model class {$modelClass} does not exist.");
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                throw new \InvalidArgumentException("Class {$modelClass} must extend Illuminate\\Database\\Eloquent\\Model.");
            }

            $attempts = 0;
            $maxAttempts = 10;

            while ($modelClass::where($column, $code)->exists()) {
                $randomPart = strtoupper(Str::random($length));
                $code = $prefix.$datePart.'-'.$randomPart;
                $attempts++;

                if ($attempts >= $maxAttempts) {
                    throw new \RuntimeException('Unable to generate unique code with date.');
                }
            }
        }

        return $code;
    }
}
