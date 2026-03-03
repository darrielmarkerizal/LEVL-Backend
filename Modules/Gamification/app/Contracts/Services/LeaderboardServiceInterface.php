<?php

namespace Modules\Gamification\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeaderboardServiceInterface
{
    public function getLeaderboardWithRanks(
        int $perPage = 10,
        int $page = 1,
        ?int $courseId = null,
        ?int $currentUserId = null,
        ?string $period = 'all_time',
        ?string $search = null
    ): array;

    public function getGlobalLeaderboard(int $perPage = 10, int $page = 1, ?int $courseId = null, ?string $period = 'all_time', ?string $search = null): LengthAwarePaginator;

    public function getUserRank(int $userId, string $period = 'all_time'): array;

    public function updateRankings(): void;
}
