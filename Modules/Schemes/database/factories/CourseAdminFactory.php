<?php

namespace Modules\Schemes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\CourseAdmin;


class CourseAdminFactory extends Factory
{
    protected $model = CourseAdmin::class;

    
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
        ];
    }
}
