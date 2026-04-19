<?php

namespace Modules\Schemes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Schemes\Enums\ContentType;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;


class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $scenario = fake()->numberBetween(1, 3);
        $contentType = match ($scenario) {
            1 => ContentType::Markdown->value,
            2 => ContentType::Video->value,
            default => ContentType::Link->value,
        };
        $contentUrl = $contentType === ContentType::Markdown->value
            ? null
            : fake()->url();

        return [
            'unit_id' => Unit::factory(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'markdown_content' => fake()->paragraph(5),
            'content_type' => $contentType,
            'content_url' => $contentUrl,
            'order' => fake()->numberBetween(1, 20),
            'duration_minutes' => fake()->numberBetween(15, 90),
        ];
    }

    
    public function videoContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => ContentType::Video->value,
        ]);
    }

    
    public function documentContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => ContentType::Document->value,
        ]);
    }

    
    public function forUnit(Unit $unit): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_id' => $unit->id,
        ]);
    }
}
