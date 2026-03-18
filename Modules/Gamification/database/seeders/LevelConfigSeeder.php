<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gamification\Services\LevelService;

class LevelConfigSeeder extends Seeder
{
    public function run(LevelService $levelService): void
    {
        $this->command->info('Syncing level configurations...');

        $synced = $levelService->syncLevelConfigs(1, 100);

        $this->command->info("✓ Successfully synced {$synced} level configurations");

        // Show some examples
        $this->command->newLine();
        $this->command->info('Examples:');
        $examples = [1, 10, 25, 50, 75, 100];

        foreach ($examples as $level) {
            $xp = $levelService->calculateXpForLevel($level);
            $total = $levelService->calculateTotalXpForLevel($level);
            $this->command->line("  Level {$level}: ".number_format($xp).' XP (Total: '.number_format($total).' XP)');
        }
    }
}
