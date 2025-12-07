<?php

namespace Modules\Schemes\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Models\Unit;

interface UnitRepositoryInterface
{
    /**
     * Find units by course ID with pagination.
     * Uses Spatie Query Builder for filter/sort from request.
     *
     * @param  int  $courseId  Course ID
     * @param  int  $perPage  Items per page
     */
    public function findByCourse(int $courseId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a unit by ID.
     *
     * @param  int  $id  Unit ID
     */
    public function findById(int $id): ?Unit;

    /**
     * Find a unit by course ID and unit ID.
     *
     * @param  int  $courseId  Course ID
     * @param  int  $id  Unit ID
     */
    public function findByCourseAndId(int $courseId, int $id): ?Unit;

    /**
     * Create a new unit.
     *
     * @param  array  $data  Unit data
     */
    public function create(array $data): Unit;

    /**
     * Update an existing unit.
     *
     * @param  Unit  $unit  Unit instance
     * @param  array  $data  Updated data
     */
    public function update(Unit $unit, array $data): Unit;

    /**
     * Delete a unit.
     *
     * @param  Unit  $unit  Unit instance
     */
    public function delete(Unit $unit): bool;

    /**
     * Get the maximum order value for units in a course.
     *
     * @param  int  $courseId  Course ID
     */
    public function getMaxOrderForCourse(int $courseId): int;

    /**
     * Reorder units within a course.
     *
     * @param  int  $courseId  Course ID
     * @param  array  $unitOrders  Array of unit ID => order mappings
     */
    public function reorderUnits(int $courseId, array $unitOrders): void;

    /**
     * Get all units for a course.
     *
     * @param  int  $courseId  Course ID
     */
    public function getAllByCourse(int $courseId): Collection;
}
