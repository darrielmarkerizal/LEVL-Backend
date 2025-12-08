<?php

namespace Modules\Grading\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Grading\Contracts\Repositories\GradingRepositoryInterface;
use Modules\Grading\Models\Grade;

class GradingRepository implements GradingRepositoryInterface
{
    public function __construct(private readonly Grade $model) {}

    public function findById(int $id): ?Grade
    {
        return $this->model->find($id);
    }

    public function findBySubmission(int $submissionId): ?Grade
    {
        return $this->model->where('submission_id', $submissionId)->first();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['submission', 'gradedBy'])->paginate($perPage);
    }

    public function create(array $data): Grade
    {
        return $this->model->create($data);
    }

    public function update(Grade $grade, array $data): Grade
    {
        $grade->update($data);

        return $grade->fresh();
    }

    public function delete(Grade $grade): bool
    {
        return $grade->delete();
    }
}
