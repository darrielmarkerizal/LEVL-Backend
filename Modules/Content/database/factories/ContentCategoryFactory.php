<?php

namespace Modules\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Models\ContentCategory;

class ContentCategoryFactory extends Factory
{
    protected $model = ContentCategory::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucfirst($name),
            
            'description' => fake()->sentence(),
        ];
    }
}
