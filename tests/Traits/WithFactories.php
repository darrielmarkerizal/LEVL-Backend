<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;


trait WithFactories
{
    
    protected function createMany(string $modelClass, int $count, array $attributes = []): Collection
    {
        return $modelClass::factory()->count($count)->create($attributes);
    }

    
    protected function createOne(string $modelClass, array $attributes = [])
    {
        return $modelClass::factory()->create($attributes);
    }

    
    protected function makeMany(string $modelClass, int $count, array $attributes = []): Collection
    {
        return $modelClass::factory()->count($count)->make($attributes);
    }

    
    protected function makeOne(string $modelClass, array $attributes = [])
    {
        return $modelClass::factory()->make($attributes);
    }

    
    protected function createWithState(string $modelClass, string $state, array $attributes = [])
    {
        return $modelClass::factory()->$state()->create($attributes);
    }

    
    protected function createWithRelations(
        string $modelClass,
        string $relationship,
        int $relationCount,
        array $attributes = []
    ) {
        return $modelClass::factory()
            ->has($modelClass::factory()->count($relationCount), $relationship)
            ->create($attributes);
    }
}
