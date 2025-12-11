<?php

namespace App\Support;

use Spatie\LaravelData\Data;

/**
 * Base DTO class extending Spatie Laravel Data.
 * Provides common functionality for all DTOs in the application.
 */
abstract class BaseDataDTO extends Data
{
    /**
     * Get only non-null values as array.
     * Useful for update operations where only provided fields should be updated.
     */
    public function toArrayWithoutNull(): array
    {
        return array_filter($this->toArray(), fn ($value) => $value !== null);
    }

    /**
     * Get only fields that have been explicitly set (not null).
     * Alias for toArrayWithoutNull for backward compatibility.
     */
    public function onlyFilled(): array
    {
        return $this->toArrayWithoutNull();
    }

    /**
     * Convert to model attributes (snake_case).
     * Override this method in child classes if custom mapping is needed.
     */
    public function toModelArray(): array
    {
        return $this->toArray();
    }
}
