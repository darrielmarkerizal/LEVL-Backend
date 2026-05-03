<?php

namespace Modules\Learning\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Learning\Enums\RandomizationType;
use Modules\Auth\Models\User;
use Modules\Learning\Enums\ReviewMode;
use Modules\Learning\Enums\SubmissionType;
use Modules\Learning\Models\Assignment;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;


class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'submission_type' => SubmissionType::Mixed->value,
            'max_score' => fake()->numberBetween(50, 100),
            'status' => 'published',
            'review_mode' => ReviewMode::Manual->value,
            'randomization_type' => 'static',
            'question_bank_count' => null,

        ];
    }

    
    public function forLesson(?Lesson $lesson = null): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'assignable_type' => Lesson::class,
            'assignable_id' => $lesson?->id ?? Lesson::factory(),
        ]);
    }

    
    public function forUnit(?Unit $unit = null): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'assignable_type' => Unit::class,
            'assignable_id' => $unit?->id ?? Unit::factory(),
        ]);
    }

    
    public function forCourse(?Course $course = null): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'assignable_type' => Course::class,
            'assignable_id' => $course?->id ?? Course::factory(),
        ]);
    }

    
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    
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

    
    public function withRandomOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'randomization_type' => RandomizationType::RandomOrder,
        ]);
    }

    
    public function withQuestionBank(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'randomization_type' => RandomizationType::Bank,
            'question_bank_count' => $count,
        ]);
    }
}
