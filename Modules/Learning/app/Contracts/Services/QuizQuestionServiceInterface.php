<?php

declare(strict_types=1);

namespace Modules\Learning\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Learning\Models\QuizQuestion;

interface QuizQuestionServiceInterface
{
    public function createQuestion(int $quizId, array $data): QuizQuestion;

    public function updateQuestion(int $questionId, array $data, ?int $quizId = null): QuizQuestion;

    public function deleteQuestion(int $questionId, ?int $quizId = null): bool;

    public function reorderQuestions(int $quizId, array $questionIds): void;

    public function getQuizQuestions(int $quizId, array $filters = []): LengthAwarePaginator;

    public function computeWeightStats(int $quizId, ?float $additionalWeight = null): array;
}
