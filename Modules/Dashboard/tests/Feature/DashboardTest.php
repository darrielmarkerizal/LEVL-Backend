<?php

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'api']);
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
                'global_top_leaderboard'
            ]
        ]);
});

test('it scopes dashboard data for instructor', function () {
    $course1 = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $course2 = Course::factory()->create(); // Another instructor
    
    Enrollment::factory()->create(['course_id' => $course1->id, 'status' => EnrollmentStatus::Pending]);
    Enrollment::factory()->create(['course_id' => $course2->id, 'status' => EnrollmentStatus::Pending]);
    
    $response = $this->actingAs($this->instructor, 'api')->getJson('/api/v1/dashboard');
    
    $response->assertStatus(200);
    expect($response->json('data.pending_enrollment'))->toBe(1);
    expect($response->json('data.total_schemes'))->toBe(1);
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
                    'progress_percent'
                ],
                'latest_learning_activity',
                'recent_achievements',
                'global_top_leaderboard'
            ]
        ]);
});
