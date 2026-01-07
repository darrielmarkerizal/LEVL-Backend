<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get query builder instance.
     */
    public function query(): Builder;

    /**
     * Find by ID.
     */
    public function findById(int $id): ?Model;

    /**
     * Find by ID or fail.
     */
    public function findByIdOrFail(int $id): Model;

    /**
     * Create new record.
     * @return Model
     */
    public function create(array $attributes);

    /**
     * Update existing record.
     * @return Model
     */
    public function update(Model $model, array $attributes);

    /**
     * Delete record.
     */
    public function delete(Model $model): bool;

    /**
     * Get paginated list.
     */
    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all records matching params.
     */
    public function list(array $params): Collection;
}
