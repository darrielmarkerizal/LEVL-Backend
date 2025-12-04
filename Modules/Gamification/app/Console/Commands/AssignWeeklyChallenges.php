<?php

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\ChallengeService;

class AssignWeeklyChallenges extends Command
{
    protected $signature = 'challenges:assign-weekly';

    protected $description = 'Assign weekly challenges to all active users';

    public function handle(ChallengeService $challengeService): int
    {
        $this->info('Assigning weekly challenges...');

        $count = $challengeService->assignWeeklyChallenges();

        $this->info("Assigned {$count} weekly challenge(s) to users.");

        return self::SUCCESS;
    }
}
