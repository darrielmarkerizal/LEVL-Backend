<?php

namespace Modules\Enrollments\Contracts\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Enrollments\Models\Enrollment;

interface EnrollmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Paginate enrollments by course ID.
     */
    public function paginateByCourse(int $courseId, array $params, int $perPage = 15): LengthAwarePaginator;

    /**
     * Paginate enrollments by multiple course IDs.
     */
    public function paginateByCourseIds(array $courseIds, array $params, int $perPage = 15): LengthAwarePaginator;

    /**
     * Paginate enrollments by user ID.
     */
    public function paginateByUser(int $userId, array $params, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find enrollment by course and user.
     */
    public function findByCourseAndUser(int $courseId, int $userId): ?Enrollment;
}
