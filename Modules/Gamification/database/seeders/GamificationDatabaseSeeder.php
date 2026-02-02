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
            BadgeSeeder::class,
            MilestoneSeeder::class,
            ChallengeSeeder::class,
            UserGamificationSeeder::class,
            LeaderboardSeeder::class,
        ]);
    }
}
