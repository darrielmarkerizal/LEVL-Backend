<?php

declare(strict_types=1);

namespace Modules\Grading\Services;

use Modules\Grading\DTOs\BulkOperationDTO;
use Modules\Grading\Jobs\BulkApplyFeedbackJob;
use Modules\Grading\Jobs\BulkReleaseGradesJob;
use Modules\Learning\Models\QuizAnswer;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Models\Submission;

class GradingBulkService
{
    public function __construct(
        private readonly GradingEntryService $entryService
    ) {}

    public function handleBulkRelease(BulkOperationDTO $data): void
    {
        $targets = $this->normalizeTargets($data->submissionIds, $data->targets);
        $this->validateBulkReleaseGrades($targets);

        if ($data->async) {
            BulkReleaseGradesJob::dispatch($targets, $data->performerId);
        } else {
            $this->bulkReleaseGrades($targets, $data->performerId);
        }
    }

    public function handleBulkFeedback(BulkOperationDTO $data): void
    {
        $targets = $this->normalizeTargets($data->submissionIds, $data->targets);
        $this->validateBulkApplyFeedback($targets);

        if ($data->async) {
            BulkApplyFeedbackJob::dispatch(
                $targets,
                (string) $data->feedback,
                $data->performerId
            );
        } else {
            $this->bulkApplyFeedback(
                $targets,
                (string) $data->feedback,
                $data->performerId
            );
        }
    }

    public function bulkReleaseGrades(array $targets, ?int $performerId): int
    {
        $count = 0;

        $assignmentIds = collect($targets)
            ->filter(fn ($target) => ($target['type'] ?? null) === 'assignment')
            ->pluck('submission_id')
            ->unique()
            ->values()
            ->all();

        $quizIds = collect($targets)
            ->filter(fn ($target) => ($target['type'] ?? null) === 'quiz')
            ->pluck('submission_id')
            ->unique()
            ->values()
            ->all();

        foreach ($assignmentIds as $id) {
            try {
                $this->entryService->releaseGrade((int) $id);
                $count++;
            } catch (\Exception $e) {
                continue;
            }
        }

        foreach ($quizIds as $id) {
            try {
                $quizSubmission = QuizSubmission::find((int) $id);

                if (! $quizSubmission) {
                    continue;
                }

                $this->entryService->finalizeQuizSubmission($quizSubmission);
                $count++;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $count;
    }

    public function bulkApplyFeedback(array $targets, string $feedback, ?int $performerId): int
    {
        $count = 0;

        foreach ($targets as $target) {
            try {
                if (($target['type'] ?? null) === 'assignment') {
                    $submission = Submission::with('grade')->find((int) $target['submission_id']);
                    if ($submission && $submission->grade) {
                        $submission->grade->update(['feedback' => $feedback]);
                        $count++;
                    }
                    continue;
                }

                $quizAnswer = QuizAnswer::where('quiz_submission_id', (int) $target['submission_id'])
                    ->where('quiz_question_id', (int) ($target['question_id'] ?? 0))
                    ->first();

                if ($quizAnswer) {
                    $quizAnswer->update(['feedback' => $feedback]);
                    $count++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $count;
    }

    private function validateBulkReleaseGrades(array $targets): void
    {
        $assignmentIds = collect($targets)
            ->filter(fn ($target) => ($target['type'] ?? null) === 'assignment')
            ->pluck('submission_id')
            ->unique()
            ->values()
            ->all();

        $quizIds = collect($targets)
            ->filter(fn ($target) => ($target['type'] ?? null) === 'quiz')
            ->pluck('submission_id')
            ->unique()
            ->values()
            ->all();

        $invalidCount = Submission::whereIn('id', $assignmentIds)
            ->where(function ($q) {
                $q->where('state', '!=', SubmissionState::Graded->value)
                    ->where('state', '!=', SubmissionState::Released->value);
            })
            ->count();

        if ($invalidCount > 0) {
            throw new \InvalidArgumentException(__('messages.grading.bulk_release_invalid_state'));
        }

        $draftCount = \Modules\Grading\Models\Grade::whereIn('submission_id', $assignmentIds)
            ->whereRaw('is_draft IS TRUE')
            ->count();

        if ($draftCount > 0) {
            throw new \InvalidArgumentException(__('messages.grading.bulk_release_draft_grades'));
        }

        if ($quizIds !== []) {
            $missingQuiz = QuizSubmission::whereIn('id', $quizIds)->count() !== count($quizIds);
            if ($missingQuiz) {
                throw new \InvalidArgumentException(__('messages.grading.invalid_submission_ids'));
            }
        }
    }

    private function validateBulkApplyFeedback(array $targets): void
    {
        $assignmentIds = collect($targets)
            ->filter(fn ($target) => ($target['type'] ?? null) === 'assignment')
            ->pluck('submission_id')
            ->unique()
            ->values()
            ->all();

        $quizTargets = collect($targets)
            ->filter(fn ($target) => ($target['type'] ?? null) === 'quiz')
            ->values();

        if ($assignmentIds !== []) {
            $count = Submission::whereIn('id', $assignmentIds)->count();
            if ($count !== count($assignmentIds)) {
                throw new \InvalidArgumentException(__('messages.grading.invalid_submission_ids'));
            }
        }

        foreach ($quizTargets as $target) {
            $exists = QuizAnswer::where('quiz_submission_id', (int) $target['submission_id'])
                ->where('quiz_question_id', (int) ($target['question_id'] ?? 0))
                ->exists();

            if (! $exists) {
                throw new \InvalidArgumentException(__('messages.grading.invalid_submission_ids'));
            }
        }
    }

    private function normalizeTargets(array $submissionIds, array $targets): array
    {
        if ($targets !== []) {
            return collect($targets)
                ->map(function ($target) {
                    return [
                        'type' => (string) ($target['type'] ?? 'assignment'),
                        'submission_id' => (int) ($target['submission_id'] ?? 0),
                        'question_id' => isset($target['question_id']) ? (int) $target['question_id'] : null,
                    ];
                })
                ->values()
                ->all();
        }

        return collect($submissionIds)
            ->map(fn ($id) => [
                'type' => 'assignment',
                'submission_id' => (int) $id,
                'question_id' => null,
            ])
            ->values()
            ->all();
    }
}
