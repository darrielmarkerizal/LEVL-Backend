<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Notifications\Enums\PostCategory;
use Modules\Notifications\Enums\PostStatus;
use Modules\Notifications\Models\Post;
use Modules\Schemes\Models\Course;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'api']);
    $this->adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'api']);
    $this->instructorRole = Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'api']);
    $this->studentRole = Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'api']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->superadminRole);

    $this->instructor = User::factory()->create();
    $this->instructor->assignRole($this->instructorRole);
});

test('it can access dashboard as admin', function () {
    $response = $this->actingAs($this->admin, 'api')->getJson('/api/v1/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'pending_enrollment',
                'total_users',
                'total_schemes',
                'registration_and_class_queue',
                'learning_content_statistic',
                'global_top_leaderboard',
                'latest_posts',
            ],
        ]);
});

test('it scopes dashboard data for instructor', function () {
    $course1 = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $course2 = Course::factory()->create(); 

    Enrollment::factory()->create(['course_id' => $course1->id, 'status' => EnrollmentStatus::Pending]);
    Enrollment::factory()->create(['course_id' => $course2->id, 'status' => EnrollmentStatus::Pending]);

    $response = $this->actingAs($this->instructor, 'api')->getJson('/api/v1/dashboard');

    $response->assertStatus(200);
    expect($response->json('data.pending_enrollment'))->toBe(1);
    expect($response->json('data.total_schemes'))->toBe(1);
});

test('admin dashboard is not scoped to managed courses', function () {
    $admin = User::factory()->create();
    $admin->assignRole($this->adminRole);

    $otherInstructor = User::factory()->create();
    $otherInstructor->assignRole($this->instructorRole);

    Course::factory()->create(['instructor_id' => $otherInstructor->id, 'status' => 'published']);
    Course::factory()->create(['status' => 'published']);

    Enrollment::factory()->create(['status' => EnrollmentStatus::Pending]);
    Enrollment::factory()->create(['status' => EnrollmentStatus::Pending]);

    $response = $this->actingAs($admin, 'api')->getJson('/api/v1/dashboard');

    $response->assertStatus(200);
    expect($response->json('data.pending_enrollment'))->toBe(2);
    expect($response->json('data.total_schemes'))->toBe(2);
    expect($response->json('data.registration_and_class_queue'))->toHaveCount(2);
    expect($response->json('data.latest_posts'))->toHaveCount(0);
});

test('admin dashboard includes latest posts', function () {
    $admin = User::factory()->create();
    $admin->assignRole($this->adminRole);

    $author = User::factory()->create();

    Post::factory()->count(2)->create([
        'author_id' => $author->id,
        'status' => PostStatus::PUBLISHED,
        'category' => PostCategory::INFORMATION,
    ]);

    $response = $this->actingAs($admin, 'api')->getJson('/api/v1/dashboard');

    $response->assertStatus(200);
    expect($response->json('data.latest_posts'))->toHaveCount(2);
    expect($response->json('data.latest_posts.0.title'))->not->toBeEmpty();
    expect($response->json('data.latest_posts.0.category.value'))->toBe(PostCategory::INFORMATION->value);
});

test('it returns student-specific dashboard data', function () {
    $student = User::factory()->create();
    $student->assignRole($this->studentRole);

    $response = $this->actingAs($student, 'api')->getJson('/api/v1/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'gamification_stats' => [
                    'day_streak',
                    'xp',
                    'level',
                    'current_level_xp',
                    'xp_to_next_level',
                    'progress_percent',
                ],
                'latest_learning_activity',
                'recent_achievements',
                'global_top_leaderboard',
            ],
        ]);
});
