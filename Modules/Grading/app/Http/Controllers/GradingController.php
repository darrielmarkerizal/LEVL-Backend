<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use InvalidArgumentException;
use Modules\Grading\DTOs\BulkOperationDTO;
use Modules\Grading\DTOs\SubmissionGradeDTO;
use Modules\Grading\Http\Requests\BulkFeedbackRequest;
use Modules\Grading\Http\Requests\BulkReleaseGradesRequest;
use Modules\Grading\Http\Requests\GradingQueueRequest;
use Modules\Grading\Http\Requests\ManualGradeRequest;
use Modules\Grading\Http\Requests\OverrideGradeRequest;
use Modules\Grading\Http\Requests\SaveDraftGradeRequest;
use Modules\Grading\Http\Resources\GradeResource;
use Modules\Grading\Http\Resources\GradingQueueItemResource;
use Modules\Grading\Services\GradingBulkService;
use Modules\Grading\Services\GradingEntryService;
use Modules\Grading\Services\GradingQueueService;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;

class GradingController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function __construct(
        private readonly GradingEntryService $entryService,
        private readonly GradingQueueService $queueService,
        private readonly GradingBulkService $bulkService,
    ) {}

    public function manualGrade(ManualGradeRequest $request, Submission $submission): JsonResponse
    {
        try {
            
            $hasGrades = !empty($request->validated('grades'));

            if ($hasGrades) {
                
                $dto = new SubmissionGradeDTO(
                    submissionId: $submission->id,
                    answers: $request->validated('grades'),
                    scoreOverride: null,
                    feedback: $request->validated('feedback'),
                    graderId: auth('api')->id()
                );

                $grade = $this->entryService->manualGrade($dto);

                return $this->success(
                    GradeResource::make($grade),
                    __('messages.grading.manual_graded')
                );
            } else {
                
                $dto = new SubmissionGradeDTO(
                    submissionId: $submission->id,
                    answers: [],
                    scoreOverride: (float) $request->validated('score'),
                    feedback: $request->validated('feedback'),
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
            }
        } catch (InvalidArgumentException|\Modules\Learning\Exceptions\SubmissionException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function queue(GradingQueueRequest $request): JsonResponse
    {
        $actor = auth('api')->user();
        $scopeToInstructorCourses = $actor
            && $actor->hasRole('Instructor')
            && ! $actor->hasAnyRole(['Admin', 'Superadmin']);
        $paginator = $this->queueService->getGradingQueue(
            $request->all(),
            $actor?->id,
            $scopeToInstructorCourses
        );
        $paginator->getCollection()->transform(fn ($item) => new GradingQueueItemResource($item));

        return $this->paginateResponse($paginator, 'messages.grading.queue_fetched');
    }

    public function show(Submission $submission): JsonResponse
    {
        $includeParam = request()->get('include', '');

        if (empty($includeParam)) {
            $submission->load([
                'user:id,name,email',
                'assignment:id,title,max_score,instructions',
                'assignment.course:id,title',
                'answers.question',
            ]);
        } else {
            $submission = \Spatie\QueryBuilder\QueryBuilder::for(\Modules\Learning\Models\Submission::class)
                ->where('id', $submission->id)
                ->allowedIncludes(['user', 'assignment', 'assignment.course', 'answers', 'answers.question', 'grade'])
                ->firstOrFail();
        }

        return $this->success(
            new GradingQueueItemResource($submission),
            __('messages.grading.submission_fetched')
        );
    }

    public function showQuizEssayQuestion(QuizSubmission $submission, int $questionId): JsonResponse
    {
        $this->authorize('view', $submission);
        $row = $this->queueService->getQuizEssayRow($submission, $questionId);

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

    public function saveDraftGrade(SaveDraftGradeRequest $request, Submission $submission): JsonResponse
    {
        try {
            $dto = new SubmissionGradeDTO(
                submissionId: $submission->id,
                answers: $request->validated('grades'),
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

    public function overrideGrade(OverrideGradeRequest $request, Submission $submission): JsonResponse
    {
        try {
            $this->entryService->overrideGrade(
                $submission->id,
                (float) $request->validated('score'),
                $request->validated('reason')
            );

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

    public function bulkReleaseGrades(BulkReleaseGradesRequest $request): JsonResponse
    {
        try {
            $dto = new BulkOperationDTO(
                submissionIds: $request->validated('submission_ids'),
                performerId: auth('api')->id(),
                async: $request->boolean('async')
            );

            
            $this->bulkService->handleBulkRelease($dto);

            
            
            
            
            
            

            $isAsync = $request->boolean('async');
            $response = [
                'async' => $isAsync,
                
                
            ];

            return $this->success($response, $isAsync ? __('messages.grading.bulk_release_queued') : __('messages.grading.bulk_released'));
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function bulkApplyFeedback(BulkFeedbackRequest $request): JsonResponse
    {
        try {
            $dto = new BulkOperationDTO(
                submissionIds: $request->validated('submission_ids'),
                feedback: $request->validated('feedback'),
                performerId: auth('api')->id(),
                async: $request->boolean('async')
            );

            $this->bulkService->handleBulkFeedback($dto);

            $isAsync = $request->boolean('async');
            $response = ['async' => $isAsync];

            return $this->success($response, $isAsync ? __('messages.grading.bulk_feedback_queued') : __('messages.grading.bulk_feedback_applied'));
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function gradingStatus(Submission $submission): JsonResponse
    {
        return $this->success($this->queueService->getGradingStatusDetails($submission->id));
    }
}
