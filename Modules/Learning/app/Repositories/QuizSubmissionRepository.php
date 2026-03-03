<?php

declare(strict_types=1);

namespace Modules\Learning\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Contracts\Repositories\QuizSubmissionRepositoryInterface;
use Modules\Learning\Models\QuizSubmission;

class QuizSubmissionRepository extends BaseRepository implements QuizSubmissionRepositoryInterface
{
    protected function model(): string
    {
        return QuizSubmission::class;
    }

    public function create(array $data): QuizSubmission
    {
        return QuizSubmission::create($data);
    }

    public function updateSubmission(QuizSubmission $submission, array $data): QuizSubmission
    {
        $submission->fill($data)->save();

        return $submission;
    }

    public function find(int $submissionId): ?QuizSubmission
    {
        return QuizSubmission::find($submissionId);
    }

    public function findForStudent(int $quizId, int $userId): Collection
    {
        return QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->with(['answers'])
            ->orderByDesc('attempt_number')
            ->get();
    }

    public function findByQuiz(int $quizId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return QuizSubmission::where('quiz_id', $quizId)
            ->with(['user:id,name,email', 'answers'])
            ->orderByDesc('submitted_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function getAttemptCount(int $quizId, int $userId): int
    {
        return QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->count();
    }
}
