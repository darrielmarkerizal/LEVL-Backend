<?php

namespace Modules\Enrollments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enrollments\Enums\ProgressStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\LessonProgress;
use Modules\Schemes\Models\Lesson;


class LessonProgressFactory extends Factory
{
    protected $model = LessonProgress::class;

    
    public function definition(): array
    {
        $statusRoll = fake()->numberBetween(1, 100);
        $status = match (true) {
            $statusRoll <= 30 => ProgressStatus::NotStarted->value,
            $statusRoll <= 80 => ProgressStatus::InProgress->value,
            default => ProgressStatus::Completed->value,
        };
        $startedAt = $status === ProgressStatus::NotStarted->value ? null : now()->subDays(rand(1, 7));
        $completedAt = $status === ProgressStatus::Completed->value ? now() : null;
        $progress = match ($status) {
            ProgressStatus::NotStarted->value => 0,
            ProgressStatus::InProgress->value => fake()->randomFloat(2, 1, 99),
            ProgressStatus::Completed->value => 100,
            default => 0,
        };
        $attemptCount = match ($status) {
            ProgressStatus::NotStarted->value => 0,
            default => fake()->numberBetween(1, 5),
        };

        return [
            'enrollment_id' => Enrollment::factory(),
            'lesson_id' => Lesson::factory(),
            'status' => $status,
            'progress_percent' => $progress,
            'attempt_count' => $attemptCount,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }

    
    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_started',
            'progress_percent' => 0,
            'attempt_count' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'progress_percent' => fake()->randomFloat(2, 1, 99),
            'started_at' => now()->subDays(rand(1, 7)),
            'completed_at' => null,
        ]);
    }

    
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress_percent' => 100,
            'started_at' => now()->subDays(rand(1, 7)),
            'completed_at' => now(),
        ]);
    }
}
