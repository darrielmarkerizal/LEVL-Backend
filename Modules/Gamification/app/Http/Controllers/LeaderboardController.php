<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Services\LeaderboardService;
use Modules\Gamification\Transformers\LeaderboardResource;

class LeaderboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LeaderboardService $leaderboardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->input('per_page', 15));
        $page = (int) ($request->input('page', 1));
        $courseId = null;

        $result = $this->leaderboardService->getLeaderboardWithRanks(
            $perPage,
            $page,
            $courseId,
            $request->user()?->id
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
        $rankData = $this->leaderboardService->getUserRank($request->user()->id);

        return $this->success($rankData, __('gamification.rank_retrieved'));
    }
}
