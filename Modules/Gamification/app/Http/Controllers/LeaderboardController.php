<?php

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Services\LeaderboardService;

class LeaderboardController extends Controller
{
    public function __construct(
        private readonly LeaderboardService $leaderboardService
    ) {}

    /**
     * Get global leaderboard.
     *
     * @summary Mengambil leaderboard global
     *
     * @allowedSorts total_xp, global_level
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 10), 100);
        $page = $request->input('page', 1);

        $leaderboard = $this->leaderboardService->getGlobalLeaderboard($perPage, $page);

        // Transform data
        $data = $leaderboard->getCollection()->map(function ($stat, $index) use ($leaderboard) {
            $rank = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;

            return [
                'rank' => $rank,
                'user' => [
                    'id' => $stat->user_id,
                    'name' => $stat->user?->name ?? 'Unknown',
                    'avatar_url' => $stat->user?->avatar_url ?? null,
                ],
                'total_xp' => $stat->total_xp,
                'level' => $stat->global_level,
            ];
        });

        return ApiResponse::success([
            'leaderboard' => $data,
            'meta' => [
                'current_page' => $leaderboard->currentPage(),
                'per_page' => $leaderboard->perPage(),
                'total' => $leaderboard->total(),
                'last_page' => $leaderboard->lastPage(),
            ],
        ]);
    }

    /**
     * Get current user's rank.
     *
     * @summary Mengambil ranking user saat ini
     */
    public function myRank(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $rankData = $this->leaderboardService->getUserRank($userId);

        return ApiResponse::success([
            'rank' => $rankData['rank'],
            'total_xp' => $rankData['total_xp'],
            'level' => $rankData['level'],
            'surrounding' => $rankData['surrounding'],
        ]);
    }
}
