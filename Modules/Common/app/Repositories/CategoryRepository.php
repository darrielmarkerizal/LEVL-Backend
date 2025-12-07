<?php

namespace Modules\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Common\Models\Category;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryRepository
{
    public function query(): Builder
    {
        return Category::query();
    }

    /**
     * Get paginated categories using Spatie Query Builder + Scout search.
     *
     * Supports:
     * - filter[name], filter[value], filter[description], filter[status]
     * - filter[search] for Scout/Meilisearch full-text search
     * - sort: name, value, status, created_at, updated_at (prefix with - for desc)
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildQuery()->paginate($perPage);
    }

    /**
     * Get all categories (no pagination) using Spatie Query Builder + Scout.
     */
    public function all(): Collection
    {
        return $this->buildQuery()->get();
    }

    /**
     * Build query with Spatie Query Builder + Scout search.
     */
    private function buildQuery(): QueryBuilder
    {
        $searchQuery = request('filter.search');

        $builder = QueryBuilder::for(Category::class);

        // If search query exists, use Scout to get matching IDs
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Category::search($searchQuery)->keys()->toArray();
            $builder->whereIn('id', $ids);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('value'),
                AllowedFilter::partial('description'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['name', 'value', 'status', 'created_at', 'updated_at'])
            ->defaultSort('-created_at');
    }

    public function create(array $attributes): Category
    {
        return Category::create($attributes);
    }

    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    public function update(Category $category, array $attributes): Category
    {
        $category->fill($attributes)->save();

        return $category;
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
