<?php

namespace Modules\Common\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Common\Models\Category;
use Modules\Common\Repositories\CategoryRepository;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $repository) {}

    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);

        return $this->repository->paginate($params, $perPage);
    }

    public function create(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function find(int $id): ?Category
    {
        return $this->repository->find($id);
    }

    public function update(int $id, array $data): ?Category
    {
        $category = $this->repository->find($id);
        if (! $category) {
            return null;
        }

        return $this->repository->update($category, $data);
    }

    public function delete(int $id): bool
    {
        $category = $this->repository->find($id);
        if (! $category) {
            return false;
        }

        return $this->repository->delete($category);
    }
}
