<?php

namespace Modules\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Models\Category;

class CategoryRepository
{
    public function query(): Builder
    {
        return Category::query();
    }

    public function paginate(array $params, int $perPage): LengthAwarePaginator
    {
        return $this->applyFilters($this->query(), $params)
            ->paginate($perPage)
            ->appends($params);
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

    private function applyFilters(Builder $query, array $params): Builder
    {
        // Global search across name and value
        $search = trim((string) ($params['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Individual field filters
        $name = $params['filter']['name'] ?? $params['name'] ?? null;
        if (is_string($name) && $name !== '') {
            $query->where('name', 'like', "%{$name}%");
        }

        $value = $params['filter']['value'] ?? $params['value'] ?? null;
        if (is_string($value) && $value !== '') {
            $query->where('value', 'like', "%{$value}%");
        }

        $description = $params['filter']['description'] ?? $params['description'] ?? null;
        if (is_string($description) && $description !== '') {
            $query->where('description', 'like', "%{$description}%");
        }

        $status = $params['filter']['status'] ?? $params['status'] ?? null;
        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        // Sorting
        $sort = (string) ($params['sort'] ?? '');
        if ($sort !== '') {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');
            $sortableFields = ['created_at', 'updated_at', 'name', 'value', 'status'];
            if (in_array($field, $sortableFields, true)) {
                $query->orderBy($field, $direction);
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query;
    }
}
