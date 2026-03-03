<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Contracts\Services\QuizSubmissionServiceInterface;
use Modules\Learning\Enums\QuizGradingStatus;
use Modules\Learning\Enums\QuizQuestionType;
use Modules\Learning\Enums\QuizSubmissionStatus;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizAnswer;
use Modules\Learning\Models\QuizQuestion;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Repositories\QuizSubmissionRepository;
use Modules\Learning\Services\Support\QuizSubmissionIncludeAuthorizer;

class QuizSubmissionService implements QuizSubmissionServiceInterface
{
    public function __construct(
        private readonly QuizSubmissionRepository $repository,
        private readonly \Modules\Schemes\Services\PrerequisiteService $prerequisiteService,
        private readonly QuizSubmissionIncludeAuthorizer $includeAuthorizer,
    ) {}

    public function start(Quiz $quiz, int $userId, ?int $enrollmentId = null): QuizSubmission
    {
        $accessCheck = $this->prerequisiteService->checkQuizAccess($quiz, $userId);

        if (! $accessCheck['accessible']) {
            throw new \Illuminate\Validation\ValidationException(
                \Illuminate\Support\Facades\Validator::make([], [])->errors()->add(
                    'quiz',
                    __('messages.quizzes.locked_cannot_start')
                )
            );
        }

        $pendingSubmission = QuizSubmission::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->whereIn('status', [QuizSubmissionStatus::Draft->value, QuizSubmissionStatus::Submitted->value])
            ->first();

        if ($pendingSubmission) {
            if ($pendingSubmission->status === QuizSubmissionStatus::Draft->value) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], [])->errors()->add(
                        'quiz',
                        __('messages.quiz_submissions.draft_exists')
                    )
                );
            }

            throw new \Illuminate\Validation\ValidationException(
                \Illuminate\Support\Facades\Validator::make([], [])->errors()->add(
                    'quiz',
                    __('messages.quiz_submissions.pending_grading')
                )
            );
        }

        return DB::transaction(function () use ($quiz, $userId, $enrollmentId) {
            $attemptNumber = $this->repository->getAttemptCount($quiz->id, $userId) + 1;

            $questionSet = null;
            if ($quiz->randomization_type !== 'static') {
                $questionSet = $quiz->questions()
                    ->inRandomOrder()
                    ->limit($quiz->question_bank_count ?? PHP_INT_MAX)
                    ->pluck('id')
                    ->toArray();
            }

            return $this->repository->create([
                'quiz_id' => $quiz->id,
                'user_id' => $userId,
                'enrollment_id' => $enrollmentId,
                'status' => QuizSubmissionStatus::Draft->value,
                'grading_status' => QuizGradingStatus::Pending->value,
                'attempt_number' => $attemptNumber,
                'started_at' => now(),
                'question_set' => $questionSet,
            ]);
        });
    }

    public function saveAnswer(QuizSubmission $submission, int $questionId, array $data): QuizAnswer
    {
        return DB::transaction(function () use ($submission, $questionId, $data) {
            $existing = QuizAnswer::where('quiz_submission_id', $submission->id)
                ->where('quiz_question_id', $questionId)
                ->first();

            $answerData = [
                'quiz_submission_id' => $submission->id,
                'quiz_question_id' => $questionId,
                'content' => $data['content'] ?? null,
                'selected_options' => $data['selected_options'] ?? null,
                'is_auto_graded' => false,
                'score' => null,
            ];

            if ($existing) {
                $existing->fill($answerData)->save();

                return $existing;
            }

            return QuizAnswer::create($answerData);
        });
    }

    public function submit(QuizSubmission $submission, int $actorId): QuizSubmission
    {
        return DB::transaction(function () use ($submission) {
            $this->repository->updateSubmission($submission, [
                'status' => QuizSubmissionStatus::Submitted->value,
                'submitted_at' => now(),
            ]);

            return $this->autoGrade($submission);
        });
    }

    public function getMySubmissions(int $quizId, int $userId): Collection
    {
        return $this->repository->findForStudent($quizId, $userId);
    }

    public function getHighestSubmission(int $quizId, int $userId): ?QuizSubmission
    {
        return QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->whereNotNull('final_score')
            ->orderByDesc('final_score')
            ->first();
    }

    public function listForQuiz(int $quizId, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->findByQuiz($quizId, $filters);
    }

    public function listQuestions(QuizSubmission $submission, int $userId): Collection
    {
        $quiz = $submission->quiz;

        if ($submission->question_set) {
            return QuizQuestion::whereIn('id', $submission->question_set)
                ->orderByRaw('ARRAY_POSITION(ARRAY['.implode(',', $submission->question_set).']::bigint[], id)')
                ->get();
        }

        return $quiz->questions()->ordered()->get();
    }

    public function getQuestionAtOrder(QuizSubmission $submission, int $order): array
    {
        $questions = $this->listQuestions($submission, $submission->user_id);
        $total = $questions->count();

        if ($order < 0 || $order >= $total) {
            throw new \InvalidArgumentException(__('messages.quiz_submissions.invalid_question_order'));
        }

        $question = $questions->get($order);

        return [
            'question' => $question,
            'navigation' => [
                'total' => $total,
                'current_order' => $order,
                'has_previous' => $order > 0,
                'has_next' => $order < ($total - 1),
            ],
        ];
    }

    private function autoGrade(QuizSubmission $submission): QuizSubmission
    {
        $submission->load(['quiz.questions', 'answers']);

        $quiz = $submission->quiz;
        $hasEssay = $quiz->hasEssayQuestions();
        $hasObjective = ! $quiz->hasOnlyEssayQuestions();

        if (! $hasObjective && $hasEssay) {
            return $this->applyGradingStatus($submission, QuizGradingStatus::WaitingForGrading, null);
        }

        $totalObjectiveWeight = 0.0;
        $earnedObjectiveScore = 0.0;

        foreach ($quiz->questions as $question) {
            if (! $question->canAutoGrade()) {
                continue;
            }

            $answer = $submission->answers
                ->where('quiz_question_id', $question->id)
                ->first();

            $score = $this->gradeObjectiveAnswer($question, $answer);
            $totalObjectiveWeight += (float) $question->weight;
            $earnedObjectiveScore += $score;

            if ($answer) {
                $answer->fill(['score' => $score, 'is_auto_graded' => true])->save();
            } else {
                QuizAnswer::create([
                    'quiz_submission_id' => $submission->id,
                    'quiz_question_id' => $question->id,
                    'score' => 0,
                    'is_auto_graded' => true,
                ]);
            }
        }

        $maxScore = (float) $quiz->max_score;
        $objectiveScore = $totalObjectiveWeight > 0
            ? round(($earnedObjectiveScore / $totalObjectiveWeight) * $maxScore, 2)
            : 0;

        if ($hasEssay) {
            return $this->applyGradingStatus($submission, QuizGradingStatus::PartiallyGraded, $objectiveScore);
        }

        return $this->applyGradingStatus($submission, QuizGradingStatus::Graded, $objectiveScore);
    }

    private function gradeObjectiveAnswer(QuizQuestion $question, ?QuizAnswer $answer): float
    {
        if (! $answer) {
            return 0.0;
        }

        $answerKey = $question->answer_key ?? [];
        $weight = (float) $question->weight;

        return match ($question->type) {
            QuizQuestionType::MultipleChoice => $this->gradeMultipleChoice($answer->selected_options, $answerKey, $weight),
            QuizQuestionType::TrueFalse => $this->gradeTrueFalse($answer->selected_options, $answerKey, $weight),
            QuizQuestionType::Checkbox => $this->gradeCheckbox($answer->selected_options, $answerKey, $weight),
            default => 0.0,
        };
    }

    private function gradeMultipleChoice(?array $selected, array $answerKey, float $weight): float
    {
        if (empty($selected)) {
            return 0.0;
        }

        $correctKey = $answerKey[0] ?? null;

        return ($selected[0] ?? null) === $correctKey ? $weight : 0.0;
    }

    private function gradeTrueFalse(?array $selected, array $answerKey, float $weight): float
    {
        if (empty($selected)) {
            return 0.0;
        }

        $correctKey = $answerKey[0] ?? null;

        return ($selected[0] ?? null) === $correctKey ? $weight : 0.0;
    }

    private function gradeCheckbox(?array $selected, array $answerKey, float $weight): float
    {
        if (empty($selected) && empty($answerKey)) {
            return $weight;
        }

        if (empty($selected) || empty($answerKey)) {
            return 0.0;
        }

        $selectedSorted = array_values($selected);
        sort($selectedSorted);
        $keySorted = array_values($answerKey);
        sort($keySorted);

        return $selectedSorted === $keySorted ? $weight : 0.0;
    }

    private function applyGradingStatus(QuizSubmission $submission, QuizGradingStatus $gradingStatus, ?float $objectiveScore): QuizSubmission
    {
        $finalScore = $gradingStatus === QuizGradingStatus::Graded ? $objectiveScore : null;

        $this->repository->updateSubmission($submission, [
            'grading_status' => $gradingStatus->value,
            'score' => $objectiveScore,
            'final_score' => $finalScore,
            'status' => $gradingStatus === QuizGradingStatus::Graded
                ? QuizSubmissionStatus::Graded->value
                : QuizSubmissionStatus::Submitted->value,
        ]);

        return $submission->fresh();
    }

    public function getSubmissionWithIncludes(QuizSubmission $submission, array $includes, int $userId): QuizSubmission
    {
        $allowedIncludes = $this->includeAuthorizer->authorize($includes, $userId);

        if (! empty($allowedIncludes)) {
            $submission->load($allowedIncludes);
        }

        return $submission;
    }

    public function getQuestionsForStudent(QuizSubmission $submission, int $page): array
    {
        $questions = $this->listQuestions($submission, $submission->user_id);
        $total = $questions->count();

        if ($page < 1 || $page > $total) {
            throw new \InvalidArgumentException(__('messages.quiz_submissions.invalid_page'));
        }

        $question = $questions->get($page - 1);

        return [
            'question' => $question,
            'meta' => [
                'pagination' => [
                    'current_page' => $page,
                    'total' => $total,
                    'has_next' => $page < $total,
                    'has_prev' => $page > 1,
                ],
            ],
        ];
    }

    public function checkExistingDraft(int $quizId, int $userId): ?QuizSubmission
    {
        return QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->where('status', QuizSubmissionStatus::Draft->value)
            ->first();
    }
}
