<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CodeGenerator
{
    public static function generate(
        string $prefix,
        int $length,
        string $modelClass,
        string $column = 'code',
        int $maxAttempts = 10
    ): string {
        $model = self::instantiateModel($modelClass);

        $attempts = 0;

        do {
            $code = $prefix.self::randomUpperString($length);
            $exists = self::tableQuery($model)->where($column, $code)->exists();
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
        $model = self::instantiateModel($modelClass);

        $attempts = 0;

        do {
            $randomNumber = str_pad(
                (string) random_int(0, (int) str_repeat('9', $length)),
                $length,
                '0',
                STR_PAD_LEFT
            );
            $code = $prefix.$randomNumber;
            $exists = self::tableQuery($model)->where($column, $code)->exists();
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
        $model = self::instantiateModel($modelClass);

        
        $query = self::tableQuery($model)->where($column, 'LIKE', $prefix.'%')
            ->orderByDesc($column);

        $lastRecord = $query->value($column);

        if ($lastRecord === null) {
            $number = 1;
        } else {
            if (! is_string($lastRecord)) {
                throw new \RuntimeException("Last code value in column {$column} must be a string.");
            }
            $lastNumber = (int) str_replace($prefix, '', $lastRecord);
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
        $randomPart = self::randomUpperString($length);
        $code = $prefix.$datePart.'-'.$randomPart;

        if ($modelClass !== null) {
            $model = self::instantiateModel($modelClass);

            $attempts = 0;
            $maxAttempts = 10;

            while (self::tableQuery($model)->where($column, $code)->exists()) {
                $randomPart = self::randomUpperString($length);
                $code = $prefix.$datePart.'-'.$randomPart;
                $attempts++;

                if ($attempts >= $maxAttempts) {
                    throw new \RuntimeException('Unable to generate unique code with date.');
                }
            }
        }

        return $code;
    }

    private static function instantiateModel(string $modelClass): Model
    {
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} does not exist.");
        }

        $model = new $modelClass;
        if (! $model instanceof Model) {
            throw new \InvalidArgumentException("Class {$modelClass} must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        return $model;
    }

    private static function randomUpperString(int $length): string
    {
        $random = Str::random($length);
        if (! is_string($random)) {
            throw new \RuntimeException('Unable to generate random string.');
        }

        return strtoupper($random);
    }

    private static function tableQuery(Model $model): Builder
    {
        $table = $model->getTable();
        if (! is_string($table) || $table === '') {
            throw new \RuntimeException('Model table name must be a non-empty string.');
        }

        $query = DB::table($table);
        if (! $query instanceof Builder) {
            throw new \RuntimeException('Unable to create database query builder.');
        }

        return $query;
    }
}
