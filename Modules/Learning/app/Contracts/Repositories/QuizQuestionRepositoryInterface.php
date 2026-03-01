<?php

declare(strict_types=1);

namespace Modules\Learning\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Models\QuizQuestion;

interface QuizQuestionRepositoryInterface
{
    public function create(array $data): QuizQuestion;

    public function updateQuizQuestion(int $questionId, array $data): QuizQuestion;

    public function deleteQuizQuestion(int $questionId): bool;

    public function find(int $questionId): ?QuizQuestion;

    public function findByQuiz(int $quizId): Collection;

    public function reorder(int $quizId, array $questionIds): void;

    public function findRandomFromBank(int $quizId, int $count, int $seed): Collection;
}
