<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Contracts\Services\ForumServiceInterface;
use Modules\Forums\Repositories\ThreadRepository;

class ForumDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ThreadRepository $threadRepository,
        private readonly ForumServiceInterface $forumService,
    ) {}

    public function allThreads(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = [
            'per_page' => (int) $request->input('per_page', 20),
        ];
        $search = $request->input('search');

        if ($user->hasRole('admin')) {
            $threads = $this->threadRepository->getAllThreads($filters, $search);

            return $this->paginateResponse($threads, __('forums.threads_retrieved'));
        }

        if ($user->hasRole('instructor')) {
            $threads = $this->threadRepository->getInstructorThreads($user->id, $filters, $search);

            return $this->paginateResponse($threads, __('forums.threads_retrieved'));
        }

        return $this->error(__('forums.unauthorized_access'), 403);
    }

    public function myThreads(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = [
            'per_page' => (int) $request->input('per_page', 20),
        ];
        $search = $request->input('search');

        $threads = $this->threadRepository->getUserThreads($user->id, $filters, $search);

        return $this->paginateResponse($threads, __('forums.my_threads_retrieved'));
    }

    public function recentThreads(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = (int) $request->input('limit', 10);

        if ($user->hasRole('admin')) {
            $threads = $this->threadRepository->getRecentThreads($limit);

            return $this->success($threads, __('forums.recent_threads_retrieved'));
        }

        if ($user->hasRole('instructor')) {
            $threads = $this->threadRepository->getInstructorRecentThreads($user->id, $limit);

            return $this->success($threads, __('forums.recent_threads_retrieved'));
        }

        return $this->error(__('forums.unauthorized_access'), 403);
    }

    public function trendingThreads(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = (int) $request->input('limit', 10);
        $period = $request->input('period', '7days');

        if ($user->hasRole('admin')) {
            $threads = $this->threadRepository->getTrendingThreads($limit, $period);

            return $this->success($threads, __('forums.trending_threads_retrieved'));
        }

        if ($user->hasRole('instructor')) {
            $threads = $this->threadRepository->getInstructorTrendingThreads($user->id, $limit, $period);

            return $this->success($threads, __('forums.trending_threads_retrieved'));
        }

        return $this->error(__('forums.unauthorized_access'), 403);
    }
}
