<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\ProfilePrivacySetting;
use Modules\Auth\Models\User;


class ProfilePrivacySettingFactory extends Factory
{
    protected $model = ProfilePrivacySetting::class;

    
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'profile_visibility' => ProfilePrivacySetting::VISIBILITY_PUBLIC,
            'show_email' => true,
            'show_phone' => false,
            'show_activity_history' => true,
            'show_achievements' => true,
            'show_statistics' => true,
        ];
    }

    
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_visibility' => ProfilePrivacySetting::VISIBILITY_PUBLIC,
            'show_email' => true,
            'show_phone' => true,
            'show_activity_history' => true,
            'show_achievements' => true,
            'show_statistics' => true,
        ]);
    }

    
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_visibility' => ProfilePrivacySetting::VISIBILITY_PRIVATE,
            'show_email' => false,
            'show_phone' => false,
            'show_activity_history' => false,
            'show_achievements' => false,
            'show_statistics' => false,
        ]);
    }

    
    public function friendsOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_visibility' => ProfilePrivacySetting::VISIBILITY_FRIENDS,
        ]);
    }
}
