<?php

namespace Modules\Assessments\Repositories;

use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Models\Question;

class QuestionRepository
{
    public function create(Exercise $exercise, array $attributes): Question
    {
        return $exercise->questions()->create($attributes);
    }

    public function update(Question $question, array $attributes): Question
    {
        $question->fill($attributes)->save();

        return $question;
    }

    public function delete(Question $question): bool
    {
        return $question->delete();
    }

    public function loadWithOptions(Question $question): Question
    {
        return $question->load('options');
    }

    public function countByExercise(Exercise $exercise): int
    {
        return $exercise->questions()->count();
    }
}
