<?php

namespace App\Services;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    public function __construct(
        protected readonly BaseRepositoryInterface $repository
    ) {}

    /**
     * Get paginated list.
     */
    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($params, max(1, $perPage));
    }

    /**
     * Get all records matching params.
     */
    public function list(array $params): Collection
    {
        return $this->repository->list($params);
    }

    /**
     * Find by ID.
     */
    public function find(int $id): ?Model
    {
        return $this->repository->findById($id);
    }

    /**
     * Find by ID or fail.
     */
    public function findOrFail(int $id): Model
    {
        return $this->repository->findByIdOrFail($id);
    }

    /**
     * Create new record.
     */
    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Update existing record.
     */
    public function update(int $id, array $data): Model
    {
        $model = $this->repository->findByIdOrFail($id);

        return $this->repository->update($model, $data);
    }

    /**
     * Delete record.
     */
    public function delete(int $id): bool
    {
        $model = $this->repository->findByIdOrFail($id);

        return $this->repository->delete($model);
    }
}
