<?php

namespace Modules\Learning\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Learning\Enums\QuestionType;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Question;


class AssignmentQuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'type' => QuestionType::Essay->value,
            'content' => fake()->paragraph(3),
            'options' => null,
            'answer_key' => null,
            'weight' => fake()->randomFloat(2, 1, 5),
            'order' => fake()->numberBetween(1, 20),
            'max_score' => 50,
            'max_file_size' => null,
            'allowed_file_types' => null,
            'allow_multiple_files' => false,
        ];
    }

    
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::MultipleChoice->value,
            'options' => json_encode([
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
            ]),
            'answer_key' => json_encode(['correct_option' => 0]),
        ]);
    }

    
    public function essay(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::Essay->value,
            'content' => fake()->paragraph(3),
            'options' => null,
            'answer_key' => null,
            'max_score' => 50,
        ]);
    }

    
    public function checkbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::Checkbox->value,
            'options' => json_encode([
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
                [
                    'id' => fake()->uuid(),
                    'label' => fake()->sentence(3),
                ],
            ]),
            'answer_key' => json_encode(['correct_options' => [0, 2]]),
            'max_score' => 25,
        ]);
    }

    public function shortAnswer(): static
    {
        return $this->essay();
    }

    
    public function fileUpload(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::FileUpload->value,
            'content' => fake()->sentence(8),
            'options' => null,
            'answer_key' => null,
            'max_file_size' => 10000000,
            'allowed_file_types' => json_encode(['pdf', 'docx', 'txt', 'png', 'jpg']),
            'allow_multiple_files' => true,
        ]);
    }

    
    public function forAssignment(Assignment $assignment): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_id' => $assignment->id,
        ]);
    }
}
