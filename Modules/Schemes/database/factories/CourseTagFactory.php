<?php

namespace Modules\Schemes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\CourseTag;
use Modules\Schemes\Models\Tag;


class CourseTagFactory extends Factory
{
    protected $model = CourseTag::class;

    
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'tag_id' => Tag::factory(),
        ];
    }
}
