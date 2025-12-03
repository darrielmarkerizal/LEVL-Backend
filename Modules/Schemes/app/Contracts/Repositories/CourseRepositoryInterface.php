<?php

namespace Modules\Schemes\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Models\Course;

interface CourseRepositoryInterface
{
    /**
     * Get a query builder instance for courses.
     */
    public function query(): Builder;

    /**
     * Find a course by ID.
     *
     * @param  int  $id  Course ID
     */
    public function findById(int $id): ?Course;

    /**
     * Find a course by slug.
     *
     * @param  string  $slug  Course slug
     */
    public function findBySlug(string $slug): ?Course;

    /**
     * Create a new course.
     *
     * @param  array  $attributes  Course attributes
     */
    public function create(array $attributes): Course;

    /**
     * Update an existing course.
     *
     * @param  Course  $course  Course instance
     * @param  array  $attributes  Updated attributes
     */
    public function update(Course $course, array $attributes): Course;

    /**
     * Delete a course.
     *
     * @param  Course  $course  Course instance
     */
    public function delete(Course $course): void;

    /**
     * Get paginated list of courses.
     *
     * @param  array  $params  Filter and pagination parameters
     * @param  int  $perPage  Number of items per page
     */
    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all courses matching the given parameters.
     *
     * @param  array  $params  Filter parameters
     */
    public function list(array $params): Collection;
}
