<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Modules\Gamification\Contracts\Services\GamificationServiceInterface;
use Modules\Gamification\Contracts\Services\LeaderboardServiceInterface;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Repositories\GamificationRepository;
use Modules\Gamification\Services\Support\BadgeManager;
use Modules\Gamification\Services\Support\LeaderboardManager;
use Modules\Gamification\Services\Support\PointManager;

class GamificationService implements GamificationServiceInterface
{
    private readonly GamificationRepository $repository;

    public function __construct(
        private readonly PointManager $pointManager,
        private readonly BadgeManager $badgeManager,
        private readonly LeaderboardManager $leaderboardManager,
        private readonly LeaderboardServiceInterface $leaderboardService,
        ?GamificationRepository $repository = null
    ) {
        $this->repository = $repository ?? app(GamificationRepository::class);
    }

    public function render(string $template, array $data = []): View
    {
        return view($this->repository->view($template), $data);
    }

    public function awardXp(
        int $userId,
        int $points,
        string $reason,
        ?string $sourceType = null,
        ?int $sourceId = null,
        array $options = []
    ): ?Point {
        return $this->pointManager->awardXp($userId, $points, $reason, $sourceType, $sourceId, $options);
    }

    public function awardBadge(
        int $userId,
        string $code,
        string $name,
        ?string $description = null
    ): ?UserBadge {
        return $this->badgeManager->awardBadge($userId, $code, $name, $description);
    }

    public function updateGlobalLeaderboard(): void
    {
        $this->leaderboardManager->updateGlobalLeaderboard();
    }

    public function getOrCreateStats(int $userId): UserGamificationStat
    {
        return $this->pointManager->getOrCreateStats($userId);
    }

    public function getUserBadges(int $userId, int $perPage = 15, $request = null): LengthAwarePaginator
    {
        return $this->badgeManager->getUserBadgesPaginated($userId, $perPage, $request);
    }

    public function getUserBadgesCollection(int $userId): Collection
    {
        return $this->badgeManager->getUserBadges($userId);
    }

    public function countUserBadges(int $userId): int
    {
        return $this->badgeManager->countUserBadges($userId);
    }

    public function getPointsHistory(int $userId, int $perPage, $request = null): LengthAwarePaginator
    {
        return $this->pointManager->getPointsHistory($userId, $perPage, $request);
    }

    public function getAchievements(int $userId): array
    {
        $stats = $this->pointManager->getOrCreateStats($userId);

        return $this->pointManager->getAchievements($stats->total_xp, $stats->global_level);
    }

    public function getSummary(int $userId, string $period = 'all_time', ?string $month = null): array
    {
        $stats = $this->pointManager->getOrCreateStats($userId);
        $rankData = $this->leaderboardService->getUserRank($userId, $period, $month);

        // Get XP for the specified period/month
        $periodXp = $this->getPeriodXp($userId, $period, $month);

        // Get badges count for the specified period/month
        $badgesCount = $this->getBadgesCountForPeriod($userId, $period, $month);

        return [
            'xp' => [
                'total' => $stats->total_xp,
                'today' => $this->getPeriodXp($userId, 'today'),
                'this_week' => $this->getPeriodXp($userId, 'this_week'),
                'this_month' => $this->getPeriodXp($userId, 'this_month'),
                'period' => $periodXp, // XP for requested period/month
            ],
            'level' => [
                'current' => $stats->global_level,
                'name' => $this->getLevelName($stats->global_level),
                'progress_percentage' => $stats->progress_to_next_level,
                'xp_to_next_level' => $stats->xp_to_next_level,
            ],
            'badges' => [
                'total_earned' => $this->badgeManager->countUserBadges($userId),
                'period_earned' => $badgesCount, // Badges earned in requested period/month
            ],
            'leaderboard' => [
                'global_rank' => $rankData['rank'],
                'total_students' => $this->getTotalStudents(),
            ],
            'activity' => [
                'current_streak' => $stats->current_streak,
                'longest_streak' => $stats->longest_streak,
            ],
        ];
    }

    private function getPeriodXp(int $userId, string $period, ?string $month = null): int
    {
        $query = \Modules\Gamification\Models\Point::where('user_id', $userId);

        // If month filter is present, use it
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                $query->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
            } catch (\Exception $e) {
                // Invalid date, fallback to period
                $this->applyPeriodFilterToQuery($query, $period);
            }
        } else {
            $this->applyPeriodFilterToQuery($query, $period);
        }

        return (int) $query->sum('points');
    }

    private function applyPeriodFilterToQuery($query, string $period): void
    {
        $dateColumn = 'created_at';

        match ($period) {
            'today' => $query->whereDate($dateColumn, \Carbon\Carbon::today()),
            'this_week' => $query->whereBetween($dateColumn, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereMonth($dateColumn, \Carbon\Carbon::now()->month)
                ->whereYear($dateColumn, \Carbon\Carbon::now()->year),
            'this_year' => $query->whereYear($dateColumn, \Carbon\Carbon::now()->year),
            default => null,
        };
    }

    private function getBadgesCountForPeriod(int $userId, string $period, ?string $month = null): int
    {
        $query = \Modules\Gamification\Models\UserBadge::where('user_id', $userId);

        // If month filter is present, use it
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                $query->whereYear('earned_at', $date->year)
                    ->whereMonth('earned_at', $date->month);
            } catch (\Exception $e) {
                // Invalid date, fallback to period
                $this->applyPeriodFilterToBadges($query, $period);
            }
        } else {
            $this->applyPeriodFilterToBadges($query, $period);
        }

        return $query->count();
    }

    private function applyPeriodFilterToBadges($query, string $period): void
    {
        match ($period) {
            'today' => $query->whereDate('earned_at', \Carbon\Carbon::today()),
            'this_week' => $query->whereBetween('earned_at', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereMonth('earned_at', \Carbon\Carbon::now()->month)
                ->whereYear('earned_at', \Carbon\Carbon::now()->year),
            'this_year' => $query->whereYear('earned_at', \Carbon\Carbon::now()->year),
            default => null,
        };
    }

    private function getLevelName(int $level): string
    {
        $levelConfig = \Modules\Common\Models\LevelConfig::where('level', $level)->first();

        return $levelConfig?->name ?? 'Level '.$level;
    }

    private function getTotalStudents(): int
    {
        return \Modules\Auth\Models\User::role('Student')->count();
    }

    public function getUnitLevels(int $userId, int $courseId): Collection
    {
        $units = \Modules\Schemes\Models\Unit::where('course_id', $courseId)
            ->orderBy('order')
            ->get(['id', 'title', 'order']);

        $stats = \Modules\Gamification\Models\UserScopeStat::where('user_id', $userId)
            ->where('scope_type', 'unit')
            ->whereIn('scope_id', $units->pluck('id'))
            ->get()
            ->keyBy('scope_id');

        return $units->map(function ($unit) use ($stats) {
            $stat = $stats->get($unit->id);

            return [
                'unit_id' => $unit->id,
                'title' => $unit->title,
                'level' => $stat?->current_level ?? 1,
                'total_xp' => $stat?->total_xp ?? 0,
                'progress' => 0,
            ];
        });
    }
}
