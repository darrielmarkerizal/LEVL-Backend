<?php

namespace Modules\Schemes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Schemes\Enums\BlockType;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\LessonBlock;


class LessonBlockFactory extends Factory
{
    protected $model = LessonBlock::class;

    
    public function definition(): array
    {
        $typeRoll = fake()->numberBetween(1, 8);
        $blockType = match (true) {
            $typeRoll === 1 => BlockType::Text->value,
            $typeRoll === 2 => BlockType::Image->value,
            $typeRoll === 3 => BlockType::Video->value,
            $typeRoll === 4 => BlockType::File->value,
            $typeRoll === 5 => BlockType::Link->value,
            $typeRoll === 6 => BlockType::YouTube->value,
            $typeRoll === 7 => BlockType::Drive->value,
            default => BlockType::Embed->value,
        };

        $content = $blockType === BlockType::Text->value
            ? fake()->paragraphs(2, true)
            : fake()->sentence();

        $mediaUrl = in_array($blockType, [BlockType::Image->value, BlockType::Video->value, BlockType::File->value], true)
            ? fake()->url()
            : null;

        $externalUrl = in_array($blockType, [BlockType::Link->value, BlockType::YouTube->value, BlockType::Drive->value, BlockType::Embed->value], true)
            ? fake()->url()
            : null;

        return [
            'lesson_id' => Lesson::factory(),
            'block_type' => $blockType,
            'content' => $content,
            'media_url' => $mediaUrl,
            'external_url' => $externalUrl,
            'order' => fake()->numberBetween(1, 10),
        ];
    }

    
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'block_type' => 'text',
            'content' => fake()->paragraphs(3, true),
        ]);
    }

    
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'block_type' => 'image',
            'content' => json_encode([
                'caption' => fake()->sentence(),
                'alt_text' => fake()->sentence(),
            ]),
        ]);
    }

    
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'block_type' => 'video',
            'content' => json_encode([
                'title' => fake()->sentence(),
                'description' => fake()->paragraph(),
            ]),
        ]);
    }

    
    public function code(): static
    {
        return $this->state(fn (array $attributes) => [
            'block_type' => BlockType::Text->value,
            'content' => json_encode([
                'language' => 'php',
                'code' => fake()->text(200),
            ]),
        ]);
    }

    
    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'block_type' => 'quiz',
            'content' => json_encode([
                'question' => fake()->sentence().'?',
                'options' => [
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                ],
                'correct_answer' => 0,
            ]),
        ]);
    }
}
