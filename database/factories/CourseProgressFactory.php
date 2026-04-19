<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enrollments\Enums\ProgressStatus;
use Modules\Enrollments\Models\CourseProgress;


class CourseProgressFactory extends Factory
{
    protected $model = CourseProgress::class;

    
    public function definition(): array
    {
        $statusRoll = fake()->numberBetween(1, 100);
        $status = match (true) {
            $statusRoll <= 30 => ProgressStatus::NotStarted->value,
            $statusRoll <= 80 => ProgressStatus::InProgress->value,
            default => ProgressStatus::Completed->value,
        };

        $progressPercent = match ($status) {
            ProgressStatus::NotStarted->value => 0,
            ProgressStatus::InProgress->value => fake()->randomFloat(2, 1, 99),
            ProgressStatus::Completed->value => 100,
            default => 0,
        };

        $startedAt = match ($status) {
            ProgressStatus::NotStarted->value => null,
            default => now()->subDays(rand(1, 30)),
        };

        $completedAt = $status === ProgressStatus::Completed->value
            ? now()->subDays(rand(0, 5))
            : null;

        return [
            'enrollment_id' => null,
            'status' => $status,
            'progress_percent' => $progressPercent,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }

    
    public function completed(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'status' => 'completed',
                'progress_percent' => 100,
                'completed_at' => now(),
            ],
        );
    }

    
    public function inProgress(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'status' => 'in_progress',
                'progress_percent' => fake()->randomFloat(2, 1, 99),
            ],
        );
    }
}
