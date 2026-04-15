<?php

declare(strict_types=1);

namespace Modules\Auth\Database\Seeders;

use App\Support\RealisticSeederContent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Enums\QuizGradingStatus;
use Modules\Learning\Enums\QuizSubmissionStatus;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Enums\SubmissionStatus;
use Modules\Schemes\Models\Course;

class UATPersonaSeeder extends Seeder
{
    public function run(): void
    {
        if (config('seeding.mode') !== 'uat') {
            return;
        }

        $course = Course::query()->where('status', 'published')->orderBy('id')->first();
        if ($course === null) {
            $this->command->warn('UAT personas skipped: no published course.');

            return;
        }

        $unitIds = DB::table('units')->where('course_id', $course->id)->pluck('id');
        if ($unitIds->isEmpty()) {
            $this->command->warn('UAT personas skipped: course has no units.');

            return;
        }

        $lessonId = DB::table('lessons')
            ->whereIn('unit_id', $unitIds)
            ->orderBy('id')
            ->value('id');

        $quizId = DB::table('quizzes')->whereIn('unit_id', $unitIds)->orderBy('id')->value('id');
        $assignmentId = DB::table('assignments')->whereIn('unit_id', $unitIds)->orderBy('id')->value('id');

        $this->seedArtanto($course, (int) $lessonId, $quizId ? (int) $quizId : null, $assignmentId ? (int) $assignmentId : null);
        $this->seedSteady($course, $lessonId ? (int) $lessonId : null);
        $this->seedStruggling($course);
    }

    private function seedArtanto(Course $course, ?int $lessonId, ?int $quizId, ?int $assignmentId): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => RealisticSeederContent::demoEmail('uat.artanto')],
            [
                'name' => 'Artanto Tarihoran',
                'username' => 'artanto_t',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (! $user->hasRole('Student')) {
            $user->assignRole('Student');
        }

        $enrolledAt = Carbon::parse('2026-03-01 08:00:00');
        $enrollment = Enrollment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'status' => EnrollmentStatus::Active,
                'enrolled_at' => $enrolledAt,
            ]
        );

        if ($lessonId !== null) {
            $lessonAt = Carbon::parse('2026-03-05 10:00:00');
            DB::table('lesson_progress')->updateOrInsert(
                [
                    'enrollment_id' => $enrollment->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'status' => 'completed',
                    'progress_percent' => 100,
                    'completed_at' => $lessonAt,
                    'updated_at' => $lessonAt,
                    'created_at' => $lessonAt,
                ]
            );
        }

        if ($quizId !== null) {
            $quizAt = Carbon::parse('2026-03-08 09:15:00');
            $passing = (float) (DB::table('quizzes')->where('id', $quizId)->value('passing_grade') ?? 75);
            $score = max($passing, 85);
            $row = DB::table('quiz_submissions')
                ->where('quiz_id', $quizId)
                ->where('user_id', $user->id)
                ->first();
            $payload = [
                'quiz_id' => $quizId,
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'status' => QuizSubmissionStatus::Graded->value,
                'grading_status' => QuizGradingStatus::Graded->value,
                'score' => $score,
                'final_score' => $score,
                'submitted_at' => $quizAt,
                'started_at' => $quizAt->copy()->subMinutes(45),
                'attempt_number' => 1,
                'updated_at' => $quizAt,
            ];
            if ($row) {
                DB::table('quiz_submissions')->where('id', $row->id)->update($payload);
            } else {
                $payload['created_at'] = $quizAt;
                DB::table('quiz_submissions')->insert($payload);
            }
        }

        if ($assignmentId !== null) {
            $subAt = Carbon::parse('2026-03-10 14:30:00');
            $existing = DB::table('submissions')
                ->where('assignment_id', $assignmentId)
                ->where('user_id', $user->id)
                ->first();
            $payload = [
                'assignment_id' => $assignmentId,
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'status' => SubmissionStatus::Submitted->value,
                'state' => SubmissionState::Submitted->value,
                'score' => null,
                'submitted_at' => $subAt,
                'attempt_number' => 1,
                'updated_at' => $subAt,
            ];
            if ($existing) {
                DB::table('submissions')->where('id', $existing->id)->update($payload);
            } else {
                $payload['created_at'] = $subAt;
                DB::table('submissions')->insert($payload);
            }
        }

        $this->command->info('UAT persona: '.RealisticSeederContent::demoEmail('uat.artanto').' / password (Artanto Tarihoran).');
    }

    private function seedSteady(Course $course, ?int $lessonId): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => RealisticSeederContent::demoEmail('uat.steady')],
            [
                'name' => 'Dewi Kartika',
                'username' => 'uat_steady',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (! $user->hasRole('Student')) {
            $user->assignRole('Student');
        }

        $enrollment = Enrollment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'status' => EnrollmentStatus::Active,
                'enrolled_at' => now()->subDays(14),
            ]
        );

        if ($lessonId !== null) {
            DB::table('lesson_progress')->updateOrInsert(
                [
                    'enrollment_id' => $enrollment->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'status' => 'completed',
                    'progress_percent' => 100,
                    'completed_at' => now()->subDays(7),
                    'updated_at' => now(),
                    'created_at' => now()->subDays(7),
                ]
            );
        }
    }

    private function seedStruggling(Course $course): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => RealisticSeederContent::demoEmail('uat.struggling')],
            [
                'name' => 'Rendra Mahendra',
                'username' => 'uat_struggling',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (! $user->hasRole('Student')) {
            $user->assignRole('Student');
        }

        Enrollment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'status' => EnrollmentStatus::Active,
                'enrolled_at' => now()->subDays(3),
            ]
        );
    }
}
