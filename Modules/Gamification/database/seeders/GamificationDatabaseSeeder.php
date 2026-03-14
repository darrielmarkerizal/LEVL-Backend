<?php

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;

class GamificationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            LevelConfigSeeder::class,  // Sync level configs first
            XpSourceSeeder::class,     // Then XP sources
            MilestoneSeeder::class,    // Then milestones
            BadgeSeeder::class,        // Then badges
            // UserGamificationSeeder::class,  // Skip for production (test data)
            // LeaderboardSeeder::class,       // Skip for production (test data)
        ]);
    }
}
