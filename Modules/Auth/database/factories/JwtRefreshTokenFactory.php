<?php

namespace Modules\Auth\Database\Factories;

use App\Support\SeederDate;
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
            'last_used_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'expires_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'idle_expires_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'absolute_expires_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ];
    }

    
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ]);
    }

    
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => SeederDate::randomPastDateTimeBetween(1, 180),
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
            'expires_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'idle_expires_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'absolute_expires_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ]);
    }
}
