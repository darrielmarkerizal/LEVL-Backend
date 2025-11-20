<?php

namespace Modules\Assessments\Services;

use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Models\Question;
use Modules\Assessments\Repositories\ExerciseRepository;
use Modules\Assessments\Repositories\QuestionRepository;

class QuestionService
{
    public function __construct(
        private readonly QuestionRepository $questions,
        private readonly ExerciseRepository $exercises
    ) {}

    public function create(Exercise $exercise, array $data): Question
    {
        $data['order'] = $data['order'] ?? ($this->questions->countByExercise($exercise) + 1);
        $question = $this->questions->create($exercise, $data);

        $this->syncQuestionCount($exercise);

        return $question;
    }

    public function show(Question $question): Question
    {
        return $this->questions->loadWithOptions($question);
    }

    public function update(Question $question, array $data): Question
    {
        $updated = $this->questions->update($question, $data);

        return $updated;
    }

    public function delete(Question $question): bool
    {
        $exercise = $question->exercise;
        $deleted = $this->questions->delete($question);
        if ($exercise) {
            $this->syncQuestionCount($exercise);
        }

        return $deleted;
    }

    private function syncQuestionCount(Exercise $exercise): void
    {
        $count = $this->questions->countByExercise($exercise);
        $this->exercises->update($exercise, ['total_questions' => $count]);
    }
}
