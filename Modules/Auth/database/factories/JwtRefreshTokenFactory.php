<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Auth\Models\JwtRefreshToken;
use Modules\Auth\Models\User;


class JwtRefreshTokenFactory extends Factory
{
    protected $model = JwtRefreshToken::class;

    
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'device_id' => Str::uuid()->toString(),
            'token' => Str::random(64),
            'replaced_by' => null,
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'revoked_at' => null,
            'last_used_at' => now(),
            'expires_at' => now()->addDays(30),
            'idle_expires_at' => now()->addDays(7),
            'absolute_expires_at' => now()->addDays(90),
        ];
    }

    
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now()->subDays(1),
        ]);
    }

    
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(1),
        ]);
    }

    
    public function replaced(): static
    {
        return $this->state(fn (array $attributes) => [
            'replaced_by' => JwtRefreshToken::factory(),
        ]);
    }

    
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => null,
            'replaced_by' => null,
            'expires_at' => now()->addDays(30),
            'idle_expires_at' => now()->addDays(7),
            'absolute_expires_at' => now()->addDays(90),
        ]);
    }
}
