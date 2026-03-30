<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshLeaderboardViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboard:refresh {--concurrent : Refresh concurrently without locking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh materialized views for leaderboards';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $concurrent = $this->option('concurrent') ? 'CONCURRENTLY' : '';

        $this->info('Refreshing leaderboard materialized views...');

        try {
            // Refresh global leaderboard
            $this->info('Refreshing global leaderboard...');
            DB::statement("REFRESH MATERIALIZED VIEW {$concurrent} mv_global_leaderboard");
            $this->info('✓ Global leaderboard refreshed');

            // Refresh course leaderboards
            $this->info('Refreshing course leaderboards...');
            DB::statement("REFRESH MATERIALIZED VIEW {$concurrent} mv_course_leaderboards");
            $this->info('✓ Course leaderboards refreshed');

            $this->newLine();
            $this->info('All leaderboard views refreshed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to refresh materialized views: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
