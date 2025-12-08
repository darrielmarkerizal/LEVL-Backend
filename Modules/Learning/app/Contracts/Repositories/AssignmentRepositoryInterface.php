<?php

namespace Modules\Learning\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Models\Assignment;
use Modules\Schemes\Models\Lesson;

interface AssignmentRepositoryInterface
{
    public function listForLesson(Lesson $lesson, array $filters = []): Collection;

    public function create(array $attributes): Assignment;

    public function update(Assignment $assignment, array $attributes): Assignment;

    public function delete(Assignment $assignment): bool;
}
