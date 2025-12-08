<?php

namespace Modules\Learning\Contracts\Services;

use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

interface SubmissionServiceInterface
{
    public function listByAssignment(Assignment $assignment, array $filters = []);

    public function create(array $data, int $userId): Submission;

    public function update(Submission $submission, array $data): Submission;

    public function delete(Submission $submission): bool;
}
