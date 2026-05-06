<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Learning\Traits\HandlesOptionImages;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Modules\Learning\Contracts\Services\QuizQuestionServiceInterface;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizQuestion;
use Modules\Learning\Repositories\QuizQuestionRepository;

class QuizQuestionService implements QuizQuestionServiceInterface
{
    use HandlesOptionImages;

    public function __construct(
        private readonly QuizQuestionRepository $repository,
    ) {}

    public function createQuestion(int $quizId, array $data): QuizQuestion
    {
        return DB::transaction(function () use ($quizId, $data) {
            $this->validateQuestionData($data);

            $quiz = Quiz::findOrFail($quizId);
            $existingCount = QuizQuestion::where('quiz_id', $quizId)->count();
            $defaultWeight = $existingCount > 0
                ? round((float) $quiz->max_score / ($existingCount + 1), 2)
                : (float) $quiz->max_score;

            $data['quiz_id'] = $quizId;
            $data['weight'] = $data['weight'] ?? $defaultWeight;

            
            
            $questionType = \Modules\Learning\Enums\QuizQuestionType::from($data['type']);
            if ($questionType->canAutoGrade()) {
                $data['max_score'] = $data['weight'];
            } else {
                $data['max_score'] = $data['max_score'] ?? $data['weight'];
            }

            if (! isset($data['order'])) {
                $maxOrder = QuizQuestion::where('quiz_id', $quizId)->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
            }

            $options = $data['options'] ?? null;
            if ($options) {
                unset($data['options']);
            }

            
            if ($questionType === \Modules\Learning\Enums\QuizQuestionType::TrueFalse) {
                $options = null; 
                unset($data['options']);
            }

            $question = $this->repository->create($data);

            if ($options) {
                $this->processOptionImages($question, $options);
            }

            $this->syncAutoGrading($quizId);

            return $question;
        });
    }

    public function updateQuestion(int $questionId, array $data, ?int $quizId = null): QuizQuestion
    {
        return DB::transaction(function () use ($questionId, $data, $quizId) {
            $this->validateQuestionData($data, isUpdate: true);

            if ($quizId !== null) {
                $question = $this->repository->find($questionId);
                if (! $question || $question->quiz_id !== $quizId) {
                    throw new \InvalidArgumentException(__('messages.questions.not_found'));
                }
            }

            $options = $data['options'] ?? null;
            if ($options) {
                unset($data['options']);
                $question = $question ?? $this->repository->find($questionId);
                $this->processOptionImages($question, $options);
            }

            $updated = $this->repository->updateQuizQuestion($questionId, $data);

            if ($quizId !== null) {
                $this->syncAutoGrading($quizId);
            }

            return $updated;
        });
    }

    public function deleteQuestion(int $questionId, ?int $quizId = null): bool
    {
        if ($quizId !== null) {
            $question = $this->repository->find($questionId);
            if (! $question || $question->quiz_id !== $quizId) {
                throw new \InvalidArgumentException(__('messages.questions.not_found'));
            }
        }

        $result = $this->repository->deleteQuizQuestion($questionId);

        if ($result && $quizId !== null) {
            $this->syncAutoGrading($quizId);
        }

        return $result;
    }

    public function reorderQuestions(int $quizId, array $questionIds): void
    {
        DB::transaction(function () use ($quizId, $questionIds) {
            $submittedIds = array_values(array_map('intval', $questionIds));
            $existingIds = QuizQuestion::where('quiz_id', $quizId)
                ->orderBy('order')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            if (count($submittedIds) !== count(array_unique($submittedIds))) {
                throw ValidationException::withMessages([
                    'ids' => ['Duplicate question IDs are not allowed.'],
                ]);
            }

            if (count($submittedIds) !== count($existingIds)) {
                throw ValidationException::withMessages([
                    'ids' => ['Reorder payload must contain all quiz question IDs.'],
                ]);
            }

            $sortedSubmittedIds = $submittedIds;
            $sortedExistingIds = $existingIds;
            sort($sortedSubmittedIds);
            sort($sortedExistingIds);

            if ($sortedSubmittedIds !== $sortedExistingIds) {
                throw ValidationException::withMessages([
                    'ids' => ['Reorder payload contains invalid question IDs.'],
                ]);
            }

            $this->repository->reorder($quizId, $submittedIds);
        });
    }

    public function getQuizQuestions(int $quizId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? $filters['perPage'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        return QueryBuilder::for(QuizQuestion::class)
            ->where('quiz_id', $quizId)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::partial('content'),
                AllowedFilter::exact('order'),
            ])
            ->allowedSorts(['order', 'weight', 'max_score', 'created_at', 'updated_at'])
            ->allowedIncludes(['media'])
            ->defaultSort('order')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function computeWeightStats(int $quizId, ?float $additionalWeight = null): array
    {
        $quiz = Quiz::findOrFail($quizId);
        $currentWeight = (float) $quiz->questions()->sum('weight');
        $maxAllowed = (float) ($quiz->max_score ?? 100);
        $totalWeight = $currentWeight + (float) ($additionalWeight ?? 0.0);

        return [
            'current' => round($currentWeight, 2),
            'max' => $maxAllowed,
            'total' => round($totalWeight, 2),
            'exceeds' => $totalWeight > $maxAllowed,
        ];
    }

    private function syncAutoGrading(int $quizId): void
    {
        $hasManualQuestion = QuizQuestion::where('quiz_id', $quizId)
            ->where('type', \Modules\Learning\Enums\QuizQuestionType::Essay->value)
            ->exists();

        Quiz::where('id', $quizId)->update([
            'auto_grading' => DB::raw($hasManualQuestion ? 'false' : 'true'),
        ]);
    }

    private function validateQuestionData(array $data, bool $isUpdate = false): void
    {
        if (isset($data['weight']) && $data['weight'] <= 0) {
            throw new \InvalidArgumentException(__('messages.questions.weight_must_be_positive'));
        }

        if (! $isUpdate && isset($data['type'])) {
            $type = $data['type'] instanceof \Modules\Learning\Enums\QuizQuestionType
                ? $data['type']
                : \Modules\Learning\Enums\QuizQuestionType::from($data['type']);

            if ($type->requiresOptions() && empty($data['options'])) {
                throw new \InvalidArgumentException(__('messages.questions.options_required'));
            }
        }
    }


    public function getQuizQuestionsForUser(int $quizId, array $filters, ?\Modules\Auth\Models\User $user): LengthAwarePaginator
    {
        if ($user && $user->hasRole('Student')) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, __('messages.quizzes.must_start_first'));
        }

        return $this->getQuizQuestions($quizId, $filters);
    }

    public function validateQuestionBelongsToQuiz(int $questionId, int $quizId): void
    {
        $question = $this->repository->find($questionId);

        if (! $question || $question->quiz_id !== $quizId) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(__('messages.questions.not_found'));
        }
    }
}
