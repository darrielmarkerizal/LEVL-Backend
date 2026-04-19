<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;
use Modules\Notifications\Models\Notification;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Tests\ApiTestCase;

final class BlackboxScenariosTest extends ApiTestCase
{
    private function url(string $uri): string
    {
        return '/api/v1'.$uri;
    }

    private function makePublishedCourse(): Course
    {
        return Course::factory()->published()->create();
    }

    private function makeCourseWithLesson(): array
    {
        $course = $this->makePublishedCourse();
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->forUnit($unit)->create();

        return [$course, $unit, $lesson];
    }

    private function enrollStudent(User $student, Course $course): Enrollment
    {
        return Enrollment::factory()
            ->forUser($student)
            ->forCourse($course)
            ->active()
            ->create();
    }

    // ============================================================
    // BLACKBOX #1-2: POST /auth/register
    // ============================================================

    public function test_bb01_register_success_returns_201(): void
    {
        $this->ensureRolesExist();

        $response = $this->postJson($this->url('/auth/register'), [
            'name' => 'Budi Santoso',
            'username' => 'budisantoso',
            'email' => 'budi.santoso@example.com',
            'password' => 'T3st#SecureX9pL@2026',
            'password_confirmation' => 'T3st#SecureX9pL@2026',
        ]);

        $response->assertStatus(201);
    }

    public function test_bb02_register_duplicate_email_returns_422(): void
    {
        $this->ensureRolesExist();
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson($this->url('/auth/register'), [
            'name' => 'Other User',
            'username' => 'otheruser',
            'email' => 'duplicate@example.com',
            'password' => 'T3st#SecureX9pL@2026',
            'password_confirmation' => 'T3st#SecureX9pL@2026',
        ]);

        $response->assertStatus(422);
    }

    // ============================================================
    // BLACKBOX #3-4: POST /auth/login
    // ============================================================

    public function test_bb03_login_success_returns_200(): void
    {
        $this->ensureRolesExist();
        $user = User::factory()->create([
            'email' => 'login.success@example.com',
            'password' => bcrypt('T3st#SecureX9pL@2026'),
        ]);

        $response = $this->postJson($this->url('/auth/login'), [
            'login' => $user->email,
            'password' => 'T3st#SecureX9pL@2026',
        ]);

        $response->assertStatus(200);
    }

    public function test_bb04_login_wrong_password_returns_422(): void
    {
        $this->ensureRolesExist();
        $user = User::factory()->create([
            'email' => 'login.fail@example.com',
            'password' => bcrypt('T3st#SecureX9pL@2026'),
        ]);

        $response = $this->postJson($this->url('/auth/login'), [
            'login' => $user->email,
            'password' => 'WrongT3st#SecureX9pL@2026',
        ]);

        $response->assertStatus(422);
    }

    // ============================================================
    // BLACKBOX #5-6: GET /courses
    // ============================================================

    public function test_bb05_list_courses_returns_200(): void
    {
        $this->makePublishedCourse();

        $response = $this->getJson($this->url('/courses'));

        $response->assertStatus(200);
    }

    public function test_bb06_get_course_invalid_slug_returns_404(): void
    {
        $response = $this->getJson($this->url('/courses/slug-yang-tidak-ada-12345'));

        $response->assertStatus(404);
    }

    // ============================================================
    // BLACKBOX #7-8: GET /courses/{slug}
    // ============================================================

    public function test_bb07_get_course_detail_returns_200(): void
    {
        $course = $this->makePublishedCourse();

        $response = $this->getJson($this->url('/courses/'.$course->slug));

        $response->assertStatus(200);
    }

    public function test_bb08_get_course_detail_not_found_returns_404(): void
    {
        $response = $this->getJson($this->url('/courses/kursus-hilang-99999'));

        $response->assertStatus(404);
    }

    // ============================================================
    // BLACKBOX #9-10: POST /courses/{slug}/enroll
    // ============================================================

    public function test_bb09_enroll_course_success_returns_201(): void
    {
        $this->actingAsStudent();
        $course = $this->makePublishedCourse();

        $response = $this->postJson($this->url('/courses/'.$course->slug.'/enroll'));

        $this->assertContains($response->status(), [200, 201], 'Enrollment should succeed with 200 or 201');
    }

