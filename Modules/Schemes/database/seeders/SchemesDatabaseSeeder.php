<?php

namespace Modules\Schemes\Database\Seeders;

use Illuminate\Database\Seeder;

class SchemesDatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->call([
            TagSeederEnhanced::class,
            CourseSeederEnhanced::class,
            LearningContentSeeder::class,
        ]);
    }
}
