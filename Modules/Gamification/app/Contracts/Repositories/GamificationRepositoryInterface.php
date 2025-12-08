<?php

namespace Modules\Gamification\Contracts\Repositories;

use Illuminate\Support\Collection;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\Leaderboard;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;

interface GamificationRepositoryInterface
{
    public function pointExists(int $userId, ?string $sourceType, ?int $sourceId, ?string $reason): bool;

    public function createPoint(array $attributes): Point;

    public function getOrCreateStats(int $userId): UserGamificationStat;

    public function saveStats(UserGamificationStat $stats): UserGamificationStat;

    public function firstOrCreateBadge(string $code, array $attributes = []): Badge;

    public function findUserBadge(int $userId, int $badgeId): ?UserBadge;

    public function createUserBadge(array $attributes): UserBadge;

    public function globalLeaderboardStats(): Collection;

    public function upsertLeaderboard(?int $courseId, int $userId, int $rank): Leaderboard;

    public function deleteGlobalLeaderboardExcept(array $userIds): int;
}
