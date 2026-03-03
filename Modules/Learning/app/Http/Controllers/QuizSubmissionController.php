<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Learning\Contracts\Services\QuizSubmissionServiceInterface;
use Modules\Learning\Http\Requests\SaveQuizAnswerRequest;
use Modules\Learning\Http\Resources\QuizSubmissionResource;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Services\Support\QuizSubmissionIncludeAuthorizer;

class QuizSubmissionController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function __construct(
        private readonly QuizSubmissionServiceInterface $submissionService,
        private readonly QuizSubmissionIncludeAuthorizer $includeAuthorizer
    ) {}

    public function index(Quiz $quiz): JsonResponse
    {
        $this->authorize('viewSubmissions', $quiz);
        $paginator = $this->submissionService->listForQuiz($quiz->id, request()->all());
        $paginator->getCollection()->transform(fn ($item) => new QuizSubmissionResource($item));

        return $this->paginateResponse($paginator, 'messages.quiz_submissions.list_retrieved');
    }

    public function start(Quiz $quiz): JsonResponse
    {
        $this->authorize('takeQuiz', $quiz);
        $userId = auth('api')->id();

        $existingDraft = QuizSubmission::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->where('status', \Modules\Learning\Enums\QuizSubmissionStatus::Draft)
            ->first();

        if ($existingDraft) {
            return $this->error(__('messages.quiz_submissions.already_started'), [
                'submission_id' => $existingDraft->id,
            ], 422);
        }

        $submission = $this->submissionService->start($quiz, $userId);

        return $this->created(QuizSubmissionResource::make($submission), __('messages.quiz_submissions.started'));
    }

    public function mySubmissions(Quiz $quiz): JsonResponse
    {
        $submissions = $this->submissionService->getMySubmissions($quiz->id, auth('api')->id());

        return $this->success(QuizSubmissionResource::collection($submissions));
    }

    public function highestSubmission(Quiz $quiz): JsonResponse
    {
        $submission = $this->submissionService->getHighestSubmission($quiz->id, auth('api')->id());

        return $this->success(
            $submission ? QuizSubmissionResource::make($submission) : null
        );
    }

    public function show(Quiz $quiz, QuizSubmission $submission): JsonResponse
    {
        $this->authorize('view', $submission);

        $includeParam = request()->get('include', '');
        if (! empty($includeParam)) {
            $user = auth('api')->user();
            $allowedIncludes = $this->includeAuthorizer->getAllowedIncludesForQueryBuilder($user, $submission);

            $submission = \Spatie\QueryBuilder\QueryBuilder::for(\Modules\Learning\Models\QuizSubmission::class)
                ->where('id', $submission->id)
                ->allowedIncludes($allowedIncludes)
                ->firstOrFail();
        }

        return $this->success(QuizSubmissionResource::make($submission));
    }

    public function listQuestions(QuizSubmission $submission): JsonResponse
    {
        $this->authorize('view', $submission);
        $user = auth('api')->user();
        $page = (int) request()->get('page', 1);

        if ($user && $user->hasRole('Student')) {
            $questions = $this->submissionService->listQuestions($submission, $user->id);
            $total = $questions->count();

            if ($page < 1 || $page > $total) {
                return $this->error(__('messages.quiz_submissions.invalid_page'), [], 404);
            }

            $question = $questions->get($page - 1);

            return $this->success([
                'data' => new \Modules\Learning\Http\Resources\QuizQuestionResource($question),
                'meta' => [
                    'pagination' => [
                        'current_page' => $page,
                        'total' => $total,
                        'has_next' => $page < $total,
                        'has_prev' => $page > 1,
                    ],
                ],
            ]);
        }

        $questions = $this->submissionService->listQuestions($submission, $submission->user_id);

        return $this->success(\Modules\Learning\Http\Resources\QuizQuestionResource::collection($questions));
    }

    public function getQuestionAtOrder(QuizSubmission $submission, int $order): JsonResponse
    {
        $this->authorize('view', $submission);
        $result = $this->submissionService->getQuestionAtOrder($submission, $order);

        return $this->success([
            'question' => new \Modules\Learning\Http\Resources\QuizQuestionResource($result['question']),
            'navigation' => $result['navigation'],
        ]);
    }

    public function saveAnswer(SaveQuizAnswerRequest $request, QuizSubmission $submission): JsonResponse
    {
        $this->authorize('update', $submission);
        $questionId = $request->validated('quiz_question_id');
        $answer = $this->submissionService->saveAnswer($submission, $questionId, $request->validated());

        return $this->success($answer, __('messages.quiz_submissions.answer_saved'));
    }

    public function submit(QuizSubmission $submission): JsonResponse
    {
        $this->authorize('update', $submission);
        $submitted = $this->submissionService->submit($submission, auth('api')->id());

        return $this->success(QuizSubmissionResource::make($submitted), __('messages.quiz_submissions.submitted'));
    }
}
