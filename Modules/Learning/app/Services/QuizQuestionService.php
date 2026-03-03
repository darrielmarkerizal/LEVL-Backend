<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Contracts\Services\QuizQuestionServiceInterface;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizQuestion;
use Modules\Learning\Repositories\QuizQuestionRepository;

class QuizQuestionService implements QuizQuestionServiceInterface
{
    public function __construct(
        private readonly QuizQuestionRepository $repository,
    ) {}

    public function createQuestion(int $quizId, array $data): QuizQuestion
    {
        return DB::transaction(function () use ($quizId, $data) {
            $this->validateQuestionData($data);

            $data['quiz_id'] = $quizId;

            if (! isset($data['order'])) {
                $maxOrder = QuizQuestion::where('quiz_id', $quizId)->max('order') ?? -1;
                $data['order'] = $maxOrder + 1;
            }

            $options = $data['options'] ?? null;
            if ($options) {
                unset($data['options']);
            }

            $question = $this->repository->create($data);

            if ($options) {
                $this->processOptionImages($question, $options);
            }

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

            return $this->repository->updateQuizQuestion($questionId, $data);
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

        return $this->repository->deleteQuizQuestion($questionId);
    }

    public function reorderQuestions(int $quizId, array $questionIds): void
    {
        $this->repository->reorder($quizId, $questionIds);
    }

    public function getQuizQuestions(int $quizId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        return \Spatie\QueryBuilder\QueryBuilder::for(QuizQuestion::class)
            ->where('quiz_id', $quizId)
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::exact('type'),
            ])
            ->allowedSorts(['order', 'weight', 'created_at'])
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

    private function processOptionImages(QuizQuestion $question, array $options): void
    {
        $modified = false;
        foreach ($options as $key => &$option) {
            if (is_array($option) && isset($option['image']) && $option['image'] instanceof \Illuminate\Http\UploadedFile) {
                $media = $question->addMedia($option['image'])->toMediaCollection('option_images');
                $option['image'] = $media->getUrl();
                $modified = true;
            }
        }

        $question->options = $options;
        $question->save();
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
