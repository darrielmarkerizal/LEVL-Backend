<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\DTOs\CreateLessonDTO;
use Modules\Schemes\DTOs\UpdateLessonDTO;
use Modules\Schemes\Models\Lesson;

class LessonService
{
    public function __construct(
        private readonly LessonRepositoryInterface $repository
    ) {}

    public function paginate(int $unitId, array $params, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByUnit($unitId, $params);
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

        if (! isset($attributes['slug']) && isset($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        return $this->repository->create($attributes);
    }

    public function update(int $id, UpdateLessonDTO|array $data): Lesson
    {
        $lesson = $this->repository->findByIdOrFail($id);
        $attributes = $data instanceof UpdateLessonDTO ? $data->toArrayWithoutNull() : $data;

        if (isset($attributes['title']) && $attributes['title'] !== $lesson->title) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        return $this->repository->update($lesson, $attributes);
    }

    public function delete(int $id): bool
    {
        $lesson = $this->repository->findByIdOrFail($id);

        return $this->repository->delete($lesson);
    }
}
