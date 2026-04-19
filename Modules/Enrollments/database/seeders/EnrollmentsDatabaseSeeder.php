<?php

namespace Modules\Enrollments\Database\Seeders;

use Illuminate\Database\Seeder;

class EnrollmentsDatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->call([
            EnrollmentSeeder::class,
        ]);
    }
}
