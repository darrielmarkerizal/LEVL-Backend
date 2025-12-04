<?php

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\LeaderboardService;

class UpdateLeaderboard extends Command
{
    protected $signature = 'leaderboard:update';

    protected $description = 'Update global leaderboard rankings';

    public function handle(LeaderboardService $leaderboardService): int
    {
        $this->info('Updating leaderboard rankings...');

        $leaderboardService->updateRankings();

        $this->info('Leaderboard rankings updated successfully.');

        return self::SUCCESS;
    }
}
