<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Schemes\Models\Tag;


class TagFactory extends Factory
{
    protected $model = Tag::class;

    
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            
        ];
    }
}
