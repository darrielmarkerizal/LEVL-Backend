<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\DTOs\CreateLessonDTO;
use Modules\Schemes\DTOs\UpdateLessonDTO;
use Modules\Schemes\Models\Lesson;

class LessonService
{
    public function __construct(
        private readonly LessonRepositoryInterface $repository
    ) {}

    public function paginate(int $unitId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByUnit($unitId, $perPage);
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
