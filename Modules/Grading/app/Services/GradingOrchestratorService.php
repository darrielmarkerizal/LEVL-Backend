<?php

declare(strict_types=1);

namespace Modules\Grading\Services;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Modules\Grading\DTOs\BulkOperationDTO;
use Modules\Grading\DTOs\SubmissionGradeDTO;
use Modules\Grading\Http\Resources\GradeResource;
use Modules\Grading\Http\Resources\GradingQueueItemResource;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;

class GradingOrchestratorService
{
    use ApiResponse;

    public function __construct(
        private readonly GradingEntryService $entryService,
        private readonly GradingQueueService $queueService,
        private readonly GradingBulkService $bulkService,
    ) {}

    public function manualGrade(Submission $submission, array $validated): JsonResponse
    {
        try {
            $hasGrades = ! empty($validated['grades']);

            if ($hasGrades) {
                $dto = new SubmissionGradeDTO(
                    submissionId: $submission->id,
                    answers: $validated['grades'],
                    scoreOverride: null,
                    feedback: $validated['feedback'] ?? null,
                    graderId: auth('api')->id()
                );

                $grade = $this->entryService->manualGrade($dto);

                return $this->success(
                    GradeResource::make($grade),
                    __('messages.grading.manual_graded')
                );
            }

            $dto = new SubmissionGradeDTO(
                submissionId: $submission->id,
                answers: [],
                scoreOverride: (float) ($validated['score'] ?? 0),
                feedback: $validated['feedback'] ?? null,
                graderId: auth('api')->id()
            );

            $grade = $this->entryService->manualGrade($dto);
            $submission->refresh();

            return $this->success(
                [
                    'submission_id' => $submission->id,
                    'score' => $submission->score,
                    'grade' => GradeResource::make($grade),
                ],
                __('messages.grading.assignment_graded')
            );
        } catch (InvalidArgumentException|\Modules\Learning\Exceptions\SubmissionException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function queue(array $filters): JsonResponse
    {
        $actor = auth('api')->user();
        $scopeToInstructorCourses = $actor
            && $actor->hasRole('Instructor')
            && ! $actor->hasAnyRole(['Admin', 'Superadmin']);

        $paginator = $this->queueService->getGradingQueue(
            $filters,
            $actor?->id,
            $scopeToInstructorCourses
        );
        $paginator->getCollection()->transform(fn ($item) => new GradingQueueItemResource($item));

        return $this->paginateResponse($paginator, 'messages.grading.queue_fetched');
    }

    public function showSubmission(Submission $submission, string $includeParam = ''): JsonResponse
    {
        if (empty($includeParam)) {
            $submission->load([
                'user:id,name,email',
                'assignment:id,title,max_score,description,submission_type,unit_id,order',
                'assignment.unit:id,course_id,order',
                'assignment.unit.course:id,title,slug,code',
                'answers.question',
                'media',
            ]);
        } else {
            $submission = \Spatie\QueryBuilder\QueryBuilder::for(\Modules\Learning\Models\Submission::class)
                ->where('id', $submission->id)
                ->allowedIncludes(['user', 'assignment', 'assignment.unit', 'assignment.unit.course', 'answers', 'answers.question', 'grade'])
                ->firstOrFail();
        }

        $submission->loadMissing([
            'user:id,name,email',
            'assignment:id,title,max_score,description,submission_type,unit_id,order',
            'assignment.unit:id,course_id,order',
            'assignment.unit.course:id,title,slug,code',
            'media',
        ]);

        return $this->success(
            new GradingQueueItemResource($submission),
            __('messages.grading.submission_fetched')
        );
    }

    public function showQuizEssayQuestion(QuizSubmission $quizSubmission, int $questionId): JsonResponse
    {
        $row = $this->queueService->getQuizEssayRow($quizSubmission, $questionId);

        if ($row === null) {
            return $this->error('Essay question not found for this submission.', [], 404);
        }

        return $this->success(new GradingQueueItemResource($row));
    }

    public function returnToQueue(Submission $submission): JsonResponse
    {
        try {
            $this->entryService->returnToQueue($submission->id);

            return $this->success(
                ['submission_id' => $submission->id, 'workflow_state' => $submission->refresh()->state?->value],
                __('messages.grading.returned_to_queue')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function saveDraftGrade(Submission $submission, array $validated): JsonResponse
    {
        try {
            $hasGrades = ! empty($validated['grades']);
            $dto = new SubmissionGradeDTO(
                submissionId: $submission->id,
                answers: $validated['grades'] ?? [],
                scoreOverride: $hasGrades ? null : (float) ($validated['score'] ?? 0),
                feedback: $validated['feedback'] ?? null,
                graderId: auth('api')->id()
            );

            $this->entryService->saveDraftGrade($dto);

            return $this->success(['submission_id' => $submission->id], __('messages.grading.draft_saved'));
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function getGrade(Submission $submission): JsonResponse
    {
        $grade = $this->queueService->getGrade($submission->id);

        return $this->success($grade ? GradeResource::make($grade) : null);
    }

    public function overrideGrade(Submission $submission, float $score, string $reason): JsonResponse
    {
        try {
            $this->entryService->overrideGrade($submission->id, $score, $reason);
            $submission->refresh();

            return $this->success(
                [
                    'submission_id' => $submission->id,
                    'score' => $submission->score,
                    'grade' => $submission->grade ? GradeResource::make($submission->grade) : null,
                ],
                __('messages.grading.grade_overridden')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function releaseGrade(Submission $submission): JsonResponse
    {
        try {
            $this->entryService->releaseGrade($submission->id);

            return $this->success(
                [
                    'submission_id' => $submission->id,
                    'workflow_state' => $submission->refresh()->state?->value,
                    'grade' => $submission->grade ? GradeResource::make($submission->grade) : null,
                ],
                __('messages.grading.grade_released')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function bulkReleaseGrades(array $submissionIds, bool $isAsync): JsonResponse
    {
        try {
            $dto = new BulkOperationDTO(
                submissionIds: $submissionIds,
                performerId: auth('api')->id(),
                async: $isAsync
            );

            $this->bulkService->handleBulkRelease($dto);

            return $this->success(
                ['async' => $isAsync],
                $isAsync ? __('messages.grading.bulk_release_queued') : __('messages.grading.bulk_released')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function bulkApplyFeedback(array $submissionIds, string $feedback, bool $isAsync): JsonResponse
    {
        try {
            $dto = new BulkOperationDTO(
                submissionIds: $submissionIds,
                feedback: $feedback,
                performerId: auth('api')->id(),
                async: $isAsync
            );

            $this->bulkService->handleBulkFeedback($dto);

            return $this->success(
                ['async' => $isAsync],
                $isAsync ? __('messages.grading.bulk_feedback_queued') : __('messages.grading.bulk_feedback_applied')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function gradingStatus(Submission $submission): JsonResponse
    {
        return $this->success($this->queueService->getGradingStatusDetails($submission->id));
    }
}
