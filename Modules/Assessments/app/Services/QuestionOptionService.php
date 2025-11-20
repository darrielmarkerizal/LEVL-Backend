<?php

namespace Modules\Assessments\Services;

use Modules\Assessments\Models\Question;
use Modules\Assessments\Models\QuestionOption;
use Modules\Assessments\Repositories\QuestionOptionRepository;

class QuestionOptionService
{
    public function __construct(private readonly QuestionOptionRepository $repository) {}

    public function create(Question $question, array $data): QuestionOption
    {
        $data['order'] = $data['order'] ?? ($question->options()->count() + 1);

        return $this->repository->create($question, $data);
    }

    public function update(QuestionOption $option, array $data): QuestionOption
    {
        return $this->repository->update($option, $data);
    }

    public function delete(QuestionOption $option): bool
    {
        return $this->repository->delete($option);
    }
}
