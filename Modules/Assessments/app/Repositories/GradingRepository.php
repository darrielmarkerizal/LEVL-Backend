<?php

namespace Modules\Assessments\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Assessments\Models\Answer;
use Modules\Assessments\Models\Attempt;
use Modules\Assessments\Models\Exercise;

class GradingRepository
{
    public function paginateExerciseAttempts(Exercise $exercise, array $params, int $perPage): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);

        return $exercise->attempts()
            ->with(['user', 'answers.question'])
            ->paginate($perPage)
            ->appends($params);
    }

    public function answersForAttempt(Attempt $attempt): Collection
    {
        return $attempt->answers()
            ->with(['question', 'selectedOption'])
            ->get();
    }

    public function updateAnswer(Answer $answer, array $attributes): Answer
    {
        $answer->fill($attributes)->save();

        return $answer;
    }

    public function updateAttempt(Attempt $attempt, array $attributes): Attempt
    {
        $attempt->fill($attributes)->save();

        return $attempt;
    }
}
