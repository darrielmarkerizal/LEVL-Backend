<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use InvalidArgumentException;
use Modules\Grading\Contracts\Services\AppealServiceInterface;
use Modules\Grading\Http\Requests\DenyAppealRequest;
use Modules\Grading\Http\Requests\SubmitAppealRequest;
use Modules\Grading\Http\Resources\AppealResource;
use Modules\Grading\Models\Appeal;
use Modules\Learning\Models\Submission;

class AppealController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function __construct(
        private readonly AppealServiceInterface $appealService
    ) {}

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $filters = $request->all();
        $appeals = $this->appealService->getAppeals($filters);

        return $this->paginateResponse(
            $appeals, 
            __('messages.appeals.list_retrieved')
        );
    }

    public function submit(SubmitAppealRequest $request, Submission $submission): JsonResponse
    {
        // $submission dependency is used for route binding check effectively, service handles ownership logic
        try {
            $appeal = $this->appealService->submitAppeal(
                $submission->id,
                auth('api')->id(),
                $request->validated('reason'),
                $request->allFiles()
            );

            return $this->created(['appeal' => AppealResource::make($appeal)], __('messages.appeals.submitted'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
             return $this->forbidden($e->getMessage());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function approve(Appeal $appeal): JsonResponse
    {
        try {
            $this->appealService->approveAppeal($appeal->id, auth('api')->id());
            return $this->success(
                AppealResource::make($appeal->refresh()->load(['submission.assignment', 'student', 'reviewer'])),
                __('messages.appeals.approved')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function deny(DenyAppealRequest $request, Appeal $appeal): JsonResponse
    {
        try {
            $this->appealService->denyAppeal($appeal->id, auth('api')->id(), $request->validated('reason'));
            return $this->success(
                AppealResource::make($appeal->refresh()->load(['submission.assignment', 'student', 'reviewer'])),
                __('messages.appeals.denied')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function pending(): JsonResponse
    {
        return $this->success(AppealResource::collection($this->appealService->getPendingAppeals(auth('api')->id())), __('messages.appeals.pending_fetched'));
    }

    public function show(Appeal $appeal): JsonResponse
    {
        try {
            $appeal = $this->appealService->getAppealForUser($appeal, auth('api')->id());
            return $this->success(AppealResource::make($appeal));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->forbidden($e->getMessage());
        }
    }
}
