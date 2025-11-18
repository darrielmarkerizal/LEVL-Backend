<?php

use Modules\Auth\Models\User;
use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Models\Attempt;
use Modules\Schemes\Models\Course;
use Modules\Enrollments\Models\Enrollment;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    createTestRoles();

    $this->instructor = User::factory()->create();
    $this->instructor->assignRole('Instructor');

    $this->student = User::factory()->create();
    $this->student->assignRole('Student');

    $this->course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    Enrollment::create([
        'user_id' => $this->student->id,
        'course_id' => $this->course->id,
        'status' => 'active',
    ]);

    $this->exercise = Exercise::factory()->create([
        'created_by' => $this->instructor->id,
        'scope_type' => 'course',
        'scope_id' => $this->course->id,
        'status' => 'published',
        'max_score' => 100,
    ]);
});

describe('Grading', function () {
    it('instructor can get exercise attempts', function () {
        $response = $this->actingAs($this->instructor, 'api')
            ->getJson(api("/assessments/exercises/{$this->exercise->id}/attempts"));

        $response->assertStatus(200);
    });

    it('instructor can get attempt answers', function () {
        $attempt = Attempt::create([
            'exercise_id' => $this->exercise->id,
            'user_id' => $this->student->id,
            'enrollment_id' => $this->student->enrollments()->first()->id,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->instructor, 'api')
            ->getJson(api("/assessments/attempts/{$attempt->id}/answers"));

        $response->assertStatus(200);
    });

    it('student cannot view others answers', function () {
        $otherStudent = User::factory()->create();
        $otherStudent->assignRole('Student');
        Enrollment::create([
            'user_id' => $otherStudent->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);

        $attempt = Attempt::create([
            'exercise_id' => $this->exercise->id,
            'user_id' => $this->student->id,
            'enrollment_id' => $this->student->enrollments()->first()->id,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($otherStudent, 'api')
            ->getJson(api("/assessments/attempts/{$attempt->id}/answers"));

        $response->assertStatus(403);
    });

    it('instructor can add feedback to answer', function () {
        $attempt = Attempt::create([
            'exercise_id' => $this->exercise->id,
            'user_id' => $this->student->id,
            'enrollment_id' => $this->student->enrollments()->first()->id,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $question = $this->exercise->questions()->create([
            'question_text' => 'Q?',
            'type' => 'free_text',
            'score_weight' => 10,
        ]);

        $answer = $attempt->answers()->create([
            'question_id' => $question->id,
            'answer_text' => 'Student answer',
        ]);

        $response = $this->actingAs($this->instructor, 'api')
            ->putJson(api("/assessments/answers/{$answer->id}/feedback"), [
                'feedback' => 'Good work!',
                'score_awarded' => 8,
            ]);

        $response->assertStatus(200);
    });
});
