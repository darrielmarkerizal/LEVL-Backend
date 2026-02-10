<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Http\Resources\ThreadResource;
use Modules\Forums\Services\ThreadDashboardService;

class ForumDashboardController extends Controller
{
    use ApiResponse;

    public function allThreads(Request $request, ThreadDashboardService $dashboardService): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole(['Admin', 'Superadmin', 'Instructor']), 403, __('messages.forums.unauthorized_access'));
        $threads = $dashboardService->getAllThreads($user, $request->all(), (int) $request->input('per_page', 20));
        $threads->getCollection()->transform(fn ($item) => new ThreadResource($item));
        return $this->paginateResponse($threads, __('messages.forums.threads_retrieved'));
    }

    public function myThreads(Request $request, ThreadDashboardService $dashboardService): JsonResponse
    {
        $user = $request->user();
        $threads = $dashboardService->getMyThreads($user, $request->all(), (int) $request->input('per_page', 20));
        $threads->getCollection()->transform(fn ($item) => new ThreadResource($item));
        return $this->paginateResponse($threads, __('messages.forums.my_threads_retrieved'));
    }

    public function trendingThreads(Request $request, ThreadDashboardService $dashboardService): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole(['Admin', 'Superadmin', 'Instructor']), 403, __('messages.forums.unauthorized_access'));
        $threads = $dashboardService->getTrendingThreads($user, $request->all(), (int) $request->input('per_page', 20));
        $threads->getCollection()->transform(fn ($item) => new ThreadResource($item));
        return $this->paginateResponse($threads, __('messages.forums.trending_threads_retrieved'));
    }
}
