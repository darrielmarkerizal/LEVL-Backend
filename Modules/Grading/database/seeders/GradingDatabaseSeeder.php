<?php

namespace Modules\Grading\Database\Seeders;

use Illuminate\Database\Seeder;

class GradingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GradeAndAppealSeeder::class,
            GradeReviewSeeder::class,
        ]);
    }
}
