<?php

namespace Modules\Schemes\Contracts\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Modules\Schemes\Models\Course;

interface CourseRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a course by slug.
     *
     * @param  string  $slug  Course slug
     */
    public function findBySlug(string $slug): ?Course;
}
