<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Gamification\Contracts\Services\LeaderboardServiceInterface;
use Modules\Gamification\Models\Leaderboard;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Models\UserScopeStat;
use Modules\Schemes\Models\Course;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LeaderboardService implements LeaderboardServiceInterface
{
    public function getLeaderboardWithRanks(
        Request $request,
        ?int $currentUserId
    ): array {
        $leaderboard = $this->getGlobalLeaderboard($request);

        $leaderboard->getCollection()->transform(function ($stat, $index) use ($leaderboard) {
            $rank = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;
            $stat->rank = $rank;

            return $stat;
        });

        $myRank = null;
        if ($currentUserId) {
            $rankData = $this->getUserRank($request);
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

    public function getGlobalLeaderboard(Request $request): LengthAwarePaginator
    {
        $courseSlug = $request->input('filter.course_slug');
        $period = $request->input('filter.period', 'all_time');
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min($perPage, 100));

        $cacheKey = 'gamification:leaderboard:'.md5(json_encode($request->query()));

        return cache()->tags(['gamification', 'leaderboard'])->remember(
            $cacheKey,
            300,
            function () use ($courseSlug, $period, $perPage) {
                $courseId = $this->resolveCourseId($courseSlug);

                if ($courseId) {
                    $query = QueryBuilder::for(Leaderboard::class)
                        ->allowedFilters([
                            AllowedFilter::exact('course_id'),
                        ])
                        ->with(['user:id,name', 'user.media'])
                        ->where('course_id', $courseId)
                        ->orderBy('rank');
                } else {
                    $query = QueryBuilder::for(UserGamificationStat::class)
                        ->allowedFilters([
                            AllowedFilter::exact('period'),
                        ])
                        ->with(['user:id,name', 'user.media'])
                        ->orderByDesc('total_xp');

                    $this->applyPeriodFilter($query, $period);
                }

                $result = $query->paginate($perPage);

                $userIds = $result->pluck('user_id')->toArray();
                $badgeCounts = $this->getBadgeCountsForUsers($userIds, $period);

                $result->getCollection()->transform(function ($item) use ($badgeCounts) {
                    $item->badges_count = $badgeCounts[$item->user_id] ?? 0;

                    return $item;
                });

                return $result;
            }
        );
    }

    public function getUserRank(Request $request): array
    {
        $userId = $request->user()->id;
        $period = $request->input('filter.period', 'all_time');

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

        return [
            'rank' => $rank,
            'total_xp' => $userStats->total_xp,
            'level' => $userStats->global_level,
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

            foreach ($stats as $stat) {
                Leaderboard::updateOrCreate(
                    [
                        'course_id' => null,
                        'user_id' => $stat->user_id,
                    ],
                    ['rank' => $rank++]
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

    private function resolveCourseId(?string $courseSlug): ?int
    {
        if (! $courseSlug) {
            return null;
        }

        $course = Course::where('slug', $courseSlug)->first();

        return $course?->id;
    }

    private function applyPeriodFilter($query, string $period): void
    {
        $dateColumn = 'user_gamification_stats.stats_updated_at';

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

            foreach ($courseStats as $stat) {
                Leaderboard::updateOrCreate(
                    [
                        'course_id' => $courseId,
                        'user_id' => $stat->user_id,
                    ],
                    ['rank' => $rank++]
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

        $result = [];

        foreach ($above as $stat) {
            $rankQuery = UserGamificationStat::where('total_xp', '>', $stat->total_xp);
            $this->applyPeriodFilter($rankQuery, $period);
            $rank = $rankQuery->count() + 1;
            $result[] = $this->formatLeaderboardEntry($stat, $rank, $period);
        }

        if ($current) {
            $rankQuery = UserGamificationStat::where('total_xp', '>', $current->total_xp);
            $this->applyPeriodFilter($rankQuery, $period);
            $rank = $rankQuery->count() + 1;
            $result[] = array_merge($this->formatLeaderboardEntry($current, $rank, $period), ['is_current_user' => true]);
        }

        foreach ($below as $stat) {
            $rankQuery = UserGamificationStat::where('total_xp', '>', $stat->total_xp);
            $this->applyPeriodFilter($rankQuery, $period);
            $rank = $rankQuery->count() + 1;
            $result[] = $this->formatLeaderboardEntry($stat, $rank, $period);
        }

        return $result;
    }

    private function formatLeaderboardEntry(UserGamificationStat $stat, int $rank, string $period = 'all_time'): array
    {
        $badgesCount = $this->getBadgeCountForUser($stat->user_id, $period);

        return [
            'rank' => $rank,
            'user' => [
                'id' => $stat->user_id,
                'name' => $stat->user?->name ?? 'Unknown',
                'avatar_url' => $stat->user?->avatar_url ?? null,
            ],
            'total_xp' => $stat->total_xp,
            'level' => $stat->global_level,
            'badges_count' => $badgesCount,
        ];
    }
}
