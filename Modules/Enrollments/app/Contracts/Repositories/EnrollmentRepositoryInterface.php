<?php

namespace Modules\Enrollments\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Enrollments\Models\Enrollment;

interface EnrollmentRepositoryInterface
{
    /**
     * Paginate enrollments by course ID.
     * Spatie Query Builder reads filter/sort from request.
     */
    public function paginateByCourse(int $courseId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Paginate enrollments by multiple course IDs.
     * Spatie Query Builder reads filter/sort from request.
     */
    public function paginateByCourseIds(array $courseIds, int $perPage = 15): LengthAwarePaginator;

    /**
     * Paginate enrollments by user ID.
     * Spatie Query Builder reads filter/sort from request.
     */
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find enrollment by course and user.
     */
    public function findByCourseAndUser(int $courseId, int $userId): ?Enrollment;

    public function findById(int $id): ?Enrollment;

    public function create(array $attributes): Enrollment;

    public function update(Enrollment $enrollment, array $attributes): Enrollment;

    public function delete(Enrollment $enrollment): bool;
}
