<?php

namespace Modules\Gamification\Services;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Repositories\GamificationRepository;

class GamificationService
{
    private readonly GamificationRepository $repository;

    public function __construct(?GamificationRepository $repository = null)
    {
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
        if ($points <= 0) {
            return null;
        }

        $allowMultiple = (bool) ($options['allow_multiple'] ?? true);

        return DB::transaction(function () use ($userId, $points, $reason, $sourceType, $sourceId, $options, $allowMultiple) {
            if (! $allowMultiple && $this->repository->pointExists($userId, $sourceType, $sourceId, $reason)) {
                return null;
            }

            $resolvedSourceType = $sourceType ?? 'system';

            $point = $this->repository->createPoint([
                'user_id' => $userId,
                'points' => $points,
                'reason' => $reason,
                'source_type' => $resolvedSourceType,
                'source_id' => $sourceId,
                'description' => $options['description'] ?? null,
            ]);

            $this->updateUserGamificationStats($userId, $points);

            return $point;
        });
    }

    public function awardBadge(
        int $userId,
        string $code,
        string $name,
        ?string $description = null
    ): ?UserBadge {
        return DB::transaction(function () use ($userId, $code, $name, $description) {
            $badge = $this->repository->firstOrCreateBadge($code, [
                'name' => $name,
                'description' => $description,
            ]);

            $existing = $this->repository->findUserBadge($userId, $badge->id);
            if ($existing) {
                return null;
            }

            return $this->repository->createUserBadge([
                'user_id' => $userId,
                'badge_id' => $badge->id,
                'awarded_at' => now(),
                'description' => $description,
            ]);
        });
    }

    public function updateGlobalLeaderboard(): void
    {
        $stats = $this->repository->globalLeaderboardStats();

        DB::transaction(function () use ($stats) {
            $rank = 1;
            $userIds = [];

            /** @var \Modules\Gamification\Models\UserGamificationStat $stat */
            foreach ($stats as $stat) {
                $userIds[] = $stat->user_id;
                $this->repository->upsertLeaderboard(null, $stat->user_id, $rank++);
            }

            $this->repository->deleteGlobalLeaderboardExcept($userIds);
        });
    }

    private function updateUserGamificationStats(int $userId, int $points): UserGamificationStat
    {
        $stats = $this->repository->getOrCreateStats($userId);
        $stats->total_xp += $points;
        $stats->global_level = $this->calculateLevelFromXp($stats->total_xp);
        $stats->stats_updated_at = Carbon::now();
        $stats->last_activity_date = Carbon::now()->startOfDay();

        return $this->repository->saveStats($stats);
    }

    private function calculateLevelFromXp(int $totalXp): int
    {
        $level = 0;
        $remainingXp = $totalXp;

        while (true) {
            $xpRequired = (int) round(100 * pow(1.1, $level));
            if ($xpRequired <= 0 || $remainingXp < $xpRequired) {
                break;
            }

            $remainingXp -= $xpRequired;
            $level++;
        }

        return $level;
    }
}
