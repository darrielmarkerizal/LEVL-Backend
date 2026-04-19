<?php

namespace App\Support\Helpers;


class ArrayParser
{
    
    public static function parseFilter($value): array
    {
        
        if (is_array($value)) {
            return $value;
        }

        
        if (is_string($value)) {
            $trim = trim($value);

            
            if ($trim === '') {
                return [];
            }

            
            if ($trim[0] === '[' || str_starts_with($trim, '%5B')) {
                
                $decoded = json_decode($trim, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }

                
                $urldecoded = urldecode($trim);
                $decoded = json_decode($urldecoded, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            
            return [$trim];
        }

        
        return [];
    }

    
    public static function parseCommaSeparated($value, bool $trimValues = true): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $parts = explode(',', $value);

        if ($trimValues) {
            return collect($parts)->map(fn ($item) => trim($item))->all();
        }

        return $parts;
    }

    
    public static function ensureArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        return [$value];
    }

    
    public static function parsePipeSeparated($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return explode('|', $value);
    }
}
