<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Learning\Contracts\Services\QuizQuestionServiceInterface;
use Modules\Learning\Contracts\Services\QuizServiceInterface;
use Modules\Learning\Http\Requests\StoreQuizQuestionRequest;
use Modules\Learning\Http\Requests\StoreQuizRequest;
use Modules\Learning\Http\Requests\UpdateQuizQuestionRequest;
use Modules\Learning\Http\Requests\UpdateQuizRequest;
use Modules\Learning\Http\Resources\QuizQuestionResource;
use Modules\Learning\Http\Resources\QuizResource;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizQuestion;

class QuizController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;
    use \Modules\Schemes\Traits\ValidatesEnrollment;

    public function __construct(
        private readonly QuizServiceInterface $quizService,
        private readonly QuizQuestionServiceInterface $questionService
    ) {}

    public function index(Request $request, \Modules\Schemes\Models\Course $course): JsonResponse
    {
        $user = auth('api')->user();

        // For students, check enrollment first
        if ($user && $user->hasRole('Student')) {
            // Use trait method for enrollment validation
            if ($error = $this->requireEnrollment($course)) {
                return $error;
            }

            // Force published status filter for students
            $filters = array_merge($request->all(), [
                'filter' => array_merge($request->input('filter', []), [
                    'status' => 'published',
                ]),
            ]);

            $paginator = $this->quizService->listForIndexWithEnrichment($course, $filters, $user);
        } else {
            $paginator = $this->quizService->listForIndexWithEnrichment($course, $request->all(), $user);
        }

        return $this->paginateResponse($paginator, 'messages.quizzes.list_retrieved');
    }

    public function store(StoreQuizRequest $request): JsonResponse
    {
        $course = $this->quizService->resolveCourseFromScopeOrFail($request->getResolvedScope());
        $this->authorize('create', [Quiz::class, $course]);

        $quiz = $this->quizService->create($request->validated(), auth('api')->id());

        return $this->created(QuizResource::make($quiz), __('messages.quizzes.created'));
    }

    public function show(Quiz $quiz): JsonResponse
    {
        $user = auth('api')->user();
        $enrichmentService = app(\Modules\Learning\Services\Support\QuizEnrichmentService::class);

        if ($user && $user->hasRole('Student')) {
            // Get course from quiz scope
            $course = $quiz->getCourse();

            if (! $course) {
                return $this->error(__('messages.quizzes.scope_not_found'), [], 404);
            }

            // Use trait method for enrollment validation
            if ($error = $this->requireEnrollment($course)) {
                return $error;
            }

            $enriched = $enrichmentService->enrichSingleForStudent($quiz, $user->id);

            if ($enriched['is_locked']) {
                return $this->error(__('messages.quizzes.locked'), [], 403);
            }

            return $this->success($enriched);
        }

        return $this->success(QuizResource::make($this->quizService->getWithRelations($quiz)));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);
        $updated = $this->quizService->update($quiz, $request->validated());

        return $this->success(QuizResource::make($updated), __('messages.quizzes.updated'));
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        $this->authorize('delete', $quiz);
        $this->quizService->delete($quiz);

        return $this->success([], __('messages.quizzes.deleted'));
    }

    public function publish(Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);
        $updated = $this->quizService->publish($quiz);

        return $this->success(QuizResource::make($updated), __('messages.quizzes.published'));
    }

    public function unpublish(Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);
        $updated = $this->quizService->unpublish($quiz);

        return $this->success(QuizResource::make($updated), __('messages.quizzes.unpublished'));
    }

    public function archive(Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);
        $archived = $this->quizService->archive($quiz);

        return $this->success(QuizResource::make($archived), __('messages.quizzes.archived'));
    }

    public function listQuestions(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('view', $quiz);
        $user = auth('api')->user();
        $questions = $this->questionService->getQuizQuestionsForUser($quiz->id, $request->all(), $user);

        return $this->paginateResponse($questions, 'messages.quizzes.questions_retrieved');
    }

    public function showQuestion(Quiz $quiz, QuizQuestion $question): JsonResponse
    {
        $this->authorize('view', $quiz);
        $this->questionService->validateQuestionBelongsToQuiz($question->id, $quiz->id);

        return $this->success(QuizQuestionResource::make($question));
    }

    public function addQuestion(StoreQuizQuestionRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);
        $question = $this->questionService->createQuestion($quiz->id, $request->validated());

        return $this->created(QuizQuestionResource::make($question), __('messages.questions.created'));
    }

    public function updateQuestion(UpdateQuizQuestionRequest $request, Quiz $quiz, QuizQuestion $question): JsonResponse
    {
        $this->authorize('update', $quiz);
        $updated = $this->questionService->updateQuestion($question->id, $request->validated(), $quiz->id);

        return $this->success(QuizQuestionResource::make($updated), __('messages.questions.updated'));
    }

    public function deleteQuestion(Quiz $quiz, QuizQuestion $question): JsonResponse
    {
        $this->authorize('update', $quiz);
        $this->questionService->deleteQuestion($question->id, $quiz->id);

        return $this->success([], __('messages.questions.deleted'));
    }

    public function reorderQuestions(\Modules\Learning\Http\Requests\ReorderQuizQuestionsRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);
        $this->questionService->reorderQuestions($quiz->id, $request->validated('ids'));

        return $this->success([], __('messages.questions.reordered'));
    }
}