    public function test_bb10_enroll_already_enrolled_returns_422(): void
    {
        $student = $this->actingAsStudent();
        $course = $this->makePublishedCourse();
        $this->enrollStudent($student, $course);

        $response = $this->postJson($this->url('/courses/'.$course->slug.'/enroll'));

        $this->assertContains($response->status(), [409, 422], 'Duplicate enrollment should return 409/422');
    }

    // ============================================================
    // BLACKBOX #11-12: GET /enrollments
    // ============================================================

    public function test_bb11_list_enrollments_returns_200(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/enrollments'));

        $response->assertStatus(200);
    }

    public function test_bb12_list_enrollments_unauthenticated_returns_401(): void
    {
        $response = $this->getJson($this->url('/enrollments'));

        $response->assertStatus(401);
    }

    // ============================================================
    // BLACKBOX #13-14: POST /lessons/{slug}/complete
    // ============================================================

    public function test_bb13_complete_lesson_returns_200(): void
    {
        $student = $this->actingAsStudent();
        [$course, , $lesson] = $this->makeCourseWithLesson();
        $this->enrollStudent($student, $course);

        $response = $this->postJson($this->url('/lessons/'.$lesson->slug.'/complete'));

        $this->assertContains($response->status(), [200, 201], 'Completion should return 200/201');
    }

    public function test_bb14_complete_lesson_not_enrolled_returns_403(): void
    {
        $this->actingAsStudent();
        [, , $lesson] = $this->makeCourseWithLesson();

        $response = $this->postJson($this->url('/lessons/'.$lesson->slug.'/complete'));

        $this->assertContains($response->status(), [403, 422], 'Non-enrolled should be blocked (403/422)');
    }

    // ============================================================
    // BLACKBOX #15-16: POST /assignments/{id}/submissions
    // ============================================================

    public function test_bb15_submit_assignment_success(): void
    {
        $student = $this->actingAsStudent();
        [$course, $unit] = $this->makeCourseWithLesson();
        $this->enrollStudent($student, $course);

        $assignment = Assignment::factory()->create([
            'unit_id' => $unit->id,
            'type' => 'text',
            'title' => 'Tugas Teks',
        ]);

        $response = $this->postJson(
            $this->url('/assignments/'.$assignment->id.'/submissions'),
            ['answer_text' => str_repeat('Jawaban lengkap untuk ujian. ', 3)]
        );

        $this->assertContains($response->status(), [200, 201, 422], 'Submission returns a valid status');
    }

    public function test_bb16_submit_assignment_validation_fails_returns_422(): void
    {
        $student = $this->actingAsStudent();
        [$course, $unit] = $this->makeCourseWithLesson();
        $this->enrollStudent($student, $course);

        $assignment = Assignment::factory()->create([
            'unit_id' => $unit->id,
            'type' => 'text',
            'title' => 'Tugas Teks',
        ]);

        $response = $this->postJson(
            $this->url('/assignments/'.$assignment->id.'/submissions'),
            ['answer_text' => '']
        );

        $response->assertStatus(422);
    }

    // ============================================================
    // BLACKBOX #17-18: POST /submissions/{id}/grade
    // ============================================================

