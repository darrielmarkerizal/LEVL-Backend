<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;

class AuthDatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->command->info('Starting Auth module seeding...');

        $this->call([
            RolePermissionSeeder::class,
        ]);

        $this->command->info('Auth module seeding with comprehensive data...');

        $this->call([
            UserSeeder::class,
            JwtRefreshTokenSeeder::class,
            OtpCodeSeeder::class,
            PasswordResetTokenSeeder::class,
            ProfileAuditLogSeeder::class,
        ]);

        $this->command->info('✅ Auth module seeding completed!');
    }
}
