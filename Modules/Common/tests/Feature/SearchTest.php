<?php

namespace Modules\Common\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_search_functionality(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);

        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'username' => 'janesmith',
        ]);

        $results = User::search('John')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($user->id, $results->first()->id);
    }

    public function test_partial_match_search(): void
    {
        $user = User::factory()->create([
            'name' => 'Michael Scott',
            'email' => 'michael@example.com',
            'username' => 'mscott',
        ]);

        $results = User::search('Mich')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($user->id, $results->first()->id);
    }

    public function test_case_insensitive_search(): void
    {
        $user = User::factory()->create([
            'name' => 'Robert Paulson',
        ]);

        $results = User::search('robert')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($user->id, $results->first()->id);
    }
    
     public function test_empty_search_returns_all_query(): void
    {
        User::factory()->count(3)->create();

        $results = User::search('')->get();

        $this->assertCount(3, $results);
    }
    public function test_search_by_slug(): void
    {
        $course = \Modules\Schemes\Models\Course::factory()->create([
            'title' => 'Advanced Laravel Course',
            'slug' => 'advanced-laravel-course-2024',
        ]);

        $results = \Modules\Schemes\Models\Course::search('advanced-laravel')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($course->id, $results->first()->id);
    }
}
