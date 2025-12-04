<?php

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\ChallengeService;

class ExpireChallenges extends Command
{
    protected $signature = 'challenges:expire';

    protected $description = 'Mark overdue challenges as expired';

    public function handle(ChallengeService $challengeService): int
    {
        $this->info('Expiring overdue challenges...');

        $count = $challengeService->expireOverdueChallenges();

        $this->info("Expired {$count} challenge(s).");

        return self::SUCCESS;
    }
}
