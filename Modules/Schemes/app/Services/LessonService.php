<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\DTOs\CreateLessonDTO;
use Modules\Schemes\DTOs\UpdateLessonDTO;
use Modules\Schemes\Models\Lesson;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonService
{
    public function __construct(
        private readonly LessonRepositoryInterface $repository
    ) {}

    /**
     * Paginate lessons for a unit.
     *
     * Supports:
     * - filter[unit_id], filter[type], filter[status]
     * - sort: order, title, created_at (prefix with - for desc)
     * - include: unit, blocks, assignments
     */
    public function paginate(int $unitId, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);

        $query = QueryBuilder::for(Lesson::class)
            ->where('unit_id', $unitId)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('status'),
            ])
            ->allowedIncludes(['unit', 'blocks', 'assignments'])
            ->allowedSorts(['order', 'title', 'created_at'])
            ->defaultSort('order');

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Lesson
    {
        return $this->repository->findById($id);
    }

    public function findOrFail(int $id): Lesson
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function create(int $unitId, CreateLessonDTO|array $data): Lesson
    {
        $attributes = $data instanceof CreateLessonDTO ? $data->toArrayWithoutNull() : $data;
        $attributes['unit_id'] = $unitId;

        // Remove slug - HasSlug trait auto-generates from title
        unset($attributes['slug']);

        return $this->repository->create($attributes);
    }

    public function update(int $id, UpdateLessonDTO|array $data): Lesson
    {
        $lesson = $this->repository->findByIdOrFail($id);
        $attributes = $data instanceof UpdateLessonDTO ? $data->toArrayWithoutNull() : $data;

        // Remove slug - HasSlug doesn't regenerate on update
        unset($attributes['slug']);

        return $this->repository->update($lesson, $attributes);
    }

    public function delete(int $id): bool
    {
        $lesson = $this->repository->findByIdOrFail($id);

        return $this->repository->delete($lesson);
    }
}
