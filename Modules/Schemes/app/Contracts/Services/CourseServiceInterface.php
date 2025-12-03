<?php

namespace Modules\Schemes\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Models\Course;

interface CourseServiceInterface
{
    /**
     * List public courses with pagination.
     *
     * @param  array  $params  Filter and pagination parameters
     */
    public function listPublic(array $params): LengthAwarePaginator;

    /**
     * List all courses with pagination.
     *
     * @param  array  $params  Filter and pagination parameters
     */
    public function list(array $params): LengthAwarePaginator;

    /**
     * Create a new course.
     *
     * @param  array  $data  Course data
     * @param  \Modules\Auth\Models\User|null  $actor  The user creating the course
     */
    public function create(array $data, ?\Modules\Auth\Models\User $actor = null): Course;

    /**
     * Update an existing course.
     *
     * @param  int  $id  Course ID
     * @param  array  $data  Updated course data
     */
    public function update(int $id, array $data): ?Course;

    /**
     * Delete a course.
     *
     * @param  int  $id  Course ID
     */
    public function delete(int $id): bool;

    /**
     * Publish a course.
     *
     * @param  int  $id  Course ID
     */
    public function publish(int $id): ?Course;

    /**
     * Unpublish a course.
     *
     * @param  int  $id  Course ID
     */
    public function unpublish(int $id): ?Course;

    /**
     * Verify an enrollment key against a course's stored hash.
     *
     * @param  Course  $course  The course to verify against
     * @param  string  $plainKey  The plain text enrollment key to verify
     * @return bool True if the key is valid, false otherwise
     */
    public function verifyEnrollmentKey(Course $course, string $plainKey): bool;

    /**
     * Generate a new enrollment key.
     *
     * @param  int  $length  The length of the key to generate (default: 12)
     * @return string The generated plain text enrollment key
     */
    public function generateEnrollmentKey(int $length = 12): string;

    /**
     * Check if a course has an enrollment key set.
     *
     * @param  Course  $course  The course to check
     * @return bool True if the course has an enrollment key hash, false otherwise
     */
    public function hasEnrollmentKey(Course $course): bool;
}
