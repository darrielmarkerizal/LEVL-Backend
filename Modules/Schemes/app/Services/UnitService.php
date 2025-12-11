<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Contracts\Repositories\UnitRepositoryInterface;
use Modules\Schemes\DTOs\CreateUnitDTO;
use Modules\Schemes\DTOs\UpdateUnitDTO;
use Modules\Schemes\Models\Unit;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UnitService
{
    public function __construct(
        private readonly UnitRepositoryInterface $repository
    ) {}

    /**
     * Paginate units for a course.
     *
     * Supports:
     * - filter[course_id], filter[status]
     * - sort: order, title, created_at (prefix with - for desc)
     * - include: course, lessons
     */
    public function paginate(int $courseId, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);

        $query = QueryBuilder::for(Unit::class)
            ->where('course_id', $courseId)
            ->allowedFilters([
                AllowedFilter::exact('status'),
            ])
            ->allowedIncludes(['course', 'lessons'])
            ->allowedSorts(['order', 'title', 'created_at'])
            ->defaultSort('order');

        return $query->paginate($perPage);
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

        // Remove slug - HasSlug trait auto-generates from title
        unset($attributes['slug']);

        return $this->repository->create($attributes);
    }

    public function update(int $id, UpdateUnitDTO|array $data): Unit
    {
        $unit = $this->repository->findByIdOrFail($id);
        $attributes = $data instanceof UpdateUnitDTO ? $data->toArrayWithoutNull() : $data;

        // Remove slug - HasSlug doesn't regenerate on update
        unset($attributes['slug']);

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
