<?php

namespace Modules\Learning\Contracts\Services;

use Modules\Learning\Models\Assignment;
use Modules\Schemes\Models\Lesson;

interface AssignmentServiceInterface
{
    public function listByLesson(Lesson $lesson, array $filters = []);

    public function create(array $data, int $createdBy): Assignment;

    public function update(Assignment $assignment, array $data): Assignment;

    public function publish(Assignment $assignment): Assignment;

    public function unpublish(Assignment $assignment): Assignment;

    public function delete(Assignment $assignment): bool;
}
