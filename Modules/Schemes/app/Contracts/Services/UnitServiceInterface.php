<?php

namespace Modules\Schemes\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Repositories\UnitRepository;

interface UnitServiceInterface
{
    /**
     * List units by course with pagination.
     *
     * @param  int  $courseId  Course ID
     * @param  array  $params  Filter and pagination parameters
     */
    public function listByCourse(int $courseId, array $params): LengthAwarePaginator;

    /**
     * Show a specific unit.
     *
     * @param  int  $courseId  Course ID
     * @param  int  $id  Unit ID
     */
    public function show(int $courseId, int $id): ?Unit;

    /**
     * Create a new unit.
     *
     * @param  int  $courseId  Course ID
     * @param  array  $data  Unit data
     */
    public function create(int $courseId, array $data): Unit;

    /**
     * Update an existing unit.
     *
     * @param  int  $courseId  Course ID
     * @param  int  $id  Unit ID
     * @param  array  $data  Updated unit data
     */
    public function update(int $courseId, int $id, array $data): ?Unit;

    /**
     * Delete a unit.
     *
     * @param  int  $courseId  Course ID
     * @param  int  $id  Unit ID
     */
    public function delete(int $courseId, int $id): bool;

    /**
     * Reorder units within a course.
     *
     * @param  int  $courseId  Course ID
     * @param  array  $unitOrders  Array of unit IDs in desired order
     */
    public function reorder(int $courseId, array $unitOrders): bool;

    /**
     * Mark a unit as completed for a user.
     *
     * @param  Unit  $unit  The unit to mark as completed
     * @param  int  $userId  User ID
     * @param  int  $enrollmentId  Enrollment ID
     */
    public function markCompleted(Unit $unit, int $userId, int $enrollmentId): void;

    /**
     * Publish a unit.
     *
     * @param  int  $courseId  Course ID
     * @param  int  $id  Unit ID
     */
    public function publish(int $courseId, int $id): ?Unit;

    /**
     * Unpublish a unit.
     *
     * @param  int  $courseId  Course ID
     * @param  int  $id  Unit ID
     */
    public function unpublish(int $courseId, int $id): ?Unit;

    /**
     * Get the underlying repository.
     */
    public function getRepository(): UnitRepository;
}
