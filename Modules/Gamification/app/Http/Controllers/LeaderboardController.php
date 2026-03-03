<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Contracts\Services\LeaderboardServiceInterface;
use Modules\Gamification\Transformers\LeaderboardResource;
use Modules\Gamification\Contracts\Services\GamificationServiceInterface;
use Modules\Gamification\Transformers\PointResource;

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
     * @queryParam filter.period string The time period to filter by. Example: today, this_week, this_month, this_year, all_time
     * @queryParam per_page int Number of items per page. Example: 15
     * @queryParam page int The page number. Example: 1
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->input('per_page', 15));
        $page = (int) ($request->input('page', 1));
        $courseId = null;
        $period = $request->input('filter.period', 'all_time');
        $search = $request->query('search');

        $result = $this->leaderboardService->getLeaderboardWithRanks(
            $perPage,
            $page,
            $courseId,
            $request->user()?->id,
            $period,
            $search
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
        $rankData = $this->leaderboardService->getUserRank($request->user()->id, $period);

        return $this->success($rankData, __('gamification.rank_retrieved'));
    }

    public function userPointsHistory(Request $request, int $userId): JsonResponse
    {
        $perPage = (int) ($request->input('per_page') ?? 15);
        $perPage = $perPage > 0 ? $perPage : 15;

        $points = $this->gamificationService->getPointsHistory($userId, $perPage);
        $points->appends($request->query());

        $points->getCollection()->transform(fn ($item) => new PointResource($item));

        return $this->paginateResponse($points, __('gamification.points_history_retrieved'));
    }
}
