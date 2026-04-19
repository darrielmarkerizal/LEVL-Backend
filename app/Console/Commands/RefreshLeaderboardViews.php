<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshLeaderboardViews extends Command
{
    
    protected $signature = 'leaderboard:refresh {--concurrent : Refresh concurrently without locking}';

    
    protected $description = 'Refresh materialized views for leaderboards';

    
    public function handle(): int
    {
        $concurrent = $this->option('concurrent') ? 'CONCURRENTLY' : '';

        $this->info('Refreshing leaderboard materialized views...');

        try {
            
            $this->info('Refreshing global leaderboard...');
            DB::statement("REFRESH MATERIALIZED VIEW {$concurrent} mv_global_leaderboard");
            $this->info('✓ Global leaderboard refreshed');

            
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
