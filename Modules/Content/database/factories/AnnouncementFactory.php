<?php

namespace Modules\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Content\Models\Announcement;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Content\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'content' => fake()->paragraphs(3, true),
            'status' => 'published',
            'priority' => 'normal',
            'published_at' => now(),
            'target_type' => 'all',
            'views_count' => fake()->numberBetween(0, 500),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_at' => now()->addDay(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function forRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'role',
            'target_value' => $role,
        ]);
    }

    public function forCourse(?\Modules\Schemes\Models\Course $course = null): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'course',
            'course_id' => $course?->id ?? \Modules\Schemes\Models\Course::factory(),
        ]);
    }
}
