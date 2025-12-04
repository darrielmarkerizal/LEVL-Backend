<?php

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Services\ChallengeService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\LeaderboardService;

class GamificationController extends Controller
{
    public function __construct(
        private readonly GamificationService $gamificationService,
        private readonly LeaderboardService $leaderboardService,
        private readonly ChallengeService $challengeService
    ) {}

    /**
     * Get user's gamification summary.
     *
     * @summary Mengambil ringkasan gamifikasi user
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = UserGamificationStat::where('user_id', $userId)->first();
        $rankData = $this->leaderboardService->getUserRank($userId);
        $activeChallenges = $this->challengeService->getUserChallenges($userId)->count();
        $badgesCount = UserBadge::where('user_id', $userId)->count();

        return ApiResponse::success([
            'total_xp' => $stats?->total_xp ?? 0,
            'level' => $stats?->global_level ?? 0,
            'xp_to_next_level' => $stats?->xp_to_next_level ?? 100,
            'progress_to_next_level' => $stats?->progress_to_next_level ?? 0,
            'badges_count' => $badgesCount,
            'current_streak' => $stats?->current_streak ?? 0,
            'longest_streak' => $stats?->longest_streak ?? 0,
            'rank' => $rankData['rank'],
            'active_challenges' => $activeChallenges,
        ]);
    }

    /**
     * Get user's badges.
     *
     * @summary Mengambil daftar badge user
     */
    public function badges(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $badges = UserBadge::with('badge')
            ->where('user_id', $userId)
            ->orderByDesc('awarded_at')
            ->get()
            ->map(function ($userBadge) {
                return [
                    'id' => $userBadge->badge_id,
                    'code' => $userBadge->badge?->code,
                    'name' => $userBadge->badge?->name,
                    'description' => $userBadge->description ?? $userBadge->badge?->description,
                    'icon_path' => $userBadge->badge?->icon_path,
                    'type' => $userBadge->badge?->type?->value,
                    'awarded_at' => $userBadge->awarded_at,
                ];
            });

        return ApiResponse::success(['badges' => $badges]);
    }

    /**
     * Get user's points history.
     *
     * @summary Mengambil riwayat XP user
     */
    public function pointsHistory(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = $request->input('per_page', 15);

        $points = Point::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $data = $points->getCollection()->map(function ($point) {
            return [
                'id' => $point->id,
                'points' => $point->points,
                'source_type' => $point->source_type?->value,
                'source_type_label' => $point->source_type?->label(),
                'reason' => $point->reason?->value,
                'reason_label' => $point->reason?->label(),
                'description' => $point->description,
                'created_at' => $point->created_at,
            ];
        });

        return ApiResponse::success([
            'points' => $data,
            'meta' => [
                'current_page' => $points->currentPage(),
                'per_page' => $points->perPage(),
                'total' => $points->total(),
                'last_page' => $points->lastPage(),
            ],
        ]);
    }

    /**
     * Get user's achievements/milestones.
     *
     * @summary Mengambil pencapaian user
     */
    public function achievements(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = UserGamificationStat::where('user_id', $userId)->first();
        $totalXp = $stats?->total_xp ?? 0;
        $level = $stats?->global_level ?? 0;

        // Define milestones
        $milestones = [
            ['name' => 'Pemula', 'xp_required' => 100, 'level_required' => 1],
            ['name' => 'Pelajar Aktif', 'xp_required' => 500, 'level_required' => 5],
            ['name' => 'Pembelajar Tekun', 'xp_required' => 1000, 'level_required' => 10],
            ['name' => 'Ahli Muda', 'xp_required' => 2500, 'level_required' => 15],
            ['name' => 'Master', 'xp_required' => 5000, 'level_required' => 20],
            ['name' => 'Grandmaster', 'xp_required' => 10000, 'level_required' => 30],
        ];

        $achievements = collect($milestones)->map(function ($milestone) use ($totalXp) {
            $achieved = $totalXp >= $milestone['xp_required'];
            $progress = min(100, ($totalXp / $milestone['xp_required']) * 100);

            return [
                'name' => $milestone['name'],
                'xp_required' => $milestone['xp_required'],
                'level_required' => $milestone['level_required'],
                'achieved' => $achieved,
                'progress' => round($progress, 2),
            ];
        });

        // Find next milestone
        $nextMilestone = $achievements->first(fn ($m) => ! $m['achieved']);

        return ApiResponse::success([
            'achievements' => $achievements,
            'next_milestone' => $nextMilestone,
            'current_xp' => $totalXp,
            'current_level' => $level,
        ]);
    }
}
