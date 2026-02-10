<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Forums\Http\Requests\IndexForumStatisticsRequest;
use Modules\Forums\Http\Requests\UserForumStatisticsRequest;
use Modules\Forums\Services\ForumStatisticsService;
use Modules\Schemes\Models\Course;

class ForumStatisticsController extends Controller
{
    use ApiResponse;

    public function index(IndexForumStatisticsRequest $request, Course $course, ForumStatisticsService $statisticsService): JsonResponse
    {
        [$periodStart, $periodEnd] = $statisticsService->parsePeriodFilters($request->input('filter', []));
        $statistics = $statisticsService->getStatistics($course->id, $request->input('filter.user_id'), $periodStart, $periodEnd);

        return $this->success($statistics, __('messages.forums.statistics_retrieved'));
    }

    public function userStats(UserForumStatisticsRequest $request, Course $course, ForumStatisticsService $statisticsService): JsonResponse
    {
        [$periodStart, $periodEnd] = $statisticsService->parsePeriodFilters($request->input('filter', []));
        $statistics = $statisticsService->getUserStatistics($course->id, $request->user(), $periodStart, $periodEnd);

        return $this->success($statistics, __('messages.forums.user_statistics_retrieved'));
    }
}
