<?php

namespace Modules\Gamification\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\Leaderboard;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;

class GamificationRepository
{
    public function view(string $template): string
    {
        return sprintf('gamification::%s', $template);
    }

    public function pointExists(
        int $userId,
        ?string $sourceType,
        ?int $sourceId,
        ?string $reason
    ): bool {
        return $this->pointDuplicateQuery($userId, $sourceType, $sourceId, $reason)->exists();
    }

    public function createPoint(array $attributes): Point
    {
        return Point::create($attributes);
    }

    public function getOrCreateStats(int $userId): UserGamificationStat
    {
        return UserGamificationStat::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_xp' => 0,
                'global_level' => 0,
                'current_streak' => 0,
                'longest_streak' => 0,
            ]
        );
    }

    public function saveStats(UserGamificationStat $stats): UserGamificationStat
    {
        $stats->save();

        return $stats;
    }

    public function firstOrCreateBadge(string $code, array $attributes = []): Badge
    {
        return Badge::firstOrCreate(['code' => $code], $attributes);
    }

    public function findUserBadge(int $userId, int $badgeId): ?UserBadge
    {
        return UserBadge::query()
            ->where('user_id', $userId)
            ->where('badge_id', $badgeId)
            ->first();
    }

    public function createUserBadge(array $attributes): UserBadge
    {
        return UserBadge::create($attributes);
    }

    public function globalLeaderboardStats(): Collection
    {
        return UserGamificationStat::query()
            ->orderByDesc('total_xp')
            ->orderBy('user_id')
            ->get();
    }

    public function upsertLeaderboard(?int $courseId, int $userId, int $rank): Leaderboard
    {
        return Leaderboard::updateOrCreate(
            [
                'course_id' => $courseId,
                'user_id' => $userId,
            ],
            ['rank' => $rank]
        );
    }

    public function deleteGlobalLeaderboardExcept(array $userIds): int
    {
        $query = Leaderboard::query()->whereNull('course_id');

        if (! empty($userIds)) {
            $query->whereNotIn('user_id', $userIds);
        }

        return $query->delete();
    }

    private function pointDuplicateQuery(
        int $userId,
        ?string $sourceType,
        ?int $sourceId,
        ?string $reason
    ): Builder {
        $query = Point::query()->where('user_id', $userId);

        if ($sourceType !== null) {
            $query->where('source_type', $sourceType);
        }

        if ($sourceId !== null) {
            $query->where('source_id', $sourceId);
        }

        if ($reason !== null) {
            $query->where('reason', $reason);
        }

        return $query;
    }
}
