<?php

namespace Modules\Common\Database\Seeders;

use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('activity_log')->count() > 1000) {
            return;
        }

        $userIds = User::query()
            ->whereNull('deleted_at')
            ->inRandomOrder()
            ->limit(400)
            ->pluck('id')
            ->all();

        if ($userIds === []) {
            return;
        }

        $actions = [
            'login' => 'User logged in successfully',
            'logout' => 'User logged out of the platform',
            'view_course' => 'User opened a course detail page',
            'view_lesson' => 'User viewed a lesson content',
            'submit_assignment' => 'User submitted an assignment',
            'start_quiz' => 'User started a quiz attempt',
            'complete_quiz' => 'User completed a quiz attempt',
            'enroll_course' => 'User enrolled in a course',
            'update_profile' => 'User updated profile details',
            'download_certificate' => 'User downloaded certificate',
        ];

        $logNames = ['auth', 'system', 'user', 'course', 'assessment'];
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $platforms = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];
        $devices = ['desktop', 'mobile', 'tablet'];
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan', 'Semarang', 'Denpasar', 'Makassar'];

        $actionKeys = array_keys($actions);

        foreach ($userIds as $userId) {
            $count = rand(5, 30);

            for ($i = 0; $i < $count; $i++) {
                $actionKey = $actionKeys[array_rand($actionKeys)];
                $logName = $logNames[array_rand($logNames)];
                $city = $cities[array_rand($cities)];

                ActivityLog::create([
                    'log_name' => $logName,
                    'description' => $actions[$actionKey],
                    'subject_type' => User::class,
                    'subject_id' => $userId,
                    'causer_type' => User::class,
                    'causer_id' => $userId,
                    'properties' => [
                        'event' => $actionKey,
                        'browser' => $browsers[array_rand($browsers)],
                        'platform' => $platforms[array_rand($platforms)],
                        'device_type' => $devices[array_rand($devices)],
                        'ip' => sprintf('%d.%d.%d.%d', rand(10, 223), rand(0, 255), rand(0, 255), rand(1, 254)),
                        'city' => $city,
                        'region' => 'Indonesia',
                        'country' => 'Indonesia',
                    ],
                    'created_at' => now()->subDays(rand(0, 90))->subMinutes(rand(0, 1440)),
                ]);
            }
        }
    }
}
