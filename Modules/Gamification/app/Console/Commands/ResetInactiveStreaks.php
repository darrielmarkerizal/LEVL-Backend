<?php

declare(strict_types=1);

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\Support\StreakResetService;

class ResetInactiveStreaks extends Command
{
    protected $signature = 'streaks:reset-inactive';

    protected $description = 'Reset streak untuk user yang tidak aktif kemarin';

    public function __construct(
        private readonly StreakResetService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Memulai reset streak untuk user yang tidak aktif...');

        $resetCount = $this->service->resetInactiveStreaks();

        $this->info("Berhasil reset {$resetCount} streak.");

        return self::SUCCESS;
    }
}
