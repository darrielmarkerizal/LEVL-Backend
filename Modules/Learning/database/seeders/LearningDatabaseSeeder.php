<?php

namespace Modules\Learning\Database\Seeders;

use Illuminate\Database\Seeder;

class LearningDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ComprehensiveAssessmentSeeder::class,
        ]);
    }
}
