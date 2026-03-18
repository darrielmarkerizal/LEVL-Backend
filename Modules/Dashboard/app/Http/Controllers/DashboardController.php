<?php

declare(strict_types=1);

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\DashboardService;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * Get student dashboard overview
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->dashboardService->getOverview($userId);

        return $this->success($data, __('messages.dashboard_retrieved'));
    }

    /**
     * Get recent learning activities
     */
    public function recentLearning(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $limit = min((int) $request->get('limit', 1), 10);

        $data = $this->dashboardService->getRecentLearning($userId, $limit);

        return $this->success($data, __('messages.recent_learning_retrieved'));
    }

    /**
     * Get recent achievements (badges)
     */
    public function recentAchievements(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $limit = min((int) $request->get('limit', 4), 20);

        $data = $this->dashboardService->getRecentAchievements($userId, $limit);

        return $this->success($data, __('messages.recent_achievements_retrieved'));
    }

    /**
     * Get recommended courses
     */
    public function recommendedCourses(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $limit = min((int) $request->get('limit', 2), 10);

        $data = $this->dashboardService->getRecommendedCourses($userId, $limit);

        return $this->success($data, __('messages.recommended_courses_retrieved'));
    }
}
