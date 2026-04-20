<?php

declare(strict_types=1);

namespace Modules\Auth\Database\Seeders;

use App\Support\SeederDate;
use App\Support\RealisticSeederContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;

class ProfileAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating profile audit logs in activity_log...\n";

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
            $logCount = 1 + ($user->id % 10);

            for ($i = 0; $i < $logCount; $i++) {
                $seed = $user->id * 17 + $i;
                $actions = [
                    'profile_update',
                    'avatar_upload',
                    'avatar_delete',
                    'email_change',
                    'password_change',
                    'privacy_settings_update',
                ];
                $action = $actions[$seed % count($actions)];

                [$n1f, $n1l] = RealisticSeederContent::indonesianNamePair($seed);
                [$n2f, $n2l] = RealisticSeederContent::indonesianNamePair($seed + 11);
                $nameBefore = $n1f.' '.$n1l;
                $nameAfter = $n2f.' '.$n2l;

                $changes = match ($action) {
                    'profile_update' => [
                        'name' => [$nameBefore, $nameAfter],
                        'bio' => [
                            RealisticSeederContent::shortSentence($seed),
                            RealisticSeederContent::shortSentence($seed + 3),
                        ],
                    ],
                    'email_change' => [
                        'email' => [
                            RealisticSeederContent::demoEmail('lama.'.$user->id),
                            RealisticSeederContent::demoEmail('baru.'.$user->id),
                        ],
                    ],
                    'password_change' => [
                        'password' => ['***', '***'],
                    ],
                    'privacy_settings_update' => [
                        'profile_visibility' => [['public', 'private', 'friends_only'][$seed % 3], 'public'],
                        'show_email' => [($seed % 2) === 0, ($seed % 2) === 1],
                    ],
                    default => [
                        'field' => [
                            RealisticSeederContent::wordToken($seed),
                            RealisticSeederContent::wordToken($seed + 1),
                        ],
                    ],
                };

                $createdAt = SeederDate::randomPastCarbonBetween(1, 180);

                $activityLogs[] = [
                    'log_name' => 'profile',
                    'description' => $action,
                    'subject_type' => 'Modules\\Auth\\Models\\User',
                    'subject_id' => $user->id,
                    'causer_type' => 'Modules\\Auth\\Models\\User',
                    'causer_id' => $user->id,
                    'properties' => json_encode([
                        'changes' => $changes,
                        'ip_address' => RealisticSeederContent::ipv4ForIndex($seed),
                        'user_agent' => substr(RealisticSeederContent::userAgentForIndex($seed), 0, 500),
                    ]),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
                $count++;
            }
        }

        if (! empty($activityLogs)) {
            foreach (array_chunk($activityLogs, 1000) as $chunk) {
                DB::table('activity_log')->insertOrIgnore($chunk);
            }
        }

        echo "✅ Created $count profile audit logs in activity_log\n";
    }
}
