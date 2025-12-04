<?php

namespace Modules\Gamification\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Gamification\Models\Leaderboard;
use Modules\Gamification\Models\UserGamificationStat;

class LeaderboardService
{
    /**
     * Get global leaderboard with pagination.
     */
    public function getGlobalLeaderboard(int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $perPage = min($perPage, 100); // Max 100 per page

        return UserGamificationStat::with(['user:id,name,avatar_path'])
            ->orderByDesc('total_xp')
            ->orderBy('user_id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get user's rank and surrounding users.
     */
    public function getUserRank(int $userId): array
    {
        $userStats = UserGamificationStat::where('user_id', $userId)->first();

        if (! $userStats) {
            return [
                'rank' => null,
                'total_xp' => 0,
                'level' => 0,
                'surrounding' => [],
            ];
        }

        // Calculate rank
        $rank = UserGamificationStat::where('total_xp', '>', $userStats->total_xp)->count() + 1;

        // Get surrounding users (2 above, 2 below)
        $surrounding = $this->getSurroundingUsers($userId, $userStats->total_xp, 2);

        return [
            'rank' => $rank,
            'total_xp' => $userStats->total_xp,
            'level' => $userStats->global_level,
            'surrounding' => $surrounding,
        ];
    }

    /**
     * Update all rankings in leaderboard table.
     */
    public function updateRankings(): void
    {
        $stats = UserGamificationStat::orderByDesc('total_xp')
            ->orderBy('user_id')
            ->get();

        DB::transaction(function () use ($stats) {
            $rank = 1;
            $userIds = [];

            foreach ($stats as $stat) {
                $userIds[] = $stat->user_id;

                Leaderboard::updateOrCreate(
                    [
                        'course_id' => null,
                        'user_id' => $stat->user_id,
                    ],
                    ['rank' => $rank++]
                );
            }

            // Remove users no longer in leaderboard
            if (! empty($userIds)) {
                Leaderboard::whereNull('course_id')
                    ->whereNotIn('user_id', $userIds)
                    ->delete();
            }
        });
    }

    /**
     * Get surrounding users in leaderboard.
     */
    private function getSurroundingUsers(int $userId, int $userXp, int $count = 2): array
    {
        // Users above (higher XP)
        $above = UserGamificationStat::with(['user:id,name,avatar_path'])
            ->where('total_xp', '>', $userXp)
            ->orderBy('total_xp')
            ->limit($count)
            ->get()
            ->reverse()
            ->values();

        // Users below (lower XP)
        $below = UserGamificationStat::with(['user:id,name,avatar_path'])
            ->where('total_xp', '<', $userXp)
            ->orderByDesc('total_xp')
            ->limit($count)
            ->get();

        // Current user
        $current = UserGamificationStat::with(['user:id,name,avatar_path'])
            ->where('user_id', $userId)
            ->first();

        $result = [];

        foreach ($above as $stat) {
            $rank = UserGamificationStat::where('total_xp', '>', $stat->total_xp)->count() + 1;
            $result[] = $this->formatLeaderboardEntry($stat, $rank);
        }

        if ($current) {
            $rank = UserGamificationStat::where('total_xp', '>', $current->total_xp)->count() + 1;
            $result[] = array_merge($this->formatLeaderboardEntry($current, $rank), ['is_current_user' => true]);
        }

        foreach ($below as $stat) {
            $rank = UserGamificationStat::where('total_xp', '>', $stat->total_xp)->count() + 1;
            $result[] = $this->formatLeaderboardEntry($stat, $rank);
        }

        return $result;
    }

    /**
     * Format leaderboard entry.
     */
    private function formatLeaderboardEntry(UserGamificationStat $stat, int $rank): array
    {
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
    }
}
