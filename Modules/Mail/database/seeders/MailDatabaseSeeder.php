<?php

declare(strict_types=1);

namespace Modules\Mail\Database\Seeders;

use Illuminate\Database\Seeder;

class MailDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Mail: no persisted mail log in schema; templates ship with module views.');
    }
}
