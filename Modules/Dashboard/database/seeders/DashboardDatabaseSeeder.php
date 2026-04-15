<?php

declare(strict_types=1);

namespace Modules\Dashboard\Database\Seeders;

use Illuminate\Database\Seeder;

class DashboardDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Dashboard: no seed tables (widgets use live enrollments, progress, points).');
    }
}
