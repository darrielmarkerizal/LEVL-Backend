<?php

namespace Modules\Gamification\Contracts\Services;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\View\View;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;

interface GamificationServiceInterface
{
    public function render(string $template, array $data = []): View;

    public function awardXp(
        int $userId,
        int $points,
        string $reason,
        ?string $sourceType = null,
        ?int $sourceId = null,
        array $options = []
    ): ?Point;

    public function awardBadge(
        int $userId,
        string $code,
        string $name,
        ?string $description = null
    ): ?UserBadge;

    public function updateGlobalLeaderboard(): void;

    public function getOrCreateStats(int $userId): \Modules\Gamification\Models\UserGamificationStat;

    public function getUserBadges(int $userId, int $perPage = 15, $request = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function getUserBadgesCollection(int $userId): \Illuminate\Support\Collection;

    public function countUserBadges(int $userId): int;

    public function getPointsHistory(int $userId, int $perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function getUserGamificationLog(int $userId, int $perPage = 15, $request = null): array;

    public function getUserGamificationLogExportRows(int $userId, $request = null): array;

    public function exportUserGamificationLog(int $userId, string $type = 'csv', $request = null): Response;

    public function getAchievements(int $userId): array;

    public function getSummary(int $userId, string $period = 'all_time', ?string $month = null): array;

    public function getCourseOverview(int $userId, int $courseId): array;
}
