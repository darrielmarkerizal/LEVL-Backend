<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\ProfileAuditLog;
use Modules\Auth\Models\User;


class ProfileAuditLogFactory extends Factory
{
    protected $model = ProfileAuditLog::class;

    
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admin_id' => User::factory(),
            'action' => 'profile_updated',
            'changes' => [
                'field' => 'name',
                'old_value' => fake()->name(),
                'new_value' => fake()->name(),
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    
    public function profileUpdated(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'profile_updated',
            'changes' => [
                'field' => 'name',
                'old_value' => fake()->name(),
                'new_value' => fake()->name(),
            ],
        ]);
    }

    
    public function statusChanged(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'status_changed',
            'changes' => [
                'field' => 'status',
                'old_value' => 'active',
                'new_value' => 'suspended',
            ],
        ]);
    }

    
    public function roleAssigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'role_assigned',
            'changes' => [
                'role' => 'Instructor',
            ],
        ]);
    }
}
