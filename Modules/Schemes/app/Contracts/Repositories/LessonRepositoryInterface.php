<?php

namespace Modules\Schemes\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Models\Lesson;

interface LessonRepositoryInterface
{
    /**
     * Find lessons by unit ID with pagination.
     *
     * @param  int  $unitId  Unit ID
     * @param  array  $params  Filter and pagination parameters
     */
    public function findByUnit(int $unitId, array $params = []): LengthAwarePaginator;

    /**
     * Find a lesson by ID.
     *
     * @param  int  $id  Lesson ID
     */
    public function findById(int $id): ?Lesson;

    /**
     * Find a lesson by unit ID and lesson ID.
     *
     * @param  int  $unitId  Unit ID
     * @param  int  $id  Lesson ID
     */
    public function findByUnitAndId(int $unitId, int $id): ?Lesson;

    /**
     * Create a new lesson.
     *
     * @param  array  $data  Lesson data
     */
    public function create(array $data): Lesson;

    /**
     * Update an existing lesson.
     *
     * @param  Lesson  $lesson  Lesson instance
     * @param  array  $data  Updated data
     */
    public function update(Lesson $lesson, array $data): Lesson;

    /**
     * Delete a lesson.
     *
     * @param  Lesson  $lesson  Lesson instance
     */
    public function delete(Lesson $lesson): bool;

    /**
     * Get the maximum order value for lessons in a unit.
     *
     * @param  int  $unitId  Unit ID
     */
    public function getMaxOrderForUnit(int $unitId): int;

    /**
     * Get all lessons for a unit.
     *
     * @param  int  $unitId  Unit ID
     */
    public function getAllByUnit(int $unitId): Collection;
}
