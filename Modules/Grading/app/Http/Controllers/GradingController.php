<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use InvalidArgumentException;
use Modules\Grading\Contracts\Services\GradingServiceInterface;
use Modules\Grading\Http\Requests\BulkFeedbackRequest;
use Modules\Grading\Http\Requests\BulkReleaseGradesRequest;
use Modules\Grading\Http\Requests\GradingQueueRequest;
use Modules\Grading\Http\Requests\ManualGradeRequest;
use Modules\Grading\Http\Requests\OverrideGradeRequest;
use Modules\Grading\Http\Requests\SaveDraftGradeRequest;
use Modules\Grading\Http\Resources\DraftGradeResource;
use Modules\Grading\Http\Resources\GradeResource;
use Modules\Grading\Http\Resources\GradingQueueItemResource;
use Modules\Grading\Jobs\BulkApplyFeedbackJob;
use Modules\Grading\Jobs\BulkReleaseGradesJob;
use Modules\Learning\Models\Submission;

class GradingController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function __construct(
        private readonly GradingServiceInterface $gradingService
    ) {}

    public function manualGrade(ManualGradeRequest $request, Submission $submission): JsonResponse
    {
        try {
            $grade = $this->gradingService->manualGrade(
                $submission->id,
                $request->validated('grades'),
                $request->validated('feedback')
            );

            return $this->success(
                GradeResource::make($grade->load(['submission', 'user', 'grader'])),
                __('messages.grading.manual_graded')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function queue(GradingQueueRequest $request): JsonResponse
    {
        $paginator = $this->gradingService->getGradingQueue($request->all());
        $paginator->getCollection()->transform(fn($item) => new GradingQueueItemResource($item));

        return $this->paginateResponse($paginator, 'messages.grading.queue_fetched');
    }

    public function show(Submission $submission): JsonResponse
    {
        $submission->load([
            'user:id,name,email',
            'assignment:id,title,max_score,instructions',
            'assignment.course:id,title',
            'answers.question'
        ]);

        return $this->success(
            new GradingQueueItemResource($submission),
            __('messages.grading.submission_fetched')
        );
    }

    public function returnToQueue(Submission $submission): JsonResponse
    {
        try {
            $this->gradingService->returnToQueue($submission->id);
            return $this->success(
                ['submission_id' => $submission->id, 'state' => $submission->refresh()->state?->value],
                __('messages.grading.returned_to_queue')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function saveDraftGrade(SaveDraftGradeRequest $request, Submission $submission): JsonResponse
    {
        try {
            $this->gradingService->saveDraftGrade($submission->id, $request->validated('grades'));
            return $this->success(['submission_id' => $submission->id], __('messages.grading.draft_saved'));
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function getDraftGrade(Submission $submission): JsonResponse
    {
        $draftGrade = $this->gradingService->getDraftGrade($submission->id);
        return $this->success($draftGrade ? DraftGradeResource::make($draftGrade) : null);
    }

    public function overrideGrade(OverrideGradeRequest $request, Submission $submission): JsonResponse
    {
        try {
            $this->gradingService->overrideGrade(
                $submission->id,
                (float) $request->validated('score'),
                $request->validated('reason')
            );

            $submission->refresh()->load('grade');

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
            $this->gradingService->releaseGrade($submission->id);
            return $this->success(
                [
                    'submission_id' => $submission->id,
                    'state' => $submission->refresh()->state?->value,
                    'grade' => $submission->load('grade')->grade ? GradeResource::make($submission->grade) : null,
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
            $result = $this->gradingService->handleBulkRelease($request->validated('submission_ids'), auth('api')->id(), $request->boolean('async'));
            return $this->success($result, $result['async'] ? __('messages.grading.bulk_release_queued') : __('messages.grading.bulk_released'));
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function bulkApplyFeedback(BulkFeedbackRequest $request): JsonResponse
    {
        try {
            $result = $this->gradingService->handleBulkFeedback($request->validated('submission_ids'), $request->validated('feedback'), auth('api')->id(), $request->boolean('async'));
            return $this->success($result, $result['async'] ? __('messages.grading.bulk_feedback_queued') : __('messages.grading.bulk_feedback_applied'));
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function gradingStatus(Submission $submission): JsonResponse
    {
        return $this->success($this->gradingService->getGradingStatusDetails($submission->id));
    }
}
