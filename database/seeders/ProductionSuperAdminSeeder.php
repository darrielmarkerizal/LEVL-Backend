<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;

class ProductionSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👤 Creating superadmin user...');

        // Ensure Superadmin role exists
        $superadminRole = Role::where('name', 'Superadmin')->where('guard_name', 'api')->first();
        if (! $superadminRole) {
            $this->command->error('❌ Superadmin role not found. Run RolePermissionSeeder first.');
            return;
        }

        // Create superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@levl.local'],
            [
                'username' => 'superadmin',
                'name' => 'Superadmin',
                'password' => Hash::make('ChangeMe123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Assign Superadmin role with all permissions
        $superadmin->syncRoles('Superadmin');

        $this->command->info('');
        $this->command->info('✅ Superadmin user created successfully!');
        $this->command->info('');
        $this->command->info('🔐 Login Credentials:');
        $this->command->info('   Email:    admin@levl.local');
        $this->command->info('   Username: superadmin');
        $this->command->info('   Password: ChangeMe123!');
        $this->command->info('');
        $this->command->warn('⚠️  IMPORTANT: Change the superadmin password immediately after first login!');
        $this->command->info('');
    }
}
