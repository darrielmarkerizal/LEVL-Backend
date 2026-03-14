<?php

declare(strict_types=1);

namespace Modules\Gamification\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;
use Modules\Gamification\Contracts\Repositories\BadgeVersionRepositoryInterface;
use Modules\Gamification\Models\BadgeVersion;

class BadgeVersionRepository extends BaseRepository implements BadgeVersionRepositoryInterface
{
    protected function model(): string
    {
        return BadgeVersion::class;
    }

    public function create(array $data): BadgeVersion
    {
        return BadgeVersion::create($data);
    }

    public function getActiveVersion(int $badgeId): ?BadgeVersion
    {
        return BadgeVersion::where('badge_id', $badgeId)
            ->active()
            ->first();
    }

    public function getAllVersions(int $badgeId): Collection
    {
        return BadgeVersion::where('badge_id', $badgeId)
            ->orderBy('version', 'desc')
            ->get();
    }

    public function deactivateOldVersions(int $badgeId): int
    {
        return BadgeVersion::where('badge_id', $badgeId)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'effective_until' => now(),
            ]);
    }

    public function getMaxVersion(int $badgeId): int
    {
        return BadgeVersion::where('badge_id', $badgeId)->max('version') ?? 0;
    }
}
