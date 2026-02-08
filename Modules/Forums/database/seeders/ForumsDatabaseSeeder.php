<?php

namespace Modules\Forums\Database\Seeders;

use Illuminate\Database\Seeder;

class ForumsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ForumSeeder::class,
        ]);
    }
}