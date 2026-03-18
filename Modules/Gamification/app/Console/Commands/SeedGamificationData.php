<?php

declare(strict_types=1);

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Database\Seeders\GamificationDataSeeder;

class SeedGamificationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:seed-data 
                            {--force : Force seeding even if data exists}
                            {--fresh : Clear existing gamification data before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed gamification data (XP, badges, levels) for existing students';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎮 Gamification Data Seeder');
        $this->newLine();

        if ($this->option('fresh')) {
            if (! $this->confirm('⚠️  This will DELETE all existing gamification data. Continue?', false)) {
                $this->warn('Operation cancelled.');

                return self::FAILURE;
            }

            $this->info('Clearing existing gamification data...');
            $this->clearGamificationData();
            $this->info('✅ Data cleared.');
            $this->newLine();
        }

        $this->info('Starting gamification data seeding...');
        $this->newLine();

        $seeder = new GamificationDataSeeder;
        $seeder->setCommand($this);
        $seeder->run();

        $this->newLine();
        $this->info('🎉 Gamification data seeding completed successfully!');

        return self::SUCCESS;
    }

    /**
     * Clear existing gamification data
     */
    private function clearGamificationData(): void
    {
        \DB::table('user_gamification_stats')->delete();
        \DB::table('user_badges')->delete();
        \DB::table('points')->delete();

        $this->info('  - Cleared user_gamification_stats');
        $this->info('  - Cleared user_badges');
        $this->info('  - Cleared points');
    }
}
