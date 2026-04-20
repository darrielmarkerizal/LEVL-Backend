<?php

namespace Modules\Content\Database\Factories;

use App\Support\SeederDate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Content\Models\News;


class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'status' => 'published',
            'is_featured' => false,
            'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'scheduled_at' => null,
            'views_count' => fake()->numberBetween(0, 1000),
            'deleted_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
