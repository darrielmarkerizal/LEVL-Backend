<?php

namespace Modules\Grading\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Grading\Models\Grade;

interface GradingRepositoryInterface
{
    public function findById(int $id): ?Grade;

    public function findBySubmission(int $submissionId): ?Grade;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Grade;

    public function update(Grade $grade, array $data): Grade;

    public function delete(Grade $grade): bool;
}
