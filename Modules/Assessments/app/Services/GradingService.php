<?php

namespace Modules\Assessments\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Assessments\Models\Answer;
use Modules\Assessments\Models\Attempt;
use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Repositories\GradingRepository;

class GradingService
{
    public function __construct(private readonly GradingRepository $repository) {}

    public function attempts(Exercise $exercise, array $params, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateExerciseAttempts($exercise, $params, $perPage);
    }

    public function answers(Attempt $attempt): Collection
    {
        return $this->repository->answersForAttempt($attempt);
    }

    public function addFeedback(Answer $answer, array $data): Answer
    {
        return $this->repository->updateAnswer($answer, $data);
    }

    public function updateAttemptScore(Attempt $attempt, array $data): Attempt
    {
        return $this->repository->updateAttempt($attempt, $data);
    }
}
