<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Models\LessonCompletion;

class CheckUserActivity extends Command
{
    protected $signature = 'user:check-activity {userId}';

    protected $description = 'Check user learning activity';

    public function handle()
    {
        $userId = $this->argument('userId');

        $this->info("=== CHECKING USER ID: {$userId} ===\n");

        // 1. Check if user exists
        $user = User::find($userId);
        if (! $user) {
            $this->error("❌ User with ID {$userId} NOT FOUND!");

            return 1;
        }

        $this->info("✅ User found: {$user->name} ({$user->email})");
        $this->info('   Role: '.$user->roles->pluck('name')->implode(', ')."\n");

        // 2. Check enrollments
        $this->line('--- ENROLLMENTS ---');
        $enrollments = Enrollment::where('user_id', $userId)->get();
        $this->info('Total enrollments: '.$enrollments->count());

        if ($enrollments->count() > 0) {
            foreach ($enrollments as $enrollment) {
                $this->line("  - Course ID: {$enrollment->course_id}");
                $this->line("    Course: {$enrollment->course->title}");
                $this->line("    Status: {$enrollment->status->value}");
                $this->line("    Enrolled at: {$enrollment->enrolled_at}");
                $this->line("    Updated at: {$enrollment->updated_at}\n");
            }
        } else {
            $this->warn("  ❌ No enrollments found\n");
        }

        // 3. Check lesson completions
        $this->line('--- LESSON COMPLETIONS ---');
        $lessonCompletions = LessonCompletion::where('user_id', $userId)->get();
        $this->info('Total lesson completions: '.$lessonCompletions->count());

        if ($lessonCompletions->count() > 0) {
            foreach ($lessonCompletions->take(5) as $completion) {
                $lesson = $completion->lesson;
                $unit = $lesson->unit;
                $course = $unit->course;

                $this->line("  - Lesson ID: {$completion->lesson_id}");
                $this->line("    Lesson: {$lesson->title}");
                $this->line("    Unit: {$unit->title}");
                $this->line("    Course ID: {$course->id}");
                $this->line("    Course: {$course->title}");
                $this->line("    Completed at: {$completion->completed_at}");
                $this->line("    Updated at: {$completion->updated_at}\n");
            }
            if ($lessonCompletions->count() > 5) {
                $this->line('  ... and '.($lessonCompletions->count() - 5)." more\n");
            }
        } else {
            $this->warn("  ❌ No lesson completions found\n");
        }

        // 4. Check assignment submissions
        $this->line('--- ASSIGNMENT SUBMISSIONS ---');
        $submissions = Submission::where('user_id', $userId)->get();
        $this->info('Total submissions: '.$submissions->count());

        if ($submissions->count() > 0) {
            foreach ($submissions->take(5) as $submission) {
                $this->line("  - Assignment ID: {$submission->assignment_id}");
                $this->line("    Status: {$submission->status->value}");
                $this->line("    Submitted at: {$submission->submitted_at}\n");
            }
            if ($submissions->count() > 5) {
                $this->line('  ... and '.($submissions->count() - 5)." more\n");
            }
        } else {
            $this->warn("  ❌ No assignment submissions found\n");
        }

        // 5. Check quiz submissions
        $this->line('--- QUIZ SUBMISSIONS ---');
        $quizSubmissions = QuizSubmission::where('user_id', $userId)->get();
        $this->info('Total quiz submissions: '.$quizSubmissions->count());

        if ($quizSubmissions->count() > 0) {
            foreach ($quizSubmissions->take(5) as $quizSub) {
                $this->line("  - Quiz ID: {$quizSub->quiz_id}");
                $this->line("    Status: {$quizSub->status->value}");
                $this->line("    Score: {$quizSub->score}");
                $this->line("    Submitted at: {$quizSub->submitted_at}\n");
            }
            if ($quizSubmissions->count() > 5) {
                $this->line('  ... and '.($quizSubmissions->count() - 5)." more\n");
            }
        } else {
            $this->warn("  ❌ No quiz submissions found\n");
        }

        // 6. Summary
        $this->info('=== SUMMARY ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Enrollments', $enrollments->count()],
                ['Lesson Completions', $lessonCompletions->count()],
                ['Assignment Submissions', $submissions->count()],
                ['Quiz Submissions', $quizSubmissions->count()],
            ]
        );

        if ($enrollments->count() === 0) {
            $this->warn("\n⚠️  User has NO enrollments - cannot access any courses");
        } elseif ($lessonCompletions->count() === 0 && $submissions->count() === 0 && $quizSubmissions->count() === 0) {
            $this->warn("\n⚠️  User has enrollments but NO activity (no lessons completed, no submissions)");
        } else {
            $this->info("\n✅ User has some learning activity");
        }

        return 0;
    }
}
