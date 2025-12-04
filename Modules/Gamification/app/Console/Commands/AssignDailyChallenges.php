<?php

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\ChallengeService;

class AssignDailyChallenges extends Command
{
    protected $signature = 'challenges:assign-daily';

    protected $description = 'Assign daily challenges to all active users';

    public function handle(ChallengeService $challengeService): int
    {
        $this->info('Assigning daily challenges...');

        $count = $challengeService->assignDailyChallenges();

        $this->info("Assigned {$count} daily challenge(s) to users.");

        return self::SUCCESS;
    }
}
