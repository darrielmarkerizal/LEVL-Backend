<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\ProfilePrivacySetting;

return new class extends Migration
{
    
    public function up(): void
    {
        
        $usersWithoutSettings = DB::table('users')
            ->leftJoin('profile_privacy_settings', 'users.id', '=', 'profile_privacy_settings.user_id')
            ->whereNull('profile_privacy_settings.id')
            ->select('users.id')
            ->get();

        
        foreach ($usersWithoutSettings as $user) {
            DB::table('profile_privacy_settings')->insert([
                'user_id' => $user->id,
                'profile_visibility' => ProfilePrivacySetting::VISIBILITY_PUBLIC,
                'show_email' => false,
                'show_phone' => false,
                'show_activity_history' => true,
                'show_achievements' => true,
                'show_statistics' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    
    public function down(): void
    {
        
    }
};
