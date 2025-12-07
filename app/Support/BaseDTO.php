<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

abstract class BaseDTO
{
    /**
     * Create DTO from request/array data.
     */
    abstract public static function fromRequest(array $data): static;

    /**
     * Convert DTO to array.
     */
    abstract public function toArray(): array;

    /**
     * Create DTO from model.
     */
    public static function fromModel(Model $model): static
    {
        return static::fromRequest($model->toArray());
    }

    /**
     * Get only non-null values as array.
     */
    public function toArrayWithoutNull(): array
    {
        return array_filter($this->toArray(), fn ($value) => $value !== null);
    }
}
