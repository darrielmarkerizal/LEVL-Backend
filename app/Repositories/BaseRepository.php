<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilder;

abstract class BaseRepository implements BaseRepositoryInterface
{
    abstract protected function model(): string;

    protected array $allowedFilters = [];

    protected array $allowedSorts = ['id', 'created_at', 'updated_at'];

    protected string $defaultSort = 'id';

    protected array $with = [];

    public function applyFiltering(Builder $query, array $params, array $allowedFilters = [], array $allowedSorts = [], string|array $defaultSort = 'id'): Builder
    {
        return QueryBuilder::for($query)
            ->allowedFilters($allowedFilters ?: $this->allowedFilters)
            ->allowedSorts($allowedSorts ?: $this->allowedSorts)
            ->defaultSort($defaultSort ?: $this->defaultSort)
            ->getSubject();
    }

    public function filteredPaginate(Builder $query, array $params, array $allowedFilters = [], array $allowedSorts = [], string|array $defaultSort = 'id', int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        return QueryBuilder::for($query)
            ->allowedFilters($allowedFilters ?: $this->allowedFilters)
            ->allowedSorts($allowedSorts ?: $this->allowedSorts)
            ->defaultSort($defaultSort ?: $this->defaultSort)
            ->paginate($perPage);
    }

    public function query(): Builder
    {
        return $this->newModel()->newQuery()->with($this->with);
    }

    public function findById(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return $this->newModel()->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        return QueryBuilder::for($this->query())
            ->allowedFilters($this->allowedFilters)
            ->allowedSorts($this->allowedSorts)
            ->defaultSort($this->defaultSort)
            ->paginate($perPage);
    }

    public function list(array $params): Collection
    {
        return QueryBuilder::for($this->query())
            ->allowedFilters($this->allowedFilters)
            ->allowedSorts($this->allowedSorts)
            ->defaultSort($this->defaultSort)
            ->get();
    }

    protected function newModel(): Model
    {
        $modelClass = $this->model();
        if (! class_exists($modelClass)) {
            throw new \RuntimeException("Model class {$modelClass} does not exist.");
        }

        $model = new $modelClass;
        if (! $model instanceof Model) {
            throw new \RuntimeException("Class {$modelClass} must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        return $model;
    }

    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    public function getAllowedSorts(): array
    {
        return $this->allowedSorts;
    }

    public function getDefaultSort(): string
    {
        return $this->defaultSort;
    }
}
