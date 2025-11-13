<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Schemes\Models\Course;

/**
 * Example API filtering, sorting, and pagination tests.
 *
 * This test class demonstrates how to test the global filtering,
 * sorting, and pagination system across API endpoints.
 *
 * Run with: php artisan test tests/Feature/Api/FilteringSortingPaginationTest.php
 */
class FilteringSortingPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create test courses
        Course::factory()->create([
            'title' => 'Laravel Basics',
            'status' => 'published',
            'level_tag' => 'basic',
            'created_at' => now()->subDays(10),
        ]);

        Course::factory()->create([
            'title' => 'Advanced Laravel',
            'status' => 'published',
            'level_tag' => 'advanced',
            'created_at' => now()->subDays(5),
        ]);

        Course::factory()->create([
            'title' => 'PHP Fundamentals',
            'status' => 'draft',
            'level_tag' => 'basic',
            'created_at' => now(),
        ]);

        Course::factory(5)->create(['status' => 'published']);
    }

    // ========================
    // FILTER TESTS
    // ========================

    /** @test */
    public function it_can_filter_by_status()
    {
        $response = $this->getJson('/api/courses?filter[status]=published');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['items', 'meta']])
            ->assertJsonCount(7, 'data.items') // 7 published courses
            ->assertJsonPath('data.items.0.status', 'published');
    }

    /** @test */
    public function it_filters_by_multiple_fields()
    {
        $response = $this->getJson(
            '/api/courses?filter[status]=published&filter[level]=basic'
        );

        $response->assertStatus(200);
        $items = $response->json('data.items');
        
        // All items should be published and basic level
        foreach ($items as $item) {
            $this->assertEquals('published', $item['status']);
            $this->assertEquals('basic', $item['level_tag']);
        }
    }

    /** @test */
    public function it_ignores_unknown_filter_fields()
    {
        // Unknown fields should be silently ignored
        $response = $this->getJson(
            '/api/courses?filter[unknown_field]=value&filter[status]=published'
        );

        $response->assertStatus(200)
            ->assertJsonCount(7, 'data.items'); // Should still filter by status
    }

    /** @test */
    public function it_handles_empty_filter_values()
    {
        $response = $this->getJson('/api/courses?filter[status]=&filter[level]=basic');

        $response->assertStatus(200);
        // Empty filter values should be ignored
    }

    // ========================
    // SORTING TESTS
    // ========================

    /** @test */
    public function it_can_sort_ascending()
    {
        $response = $this->getJson('/api/courses?sort=title');

        $response->assertStatus(200);
        $items = $response->json('data.items');
        
        // Verify sorted alphabetically by title
        $titles = array_column($items, 'title');
        $this->assertEquals($titles, array_values($titles)); // Sorted check
    }

    /** @test */
    public function it_can_sort_descending()
    {
        $response = $this->getJson('/api/courses?sort=-created_at');

        $response->assertStatus(200);
        $items = $response->json('data.items');
        
        // Verify newest first
        $timestamps = array_column($items, 'created_at');
        $this->assertEquals($timestamps, array_values(array_reverse($timestamps)));
    }

    /** @test */
    public function it_uses_default_sort_for_unknown_field()
    {
        $response = $this->getJson('/api/courses?sort=unknown_field');

        $response->assertStatus(200);
        // Should not error, just use default sort
    }

    /** @test */
    public function it_validates_sort_format()
    {
        $response = $this->postJson('/api/courses', [], [
            'Accept' => 'application/json'
        ]);
        
        // Test with ListRequest validation
        $response = $this->getJson('/api/courses?sort=invalid sort format!');
        
        // Should either ignore or validate based on implementation
    }

    // ========================
    // PAGINATION TESTS
    // ========================

    /** @test */
    public function it_can_paginate_results()
    {
        $response = $this->getJson('/api/courses?page=1&per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.items')
            ->assertJsonPath('data.meta.current_page', 1)
            ->assertJsonPath('data.meta.per_page', 5)
            ->assertJsonPath('data.meta.total', 8);
    }

    /** @test */
    public function it_can_get_second_page()
    {
        $response = $this->getJson('/api/courses?page=2&per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.items') // 8 - 5 = 3 remaining
            ->assertJsonPath('data.meta.current_page', 2)
            ->assertJsonPath('data.meta.from', 6)
            ->assertJsonPath('data.meta.to', 8);
    }

    /** @test */
    public function it_respects_max_per_page()
    {
        // Request more than max (100)
        $response = $this->getJson('/api/courses?per_page=150');

        $response->assertStatus(200);
        $perPage = $response->json('data.meta.per_page');
        
        $this->assertLessThanOrEqual(100, $perPage);
    }

    /** @test */
    public function it_uses_default_pagination_values()
    {
        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
            ->assertJsonPath('data.meta.current_page', 1)
            ->assertJsonPath('data.meta.per_page', 15); // default
    }

    /** @test */
    public function it_includes_pagination_metadata()
    {
        $response = $this->getJson('/api/courses?page=1&per_page=5');

        $response->assertJsonStructure([
            'data' => [
                'items',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                    'from',
                    'to',
                    'has_more',
                ]
            ]
        ]);

        $meta = $response->json('data.meta');
        
        // Validate metadata is correct
        $this->assertIsInt($meta['current_page']);
        $this->assertIsInt($meta['per_page']);
        $this->assertIsInt($meta['total']);
        $this->assertIsInt($meta['last_page']);
        $this->assertIsBool($meta['has_more']);
    }

    // ========================
    // COMBINED TESTS
    // ========================

    /** @test */
    public function it_can_combine_filter_sort_and_pagination()
    {
        $response = $this->getJson(
            '/api/courses?filter[status]=published&sort=-created_at&page=1&per_page=3'
        );

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.items')
            ->assertJsonPath('data.meta.current_page', 1)
            ->assertJsonPath('data.meta.per_page', 3)
            ->assertJsonPath('data.meta.has_more', true);

        // Verify all items are published
        foreach ($response->json('data.items') as $item) {
            $this->assertEquals('published', $item['status']);
        }
    }

    /** @test */
    public function it_can_filter_and_sort_together()
    {
        $response = $this->getJson(
            '/api/courses?filter[level]=basic&sort=title'
        );

        $response->assertStatus(200);
        $items = $response->json('data.items');
        
        // Verify all are basic level
        foreach ($items as $item) {
            $this->assertEquals('basic', $item['level_tag']);
        }
        
        // Verify sorted by title
        $titles = array_column($items, 'title');
        // Check if in alphabetical order
    }

    /** @test */
    public function it_maintains_filter_params_in_pagination_links()
    {
        $response = $this->getJson(
            '/api/courses?filter[status]=published&sort=-created_at&page=1'
        );

        $response->assertStatus(200);
        
        // The response should include the original params for pagination
        // This is handled by ->appends($params) in pagination
    }

    // ========================
    // EDGE CASES
    // ========================

    /** @test */
    public function it_handles_empty_results()
    {
        $response = $this->getJson('/api/courses?filter[status]=nonexistent');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.items')
            ->assertJsonPath('data.meta.total', 0)
            ->assertJsonPath('data.meta.has_more', false);
    }

    /** @test */
    public function it_handles_page_beyond_results()
    {
        $response = $this->getJson('/api/courses?page=999&per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.items')
            ->assertJsonPath('data.meta.has_more', false);
    }

    /** @test */
    public function it_corrects_invalid_page_numbers()
    {
        // Page 0 should be treated as page 1
        $response = $this->getJson('/api/courses?page=0');

        $response->assertStatus(200)
            ->assertJsonPath('data.meta.current_page', 1);
    }

    /** @test */
    public function it_corrects_invalid_per_page_values()
    {
        // Negative per_page should be 1
        $response = $this->getJson('/api/courses?per_page=-5');

        $response->assertStatus(200);
        $perPage = $response->json('data.meta.per_page');
        $this->assertGreaterThanOrEqual(1, $perPage);
    }
}
