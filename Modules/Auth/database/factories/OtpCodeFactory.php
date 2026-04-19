<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Auth\Models\OtpCode;
use Modules\Auth\Models\User;


class OtpCodeFactory extends Factory
{
    protected $model = OtpCode::class;

    
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'user_id' => User::factory(),
            'channel' => 'email',
            'provider' => 'smtp',
            'purpose' => 'register_verification',
            'code' => str_pad((string) fake()->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT),
            'meta' => [],
            'expires_at' => now()->addMinutes(10),
            'consumed_at' => null,
        ];
    }

    
    public function forEmailVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'purpose' => 'register_verification',
            'channel' => 'email',
        ]);
    }

    
    public function forPasswordReset(): static
    {
        return $this->state(fn (array $attributes) => [
            'purpose' => 'password_reset',
            'channel' => 'email',
        ]);
    }

    
    public function forLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'purpose' => 'two_factor_auth',
        ]);
    }

    
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(10),
        ]);
    }

    
    public function consumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'consumed_at' => now()->subMinutes(5),
        ]);
    }

    
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addMinutes(10),
            'consumed_at' => null,
        ]);
    }
}
