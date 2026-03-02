<?php

declare(strict_types=1);

namespace Modules\Learning\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\Submission;
use Modules\Learning\Models\QuizSubmission;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Carbon\Carbon;

class SequentialProgressSeeder extends Seeder
{
    private array $userProgress = [];
    private array $pregenAnswers = [];
    private string $createdAt;

    public function run(): void
    {
        \DB::connection()->disableQueryLog();
        ini_set('memory_limit', '2048M');
        
        echo "🎯 Seeding sequential progress for students...\n";
        
        $shouldClean = $this->command->confirm('Do you want to clean existing progress data first?', true);
        
        if ($shouldClean) {
            $this->cleanExistingProgress();
        }

        $this->pregenerateFakeData();
        $this->createdAt = now()->toDateTimeString();

        $students = \DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Student')
            ->select('users.id')
            ->limit(50)
            ->get();

        if ($students->isEmpty()) {
            echo "⚠️  No students found.\n";
            return;
        }

        echo "Processing " . count($students) . " students...\n";

        foreach ($students as $student) {
            $this->processStudentProgress($student->id);
            gc_collect_cycles();
        }

        echo "✅ Sequential progress seeding completed!\n";
        
        \DB::connection()->enableQueryLog();
    }

    private function processStudentProgress(int $studentId): void
    {
        $enrollments = \DB::table('enrollments')
            ->where('user_id', $studentId)
            ->where('status', 'active')
            ->get();

        foreach ($enrollments as $enrollment) {
            $this->processCourseProgress($studentId, $enrollment->course_id, $enrollment->id);
        }
    }

    private function processCourseProgress(int $studentId, int $courseId, int $enrollmentId): void
    {
        $units = Unit::where('course_id', $courseId)
            ->orderBy('order')
            ->get();

        foreach ($units as $unit) {
            $shouldContinue = $this->processUnitProgress($studentId, $unit, $enrollmentId);
            
            if (!$shouldContinue) {
                break;
            }
        }
    }

    private function processUnitProgress(int $studentId, Unit $unit, int $enrollmentId): bool
    {
        $lessons = Lesson::where('unit_id', $unit->id)
            ->orderBy('order')
            ->get();

        foreach ($lessons as $lesson) {
            $shouldContinue = $this->processLessonProgress($studentId, $lesson, $enrollmentId);
            
            if (!$shouldContinue) {
                return false;
            }
        }

        return true;
    }

    private function processLessonProgress(int $studentId, Lesson $lesson, int $enrollmentId): bool
    {
        $completionChance = rand(1, 100);
        
        if ($completionChance > 70) {
            return false;
        }

        \DB::table('lesson_completions')->insertOrIgnore([
            'lesson_id' => $lesson->id,
            'user_id' => $studentId,
            'completed_at' => $this->createdAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);

        $assignments = Assignment::where('lesson_id', $lesson->id)
            ->where('status', 'published')
            ->get();

        foreach ($assignments as $assignment) {
            $shouldContinue = $this->processAssignmentProgress($studentId, $assignment, $enrollmentId);
            
            if (!$shouldContinue) {
                return false;
            }
        }

        $quizzes = Quiz::where('lesson_id', $lesson->id)
            ->where('status', 'published')
            ->get();

        foreach ($quizzes as $quiz) {
            $shouldContinue = $this->processQuizProgress($studentId, $quiz, $enrollmentId);
            
            if (!$shouldContinue) {
                return false;
            }
        }

        return true;
    }

    private function processAssignmentProgress(int $studentId, Assignment $assignment, int $enrollmentId): bool
    {
        $completionChance = rand(1, 100);
        
        if ($completionChance > 80) {
            return false;
        }

        $statusRandom = rand(1, 100);
        $status = match (true) {
            $statusRandom <= 20 => 'submitted',
            $statusRandom <= 85 => 'graded',
            default => 'draft',
        };

        if ($status === 'draft') {
            return false;
        }

        $score = $status === 'graded' ? rand(60, 100) : null;

        $answerText = null;
        if ($assignment->submission_type === 'text') {
            $answerText = $this->pregenAnswers[array_rand($this->pregenAnswers)];
        }

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $studentId,
            'enrollment_id' => $enrollmentId,
            'answer_text' => $answerText,
            'status' => $status,
            'state' => $status,
            'score' => $score,
            'submitted_at' => $this->createdAt,
            'attempt_number' => 1,
        ]);

