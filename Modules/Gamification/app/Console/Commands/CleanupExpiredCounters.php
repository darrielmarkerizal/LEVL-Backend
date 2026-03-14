<?php

declare(strict_types=1);

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\EventCounterService;

class CleanupExpiredCounters extends Command
{
    protected $signature = 'gamification:cleanup-counters';

    protected $description = 'Cleanup expired event counters';

    public function handle(EventCounterService $counterService): int
    {
        $this->info(__('gamification::gamification.cleaning_counters'));

        $deleted = $counterService->cleanupExpiredCounters();

        $this->info("✅ " . __('gamification::gamification.counters_cleaned', ['count' => $deleted]));

        return 0;
    }
}
