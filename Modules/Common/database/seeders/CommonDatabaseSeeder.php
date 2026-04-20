<?php

namespace Modules\Common\Database\Seeders;

use Illuminate\Database\Seeder;

class CommonDatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->call([
            SystemSettingSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
