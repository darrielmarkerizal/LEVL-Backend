<?php

declare(strict_types=1);

namespace Modules\Gamification\Contracts\Repositories;

use Illuminate\Support\Collection;
use Modules\Gamification\Models\BadgeVersion;

interface BadgeVersionRepositoryInterface
{
    public function create(array $data): BadgeVersion;

    public function getActiveVersion(int $badgeId): ?BadgeVersion;

    public function getAllVersions(int $badgeId): Collection;

    public function deactivateOldVersions(int $badgeId): int;

    public function getMaxVersion(int $badgeId): int;
}
