<?php

namespace Modules\Gamification\Contracts\Services;

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
}
