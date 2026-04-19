<?php

namespace Modules\Learning\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;


class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    
    public function definition(): array
    {
        $submittedAt = fake()->dateTimeBetween('-3 months', 'now');
        $roll = fake()->numberBetween(1, 100);
        $status = match (true) {
            $roll <= 25 => 'draft',
            $roll <= 65 => 'submitted',
            default => 'graded',
        };
        $state = match ($status) {
            'draft' => 'in_progress',
            'submitted' => 'pending_manual_grading',
            'graded' => 'graded',
            default => 'in_progress',
        };
        $score = $status === 'graded'
            ? fake()->randomFloat(2, 0, 100)
            : null;

        return [
            'assignment_id' => Assignment::factory(),
            'user_id' => User::factory(),
            'enrollment_id' => Enrollment::factory(),
            'answer_text' => fake()->optional(0.7)->paragraphs(3, true),
            'status' => $status,
            'submitted_at' => $status === 'draft' ? null : $submittedAt,
            'attempt_number' => 1,
            'score' => $score,
            'question_set' => null,
            'state' => $state,
            'started_at' => fake()->optional(0.8)->dateTimeBetween('-3 months', $submittedAt),
            'time_expired_at' => null,
            'auto_submitted_on_timeout' => false,
        ];
    }

    
    public function forAssignment(Assignment $assignment): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_id' => $assignment->id,
        ]);
    }

    
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
        ]);
    }

    
    public function graded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'graded',
            'state' => 'graded',
            'score' => rand(0, 100),
        ]);
    }

    
    public function inProgress(): static
    {
        return $this->draft();
    }

    
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'state' => 'pending_manual_grading',
            'submitted_at' => now(),
        ]);
    }

    public function autoGraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'graded',
            'state' => 'auto_graded',
            'submitted_at' => now(),
            'score' => rand(0, 100),
        ]);
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'graded',
            'state' => 'released',
            'submitted_at' => now(),
            'score' => rand(0, 100),
        ]);
    }

    public function withScore(float $score): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $score,
            'status' => 'graded',
        ]);
    }

    
    public function withQuestionSet(array $questionIds): static
    {
        return $this->state(fn (array $attributes) => [
            'question_set' => $questionIds,
        ]);
    }
}
