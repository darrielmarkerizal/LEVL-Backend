<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Repositories\UnitRepositoryInterface;
use Modules\Schemes\DTOs\CreateUnitDTO;
use Modules\Schemes\DTOs\UpdateUnitDTO;
use Modules\Schemes\Models\Unit;

class UnitService
{
    public function __construct(
        private readonly UnitRepositoryInterface $repository
    ) {}

    public function paginate(int $courseId, array $params, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByCourse($courseId, $params);
    }

    public function find(int $id): ?Unit
    {
        return $this->repository->findById($id);
    }

    public function findOrFail(int $id): Unit
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function create(int $courseId, CreateUnitDTO|array $data): Unit
    {
        $attributes = $data instanceof CreateUnitDTO ? $data->toArrayWithoutNull() : $data;
        $attributes['course_id'] = $courseId;

        if (! isset($attributes['slug']) && isset($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        return $this->repository->create($attributes);
    }

    public function update(int $id, UpdateUnitDTO|array $data): Unit
    {
        $unit = $this->repository->findByIdOrFail($id);
        $attributes = $data instanceof UpdateUnitDTO ? $data->toArrayWithoutNull() : $data;

        if (isset($attributes['title']) && $attributes['title'] !== $unit->title) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        return $this->repository->update($unit, $attributes);
    }

    public function delete(int $id): bool
    {
        $unit = $this->repository->findByIdOrFail($id);

        return $this->repository->delete($unit);
    }

    public function reorder(int $courseId, array $order): void
    {
        foreach ($order as $index => $unitId) {
            Unit::where('id', $unitId)
                ->where('course_id', $courseId)
                ->update(['order' => $index + 1]);
        }
    }
}
