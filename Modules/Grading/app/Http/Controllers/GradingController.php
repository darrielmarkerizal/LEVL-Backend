<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Grading\Http\Requests\BulkFeedbackRequest;
use Modules\Grading\Http\Requests\BulkReleaseGradesRequest;
use Modules\Grading\Http\Requests\GradingQueueRequest;
use Modules\Grading\Http\Requests\ManualGradeRequest;
use Modules\Grading\Http\Requests\OverrideGradeRequest;
use Modules\Grading\Http\Requests\SaveDraftGradeRequest;
use Modules\Grading\Services\GradingOrchestratorService;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;

class GradingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly GradingOrchestratorService $orchestrator,
    ) {}

    public function manualGrade(ManualGradeRequest $request, Submission $submission): JsonResponse
    {
        return $this->orchestrator->manualGrade($submission, $request->validated());
    }

    public function manualGradeUnified(ManualGradeRequest $request, int $submissionId): JsonResponse
    {
        $validated = $request->validated();
        $preferQuiz = ! empty($validated['grades']) && ! array_key_exists('score', $validated);

        if ($preferQuiz) {
            $quizSubmission = QuizSubmission::find($submissionId);

            if ($quizSubmission !== null) {
                $this->authorize('view', $quizSubmission);

                return $this->orchestrator->manualGradeQuiz($quizSubmission, $validated);
            }
        }

        $submission = Submission::find($submissionId);

        if ($submission !== null) {
            $this->authorize('grade', $submission);

            return $this->orchestrator->manualGrade($submission, $validated);
        }

        $quizSubmission = QuizSubmission::find($submissionId);

        if ($quizSubmission !== null) {
            $this->authorize('view', $quizSubmission);

            return $this->orchestrator->manualGradeQuiz($quizSubmission, $validated);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.not_found'),
            'data' => null,
            'meta' => null,
            'errors' => null,
        ], 404);
    }

    public function queue(GradingQueueRequest $request): JsonResponse
    {
        return $this->orchestrator->queue($request->all());
    }

    public function show(int $submissionId): JsonResponse
    {
        return $this->orchestrator->showSubmission(
            $submissionId,
            (string) request()->get('include', ''),
            request()->query('type')
        );
    }

    public function showQuizEssayQuestion(QuizSubmission $quizSubmission, int $questionId): JsonResponse
    {
        $this->authorize('view', $quizSubmission);

        return $this->orchestrator->showQuizEssayQuestion($quizSubmission, $questionId);
    }

    public function returnToQueue(Submission $submission): JsonResponse
    {
        return $this->orchestrator->returnToQueue($submission);
    }

    public function saveDraftGrade(SaveDraftGradeRequest $request, Submission $submission): JsonResponse
    {
        return $this->orchestrator->saveDraftGrade($submission, $request->validated());
    }

    public function saveDraftGradeUnified(SaveDraftGradeRequest $request, int $submissionId): JsonResponse
    {
        $validated = $request->validated();
        $preferQuiz = ! empty($validated['grades']) && ! array_key_exists('score', $validated);

        if ($preferQuiz) {
            $quizSubmission = QuizSubmission::find($submissionId);

            if ($quizSubmission !== null) {
                $this->authorize('view', $quizSubmission);

                return $this->orchestrator->saveDraftGradeQuiz($quizSubmission, $validated);
            }
        }

        $submission = Submission::find($submissionId);

        if ($submission !== null) {
            $this->authorize('grade', $submission);

            return $this->orchestrator->saveDraftGrade($submission, $validated);
        }

        $quizSubmission = QuizSubmission::find($submissionId);

        if ($quizSubmission !== null) {
            $this->authorize('view', $quizSubmission);

            return $this->orchestrator->saveDraftGradeQuiz($quizSubmission, $validated);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.not_found'),
            'data' => null,
            'meta' => null,
            'errors' => null,
        ], 404);
    }

    public function getGrade(Submission $submission): JsonResponse
    {
        return $this->orchestrator->getGrade($submission);
    }

    public function overrideGrade(OverrideGradeRequest $request, Submission $submission): JsonResponse
    {
        return $this->orchestrator->overrideGrade(
            $submission,
            (float) $request->validated('score'),
            $request->validated('reason')
        );
    }

    public function releaseGrade(Submission $submission): JsonResponse
    {
        return $this->orchestrator->releaseGrade($submission);
    }

    public function bulkReleaseGrades(BulkReleaseGradesRequest $request): JsonResponse
    {
        return $this->orchestrator->bulkReleaseGrades(
            $request->validated('submission_ids'),
            $request->boolean('async')
        );
    }

    public function bulkApplyFeedback(BulkFeedbackRequest $request): JsonResponse
    {
        return $this->orchestrator->bulkApplyFeedback(
            $request->validated('submission_ids'),
            $request->validated('feedback'),
            $request->boolean('async')
        );
    }

    public function gradingStatus(Submission $submission): JsonResponse
    {
        return $this->orchestrator->gradingStatus($submission);
    }
}
