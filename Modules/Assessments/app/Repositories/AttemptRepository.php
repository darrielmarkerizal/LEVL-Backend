<?php

namespace Modules\Assessments\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Assessments\Models\Answer;
use Modules\Assessments\Models\Attempt;
use Modules\Assessments\Models\Exercise;
use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;

class AttemptRepository
{
    public function paginateForUser(User $user, array $params, int $perPage): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);

        return $user->attempts()
            ->with('exercise')
            ->paginate($perPage)
            ->appends($params);
    }

    public function create(array $attributes): Attempt
    {
        return Attempt::create($attributes);
    }

    public function refreshWithDetails(Attempt $attempt): Attempt
    {
        return $attempt->load([
            'exercise.questions.options',
            'answers.selectedOption',
        ]);
    }

    public function findEnrollmentForExercise(User $user, Exercise $exercise): ?Enrollment
    {
        return Enrollment::query()
            ->where('user_id', $user->id)
            ->whereHas('course', function ($query) use ($exercise) {
                if ($exercise->scope_type === 'course') {
                    $query->where('id', $exercise->scope_id);
                } elseif ($exercise->scope_type === 'unit') {
                    $query->whereHas('units', function ($unitQuery) use ($exercise) {
                        $unitQuery->where('id', $exercise->scope_id);
                    });
                } else {
                    $query->whereHas('units.lessons', function ($lessonQuery) use ($exercise) {
                        $lessonQuery->where('id', $exercise->scope_id);
                    });
                }
            })
            ->first();
    }

    public function answersWithRelations(Attempt $attempt): Collection
    {
        return $attempt->answers()
            ->with('question.options', 'selectedOption')
            ->get();
    }

    public function firstOrCreateAnswer(Attempt $attempt, array $conditions, array $values): Answer
    {
        return Answer::firstOrCreate(
            array_merge(['attempt_id' => $attempt->id], $conditions),
            $values
        );
    }

    public function updateAnswer(Answer $answer, array $values): Answer
    {
        $answer->fill($values)->save();

        return $answer;
    }
}
