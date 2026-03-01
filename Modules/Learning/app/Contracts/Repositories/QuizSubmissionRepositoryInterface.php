<?php

declare(strict_types=1);

namespace Modules\Learning\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Models\QuizSubmission;

interface QuizSubmissionRepositoryInterface
{
    public function create(array $data): QuizSubmission;

    public function updateSubmission(QuizSubmission $submission, array $data): QuizSubmission;

    public function find(int $submissionId): ?QuizSubmission;

    public function findForStudent(int $quizId, int $userId): Collection;

    public function findByQuiz(int $quizId, array $filters = []): LengthAwarePaginator;

    public function getAttemptCount(int $quizId, int $userId): int;
}
