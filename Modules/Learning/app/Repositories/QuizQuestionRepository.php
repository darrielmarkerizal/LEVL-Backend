<?php declare(strict_types=1);

namespace Modules\Learning\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Contracts\Repositories\QuizQuestionRepositoryInterface;
use Modules\Learning\Models\QuizQuestion;

class QuizQuestionRepository extends BaseRepository implements QuizQuestionRepositoryInterface
{
    protected function model(): string
    {
        return QuizQuestion::class;
    }

    public function create(array $data): QuizQuestion
    {
        return QuizQuestion::create($data);
    }

    public function updateQuizQuestion(int $questionId, array $data): QuizQuestion
    {
        $question = QuizQuestion::findOrFail($questionId);
        $question->fill($data)->save();
        return $question;
    }

    public function deleteQuizQuestion(int $questionId): bool
    {
        return QuizQuestion::where('id', $questionId)->delete() > 0;
    }

    public function find(int $questionId): ?QuizQuestion
    {
        return QuizQuestion::find($questionId);
    }

    public function findByQuiz(int $quizId): Collection
    {
        return QuizQuestion::where('quiz_id', $quizId)
            ->orderBy('order')
            ->get();
    }

    public function reorder(int $quizId, array $questionIds): void
    {
        foreach ($questionIds as $order => $questionId) {
            QuizQuestion::where('id', $questionId)
                ->where('quiz_id', $quizId)
                ->update(['order' => $order]);
        }
    }

    public function findRandomFromBank(int $quizId, int $count, int $seed): Collection
    {
        return QuizQuestion::where('quiz_id', $quizId)
            ->inRandomOrder($seed)
            ->limit($count)
            ->get();
    }
}
