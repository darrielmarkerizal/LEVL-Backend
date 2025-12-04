<?php

namespace Modules\Schemes\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Repositories\LessonRepository;

interface LessonServiceInterface
{
    /**
     * List lessons by unit with pagination.
     *
     * @param  int  $unitId  Unit ID
     * @param  array  $params  Filter and pagination parameters
     */
    public function listByUnit(int $unitId, array $params): LengthAwarePaginator;

    /**
     * Show a specific lesson.
     *
     * @param  int  $unitId  Unit ID
     * @param  int  $id  Lesson ID
     */
    public function show(int $unitId, int $id): ?Lesson;

    /**
     * Create a new lesson.
     *
     * @param  int  $unitId  Unit ID
     * @param  array  $data  Lesson data
     */
    public function create(int $unitId, array $data): Lesson;

    /**
     * Update an existing lesson.
     *
     * @param  int  $unitId  Unit ID
     * @param  int  $id  Lesson ID
     * @param  array  $data  Updated lesson data
     */
    public function update(int $unitId, int $id, array $data): ?Lesson;

    /**
     * Delete a lesson.
     *
     * @param  int  $unitId  Unit ID
     * @param  int  $id  Lesson ID
     */
    public function delete(int $unitId, int $id): bool;

    /**
     * Publish a lesson.
     *
     * @param  int  $unitId  Unit ID
     * @param  int  $id  Lesson ID
     */
    public function publish(int $unitId, int $id): ?Lesson;

    /**
     * Unpublish a lesson.
     *
     * @param  int  $unitId  Unit ID
     * @param  int  $id  Lesson ID
     */
    public function unpublish(int $unitId, int $id): ?Lesson;

    /**
     * Mark a lesson as viewed for a user.
     *
     * @param  Lesson  $lesson  The lesson to mark as viewed
     * @param  int  $userId  User ID
     * @param  int  $enrollmentId  Enrollment ID
     */
    public function markViewed(Lesson $lesson, int $userId, int $enrollmentId): void;

    /**
     * Mark a lesson as completed for a user.
     *
     * @param  Lesson  $lesson  The lesson to mark as completed
     * @param  int  $userId  User ID
     * @param  int  $enrollmentId  Enrollment ID
     */
    public function markCompleted(Lesson $lesson, int $userId, int $enrollmentId): void;

    /**
     * Get the underlying repository.
     */
    public function getRepository(): LessonRepository;
}
