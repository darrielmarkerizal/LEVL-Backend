<?php

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Repositories\ForumStatisticsRepository;
use Modules\Schemes\Models\Course;

class ForumStatisticsController extends Controller
{
    use ApiResponse;

    protected ForumStatisticsRepository $statisticsRepository;

    public function __construct(ForumStatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function index(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'filter.period_start' => 'nullable|date',
            'filter.period_end' => 'nullable|date|after_or_equal:filter.period_start',
            'filter.user_id' => 'nullable|integer|exists:users,id',
        ]);

        $periodStart = $request->input('filter.period_start')
            ? Carbon::parse($request->input('filter.period_start'))
            : Carbon::now()->startOfMonth();

        $periodEnd = $request->input('filter.period_end')
            ? Carbon::parse($request->input('filter.period_end'))
            : Carbon::now()->endOfMonth();

        $userId = $request->input('filter.user_id');

        if ($userId) {
            $statistics = $this->statisticsRepository->getUserStatistics(
                $course->id,
                $userId,
                $periodStart,
                $periodEnd
            );

            if (! $statistics) {
                $statistics = $this->statisticsRepository->updateUserStatistics(
                    $course->id,
                    $userId,
                    $periodStart,
                    $periodEnd
                );
            }
        } else {
            $statistics = $this->statisticsRepository->getSchemeStatistics(
                $course->id,
                $periodStart,
                $periodEnd
            );

            if (! $statistics) {
                $statistics = $this->statisticsRepository->updateSchemeStatistics(
                    $course->id,
                    $periodStart,
                    $periodEnd
                );
            }
        }

        return $this->success($statistics, __('messages.forums.statistics_retrieved'));
    }

    public function userStats(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'filter.period_start' => 'nullable|date',
            'filter.period_end' => 'nullable|date|after_or_equal:filter.period_start',
        ]);

        $periodStart = $request->input('filter.period_start')
            ? Carbon::parse($request->input('filter.period_start'))
            : Carbon::now()->startOfMonth();

        $periodEnd = $request->input('filter.period_end')
            ? Carbon::parse($request->input('filter.period_end'))
            : Carbon::now()->endOfMonth();

        $statistics = $this->statisticsRepository->getUserStatistics(
            $course->id,
            $request->user()->id,
            $periodStart,
            $periodEnd
        );

        if (! $statistics) {
            $statistics = $this->statisticsRepository->updateUserStatistics(
                $course->id,
                $request->user()->id,
                $periodStart,
                $periodEnd
            );
        }

        return $this->success($statistics, __('messages.forums.user_statistics_retrieved'));
    }
}
