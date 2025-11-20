<?php

namespace Modules\Assessments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Assessments\Models\Exercise;
use Modules\Auth\Models\User;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition()
    {
        return [
            'scope_type' => $this->faker->randomElement(['course', 'unit', 'lesson']),
            'scope_id' => $this->faker->numberBetween(1, 100),
            'created_by' => User::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['quiz', 'exam']),
            'time_limit_minutes' => $this->faker->randomElement([15, 30, 45, 60, 90]),
            'max_score' => $this->faker->numberBetween(50, 200),
            'total_questions' => 0,
            'status' => 'draft',
            'available_from' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'available_until' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
        ];
    }

    public function published()
    {
        return $this->state(function () {
            return [
                'status' => 'published',
            ];
        });
    }

    public function draft()
    {
        return $this->state(function () {
            return [
                'status' => 'draft',
            ];
        });
    }
}
