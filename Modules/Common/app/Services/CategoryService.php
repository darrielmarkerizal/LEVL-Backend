<?php

declare(strict_types=1);

namespace Modules\Common\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Common\Contracts\Services\CategoryServiceInterface;
use Modules\Common\DTOs\CreateCategoryDTO;
use Modules\Common\DTOs\UpdateCategoryDTO;
use Modules\Common\Models\Category;
use Modules\Common\Repositories\CategoryRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(private readonly CategoryRepository $repository) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = request()->get('page', 1);
        $search = request('search');
        
        return cache()->tags(['common', 'categories'])->remember(
            "common:categories:paginate:{$perPage}:{$page}:{$search}",
            300, // 5 minutes
            function () use ($perPage) {
                $eloquentQuery = Category::query();
                if (request()->has('search') && request('search')) {
                     $eloquentQuery->search(request('search'));
                }

                return QueryBuilder::for($eloquentQuery)
                    ->allowedFilters([
                        AllowedFilter::partial('name'),
                        AllowedFilter::callback('search', fn ($query, $value) => $query->search($value)),
                    ])
                    ->allowedSorts(['name', 'created_at'])
                    ->defaultSort('name')
                    ->paginate($perPage);
            }
        );
    }

    public function create(CreateCategoryDTO|array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $dto = $data instanceof CreateCategoryDTO ? $data : CreateCategoryDTO::fromRequest($data);
            $category = $this->repository->create($dto->toArray());
            cache()->tags(['common', 'categories'])->flush();
            return $category;
        });
    }

    public function find(int $id): ?Category
    {
        return $this->repository->find($id);
    }

    public function update(int $id, UpdateCategoryDTO|array $data): ?Category
    {
        $category = $this->repository->find($id);
        if (! $category) {
            return null;
        }

        $dto = $data instanceof UpdateCategoryDTO ? $data : UpdateCategoryDTO::fromRequest($data);
        return DB::transaction(function () use ($category, $dto) {
            $updated = $this->repository->update($category, array_filter($dto->toArray(), fn ($value) => $value !== null));
            cache()->tags(['common', 'categories'])->flush();
            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $category = $this->repository->find($id);
            if (! $category) {
                return false;
            }

            $result = $this->repository->delete($category);
            cache()->tags(['common', 'categories'])->flush();
            return $result;
        });
    }
}

