<?php

namespace Modules\Schemes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\CourseOutcome;


class CourseOutcomeFactory extends Factory
{
    protected $model = CourseOutcome::class;

    
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'outcome_text' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
