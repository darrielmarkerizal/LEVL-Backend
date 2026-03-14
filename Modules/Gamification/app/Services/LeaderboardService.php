<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Gamification\Contracts\Services\LeaderboardServiceInterface;
use Modules\Gamification\Models\Leaderboard;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Models\UserScopeStat;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LeaderboardService implements LeaderboardServiceInterface
{
    public function getLeaderboardWithRanks(
        int $perPage = 10,
        int $page = 1,
        ?int $courseId = null,
        ?int $currentUserId = null,
        ?string $period = 'all_time',
        ?string $search = null
    ): array {
        $leaderboard = $this->getGlobalLeaderboard($perPage, $page, $courseId, $period, $search);

        $leaderboard->getCollection()->transform(function ($stat, $index) use ($leaderboard, $search, $period) {
            if ($search) {
                $rankData = $this->getUserRank($stat->user_id, $period);
                $stat->rank = $rankData['rank'];
            } else {
                $rank = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;
                $stat->rank = $rank;
            }

            return $stat;
        });

        $myRank = null;
        if ($currentUserId) {
            $rankData = $this->getUserRank($currentUserId, $period);
            $user = \Modules\Auth\Models\User::find($currentUserId);

            $myRank = [
                'rank' => $rankData['rank'],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                ],
                'total_xp' => $rankData['total_xp'],
                'level' => $rankData['level'],
                'badges_count' => $rankData['badges_count'],
            ];
        }

        return [
            'leaderboard' => $leaderboard,
            'my_rank' => $myRank,
        ];
    }

    public function getGlobalLeaderboard(int $perPage = 10, int $page = 1, ?int $courseId = null, ?string $period = 'all_time', ?string $search = null): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $period = $period ?? 'all_time';

        $cacheKey = 'gamification:leaderboard:'.md5(json_encode(compact('perPage', 'page', 'courseId', 'period', 'search')));

        return cache()->tags(['gamification', 'leaderboard'])->remember(
            $cacheKey,
            300,
            function () use ($courseId, $period, $perPage, $page, $search) {
                if ($courseId) {
                    $query = QueryBuilder::for(Leaderboard::class)
                        ->allowedFilters([
                            AllowedFilter::exact('course_id'),
                        ])
                        ->with(['user:id,name', 'user.media'])
                        ->where('course_id', $courseId);

                    if ($search) {
                        $query->whereHas('user', function ($q) use ($search) {
                            $q->search($search);
                        });
                    }
                    $query->orderBy('rank');
                } else {
                    if ($period === 'all_time') {
                        $query = QueryBuilder::for(UserGamificationStat::class)
                            ->allowedFilters([
                                AllowedFilter::callback('period', function ($query, $value) {
                                    // Handled manually below via $this->applyPeriodFilter()
                                }),
                            ])
                            ->with(['user:id,name', 'user.media']);

                        if ($search) {
                            $query->whereHas('user', function ($q) use ($search) {
                                $q->search($search);
                            });
                        }
                        $query->orderByDesc('total_xp');

                        $this->applyPeriodFilter($query, $period);
                    } else {
                        $query = QueryBuilder::for(\Modules\Gamification\Models\Point::class)
                            ->select('user_id', DB::raw('SUM(points) as total_xp'))
                            ->groupBy('user_id')
                            ->allowedFilters([
                                AllowedFilter::callback('period', function ($query, $value) {}),
                            ])
                            ->with(['user:id,name', 'user.media', 'user.gamificationStats']);

                        if ($search) {
                            $query->whereHas('user', function ($q) use ($search) {
                                $q->search($search);
                            });
                        }
                        $query->orderByDesc('total_xp');

                        $this->applyPeriodFilter($query, $period, true);
                    }
                }

                $result = $query->paginate($perPage, ['*'], 'page', $page);

                $userIds = $result->pluck('user_id')->toArray();
                $badgeCounts = $this->getBadgeCountsForUsers($userIds, $period);

                $result->getCollection()->transform(function ($item) use ($badgeCounts, $period) {
                    $item->badges_count = $badgeCounts[$item->user_id] ?? 0;

                    // Remap the loaded relationship into object variable for uniform output if it's points model
                    if ($period !== 'all_time') {
                        $item->global_level = $item->user?->gamificationStats?->global_level ?? 1;
                    }

                    return $item;
                });

                return $result;
            }
        );
    }

    public function getUserRank(int $userId, string $period = 'all_time'): array
    {
        if ($period === 'all_time') {
            $userStats = UserGamificationStat::where('user_id', $userId)->first();

            if (! $userStats) {
                return [
                    'rank' => null,
                    'total_xp' => 0,
                    'level' => 0,
                    'badges_count' => 0,
                    'surrounding' => [],
                ];
            }

            $query = UserGamificationStat::where('total_xp', '>', $userStats->total_xp);
            $this->applyPeriodFilter($query, $period);
            $rank = $query->count() + 1;

            $surrounding = $this->getSurroundingUsers($userId, $userStats->total_xp, 2, $period);
            $badgesCount = $this->getBadgeCountForUser($userId, $period);
            $globalLevel = $userStats->global_level;
            $totalXp = $userStats->total_xp;
        } else {
            $userPointQuery = \Modules\Gamification\Models\Point::where('user_id', $userId);
            $this->applyPeriodFilter($userPointQuery, $period, true);
            $totalXp = (int) $userPointQuery->sum('points');

            if ($totalXp === 0) {
                return [
                    'rank' => null,
                    'total_xp' => 0,
                    'level' => 0,
                    'badges_count' => 0,
                    'surrounding' => [],
                ];
            }

            $rankQuery = \Modules\Gamification\Models\Point::select('user_id', DB::raw('SUM(points) as period_xp'))
                ->groupBy('user_id')
                ->having(DB::raw('SUM(points)'), '>', $totalXp);
            $this->applyPeriodFilter($rankQuery, $period, true);

            $rank = $rankQuery->get()->count() + 1;

            $surrounding = $this->getSurroundingUsers($userId, $totalXp, 2, $period);
            $badgesCount = $this->getBadgeCountForUser($userId, $period);
            $globalLevel = \Modules\Gamification\Models\UserGamificationStat::where('user_id', $userId)->value('global_level') ?? 1;
        }

        return [
            'rank' => $rank,
            'total_xp' => $totalXp,
            'level' => $globalLevel,
            'badges_count' => $badgesCount,
            'surrounding' => $surrounding,
        ];
    }

    public function updateRankings(): void
    {
        $stats = UserGamificationStat::orderByDesc('total_xp')
            ->orderBy('user_id')
            ->get();

        DB::transaction(function () use ($stats) {
            $rank = 1;
            $userIds = $stats->pluck('user_id')->toArray();
            $data = [];

            // FIX: Prepare batch data instead of individual upserts
            foreach ($stats as $stat) {
                $data[] = [
                    'course_id' => null,
                    'user_id' => $stat->user_id,
                    'rank' => $rank++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // FIX: Batch upsert (Laravel 8+)
            if (!empty($data)) {
                Leaderboard::upsert(
                    $data,
                    ['course_id', 'user_id'], // Unique keys
                    ['rank', 'updated_at'] // Update columns
                );
            }

            if (! empty($userIds)) {
                Leaderboard::whereNull('course_id')
                    ->whereNotIn('user_id', $userIds)
                    ->delete();
            }

            $this->updateCourseRankings();

            cache()->tags(['gamification', 'leaderboard'])->flush();
        });
    }

    private function applyPeriodFilter($query, string $period, bool $isPointTable = false): void
    {
        $dateColumn = $isPointTable ? 'points.created_at' : 'user_gamification_stats.stats_updated_at';

        match ($period) {
            'today' => $query->whereDate($dateColumn, Carbon::today()),
            'this_week' => $query->whereBetween($dateColumn, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereMonth($dateColumn, Carbon::now()->month)
                ->whereYear($dateColumn, Carbon::now()->year),
            'this_year' => $query->whereYear($dateColumn, Carbon::now()->year),
            default => null,
        };
    }

    private function getBadgeCountsForUsers(array $userIds, string $period): array
    {
        if (empty($userIds)) {
            return [];
        }

        $query = DB::table('user_badges')
            ->whereIn('user_id', $userIds)
            ->select('user_id', DB::raw('count(*) as badges_count'))
            ->groupBy('user_id');

        $this->applyPeriodFilterToBadges($query, $period);

        return $query->pluck('badges_count', 'user_id')->toArray();
    }

    private function getBadgeCountForUser(int $userId, string $period): int
    {
        $query = DB::table('user_badges')->where('user_id', $userId);

        $this->applyPeriodFilterToBadges($query, $period);

        return $query->count();
    }

    private function applyPeriodFilterToBadges($query, string $period): void
    {
        match ($period) {
            'today' => $query->whereDate('earned_at', Carbon::today()),
            'this_week' => $query->whereBetween('earned_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereMonth('earned_at', Carbon::now()->month)
                ->whereYear('earned_at', Carbon::now()->year),
            'this_year' => $query->whereYear('earned_at', Carbon::now()->year),
            default => null,
        };
    }

    private function updateCourseRankings(): void
    {
        $courses = UserScopeStat::where('scope_type', 'course')
            ->select('scope_id')
            ->distinct()
            ->pluck('scope_id');

        foreach ($courses as $courseId) {
            $courseStats = UserScopeStat::where('scope_type', 'course')
                ->where('scope_id', $courseId)
                ->orderByDesc('total_xp')
                ->orderBy('user_id')
                ->get();

            $rank = 1;
            $userIds = $courseStats->pluck('user_id')->toArray();
            $data = [];

            // FIX: Prepare batch data
            foreach ($courseStats as $stat) {
                $data[] = [
                    'course_id' => $courseId,
                    'user_id' => $stat->user_id,
                    'rank' => $rank++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // FIX: Batch upsert
            if (!empty($data)) {
                Leaderboard::upsert(
                    $data,
                    ['course_id', 'user_id'], // Unique keys
                    ['rank', 'updated_at'] // Update columns
                );
            }

            if (! empty($userIds)) {
                Leaderboard::where('course_id', $courseId)
                    ->whereNotIn('user_id', $userIds)
                    ->delete();
            }
        }
    }

    private function getSurroundingUsers(int $userId, int $userXp, int $count = 2, string $period = 'all_time'): array
    {
        if ($period === 'all_time') {
            $aboveQuery = UserGamificationStat::with(['user:id,name', 'user.media'])
                ->where('total_xp', '>', $userXp)
                ->orderBy('total_xp');
            $this->applyPeriodFilter($aboveQuery, $period);
            $above = $aboveQuery->limit($count)->get()->reverse()->values();

            $belowQuery = UserGamificationStat::with(['user:id,name', 'user.media'])
                ->where('total_xp', '<', $userXp)
                ->orderByDesc('total_xp');
            $this->applyPeriodFilter($belowQuery, $period);
            $below = $belowQuery->limit($count)->get();

            $current = UserGamificationStat::with(['user:id,name', 'user.media'])
                ->where('user_id', $userId)
                ->first();
        } else {
            $aboveQuery = \Modules\Gamification\Models\Point::select('user_id', DB::raw('SUM(points) as total_xp'))
                ->with(['user:id,name', 'user.media', 'user.gamificationStats'])
                ->groupBy('user_id')
                ->having(DB::raw('SUM(points)'), '>', $userXp)
                ->orderByDesc('total_xp');
            $this->applyPeriodFilter($aboveQuery, $period, true);
            $above = $aboveQuery->limit($count)->get()->reverse()->values();

            $belowQuery = \Modules\Gamification\Models\Point::select('user_id', DB::raw('SUM(points) as total_xp'))
                ->with(['user:id,name', 'user.media', 'user.gamificationStats'])
                ->groupBy('user_id')
                ->having(DB::raw('SUM(points)'), '<', $userXp)
                ->orderByDesc('total_xp');
            $this->applyPeriodFilter($belowQuery, $period, true);
            $below = $belowQuery->limit($count)->get();

            $currentQuery = \Modules\Gamification\Models\Point::select('user_id', DB::raw('SUM(points) as total_xp'))
                ->with(['user:id,name', 'user.media', 'user.gamificationStats'])
                ->where('user_id', $userId)
                ->groupBy('user_id');
            $this->applyPeriodFilter($currentQuery, $period, true);
            $current = $currentQuery->first();
        }

        // FIX: Collect all stats and calculate ranks in batch
        $allStats = collect();
        if ($current) {
            $allStats->push($current);
        }
        $allStats = $allStats->merge($above)->merge($below);

        // FIX: Calculate all ranks in single query
        $ranks = [];
        if ($allStats->isNotEmpty()) {
            if ($period === 'all_time') {
                // Get all XP values
                $xpValues = $allStats->pluck('total_xp')->unique()->toArray();
                
                // Single query to get ranks for all XP values
                $rankData = UserGamificationStat::select('total_xp', DB::raw('COUNT(*) as higher_count'))
                    ->whereIn('total_xp', $xpValues)
                    ->get()
                    ->mapWithKeys(function ($item) use ($xpValues) {
                        $higherCount = UserGamificationStat::where('total_xp', '>', $item->total_xp)->count();
                        return [$item->total_xp => $higherCount + 1];
                    });
                
                $ranks = $rankData->toArray();
            } else {
                // For period-based, calculate ranks
                foreach ($allStats as $stat) {
                    $rankQuery = \Modules\Gamification\Models\Point::select('user_id', DB::raw('SUM(points) as period_xp'))
                        ->groupBy('user_id')
                        ->having(DB::raw('SUM(points)'), '>', $stat->total_xp);
                    $this->applyPeriodFilter($rankQuery, $period, true);
                    $ranks[$stat->total_xp] = $rankQuery->get()->count() + 1;
                }
            }
        }

        // FIX: Batch load badge counts for all users
        $userIds = $allStats->pluck('user_id')->unique()->toArray();
        $badgeCounts = $this->getBadgeCountsForUsers($userIds, $period);

        $result = [];

        foreach ($above as $stat) {
            $rank = $ranks[$stat->total_xp] ?? 1;
            $result[] = $this->formatLeaderboardEntryOptimized($stat, $rank, $period, $badgeCounts);
        }

        if ($current) {
            $rank = $ranks[$current->total_xp] ?? 1;
            $result[] = array_merge($this->formatLeaderboardEntryOptimized($current, $rank, $period, $badgeCounts), ['is_current_user' => true]);
        }

        foreach ($below as $stat) {
            $rank = $ranks[$stat->total_xp] ?? 1;
            $result[] = $this->formatLeaderboardEntryOptimized($stat, $rank, $period, $badgeCounts);
        }

        return $result;
    }

    private function formatLeaderboardEntry($stat, int $rank, string $period = 'all_time'): array
    {
        $badgesCount = $this->getBadgeCountForUser($stat->user_id, $period);

        $globalLevel = $period === 'all_time' ? $stat->global_level : ($stat->user?->gamificationStats?->global_level ?? 1);

        return [
            'rank' => $rank,
            'user' => [
                'id' => $stat->user_id,
                'name' => $stat->user?->name ?? 'Unknown',
                'avatar_url' => $stat->user?->avatar_url ?? null,
            ],
            'total_xp' => $stat->total_xp,
            'level' => $globalLevel,
            'badges_count' => $badgesCount,
        ];
    }

    /**
     * Optimized version that uses pre-loaded badge counts
     */
    private function formatLeaderboardEntryOptimized($stat, int $rank, string $period, array $badgeCounts): array
    {
        $badgesCount = $badgeCounts[$stat->user_id] ?? 0;
        $globalLevel = $period === 'all_time' ? $stat->global_level : ($stat->user?->gamificationStats?->global_level ?? 1);

        return [
            'rank' => $rank,
            'user' => [
                'id' => $stat->user_id,
                'name' => $stat->user?->name ?? 'Unknown',
                'avatar_url' => $stat->user?->avatar_url ?? null,
            ],
            'total_xp' => $stat->total_xp,
            'level' => $globalLevel,
            'badges_count' => $badgesCount,
        ];
    }
}
