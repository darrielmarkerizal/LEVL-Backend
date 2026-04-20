<?php

namespace Modules\Auth\Database\Factories;

use App\Support\SeederDate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Auth\Models\PasswordResetToken;


class PasswordResetTokenFactory extends Factory
{
    protected $model = PasswordResetToken::class;

    
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(60),
            'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ];
    }

    
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ]);
    }

    
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ]);
    }
}
