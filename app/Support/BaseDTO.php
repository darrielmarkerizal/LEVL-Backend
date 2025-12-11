<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

abstract class BaseDTO
{
    abstract public static function fromRequest(array $data): static;

    abstract public function toArray(): array;

    public static function fromModel(Model $model): static
    {
        return static::fromRequest($model->toArray());
    }

    public function toArrayWithoutNull(): array
    {
        return array_filter($this->toArray(), fn ($value) => $value !== null);
    }
}