        if ($assignment->submission_type === 'file') {
            $this->attachFileToSubmission($submission);
        }

        if ($status === 'graded') {
            $this->createGradeForSubmission($submission, $assignment);
        }

        return $status === 'graded' && $score >= ($assignment->passing_grade ?? 75);
    }

    private function processQuizProgress(int $studentId, Quiz $quiz, int $enrollmentId): bool
    {
        $completionChance = rand(1, 100);
        
        if ($completionChance > 80) {
            return false;
        }

        $score = rand(60, 100);
        $status = 'graded';

        $quizSubmission = QuizSubmission::create([
            'quiz_id' => $quiz->id,
            'user_id' => $studentId,
            'enrollment_id' => $enrollmentId,
            'status' => $status,
            'score' => $score,
            'submitted_at' => $this->createdAt,
            'attempt_number' => 1,
        ]);

        $this->createGradeForQuizSubmission($quizSubmission, $quiz);

        return $score >= ($quiz->passing_grade ?? 75);
    }

    private function attachFileToSubmission(Submission $submission): void
    {
        $dummyFilePath = public_path('dummy/pdf-sample_0.pdf');
        
        if (!file_exists($dummyFilePath)) {
            return;
        }

        try {
            $submission->addMedia($dummyFilePath)
                ->preservingOriginal()
                ->usingName('submission-' . $submission->id)
                ->usingFileName('submission-' . $submission->id . '.pdf')
                ->toMediaCollection('submissions', 'do');
        } catch (\Exception $e) {
        }
    }

    private function createGradeForSubmission(Submission $submission, Assignment $assignment): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $instructorIds = \DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Instructor')
            ->pluck('users.id')
            ->toArray();

        if (empty($instructorIds)) {
            return;
        }

        \DB::table('grades')->insertOrIgnore([
            'source_type' => 'assignment',
            'source_id' => $assignment->id,
            'user_id' => $submission->user_id,
            'submission_id' => $submission->id,
            'graded_by' => $instructorIds[array_rand($instructorIds)],
            'score' => $submission->score,
            'max_score' => $assignment->max_score,
            'feedback' => $faker->paragraph(1),
            'status' => 'graded',
            'graded_at' => $submission->graded_at,
            'created_at' => $submission->graded_at,
            'updated_at' => $submission->graded_at,
        ]);
    }

    private function createGradeForQuizSubmission(QuizSubmission $quizSubmission, Quiz $quiz): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $instructorIds = \DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Instructor')
            ->pluck('users.id')
            ->toArray();

        if (empty($instructorIds)) {
            return;
        }

        \DB::table('grades')->insertOrIgnore([
            'source_type' => 'quiz',
            'source_id' => $quiz->id,
            'user_id' => $quizSubmission->user_id,
            'quiz_submission_id' => $quizSubmission->id,
            'graded_by' => $instructorIds[array_rand($instructorIds)],
            'score' => $quizSubmission->score,
            'max_score' => $quiz->max_score,
            'feedback' => $faker->paragraph(1),
            'status' => 'graded',
            'graded_at' => $quizSubmission->graded_at,
            'created_at' => $quizSubmission->graded_at,
            'updated_at' => $quizSubmission->graded_at,
        ]);
    }

    private function pregenerateFakeData(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        
        for ($i = 0; $i < 50; $i++) {
            $this->pregenAnswers[] = $faker->paragraph(3);
        }
        
        unset($faker);
    }

    private function cleanExistingProgress(): void
    {
        echo "🧹 Cleaning existing progress data...\n";
        
        \DB::table('grades')->whereIn('source_type', ['assignment', 'quiz'])->delete();
        echo "  ✓ Cleaned grades\n";
        
        \DB::table('media')->where('collection_name', 'submissions')->delete();
        echo "  ✓ Cleaned submission files\n";
        
        \DB::table('submissions')->delete();
        echo "  ✓ Cleaned submissions\n";
        
        \DB::table('quiz_submissions')->delete();
        echo "  ✓ Cleaned quiz submissions\n";
        
        \DB::table('lesson_completions')->delete();
        echo "  ✓ Cleaned lesson completions\n";
        
        echo "✅ Existing progress data cleaned!\n\n";
    }
}
