<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Auth Module Comprehensive Seeder (Enhanced)
 *
 * Creates comprehensive, realistic test data for the Auth module.
 * Uses faker-provider-collection for realistic names, roles, and data.
 *
 * Usage:
 *   php artisan db:seed --class="Modules\Auth\Database\Seeders\AuthComprehensiveDataSeeder"
 *
 * Demo Credentials (password: password):
 *   - Superadmin: superadmin.demo@test.com
 *   - Admin: admin.demo@test.com
 *   - Instructor: instructor.demo@test.com
 *   - Student: student.demo@test.com
 *
 * Special Test Users (password: password):
 *   - unverified.student@test.com - Email not verified
 *   - no.password.student@test.com - Social login, no password set
 *   - inactive.student@test.com - Inactive account
 *   - banned.student@test.com - Banned account
 *   - email.change.student@test.com - Pending email change
 *   - deletion.pending@test.com - Pending account deletion
 *   - password.reset.student@test.com - Active password reset token
 *   - soft.deleted.student@test.com - Soft deleted (recoverable)
 *
 * Data Distribution:
 *   Roles:
 *   - 50 Superadmin users
 *   - 100 Admin users
 *   - 200 Instructor users
 *   - 650 Student users
 *   Total: 1000+ users
 *
 *   User Status Distribution (per role):
 *   - 70% Active (verified email, can login)
 *   - 15% Pending (unverified email)
 *   - 10% Inactive (account disabled)
 *   - 5% Banned (account banned)
 *
 * Related Data Created:
 *   - Privacy settings for all users
 *   - User activities (10-30 per active user)
 *   - OTP codes for various purposes:
 *     * Email verification (all pending users)
 *     * Password reset (5% of active users)
 *     * Email change verification (3% of active users)
 *     * Account deletion (1% of active users)
 *   - Password reset tokens (valid and expired)
 *   - Profile audit logs
 *
 * Performance:
 *   - Batch inserts to prevent N+1 queries
 *   - Chunked processing (100 users per chunk)
 *   - Progress output for monitoring
 *   - Completes in < 60 seconds for 1000 users
 *
 * @see UserSeederEnhanced - Creates users with realistic data
 * @see OtpCodeSeeder - Creates OTP codes for various flows
 * @see PasswordResetTokenSeeder - Creates password reset tokens
 * @see RolePermissionSeeder - Creates roles and permissions
 */
class AuthComprehensiveDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════════════════════════╗');
        $this->command->info('║     Auth Module Comprehensive Data Seeding                ║');
        $this->command->info('║     Creating 1000+ users with realistic test data         ║');
        $this->command->info('╚════════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $startTime = microtime(true);

        $this->call([
            RolePermissionSeeder::class,
            UserSeederEnhanced::class,
            ProfilePrivacySettingSeeder::class,
            ProfileSeeder::class,
            JwtRefreshTokenSeeder::class,
            OtpCodeSeeder::class,
            PasswordResetTokenSeeder::class,
            ProfileAuditLogSeeder::class,
        ]);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════════════════════════╗');
        $this->command->info('║  ✅ Seeding Completed Successfully!                       ║');
        $this->command->info("║  ⏱️  Time taken: {$duration} seconds                      ║");
        $this->command->info('╚════════════════════════════════════════════════════════════╝');
        $this->command->newLine();
        $this->printSummary();
        $this->command->newLine();
    }

    /**
     * Print a summary of the seeded data
     */
    private function printSummary(): void
    {
        $this->command->info('📊 Data Summary:');
        $this->command->info('  • Total Users: '.\Modules\Auth\Models\User::count());
        $this->command->info('  • Demo Accounts: 4');
        $this->command->info('  • Special Test Users: 8');
        $this->command->info('  • Privacy Settings: '.\Illuminate\Support\Facades\DB::table('profile_privacy_settings')->count());
        $this->command->info('  • OTP Codes: '.\Modules\Auth\Models\OtpCode::count());
        $this->command->info('  • Password Reset Tokens: '.\Illuminate\Support\Facades\DB::table('password_reset_tokens')->count());

        $activityCount = \Illuminate\Support\Facades\DB::getSchemaBuilder()->hasTable('user_activities')
            ? \Illuminate\Support\Facades\DB::table('user_activities')->count()
            : 0;
        $this->command->info('  • User Activities: '.$activityCount);
        $this->command->newLine();

        $this->command->info('🔐 Demo Credentials (password: password):');
        $this->command->info('  Email                        | Username         | Role       | Status');
        $this->command->info('  ─────────────────────────────┼──────────────────┼────────────┼────────');
        $this->command->info('  superadmin.demo@test.com     | superadmin_demo  | Superadmin | Active');
        $this->command->info('  admin.demo@test.com          | admin_demo       | Admin      | Active');
        $this->command->info('  instructor.demo@test.com     | instructor_demo  | Instructor | Active');
        $this->command->info('  student.demo@test.com        | student_demo     | Student    | Active');
        $this->command->newLine();

        $this->command->info('🎯 Special Test Users (password: password):');
        $this->command->info('  • unverified.student@test.com       - Unverified email (pending verification)');
        $this->command->info('  • no.password.student@test.com      - No password set (social login)');
        $this->command->info('  • inactive.student@test.com         - Inactive account');
        $this->command->info('  • banned.student@test.com           - Banned account');
        $this->command->info('  • email.change.student@test.com     - Pending email change request');
        $this->command->info('  • deletion.pending@test.com         - Pending account deletion');
        $this->command->info('  • password.reset.student@test.com   - Has active password reset token');
        $this->command->info('  • soft.deleted.student@test.com     - Soft deleted (can be restored)');
        $this->command->newLine();

        $this->command->info('🧪 Testing Scenarios Covered:');
        $this->command->info('  ✓ Login with various user roles and statuses');
        $this->command->info('  ✓ Email verification flow (pending users)');
        $this->command->info('  ✓ Password reset flow (expired and valid tokens)');
        $this->command->info('  ✓ Email change verification');
        $this->command->info('  ✓ Account deletion flow');
        $this->command->info('  ✓ Multi-device token management');
        $this->command->info('  ✓ Role-based access control (RBAC)');
        $this->command->info('  ✓ Privacy settings filtering');
        $this->command->info('  ✓ Activity tracking and history');
        $this->command->info('  ✓ Social login scenarios (no password)');
        $this->command->info('  ✓ Soft delete and account recovery');
        $this->command->newLine();
    }
}
