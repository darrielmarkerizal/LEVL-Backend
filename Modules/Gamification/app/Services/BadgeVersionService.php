<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Gamification\Contracts\Repositories\BadgeVersionRepositoryInterface;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\BadgeVersion;

class BadgeVersionService
{
    public function __construct(
        private readonly BadgeVersionRepositoryInterface $repository
    ) {}

    public function createNewVersion(
        int $badgeId,
        int $threshold,
        array $rules
    ): BadgeVersion {
        return DB::transaction(function () use ($badgeId, $threshold, $rules) {
            // Deactivate old versions
            $this->repository->deactivateOldVersions($badgeId);

            // Get next version number
            $lastVersion = $this->repository->getMaxVersion($badgeId);

            // Create new version
            return $this->repository->create([
                'badge_id' => $badgeId,
                'version' => $lastVersion + 1,
                'threshold' => $threshold,
                'rules' => $rules,
                'effective_from' => now(),
                'is_active' => true,
            ]);
        });
    }

    public function getActiveVersion(int $badgeId): ?BadgeVersion
    {
        return $this->repository->getActiveVersion($badgeId);
    }

    public function getAllVersions(int $badgeId): Collection
    {
        return $this->repository->getAllVersions($badgeId);
    }

    public function createInitialVersionsForExistingBadges(): int
    {
        $badges = Badge::whereDoesntHave('versions')->get();
        $count = 0;

        foreach ($badges as $badge) {
            $this->repository->create([
                'badge_id' => $badge->id,
                'version' => 1,
                'threshold' => $badge->threshold ?? 1,
                'rules' => $badge->rules->map(fn ($rule) => [
                    'event_trigger' => $rule->event_trigger,
                    'conditions' => $rule->conditions,
                ])->toArray(),
                'effective_from' => $badge->created_at ?? now(),
                'is_active' => true,
            ]);
            $count++;
        }

        return $count;
    }
}
