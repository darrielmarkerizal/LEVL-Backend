<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;

class ProfileAuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates audit logs for profile changes in activity_log table
     * (profile_audit_logs was consolidated into activity_log)
     */
    public function run(): void
    {
        echo "Creating profile audit logs in activity_log...\n";

        // ✅ Load active users once (no query per user)
        $activeUsers = User::where('status', 'active')
            ->limit(500)
            ->get();

        if ($activeUsers->isEmpty()) {
            echo "⚠️  No active users found. Skipping profile audit log seeding.\n";

            return;
        }

        $activityLogs = [];
        $count = 0;

        foreach ($activeUsers as $user) {
            $logCount = fake()->numberBetween(1, 10);

            for ($i = 0; $i < $logCount; $i++) {
                $action = fake()->randomElement([
                    'profile_update',
                    'avatar_upload',
                    'avatar_delete',
                    'email_change',
                    'password_change',
                    'privacy_settings_update',
                ]);

                $changes = match ($action) {
                    'profile_update' => [
                        'name' => [fake()->name(), fake()->name()],
                        'bio' => [fake()->sentence(), fake()->sentence()],
                    ],
                    'email_change' => [
                        'email' => [fake()->email(), fake()->email()],
                    ],
                    'password_change' => [
                        'password' => ['***', '***'],
                    ],
                    'privacy_settings_update' => [
                        'profile_visibility' => [fake()->randomElement(['public', 'private', 'friends_only']), 'public'],
                        'show_email' => [fake()->boolean(), fake()->boolean()],
                    ],
                    default => ['field' => [fake()->word(), fake()->word()]],
                };

                $createdAt = now()->subDays(rand(1, 365));

                // ✅ Build activity log data for batch insert (matching activity_log schema)
                $activityLogs[] = [
                    'log_name' => 'profile',
                    'description' => $action,
                    'subject_type' => 'Modules\\Auth\\Models\\User',
                    'subject_id' => $user->id,
                    'causer_type' => 'Modules\\Auth\\Models\\User',
                    'causer_id' => $user->id,
                    'properties' => json_encode([
                        'changes' => $changes,
                        'ip_address' => fake()->ipv4(),
                        'user_agent' => substr(fake()->userAgent(), 0, 500),
                    ]),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
                $count++;
            }
        }

        // ✅ Batch insert all activity logs at once
        if (! empty($activityLogs)) {
            foreach (array_chunk($activityLogs, 1000) as $chunk) {
                DB::table('activity_log')->insertOrIgnore($chunk);
            }
        }

        echo "✅ Created $count profile audit logs in activity_log\n";
    }
}
