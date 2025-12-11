<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Use BaseDataDTO instead. This class will be removed in future versions.
 *
 * All new DTOs should extend BaseDataDTO which uses Spatie Laravel Data package.
 */
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
