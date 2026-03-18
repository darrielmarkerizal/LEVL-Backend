<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizSubmission;
use Modules\Schemes\Services\PrerequisiteService;

class CheckUnlockedQuizzes extends Command
{
    protected $signature = 'quiz:check-unlocked';

    protected $description = 'Find students with unlocked quizzes that haven\'t been attempted';

    public function handle()
    {
        $this->info('=== Finding Students with Unlocked Quizzes ===');
        $this->newLine();

        // Get all active students
        $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'Student');
        })
            ->where('status', 'active')
            ->get();

        $this->info("Checking {$students->count()} students...");
        $this->newLine();

        $prerequisiteService = app(PrerequisiteService::class);
        $results = [];

        $progressBar = $this->output->createProgressBar($students->count());
        $progressBar->start();

        foreach ($students as $student) {
            // Get student's enrollments
            $enrollments = Enrollment::where('user_id', $student->id)
                ->where('status', 'active')
                ->with('course')
                ->get();

            foreach ($enrollments as $enrollment) {
                $course = $enrollment->course;

                // Get all published quizzes in this course
                $quizzes = Quiz::whereHas('unit', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })
                    ->where('status', 'published')
                    ->with(['unit'])
                    ->get();

                foreach ($quizzes as $quiz) {
                    // Check if student has attempted this quiz
                    $hasAttempt = QuizSubmission::where('quiz_id', $quiz->id)
                        ->where('user_id', $student->id)
                        ->exists();

                    if (! $hasAttempt) {
                        // Check if quiz is unlocked
                        $accessCheck = $prerequisiteService->checkQuizAccess($quiz, $student->id);

                        if ($accessCheck['accessible']) {
                            $results[] = [
                                'student_id' => $student->id,
                                'student_name' => $student->name,
                                'student_email' => $student->email,
                                'course_id' => $course->id,
                                'course_name' => $course->title,
                                'quiz_id' => $quiz->id,
                                'quiz_title' => $quiz->title,
                                'unit_title' => $quiz->unit->title ?? 'N/A',
                            ];
                        }
                    }
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('=== SUMMARY ===');
        $this->info('Total unlocked quizzes not attempted: '.count($results));
        $this->newLine();

        if (count($results) > 0) {
            $this->table(
                ['Student', 'Email', 'Course', 'Quiz', 'Unit'],
                collect($results)->map(function ($r) {
                    return [
                        substr($r['student_name'], 0, 20),
                        substr($r['student_email'], 0, 25),
                        substr($r['course_name'], 0, 25),
                        substr($r['quiz_title'], 0, 25),
                        substr($r['unit_title'], 0, 20),
                    ];
                })
            );

            // Show first result details for testing
            $first = $results[0];
            $this->newLine();
            $this->info('=== FIRST RESULT (For Testing) ===');
            $this->line("Student ID: {$first['student_id']}");
            $this->line("Student Name: {$first['student_name']}");
            $this->line("Student Email: {$first['student_email']}");
            $this->line("Quiz ID: {$first['quiz_id']}");
            $this->line("Quiz Title: {$first['quiz_title']}");
            $this->line("Course: {$first['course_name']}");
            $this->newLine();
            $this->comment('You can test with this student and quiz!');
        } else {
            $this->warn('No unlocked quizzes found that haven\'t been attempted.');
        }

        return 0;
    }
}