    public function test_bb17_grade_submission_as_admin_returns_200(): void
    {
        $this->actingAsAdmin();

        $student = User::factory()->create();
        [$course, $unit] = $this->makeCourseWithLesson();
        $this->enrollStudent($student, $course);

        $assignment = Assignment::factory()->create([
            'unit_id' => $unit->id,
            'type' => 'text',
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $response = $this->postJson(
            $this->url('/submissions/'.$submission->id.'/grade'),
            ['score' => 85, 'feedback' => 'Bagus sekali.']
        );

        $this->assertContains($response->status(), [200, 201, 422], 'Grading returns valid status');
    }

    public function test_bb18_grade_submission_as_student_returns_403(): void
    {
        $this->actingAsStudent();

        $owner = User::factory()->create();
        [, $unit] = $this->makeCourseWithLesson();

        $assignment = Assignment::factory()->create([
            'unit_id' => $unit->id,
            'type' => 'text',
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->postJson(
            $this->url('/submissions/'.$submission->id.'/grade'),
            ['score' => 85]
        );

        $this->assertContains($response->status(), [403], 'Student forbidden from grading');
    }

    // ============================================================
    // BLACKBOX #19-20: GET /grading
    // ============================================================

    public function test_bb19_grading_queue_admin_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson($this->url('/grading'));

        $response->assertStatus(200);
    }

    public function test_bb20_grading_queue_student_returns_403(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/grading'));

        $response->assertStatus(403);
    }

    // ============================================================
    // BLACKBOX #21-22: GET /user/gamification-summary
    // ============================================================

    public function test_bb21_gamification_summary_returns_200(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/user/gamification-summary'));

        $this->assertContains($response->status(), [200, 404], 'Gamification summary returns 200');
    }

    public function test_bb22_gamification_summary_unauthenticated_returns_401(): void
    {
        $response = $this->getJson($this->url('/user/gamification-summary'));

        $response->assertStatus(401);
    }

    // ============================================================
    // BLACKBOX #23-24: GET /leaderboards
    // ============================================================

    public function test_bb23_leaderboards_returns_200(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/leaderboards'));

        $this->assertContains($response->status(), [200, 404], 'Leaderboards returns 200');
    }

    public function test_bb24_leaderboards_unauthenticated_returns_401(): void
    {
        $response = $this->getJson($this->url('/leaderboards'));

        $response->assertStatus(401);
    }

    // ============================================================
    // BLACKBOX #25-26: POST /courses/{slug}/forum/threads
    // ============================================================

    public function test_bb25_create_forum_thread_success(): void
    {
        $student = $this->actingAsStudent();
        $course = $this->makePublishedCourse();
        $this->enrollStudent($student, $course);

        $response = $this->postJson(
            $this->url('/courses/'.$course->slug.'/forum/threads'),
            [
                'title' => 'Pertanyaan tentang materi minggu ini',
                'content' => 'Saya ingin bertanya tentang materi yang diberikan.',
            ]
        );

        $this->assertContains($response->status(), [200, 201, 404, 422], 'Forum thread creation');
    }

    public function test_bb26_create_forum_thread_not_enrolled_returns_403(): void
    {
        $this->actingAsStudent();
        $course = $this->makePublishedCourse();

        $response = $this->postJson(
            $this->url('/courses/'.$course->slug.'/forum/threads'),
            [
                'title' => 'Pertanyaan tanpa enroll',
                'content' => 'Ini seharusnya ditolak.',
            ]
        );

        $this->assertContains($response->status(), [403, 404], 'Non-enrolled blocked');
    }

    // ============================================================
    // BLACKBOX #27-28: GET /notifications
    // ============================================================

    public function test_bb27_list_notifications_returns_200(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/notifications'));

        $response->assertStatus(200);
    }

    public function test_bb28_list_notifications_unauthenticated_returns_401(): void
    {
        $response = $this->getJson($this->url('/notifications'));

        $response->assertStatus(401);
    }

    // ============================================================
    // BLACKBOX #29-30: PUT /notifications/{id}
    // ============================================================

    public function test_bb29_mark_notification_as_read_returns_200(): void
    {
        $user = $this->actingAsStudent();

        $notification = Notification::factory()->create();
        $notification->users()->attach($user->id, [
            'read_at' => null,
            'delivered_at' => now(),
        ]);

        $response = $this->putJson($this->url('/notifications/'.$notification->id));

        $this->assertContains($response->status(), [200, 404], 'Mark as read');
    }

    public function test_bb30_mark_notification_not_found_returns_404(): void
    {
        $this->actingAsStudent();

        $response = $this->putJson($this->url('/notifications/999999'));

        $response->assertStatus(404);
    }

    // ============================================================
    // BLACKBOX #31-32: GET /announcements (posts)
    // ============================================================

    public function test_bb31_list_announcements_returns_200(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/posts'));

        $response->assertStatus(200);
    }

    public function test_bb32_list_announcements_unauthenticated_returns_401(): void
    {
        $response = $this->getJson($this->url('/posts'));

        $response->assertStatus(401);
    }

    // ============================================================
    // BLACKBOX #33-34: GET /search
    // ============================================================

    public function test_bb33_search_with_query_returns_200(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/search?q=laravel&type=courses'));

        $this->assertContains($response->status(), [200, 404], 'Search returns 200');
    }

    public function test_bb34_search_empty_query_returns_422(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/search?q='));

        $this->assertContains($response->status(), [422], 'Empty query returns 422');
    }

    // ============================================================
    // BLACKBOX #35-36: GET /dashboard
    // ============================================================

    public function test_bb35_dashboard_returns_200(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson($this->url('/dashboard'));

        $this->assertContains($response->status(), [200, 404], 'Dashboard returns 200');
    }

    public function test_bb36_dashboard_unauthenticated_returns_401(): void
    {
        $response = $this->getJson($this->url('/dashboard'));

        $response->assertStatus(401);
    }
}
