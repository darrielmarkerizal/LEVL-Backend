<?php

namespace Modules\Learning\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

interface SubmissionRepositoryInterface
{
    public function listForAssignment(Assignment $assignment, array $filters = []): Collection;

    public function findByUserAndAssignment(int $userId, int $assignmentId): ?Submission;

    public function create(array $attributes): Submission;

    public function update(Submission $submission, array $attributes): Submission;

    public function delete(Submission $submission): bool;
}
