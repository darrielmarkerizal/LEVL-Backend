<?php

namespace Modules\Schemes\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Models\Course;

interface CourseRepositoryInterface
{
    public function findById(int $id): ?Course;

    public function findBySlug(string $slug): ?Course;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function list(): Collection;

    public function create(array $attributes): Course;

    public function update(Course $course, array $attributes): Course;

    public function delete(Course $course): bool;
}
