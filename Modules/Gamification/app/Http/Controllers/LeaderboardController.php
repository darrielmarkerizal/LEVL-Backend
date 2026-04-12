<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Contracts\Services\GamificationServiceInterface;
use Modules\Gamification\Contracts\Services\LeaderboardServiceInterface;
use Modules\Gamification\Transformers\LeaderboardResource;
use Symfony\Component\HttpFoundation\Response;

class LeaderboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LeaderboardServiceInterface $leaderboardService,
        private readonly GamificationServiceInterface $gamificationService
    ) {}

    /**
     * Get Leaderboard
     *
     * Retrieves the global leaderboard with optional pagination and period filtering.
     *
     * @unauthenticated
     *
     * @queryParam filter.period string The time period to filter by. Example: today, this_week, this_month, this_year, all_time
     * @queryParam filter.month string Filter by specific month (YYYY-MM). Example: 2026-01, 2026-02
     * @queryParam per_page int Number of items per page. Example: 15
     * @queryParam page int The page number. Example: 1
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->input('per_page', 15));
        $page = (int) ($request->input('page', 1));
        $courseId = null;
        $period = $request->input('filter.period', 'all_time');
        $month = $request->input('filter.month');
        $search = $request->query('search');

        $result = $this->leaderboardService->getLeaderboardWithRanks(
            $perPage,
            $page,
            $courseId,
            $request->user()?->id,
            $period,
            $search,
            $month
        );

        $result['leaderboard']->appends($request->query());
        $result['leaderboard']->getCollection()->transform(fn ($item) => new LeaderboardResource($item));

        return $this->paginateResponse(
            $result['leaderboard'],
            __('gamification.leaderboard_retrieved'),
            200,
            $result['my_rank'] ? ['my_rank' => $result['my_rank']] : []
        );
    }

    public function myRank(Request $request): JsonResponse
    {
        $period = $request->input('filter.period', 'all_time');
        $month = $request->input('filter.month');
        $rankData = $this->leaderboardService->getUserRank($request->user()->id, $period, $month);

        return $this->success($rankData, __('gamification.rank_retrieved'));
    }

    public function userGamificationLog(Request $request, string $userId): JsonResponse
    {
        $userId = (int) $userId;
        $perPage = (int) ($request->input('per_page') ?? 15);
        $result = $this->gamificationService->getUserGamificationLog($userId, $perPage, $request);
        $result['logs']->appends($request->query());

        return $this->paginateResponse(
            $result['logs'],
            __('gamification.points_history_retrieved'),
            200,
            [
                'summary' => $result['summary'],
                'point_history' => $result['point_history'],
                'badge_history' => $result['badge_history'],
            ]
        );
    }

    public function exportUserGamificationLog(Request $request, string $userId): Response
    {
        $userId = (int) $userId;
        $type = (string) $request->query('type', 'csv');

        return $this->gamificationService->exportUserGamificationLog($userId, $type, $request);
    }
}
