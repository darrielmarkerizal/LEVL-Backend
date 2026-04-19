<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    
    public function query(): Builder;

    
    public function findById(int $id): ?Model;

    
    public function findByIdOrFail(int $id): Model;

    
    public function create(array $attributes);

    
    public function update(Model $model, array $attributes);

    
    public function delete(Model $model): bool;

    
    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator;

    
    public function list(array $params): Collection;
}
