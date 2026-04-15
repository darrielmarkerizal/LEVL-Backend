<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;

class GamificationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $chain = [
            LevelConfigSeeder::class,
            XpSourceSeeder::class,
            MilestoneSeeder::class,
            BadgeSeeder::class,
            LinkMilestoneBadgesSeeder::class,
        ];

        if (config('seeding.mode') !== 'uat') {
            $chain[] = GamificationDataSeeder::class;
        }

        $this->call($chain);
    }
}
