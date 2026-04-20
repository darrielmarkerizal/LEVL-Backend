<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserGamificationStat;

class BackfillPointsSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->command->info('Backfilling points for users with XP but no point history...');

        
        $usersWithXp = UserGamificationStat::where('total_xp', '>', 0)
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('points')
                    ->whereColumn('points.user_id', 'user_gamification_stats.user_id');
            })
            ->get();

        if ($usersWithXp->isEmpty()) {
            $this->command->info('No users need backfilling.');
            return;
        }

        $this->command->info("Found {$usersWithXp->count()} users to backfill.");

        $bar = $this->command->getOutput()->createProgressBar($usersWithXp->count());
        $bar->start();

        foreach ($usersWithXp as $stat) {
            $this->backfillUserPoints($stat);
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Backfill completed successfully!');
    }

    private function backfillUserPoints(UserGamificationStat $stat): void
    {
        $userId = $stat->user_id;
        $totalXp = $stat->total_xp;
        $currentLevel = $stat->global_level;

        
        Point::create([
            'user_id' => $userId,
            'points' => $totalXp,
            'reason' => 'bonus', 
            'source_type' => 'system',
            'source_id' => null,
            'description' => 'Historical XP backfill - accumulated points from before point tracking',
            'created_at' => $stat->created_at ?? SeederDate::randomPastCarbonBetween(30, 180),
            'updated_at' => SeederDate::randomPastCarbonBetween(1, 180),
        ]);
    }
}
