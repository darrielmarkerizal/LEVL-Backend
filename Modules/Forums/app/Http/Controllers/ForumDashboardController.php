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
        $filters = $request->all();
        $search = $request->input('search');

        if ($user->hasRole(['Admin', 'Superadmin'])) {
            $threads = $this->threadRepository->getAllThreads($filters, $search);

            return $this->paginateResponse($threads, __('messages.forums.threads_retrieved'));
        }

        if ($user->hasRole('Instructor')) {
            $threads = $this->threadRepository->getInstructorThreads($user->id, $filters, $search);

            return $this->paginateResponse($threads, __('messages.forums.threads_retrieved'));
        }

        return $this->error(__('messages.forums.unauthorized_access'), [], 403);
    }

    public function myThreads(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->all();
        $search = $request->input('search');

        $threads = $this->threadRepository->getUserThreads($user->id, $filters, $search);

        return $this->paginateResponse($threads, __('messages.forums.my_threads_retrieved'));
    }

    public function trendingThreads(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = (array) $request->input('filter', []);
        $search = $request->input('search');

        if ($user->hasRole(['Admin', 'Superadmin'])) {
            $threads = $this->threadRepository->getTrendingThreads($filters, $search);

            return $this->paginateResponse($threads, __('messages.forums.trending_threads_retrieved'));
        }

        if ($user->hasRole('Instructor')) {
            $threads = $this->threadRepository->getInstructorTrendingThreads($user->id, $filters, $search);

            return $this->paginateResponse($threads, __('messages.forums.trending_threads_retrieved'));
        }

        return $this->error(__('messages.forums.unauthorized_access'), [], 403);
    }
}
