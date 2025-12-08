<?php

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Repositories\ForumStatisticsRepository;

/**
 * @tags Forum Diskusi
 */
class ForumStatisticsController extends Controller
{
    use ApiResponse;

    protected ForumStatisticsRepository $statisticsRepository;

    public function __construct(ForumStatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    /**
     * Get forum statistics for a scheme.
     */
    public function index(Request $request, int $schemeId): JsonResponse
    {
        $request->validate([
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $periodStart = $request->input('period_start')
            ? Carbon::parse($request->input('period_start'))
            : Carbon::now()->startOfMonth();

        $periodEnd = $request->input('period_end')
            ? Carbon::parse($request->input('period_end'))
            : Carbon::now()->endOfMonth();

        $userId = $request->input('user_id');

        try {
            if ($userId) {
                $statistics = $this->statisticsRepository->getUserStatistics(
                    $schemeId,
                    $userId,
                    $periodStart,
                    $periodEnd
                );

                if (! $statistics) {
                    $statistics = $this->statisticsRepository->updateUserStatistics(
                        $schemeId,
                        $userId,
                        $periodStart,
                        $periodEnd
                    );
                }
            } else {
                $statistics = $this->statisticsRepository->getSchemeStatistics(
                    $schemeId,
                    $periodStart,
                    $periodEnd
                );

                if (! $statistics) {
                    $statistics = $this->statisticsRepository->updateSchemeStatistics(
                        $schemeId,
                        $periodStart,
                        $periodEnd
                    );
                }
            }

            return $this->success($statistics, __('forums.statistics_retrieved'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get current user's forum statistics.
     */
    public function userStats(Request $request, int $schemeId): JsonResponse
    {
        $request->validate([
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $periodStart = $request->input('period_start')
            ? Carbon::parse($request->input('period_start'))
            : Carbon::now()->startOfMonth();

        $periodEnd = $request->input('period_end')
            ? Carbon::parse($request->input('period_end'))
            : Carbon::now()->endOfMonth();

        try {
            $statistics = $this->statisticsRepository->getUserStatistics(
                $schemeId,
                $request->user()->id,
                $periodStart,
                $periodEnd
            );

            if (! $statistics) {
                $statistics = $this->statisticsRepository->updateUserStatistics(
                    $schemeId,
                    $request->user()->id,
                    $periodStart,
                    $periodEnd
                );
            }

            return $this->success($statistics, __('forums.user_statistics_retrieved'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
