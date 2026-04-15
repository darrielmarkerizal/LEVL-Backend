<?php

declare(strict_types=1);

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\ProfilePrivacySetting;
use Modules\Auth\Models\User;

class ProfilePrivacySettingSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating profile privacy settings...\n";

        $users = User::whereDoesntHave('privacySettings')->get();
        $count = 0;

        foreach ($users as $user) {
            $m = $user->id % 10;
            $visibility = match ($user->status) {
                'pending' => $m < 5 ? 'public' : 'private',
                'active' => match ($m % 3) {
                    0 => 'public',
                    1 => 'friends_only',
                    default => 'private',
                },
                'inactive' => 'private',
                'banned' => 'private',
                default => 'public',
            };

            ProfilePrivacySetting::create([
                'user_id' => $user->id,
                'profile_visibility' => $visibility,
                'show_email' => ($m % 3) === 0,
                'show_phone' => ($m % 4) === 0,
                'show_activity_history' => ($m % 10) < 7,
                'show_achievements' => ($m % 10) < 8,
                'show_statistics' => ($m % 10) < 6,
            ]);

            $count++;
        }

        echo "✅ Created $count profile privacy settings\n";
    }
}
