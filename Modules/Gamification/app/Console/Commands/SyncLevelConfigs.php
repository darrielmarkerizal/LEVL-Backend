<?php

declare(strict_types=1);

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\LevelService;

class SyncLevelConfigs extends Command
{
    protected $signature = 'gamification:sync-levels 
                            {--start=1 : Start level}
                            {--end=100 : End level}
                            {--force : Force sync without confirmation}';

    protected $description = 'Sync level configurations using formula: XP(level) = 100 × level^1.6';

    public function handle(LevelService $levelService): int
    {
        $startLevel = max(1, (int) $this->option('start'));
        $endLevel = min(100, (int) $this->option('end'));

        $this->info("Syncing level configurations from level {$startLevel} to {$endLevel}");
        $this->info('Formula: XP(level) = 100 × level^1.6');
        $this->newLine();

        // Show preview
        $this->table(
            ['Level', 'XP Required', 'Total XP', 'Name'],
            collect(range($startLevel, min($startLevel + 9, $endLevel)))->map(function ($level) use ($levelService) {
                return [
                    $level,
                    number_format($levelService->calculateXpForLevel($level)),
                    number_format($levelService->calculateTotalXpForLevel($level)),
                    $levelService->getLevelName($level),
                ];
            })
        );

        if ($endLevel > $startLevel + 9) {
            $this->info('... and '.($endLevel - $startLevel - 9).' more levels');
            $this->newLine();
        }

        if (! $this->option('force') && ! $this->confirm('Do you want to proceed with syncing?')) {
            $this->warn('Sync cancelled');

            return self::FAILURE;
        }

        $this->info('Syncing level configurations...');
        $bar = $this->output->createProgressBar($endLevel - $startLevel + 1);

        $synced = 0;
        for ($level = $startLevel; $level <= $endLevel; $level++) {
            $levelService->syncLevelConfigs($level, $level);
            $synced++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Successfully synced {$synced} level configurations");

        // Show some examples
        $this->newLine();
        $this->info('Examples:');
        $examples = [1, 10, 25, 50, 75, 100];
        foreach ($examples as $level) {
            if ($level >= $startLevel && $level <= $endLevel) {
                $xp = $levelService->calculateXpForLevel($level);
                $total = $levelService->calculateTotalXpForLevel($level);
                $this->line("  Level {$level}: ".number_format($xp).' XP (Total: '.number_format($total).' XP)');
            }
        }

        return self::SUCCESS;
    }
}
