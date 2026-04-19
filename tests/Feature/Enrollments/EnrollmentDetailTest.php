<?php

namespace Tests\Feature\Enrollments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\EnrollmentActivity;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Models\Course;
use Tests\TestCase;

class EnrollmentDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $instructor;

    private Course $course;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        
        $this->student = User::factory()->create();
        $this->student->assignRole('Student');

        $this->instructor = User::factory()->create();
        $this->instructor->assignRole('Instructor');

        
        $this->course = Course::factory()->create([
            'status' => 'published',
        ]);

        
        $this->enrollment = Enrollment::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);
    }

    
    public function it_can_get_enrollment_detail_with_all_statistics()
    {
        
        $this->enrollment->unitProgress()->create([
            'unit_id' => 1,
            'status' => 'completed',
            'progress_percent' => 100,
        ]);

        $this->enrollment->unitProgress()->create([
            'unit_id' => 2,
            'status' => 'in_progress',
            'progress_percent' => 50,
        ]);

        
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);
        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'enrollment_id' => $this->enrollment->id,
            'status' => 'submitted',
        ]);

        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'enrollment_id' => $this->enrollment->id,
            'status' => 'graded',
            'score' => 85,
        ]);

        
        $quiz = Quiz::factory()->create(['course_id' => $this->course->id]);
        QuizSubmission::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'enrollment_id' => $this->enrollment->id,
            'status' => 'graded',
            'score' => 85,
        ]);

        $response = $this->actingAs($this->student, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status',
                    'progress',
                    'user' => ['id', 'name', 'email', 'username', 'avatar_url'],
                    'course' => ['id', 'title', 'slug', 'code'],
                    'completed_units' => ['completed', 'total', 'text'],
                    'assignments' => ['submitted', 'graded', 'text'],
                    'quizzes' => ['passed', 'average_score', 'text'],
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->enrollment->id,
                    'completed_units' => [
                        'completed' => 1,
                        'total' => 2,
                        'text' => '1 of 2',
                    ],
                    'assignments' => [
                        'submitted' => 2,
                        'graded' => 1,
                    ],
                    'quizzes' => [
                        'passed' => 1,
                        'average_score' => 85,
                    ],
                ],
            ]);
    }

    
    public function it_can_get_enrollment_activities_with_pagination()
    {
        
        EnrollmentActivity::factory()->count(25)->create([
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
        ]);

        $response = $this->actingAs($this->student, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}/activities?per_page=10");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'date',
                        'time',
                        'datetime',
                        'description',
                        'event_type',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ])
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonCount(10, 'data');
    }

    
    public function it_can_sort_activities_by_occurred_at()
    {
        
        $activity1 = EnrollmentActivity::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'occurred_at' => now()->subDays(3),
        ]);

        $activity2 = EnrollmentActivity::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'occurred_at' => now()->subDays(1),
        ]);

        
        $response = $this->actingAs($this->student, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}/activities?sort=-occurred_at");

        $response->assertOk()
            ->assertJsonPath('data.0.id', $activity2->id)
            ->assertJsonPath('data.1.id', $activity1->id);

        
        $response = $this->actingAs($this->student, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}/activities?sort=occurred_at");

        $response->assertOk()
            ->assertJsonPath('data.0.id', $activity1->id)
            ->assertJsonPath('data.1.id', $activity2->id);
    }

    
    public function it_can_filter_activities_by_event_type()
    {
        
        EnrollmentActivity::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'event_type' => 'lesson_completed',
        ]);

        EnrollmentActivity::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'event_type' => 'quiz_completed',
        ]);

        EnrollmentActivity::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'event_type' => 'lesson_completed',
        ]);

        $response = $this->actingAs($this->student, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}/activities?filter[event_type]=lesson_completed");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.event_type', 'lesson_completed')
            ->assertJsonPath('data.1.event_type', 'lesson_completed');
    }

    
    public function it_prevents_unauthorized_access_to_enrollment_detail()
    {
        $otherStudent = User::factory()->create();
        $otherStudent->assignRole('Student');

        $response = $this->actingAs($otherStudent, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}");

        $response->assertForbidden();
    }

    
    public function it_prevents_unauthorized_access_to_enrollment_activities()
    {
        $otherStudent = User::factory()->create();
        $otherStudent->assignRole('Student');

        $response = $this->actingAs($otherStudent, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}/activities");

        $response->assertForbidden();
    }

    
    public function instructor_can_view_student_enrollment_detail()
    {
        
        $this->course->update(['instructor_id' => $this->instructor->id]);

        $response = $this->actingAs($this->instructor, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}");

        $response->assertOk();
    }

    
    public function it_limits_activities_per_page_to_maximum_100()
    {
        EnrollmentActivity::factory()->count(150)->create([
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
        ]);

        $response = $this->actingAs($this->student, 'api')
            ->getJson("/api/v1/enrollments/{$this->enrollment->id}/activities?per_page=200");

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonCount(100, 'data');
    }
}

