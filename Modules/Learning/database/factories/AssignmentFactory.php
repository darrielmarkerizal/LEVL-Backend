<?php

namespace Modules\Learning\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Learning\Enums\ReviewMode;
use Modules\Learning\Models\Assignment;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Learning\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'submission_type' => fake()->randomElement(['text', 'file', 'mixed']),
            'max_score' => fake()->numberBetween(50, 100),
            'status' => 'published',
            'review_mode' => 'immediate',
            'randomization_type' => 'static',
            'question_bank_count' => null,
            'time_limit_minutes' => fake()->optional(0.5)->numberBetween(15, 120),
        ];
    }

    /**
     * Attach to a lesson using polymorphic relationship.
     */
    public function forLesson(?Lesson $lesson = null): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'assignable_type' => Lesson::class,
            'assignable_id' => $lesson?->id ?? Lesson::factory(),
        ]);
    }

    /**
     * Attach to a unit using polymorphic relationship.
     */
    public function forUnit(?Unit $unit = null): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'assignable_type' => Unit::class,
            'assignable_id' => $unit?->id ?? Unit::factory(),
        ]);
    }

    /**
     * Attach to a course using polymorphic relationship.
     */
    public function forCourse(?Course $course = null): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'assignable_type' => Course::class,
            'assignable_id' => $course?->id ?? Course::factory(),
        ]);
    }

    /**
     * Indicate that the assignment is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the assignment is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function withReviewMode(ReviewMode $mode): static
    {
        return $this->state(fn (array $attributes) => [
            'review_mode' => $mode,
        ]);
    }

    public function deferredReview(): static
    {
        return $this->withReviewMode(ReviewMode::Deferred);
    }

    public function hiddenReview(): static
    {
        return $this->withReviewMode(ReviewMode::Hidden);
    }

    /**
     * Enable random question order.
     */
    public function withRandomOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'randomization_type' => RandomizationType::RandomOrder,
        ]);
    }

    /**
     * Enable question bank selection.
     */
    public function withQuestionBank(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'randomization_type' => RandomizationType::Bank,
            'question_bank_count' => $count,
        ]);
    }
}
