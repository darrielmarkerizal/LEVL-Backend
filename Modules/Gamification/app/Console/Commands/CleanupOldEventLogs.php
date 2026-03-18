<?php

declare(strict_types=1);

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\EventLoggerService;

class CleanupOldEventLogs extends Command
{
    protected $signature = 'gamification:cleanup-logs {--days=90 : Number of days to keep}';

    protected $description = 'Cleanup old gamification event logs';

    public function handle(EventLoggerService $loggerService): int
    {
        $days = (int) $this->option('days');

        $this->info(__('gamification::gamification.cleaning_logs', ['days' => $days]));

        $deleted = $loggerService->cleanupOldLogs($days);

        $this->info('✅ '.__('gamification::gamification.logs_cleaned', ['count' => $deleted]));

        return 0;
    }
}
