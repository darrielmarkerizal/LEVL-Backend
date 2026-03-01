<?php

declare(strict_types=1);

namespace Modules\Learning\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizAnswer;
use Modules\Learning\Models\QuizSubmission;

interface QuizSubmissionServiceInterface
{
    public function start(Quiz $quiz, int $userId, ?int $enrollmentId = null): QuizSubmission;

    public function saveAnswer(QuizSubmission $submission, int $questionId, array $data): QuizAnswer;

    public function submit(QuizSubmission $submission, int $actorId): QuizSubmission;

    public function getMySubmissions(int $quizId, int $userId): Collection;

    public function getHighestSubmission(int $quizId, int $userId): ?QuizSubmission;

    public function listForQuiz(int $quizId, array $filters = []): LengthAwarePaginator;

    public function listQuestions(QuizSubmission $submission, int $userId): Collection;
}
