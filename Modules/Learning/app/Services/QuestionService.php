<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Traits\HandlesOptionImages;
use Modules\Grading\Jobs\RecalculateGradesJob;
use Modules\Learning\Contracts\Repositories\QuestionRepositoryInterface;
use Modules\Learning\Contracts\Services\QuestionServiceInterface;
use Modules\Learning\Enums\QuestionType;
use Modules\Learning\Events\AnswerKeyChanged;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Question;

class QuestionService implements QuestionServiceInterface
{
    use HandlesOptionImages;

    public function __construct(
        private QuestionRepositoryInterface $questionRepository
    ) {}

    public function createQuestion(int $assignmentId, array $data): Question
    {
        return DB::transaction(function () use ($assignmentId, $data) {
            $this->validateQuestionData($data);

            
            $this->validateQuestionWeight($assignmentId, $data['weight'] ?? 0);

            $data['assignment_id'] = $assignmentId;

            if (! isset($data['order'])) {
                $maxOrder = Question::where('assignment_id', $assignmentId)->max('order') ?? -1;
                $data['order'] = $maxOrder + 1;
            }

            
            
            
            
            
            
            

            $options = $data['options'] ?? null;
            if ($options) {
                unset($data['options']);
            }

            $attachments = $data['attachments'] ?? null;
            if ($attachments) {
                unset($data['attachments']);
            }

            $question = $this->questionRepository->create($data);

            if ($options) {
                $this->processOptionImages($question, $options);
            }

            if ($attachments) {
                $this->processQuestionAttachments($question, $attachments);
            }

            cache()->tags(['learning', 'questions'])->flush();

            return $question;
        });
    }

    public function updateQuestion(int $questionId, array $data, ?int $assignmentId = null): Question
    {
        return DB::transaction(function () use ($questionId, $data, $assignmentId) {
            $this->validateQuestionData($data, isUpdate: true);

            if ($assignmentId !== null) {
                $question = $this->questionRepository->find($questionId);
                if (! $question || $question->assignment_id !== $assignmentId) {
                    throw new \InvalidArgumentException(__('messages.questions.not_found'));
                }
            } else {
                $question = $this->questionRepository->find($questionId);
            }

            $options = $data['options'] ?? null;
            if ($options) {
                unset($data['options']);
                $this->processOptionImages($question, $options);
            }

            $attachments = $data['attachments'] ?? null;
            if ($attachments) {
                unset($data['attachments']);
                $this->processQuestionAttachments($question, $attachments);
            }

            $updated = $this->questionRepository->updateQuestion($questionId, $data);
            cache()->tags(['learning', 'questions'])->flush();

            return $updated;
        });
    }

    public function deleteQuestion(int $questionId, ?int $assignmentId = null): bool
    {
        if ($assignmentId !== null) {
            $question = $this->questionRepository->find($questionId);
            if (! $question || $question->assignment_id !== $assignmentId) {
                throw new \InvalidArgumentException(__('messages.questions.not_found'));
            }
        }

        $result = $this->questionRepository->deleteQuestion($questionId);
        cache()->tags(['learning', 'questions'])->flush();

        return $result;
    }

    public function updateAnswerKey(int $questionId, array $answerKey, int $instructorId): void
    {
        $question = $this->questionRepository->find($questionId);

        if (! $question) {
            throw new \InvalidArgumentException(__('messages.questions.not_found_with_id', ['id' => $questionId]));
        }

        if (! $question->canAutoGrade()) {
            throw new \InvalidArgumentException(__('messages.questions.cannot_set_answer_key_manual'));
        }

        $oldAnswerKey = $question->answer_key ?? [];

        $this->questionRepository->updateQuestion($questionId, ['answer_key' => $answerKey]);

        $question = $this->questionRepository->find($questionId);

        AnswerKeyChanged::dispatch($question, $oldAnswerKey, $answerKey, $instructorId);

        RecalculateGradesJob::dispatch(
            $questionId,
            $oldAnswerKey,
            $answerKey,
            $instructorId
        );
    }

    public function generateQuestionSet(int $assignmentId, ?int $seed = null): Collection
    {
        return $this->questionRepository->findByAssignment($assignmentId);
    }

    public function getQuestionsByAssignment(int $assignmentId, ?\Modules\Auth\Models\User $user = null, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        return cache()->tags(['learning', 'questions'])->remember(
            "learning:questions:assignment:{$assignmentId}:{$perPage}:".md5(json_encode($filters)),
            300,
            function () use ($assignmentId, $perPage, $filters) {
                return \Spatie\QueryBuilder\QueryBuilder::for(Question::class)
                    ->where('assignment_id', $assignmentId)
                    ->allowedFilters([
                        \Spatie\QueryBuilder\AllowedFilter::exact('type'),
                        \Spatie\QueryBuilder\AllowedFilter::callback('search', function ($query, $value) {
                            $ids = Question::search($value)->keys();
                            $query->whereIn('id', $ids);
                        }),
                    ])
                    ->allowedSorts(['order', 'weight', 'created_at'])
                    ->defaultSort('order')
                    ->paginate($perPage)
                    ->appends($filters);
            }
        );
    }

    public function reorderQuestions(int $assignmentId, array $questionIds): void
    {
        $this->questionRepository->reorder($assignmentId, $questionIds);
        cache()->tags(['learning', 'questions'])->flush();
    }

    private function validateQuestionData(array $data, bool $isUpdate = false): void
    {

        if (isset($data['weight']) && $data['weight'] <= 0) {
            throw new \InvalidArgumentException(__('messages.questions.weight_must_be_positive'));
        }

        if (! $isUpdate && isset($data['type'])) {
            $type = $data['type'] instanceof QuestionType
                ? $data['type']
                : QuestionType::from($data['type']);

            if ($type->requiresOptions() && empty($data['options'])) {
                throw new \InvalidArgumentException(__('messages.questions.options_required'));
            }
        }
    }


    private function processQuestionAttachments(Question $question, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if ($attachment instanceof \Illuminate\Http\UploadedFile) {
                $question->addMedia($attachment)->toMediaCollection('question_attachments');
            }
        }
    }

    
    private function validateQuestionWeight(int $assignmentId, float $newWeight): void
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $currentWeight = (float) Question::where('assignment_id', $assignmentId)->sum('weight');
        $maxAllowed = (float) ($assignment->max_score ?? 100);
        $totalWeight = $currentWeight + (float) $newWeight;

        if ($totalWeight > $maxAllowed) {
            \Log::warning('Question weight exceeds assignment max score (soft warning).', [
                'assignment_id' => $assignmentId,
                'current_weight' => round($currentWeight, 2),
                'new_weight' => round($newWeight, 2),
                'max_score' => $maxAllowed,
                'total_weight' => round($totalWeight, 2),
            ]);
        }
    }

    public static function computeWeightStats(int $assignmentId, ?float $additionalWeight = null): array
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $currentWeight = (float) Question::where('assignment_id', $assignmentId)->sum('weight');
        $maxAllowed = (float) ($assignment->max_score ?? 100);
        $totalWeight = $currentWeight + (float) ($additionalWeight ?? 0.0);

        return [
            'current' => round($currentWeight, 2),
            'max' => $maxAllowed,
            'total' => round($totalWeight, 2),
            'exceeds' => $totalWeight > $maxAllowed,
        ];
    }
}
