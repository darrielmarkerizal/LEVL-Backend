<?php

namespace Modules\Assessments\Repositories;

use Modules\Assessments\Models\Question;
use Modules\Assessments\Models\QuestionOption;

class QuestionOptionRepository
{
    public function create(Question $question, array $attributes): QuestionOption
    {
        return $question->options()->create($attributes);
    }

    public function update(QuestionOption $option, array $attributes): QuestionOption
    {
        $option->fill($attributes)->save();

        return $option;
    }

    public function delete(QuestionOption $option): bool
    {
        return $option->delete();
    }
}
