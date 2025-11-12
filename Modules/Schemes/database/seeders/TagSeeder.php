<?php

namespace Modules\Schemes\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Schemes\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = Collection::make([
            ['name' => 'Design'],
            ['name' => 'Frontend'],
            ['name' => 'Backend'],
            ['name' => 'DevOps'],
            ['name' => 'Data Science'],
            ['name' => 'Cybersecurity'],
            ['name' => 'Soft Skills'],
            ['name' => 'Project Management'],
            ['name' => 'Testing'],
        ]);

        $tags->each(function (array $tag) {
            $slug = Str::slug($tag['name']);

            Tag::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $tag['name'],
                    'slug' => $slug,
                ]
            );
        });
    }
}


