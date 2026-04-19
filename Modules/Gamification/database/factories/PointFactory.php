<?php

namespace Modules\Gamification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Gamification\Enums\PointReason;
use Modules\Gamification\Enums\PointSourceType;
use Modules\Gamification\Models\Point;

class PointFactory extends Factory
{
    protected $model = Point::class;

    public function definition(): array
    {
        $scenario = fake()->numberBetween(1, 4);
        $sourceType = match ($scenario) {
            1 => PointSourceType::Lesson->value,
            2 => PointSourceType::Assignment->value,
            3 => PointSourceType::Quiz->value,
            default => PointSourceType::System->value,
        };
        $reason = match ($sourceType) {
            PointSourceType::Lesson->value => PointReason::LessonCompleted->value,
            PointSourceType::Assignment->value => PointReason::AssignmentSubmitted->value,
            PointSourceType::Quiz->value => PointReason::QuizCompleted->value,
            default => PointReason::Bonus->value,
        };
        $points = match ($sourceType) {
            PointSourceType::Lesson->value => 10,
            PointSourceType::Assignment->value => 50,
            PointSourceType::Quiz->value => 30,
            default => 20,
        };

        return [
            'user_id' => User::factory(),
            'source_type' => $sourceType,
            'source_id' => fake()->numberBetween(1, 100),
            'points' => $points,
            'reason' => $reason,
            'description' => fake()->sentence(),
        ];
    }

    public function lessonCompletion(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => PointSourceType::Lesson->value,
            'reason' => PointReason::LessonCompleted->value,
            'points' => 10,
        ]);
    }

    public function assignmentSubmission(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => PointSourceType::Assignment->value,
            'reason' => PointReason::AssignmentSubmitted->value,
            'points' => 50,
        ]);
    }

    public function courseCompletion(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => PointSourceType::Course->value,
            'reason' => PointReason::Completion->value,
            'points' => 100,
        ]);
    }
}
