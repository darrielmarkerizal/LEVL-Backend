<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Enrollments\Database\Seeders\EnrollmentActivityTimelineSeeder;
use Modules\Gamification\Database\Seeders\AwardBadgesFromUATMetricsSeeder;
use Modules\Gamification\Database\Seeders\LedgerPointsFromLearningFactsSeeder;
use Modules\Gamification\Database\Seeders\ReconcileUserGamificationStatsSeeder;

class UATGamificationPipelineSeeder extends Seeder
{
    public function run(): void
    {
        if (config('seeding.mode') !== 'uat') {
            return;
        }

        $this->call([
            EnrollmentActivityTimelineSeeder::class,
            LedgerPointsFromLearningFactsSeeder::class,
            ReconcileUserGamificationStatsSeeder::class,
            AwardBadgesFromUATMetricsSeeder::class,
            UATSeederIntegrityCheckSeeder::class,
        ]);
    }
}
