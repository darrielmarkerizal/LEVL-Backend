<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Auth\Models\UserActivity;


class UserActivityFactory extends Factory
{
    protected $model = UserActivity::class;

    
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'activity_type' => UserActivity::TYPE_ENROLLMENT,
            'activity_data' => [
                'title' => 'Enrolled in course',
                'description' => fake()->sentence(),
            ],
            'related_type' => null,
            'related_id' => null,
        ];
    }

    
    public function enrollment(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => UserActivity::TYPE_ENROLLMENT,
        ]);
    }

    
    public function completion(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => UserActivity::TYPE_COMPLETION,
        ]);
    }

    
    public function submission(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => UserActivity::TYPE_SUBMISSION,
        ]);
    }

    
    public function achievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => UserActivity::TYPE_ACHIEVEMENT,
        ]);
    }

    
    public function badgeEarned(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => UserActivity::TYPE_BADGE_EARNED,
        ]);
    }

    
    public function certificateEarned(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => UserActivity::TYPE_CERTIFICATE_EARNED,
        ]);
    }
}
