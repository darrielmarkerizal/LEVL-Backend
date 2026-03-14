<?php

declare(strict_types=1);

namespace Modules\Gamification\Traits;

use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\XpSource;

trait IncludesXpInfo
{
    /**
     * Get XP info for a specific action
     */
    protected function getXpInfo(string $xpSourceCode): array
    {
        $xpSource = XpSource::byCode($xpSourceCode)->active()->first();

        if (!$xpSource) {
            return [
                'xp_available' => false,
                'xp_amount' => 0,
                'xp_source' => null,
            ];
        }

        return [
            'xp_available' => true,
            'xp_amount' => $xpSource->xp_amount,
            'xp_source' => [
                'code' => $xpSource->code,
                'name' => $xpSource->name,
                'description' => $xpSource->description,
                'cooldown_seconds' => $xpSource->cooldown_seconds,
                'daily_limit' => $xpSource->daily_limit,
                'daily_xp_cap' => $xpSource->daily_xp_cap,
            ],
        ];
    }

    /**
     * Get recent XP awards for a user
     */
    protected function getRecentXpAwards(int $userId, int $limit = 5): array
    {
        $recentAwards = Point::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($point) {
                return [
                    'xp_awarded' => $point->points,
                    'reason' => $point->reason,
                    'description' => $point->description,
                    'xp_source_code' => $point->xp_source_code,
                    'leveled_up' => $point->triggered_level_up,
                    'old_level' => $point->old_level,
                    'new_level' => $point->new_level,
                    'awarded_at' => $point->created_at->toIso8601String(),
                ];
            })
            ->toArray();

        return $recentAwards;
    }

    /**
     * Add XP info to response data
     */
    protected function withXpInfo(array $data, string $xpSourceCode, ?int $userId = null): array
    {
        $data['xp_info'] = $this->getXpInfo($xpSourceCode);

        if ($userId) {
            $data['recent_xp_awards'] = $this->getRecentXpAwards($userId, 3);
        }

        return $data;
    }
}
