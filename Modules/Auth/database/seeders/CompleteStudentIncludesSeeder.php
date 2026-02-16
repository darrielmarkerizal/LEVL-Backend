<?php

declare(strict_types=1);

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\Auth\Models\ProfilePrivacySetting;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Modules\Enrollments\Models\Enrollment;
use Modules\Forums\Models\Thread;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\Challenge;
use Modules\Gamification\Models\LearningStreak;
use Modules\Gamification\Models\Level;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserChallengeAssignment;
use Modules\Gamification\Models\UserChallengeCompletion;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Learning\Enums\OverrideType;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Override;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;

/**
 * Creates one deterministic "complete" Student user with non-empty data
 * for all includes used by /users/:userId?include=...
 *
 * Idempotent: safe to run multiple times (no duplicates / no refresh needed).
 *
 * Usage:
 *   php artisan db:seed --class="Modules\Auth\Database\Seeders\CompleteStudentIncludesSeeder"
 */
class CompleteStudentIncludesSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist (without touching permission cache store like Redis)
        Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'api']);

        // Deterministic identifiers so we can update-or-create safely.
        $studentEmail = 'student.full.includes@test.com';
        $studentUsername = 'student_full_includes';

        $adminEmail = 'admin.full.includes@test.com';
        $adminUsername = 'admin_full_includes';

        $instructorEmail = 'instructor.full.includes@test.com';
        $instructorUsername = 'instructor_full_includes';

        $otherStudentEmail = 'student.granted.overrides@test.com';
        $otherStudentUsername = 'student_granted_overrides';

        $student = User::updateOrCreate(
            ['email' => $studentEmail],
            $this->onlyExistingColumns('users', [
                'name' => 'Student Full Includes',
                'username' => $studentUsername,
                'password' => Hash::make('password'),
                'status' => 'active',
                'account_status' => 'active',
                'email_verified_at' => now(),
                'is_password_set' => true,
            ]),
        );
        $student->syncRoles(['Student']);

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            $this->onlyExistingColumns('users', [
                'name' => 'Admin Full Includes',
                'username' => $adminUsername,
                'password' => Hash::make('password'),
                'status' => 'active',
                'account_status' => 'active',
                'email_verified_at' => now(),
                'is_password_set' => true,
            ]),
        );
        $admin->syncRoles(['Admin']);

        $instructor = User::updateOrCreate(
            ['email' => $instructorEmail],
            $this->onlyExistingColumns('users', [
                'name' => 'Instructor Full Includes',
                'username' => $instructorUsername,
                'password' => Hash::make('password'),
                'status' => 'active',
                'account_status' => 'active',
                'email_verified_at' => now(),
                'is_password_set' => true,
            ]),
        );
        $instructor->syncRoles(['Instructor']);

        $otherStudent = User::updateOrCreate(
            ['email' => $otherStudentEmail],
            $this->onlyExistingColumns('users', [
                'name' => 'Student Granted Overrides',
                'username' => $otherStudentUsername,
                'password' => Hash::make('password'),
                'status' => 'active',
                'account_status' => 'active',
                'email_verified_at' => now(),
                'is_password_set' => true,
            ]),
        );
        $otherStudent->syncRoles(['Student']);

        // Privacy settings
        ProfilePrivacySetting::updateOrCreate(
            ['user_id' => $student->id],
            $this->onlyExistingColumns('profile_privacy_settings', [
                'profile_visibility' => ProfilePrivacySetting::VISIBILITY_PRIVATE,
                'show_email' => false,
                'show_phone' => true,
                'show_activity_history' => true,
                'show_achievements' => true,
                'show_statistics' => true,
            ]),
        );

        // Course + Unit + Lesson (deterministic to avoid duplicates)
        $course = Course::updateOrCreate(
            ['slug' => 'seed-complete-student-course'],
            $this->onlyExistingColumns('courses', [
                'code' => 'SEEDC001',
                'title' => 'Seed: Complete Student Course',
                'short_desc' => 'Deterministic course for complete student includes seeder.',
                'type' => 'okupasi',
                'level_tag' => 'dasar',
                'tags_json' => json_encode([]),
                'prereq_text' => 'None',
                'progression_mode' => 'sequential',
                'enrollment_type' => 'auto_accept',
                'status' => 'published',
                'published_at' => now(),
                'instructor_id' => $instructor->id,
            ]),
        );

        $unit = Unit::updateOrCreate(
            ['slug' => 'seed-complete-student-unit'],
            $this->onlyExistingColumns('units', [
                'course_id' => $course->id,
                'code' => 'SEEDU01',
                'title' => 'Seed Unit',
                'description' => 'Unit for deterministic seeded course.',
                'order' => 1,
            ]),
        );

        $lesson = Lesson::updateOrCreate(
            ['slug' => 'seed-complete-student-lesson'],
            $this->onlyExistingColumns('lessons', [
                'unit_id' => $unit->id,
                'title' => 'Seed Lesson',
                'description' => 'Lesson for deterministic seeded unit.',
                'markdown_content' => 'Seeded lesson content.',
                'content_type' => 'markdown',
                'content_url' => null,
                'order' => 1,
            ]),
        );

        // Enrollment (unique user_id + course_id)
        $enrollment = Enrollment::firstOrCreate(
            [
                'user_id' => $student->id,
                'course_id' => $course->id,
            ],
            $this->onlyExistingColumns('enrollments', [
                'status' => 'active',
                'enrolled_at' => now()->subDays(7),
                'completed_at' => null,
            ]),
        );

        // managedCourses (pivot)
        $student->managedCourses()->syncWithoutDetaching([$course->id]);

        // Gamification: stats
        UserGamificationStat::updateOrCreate(
            ['user_id' => $student->id],
            $this->onlyExistingColumns('user_gamification_stats', [
                'total_xp' => 1200,
                'global_level' => 3,
                'current_streak' => 5,
                'longest_streak' => 7,
                'last_activity_date' => now()->subDay(),
                'stats_updated_at' => now(),
            ]),
        );

        // Gamification: badge + user_badge
        $badge = Badge::firstOrCreate(
            ['code' => 'BADGE-SEED-FULL-001'],
            $this->onlyExistingColumns('badges', [
                'name' => 'Seed Badge',
                'description' => 'Badge created by CompleteStudentIncludesSeeder.',
                'type' => 'achievement',
                'threshold' => 1,
            ]),
        );
        UserBadge::firstOrCreate(
            ['user_id' => $student->id, 'badge_id' => $badge->id],
            $this->onlyExistingColumns('user_badges', ['earned_at' => now()->subDays(2)]),
        );

        // Gamification: challenge + assignment + completion
        $challenge = Challenge::firstOrCreate(
            ['title' => 'Seed Challenge: Complete Student'],
            $this->onlyExistingColumns('challenges', [
                'description' => 'Challenge created by CompleteStudentIncludesSeeder.',
                'type' => 'daily',
                'criteria' => ['type' => 'lessons_completed', 'target' => 1],
                'target_count' => 1,
                'points_reward' => 50,
                'badge_id' => $badge->id,
                'start_at' => now()->subDays(1),
                'end_at' => now()->addDays(7),
            ]),
        );

        UserChallengeAssignment::firstOrCreate(
            ['user_id' => $student->id, 'challenge_id' => $challenge->id],
            $this->onlyExistingColumns('user_challenge_assignments', [
                'assigned_date' => now()->toDateString(),
                'status' => 'in_progress',
                'current_progress' => 1,
                'completed_at' => null,
                'reward_claimed' => false,
                'expires_at' => null,
            ]),
        );

        UserChallengeCompletion::firstOrCreate(
            [
                'user_id' => $student->id,
                'challenge_id' => $challenge->id,
                'completed_date' => now()->toDateString(),
            ],
            $this->onlyExistingColumns('user_challenge_completions', [
                'xp_earned' => 100,
                'completion_data' => ['seeded' => true],
            ]),
        );

        // Gamification: points
        Point::firstOrCreate(
            [
                'user_id' => $student->id,
                'source_type' => 'lesson',
                'source_id' => 900001,
            ],
            $this->onlyExistingColumns('points', [
                'points' => 75,
                'reason' => 'completion',
                'description' => 'Seeded points for complete includes.',
            ]),
        );

        // Gamification: levels (unique user_id + course_id)
        Level::updateOrCreate(
            ['user_id' => $student->id, 'course_id' => null],
            $this->onlyExistingColumns('levels', ['current_level' => 3]),
        );
        Level::updateOrCreate(
            ['user_id' => $student->id, 'course_id' => $course->id],
            $this->onlyExistingColumns('levels', ['current_level' => 2]),
        );

        // Gamification: learning streaks (unique user_id + activity_date)
        LearningStreak::updateOrCreate(
            ['user_id' => $student->id, 'activity_date' => now()->toDateString()],
            $this->onlyExistingColumns('learning_streaks', ['xp_earned' => 120]),
        );
        LearningStreak::updateOrCreate(
            ['user_id' => $student->id, 'activity_date' => now()->subDay()->toDateString()],
            $this->onlyExistingColumns('learning_streaks', ['xp_earned' => 80]),
        );

        // Learning: assignment (for submission) + submission
        $assignmentForSubmission = Assignment::updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'created_by' => $instructor->id,
                'title' => 'Seed Assignment: Complete Student',
            ],
            $this->onlyExistingColumns('assignments', [
                'description' => 'Assignment created by CompleteStudentIncludesSeeder.',
                'submission_type' => 'text',
                'max_score' => 100,
                'available_from' => now()->subDays(3),
                'deadline_at' => now()->addDays(7),
                'status' => 'published',
            ]),
        );

        Submission::updateOrCreate(
            [
                'assignment_id' => $assignmentForSubmission->id,
                'user_id' => $student->id,
            ],
            $this->onlyExistingColumns('submissions', [
                'enrollment_id' => $enrollment->id,
                'answer_text' => 'This is a seeded submission answer.',
                'status' => 'submitted',
                'score' => null,
                'feedback' => null,
                'submitted_at' => now()->subDay(),
                'graded_at' => null,
            ]),
        );

        // Learning: assignments created by student (to make `assignments` include non-empty)
        Assignment::updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'created_by' => $student->id,
                'title' => 'Seed Assignment: Student Created',
            ],
            $this->onlyExistingColumns('assignments', [
                'description' => 'Assignment created by the seeded student (for include coverage).',
                'submission_type' => 'text',
                'max_score' => 50,
                'available_from' => now()->subDays(1),
                'deadline_at' => now()->addDays(3),
                'status' => 'published',
            ]),
        );

        // Learning: overrides (received + granted)
        Override::firstOrCreate(
            [
                'assignment_id' => $assignmentForSubmission->id,
                'student_id' => $student->id,
                'grantor_id' => $admin->id,
                'type' => OverrideType::Deadline,
            ],
            $this->onlyExistingColumns('overrides', [
                'reason' => 'Seeded deadline extension override.',
                'value' => ['extended_deadline' => now()->addDays(14)->toISOString()],
                'granted_at' => now()->subHours(2),
                'expires_at' => now()->addDays(30),
            ]),
        );

        Override::firstOrCreate(
            [
                'assignment_id' => $assignmentForSubmission->id,
                'student_id' => $otherStudent->id,
                'grantor_id' => $student->id,
                'type' => OverrideType::Attempts,
            ],
            $this->onlyExistingColumns('overrides', [
                'reason' => 'Seeded additional attempts override (grantor=seeded student).',
                'value' => ['additional_attempts' => 2],
                'granted_at' => now()->subHours(1),
                'expires_at' => now()->addDays(30),
            ]),
        );

        // Forums: thread authored by student
        Thread::firstOrCreate(
            [
                'course_id' => $course->id,
                'author_id' => $student->id,
                'title' => '[Seed] Complete Student Thread',
            ],
            $this->onlyExistingColumns('threads', [
                'content' => 'Thread created by CompleteStudentIncludesSeeder.',
                'is_pinned' => false,
                'is_closed' => false,
                'is_resolved' => false,
                'views_count' => 10,
                'replies_count' => 0,
                'last_activity_at' => now(),
            ]),
        );

        $this->command?->info('✅ CompleteStudentIncludesSeeder done.');
        $this->command?->info("   Student email: {$studentEmail}");
        $this->command?->info("   Student id: {$student->id}");
        $this->command?->info('   Try: GET /api/v1/users/'.$student->id.'?include=roles,privacySettings,enrollments,managedCourses,gamificationStats,badges,challenges,challengeCompletions,points,levels,learningStreaks,submissions,assignments,receivedOverrides,grantedOverrides,threads');
    }

    /**
     * Filter attributes so we only set columns that actually exist
     * in the current database schema. This prevents "undefined column"
     * errors when schema has evolved (columns added/removed/renamed)
     * but seeders are ahead.
     */
    private function onlyExistingColumns(string $table, array $attributes): array
    {
        static $columnsCache = [];

        if (! isset($columnsCache[$table])) {
            if (! Schema::hasTable($table)) {
                return [];
            }

            $columnsCache[$table] = array_flip(Schema::getColumnListing($table));
        }

        return array_intersect_key($attributes, $columnsCache[$table]);
    }
}

