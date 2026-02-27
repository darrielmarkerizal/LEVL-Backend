<?php

declare(strict_types=1);

namespace Modules\Gamification\Services\Support;

use Carbon\Carbon;
use Modules\Gamification\Models\UserGamificationStat;

class StreakResetService
{
    public function resetInactiveStreaks(): int
    {
        $yesterday = Carbon::yesterday();

        return UserGamificationStat::query()
            ->where('current_streak', '>', 0)
            ->where(function ($query) use ($yesterday) {
                $query->whereNull('last_activity_date')
                    ->orWhere('last_activity_date', '<', $yesterday);
            })
            ->update(['current_streak' => 0]);
    }
}
