<?php

namespace Modules\Enrollments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;


class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        $statusRoll = fake()->numberBetween(1, 100);
        $status = match (true) {
            $statusRoll <= 20 => EnrollmentStatus::Pending->value,
            $statusRoll <= 75 => EnrollmentStatus::Active->value,
            $statusRoll <= 90 => EnrollmentStatus::Completed->value,
            default => EnrollmentStatus::Cancelled->value,
        };
        $enrolledAt = fake()->dateTimeBetween('-6 months', 'now');
        $completedAt = $status === EnrollmentStatus::Completed->value
            ? now()->subDays(rand(1, 30))
            : null;

        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'status' => $status,
            'enrolled_at' => $enrolledAt,
            'completed_at' => $completedAt,
        ];
    }

    
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    
    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $course->id,
        ]);
    }

    
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::Active->value,
            'approved_at' => now()->subDays(rand(5, 30)),
        ]);
    }

    
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::Pending->value,
            'approved_at' => null,
        ]);
    }

    
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::Completed->value,
            'progress_percent' => 100,
            'completed_at' => now()->subDays(rand(1, 30)),
            'approved_at' => now()->subDays(rand(30, 60)),
        ]);
    }
}
