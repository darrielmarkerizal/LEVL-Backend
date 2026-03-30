<?php

declare(strict_types=1);

namespace Modules\Learning\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;

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
        $this->cleanExistingProgress();

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

        echo 'Processing '.count($students)." students...\n";

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
            $studentType = $this->determineStudentType();
            $this->processCourseProgress($studentId, $enrollment->course_id, $enrollment->id, $studentType);
        }
    }

    private function determineStudentType(): string
    {
        $rand = rand(1, 100);

        return match (true) {
            $rand <= 25 => 'complete',
            $rand <= 55 => 'high_progress',
            $rand <= 85 => 'medium_progress',
            default => 'low_progress',
        };
    }

    private function processCourseProgress(int $studentId, int $courseId, int $enrollmentId, string $studentType): void
    {
        $units = Unit::where('course_id', $courseId)
            ->orderBy('order')
            ->get();

        foreach ($units as $unit) {
            $shouldContinue = $this->processUnitProgress($studentId, $unit, $enrollmentId, $studentType);

            if (! $shouldContinue) {
                break;
            }
        }
    }

    private function processUnitProgress(int $studentId, Unit $unit, int $enrollmentId, string $studentType): bool
    {
        $allContent = collect();

        $lessons = Lesson::where('unit_id', $unit->id)->get();
        foreach ($lessons as $lesson) {
            $allContent->push(['type' => 'lesson', 'order' => $lesson->order, 'data' => $lesson]);
        }

        $assignments = Assignment::where('unit_id', $unit->id)
            ->where('status', 'published')
            ->get();
        foreach ($assignments as $assignment) {
            $allContent->push(['type' => 'assignment', 'order' => $assignment->order, 'data' => $assignment]);
        }

        $quizzes = Quiz::where('unit_id', $unit->id)
            ->where('status', 'published')
            ->get();
        foreach ($quizzes as $quiz) {
            $allContent->push(['type' => 'quiz', 'order' => $quiz->order, 'data' => $quiz]);
        }

        $allContent = $allContent->sortBy('order');

        foreach ($allContent as $content) {
            $shouldContinue = match ($content['type']) {
                'lesson' => $this->processLessonCompletion($studentId, $content['data'], $studentType),
                'assignment' => $this->processAssignmentProgress($studentId, $content['data'], $enrollmentId, $studentType),
                'quiz' => $this->processQuizProgress($studentId, $content['data'], $enrollmentId, $studentType),
            };

            if (! $shouldContinue) {
                return false;
            }
        }

        return true;
    }

    private function processLessonCompletion(int $studentId, Lesson $lesson, string $studentType): bool
    {
        $prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);
        $accessCheck = $prerequisiteService->checkLessonAccess($lesson, $studentId);

        if (! $accessCheck['accessible']) {
            return false;
        }

        $completionChance = match ($studentType) {
            'complete' => 100,
            'high_progress' => 85,
            'medium_progress' => 60,
            'low_progress' => 30,
        };

        if (rand(1, 100) > $completionChance) {
            return false;
        }

        // Lesson completions are now tracked via lesson_progress table
        \DB::table('lesson_progress')->insertOrIgnore([
            'lesson_id' => $lesson->id,
            'user_id' => $studentId,
            'status' => 'completed',
            'completed_at' => $this->createdAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);

        return true;
    }

    private function processAssignmentProgress(int $studentId, Assignment $assignment, int $enrollmentId, string $studentType): bool
    {
        $prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);
        $accessCheck = $prerequisiteService->checkAssignmentAccess($assignment, $studentId);

        if (! $accessCheck['accessible']) {
            return false;
        }

        if (! $enrollmentId) {
            return false;
        }

        $completionChance = match ($studentType) {
            'complete' => 100,
            'high_progress' => 80,
            'medium_progress' => 50,
            'low_progress' => 20,
        };

        if (rand(1, 100) > $completionChance) {
            return false;
        }

        $statusWeights = match ($studentType) {
            'complete' => ['submitted' => 5, 'graded' => 95, 'draft' => 0],
            'high_progress' => ['submitted' => 15, 'graded' => 80, 'draft' => 5],
            'medium_progress' => ['submitted' => 25, 'graded' => 65, 'draft' => 10],
            'low_progress' => ['submitted' => 30, 'graded' => 50, 'draft' => 20],
        };

        $statusRandom = rand(1, 100);
        $status = match (true) {
            $statusRandom <= $statusWeights['draft'] => 'draft',
            $statusRandom <= ($statusWeights['draft'] + $statusWeights['submitted']) => 'submitted',
            default => 'graded',
        };

        if ($status === 'draft') {
            return false;
        }

        $passingGrade = (float) ($assignment->passing_grade ?? ($assignment->max_score * 0.6));
        $maxScore = (float) $assignment->max_score;
        $score = null;

        if ($status === 'graded') {
            $passRate = match ($studentType) {
                'complete' => 95,
                'high_progress' => 85,
                'medium_progress' => 70,
                'low_progress' => 50,
            };

            $willPass = rand(1, 100) <= $passRate;

            if ($willPass) {
                $minScore = (int) max($passingGrade, $maxScore * 0.7);
                $maxScoreInt = match ($studentType) {
                    'complete' => (int) $maxScore,
                    'high_progress' => (int) ($maxScore * 0.95),
                    'medium_progress' => (int) ($maxScore * 0.85),
                    'low_progress' => (int) min($passingGrade + 10, $maxScore),
                };
                $score = rand($minScore, $maxScoreInt);
            } else {
                $maxFailScore = max(1, (int) ($passingGrade - 1));
                $minFailScore = (int) ($maxScore * 0.4);
                $minFailScore = min($minFailScore, $maxFailScore);
                $score = rand($minFailScore, $maxFailScore);
            }

            $score = min($score, (int) $maxScore);
        }

        $answerText = null;
        if ($assignment->submission_type === \Modules\Learning\Enums\SubmissionType::Text) {
            $answerText = $this->pregenAnswers[array_rand($this->pregenAnswers)];
        }

        $submittedAt = now()->subDays(rand(1, 14))->toDateTimeString();

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $studentId,
            'enrollment_id' => $enrollmentId,
            'answer_text' => $answerText,
            'status' => $status,
            'state' => $status,
            'score' => $score,
            'submitted_at' => $submittedAt,
            'attempt_number' => 1,
        ]);

        if ($assignment->submission_type === \Modules\Learning\Enums\SubmissionType::File) {
            $this->attachFileToSubmission($submission);
        }

        if ($status === 'graded' || $status === 'submitted') {
            $this->createGradeForSubmission($submission, $assignment, $status, $score);
        }

        return $status === 'graded' && $score >= $passingGrade;
    }

    private function processQuizProgress(int $studentId, Quiz $quiz, int $enrollmentId, string $studentType): bool
    {
        $prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);
        $accessCheck = $prerequisiteService->checkQuizAccess($quiz, $studentId);

        if (! $accessCheck['accessible']) {
            return false;
        }

        if (! $enrollmentId) {
            return false;
        }

        $completionChance = match ($studentType) {
            'complete' => 100,
            'high_progress' => 80,
            'medium_progress' => 50,
            'low_progress' => 20,
        };

        if (rand(1, 100) > $completionChance) {
            return false;
        }

        $questions = \DB::table('quiz_questions')
            ->where('quiz_id', $quiz->id)
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            return false;
        }

        $daysAgo = rand(1, 14);
        $submittedAt = now()->subDays($daysAgo)->toDateTimeString();
        $startedAt = now()->subDays($daysAgo)->subMinutes(rand(30, 120))->toDateTimeString();

        $submission = QuizSubmission::create([
            'quiz_id' => $quiz->id,
            'user_id' => $studentId,
            'enrollment_id' => $enrollmentId,
            'status' => 'graded',
            'grading_status' => 'graded',
            'score' => 0,
            'final_score' => 0,
            'submitted_at' => $submittedAt,
            'started_at' => $startedAt,
            'attempt_number' => 1,
        ]);

        $passingGrade = (float) ($quiz->passing_grade ?? 75);
        $passRate = match ($studentType) {
            'complete' => 95,
            'high_progress' => 85,
            'medium_progress' => 70,
            'low_progress' => 50,
        };

        $willPass = rand(1, 100) <= $passRate;

        $correctnessRate = match (true) {
            $willPass && $studentType === 'complete' => rand(90, 100),
            $willPass && $studentType === 'high_progress' => rand(80, 95),
            $willPass && $studentType === 'medium_progress' => rand(70, 85),
            $willPass && $studentType === 'low_progress' => rand((int) $passingGrade, min(100, (int) ($passingGrade + 10))),
            default => rand(max(30, (int) ($passingGrade - 20)), max(40, (int) ($passingGrade - 5))),
        };

        $totalScore = 0;

        foreach ($questions as $question) {
            $randomChance = rand(1, 100);
            $isCorrect = $randomChance <= $correctnessRate;
            $weight = (float) $question->weight;
            $questionScore = $isCorrect ? $weight : rand(0, (int) ($weight * 0.3));

            $answerData = [
                'quiz_submission_id' => $submission->id,
                'quiz_question_id' => $question->id,
                'content' => null,
                'selected_options' => null,
                'score' => $questionScore,
                'is_auto_graded' => true,
                'feedback' => null,
                'created_at' => $this->createdAt,
                'updated_at' => $this->createdAt,
            ];

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $options = json_decode($question->options, true);
                $answerKey = json_decode($question->answer_key, true);
                $selectedOption = $isCorrect ? $answerKey[0] : (($answerKey[0] + 1) % count($options));
                $answerData['selected_options'] = json_encode([$selectedOption]);
            } elseif ($question->type === 'checkbox') {
                $answerKey = json_decode($question->answer_key, true);
                $answerData['selected_options'] = $isCorrect ? $question->answer_key : json_encode([0]);
            } elseif ($question->type === 'essay') {
                $answerData['content'] = $this->pregenAnswers[array_rand($this->pregenAnswers)];
                $answerData['is_auto_graded'] = false;
                if ($isCorrect) {
                    $answerData['score'] = rand((int) ($weight * 0.8), (int) $weight);
                    $answerData['feedback'] = 'Good answer';
                } else {
                    $answerData['score'] = rand((int) ($weight * 0.3), (int) ($weight * 0.6));
                    $answerData['feedback'] = 'Needs improvement';
                }
                $questionScore = $answerData['score'];
            }

            \DB::table('quiz_answers')->insert($answerData);
            $totalScore += $answerData['score'];
        }

        $submission->update([
            'score' => $totalScore,
            'final_score' => $totalScore,
        ]);

        return $totalScore >= $passingGrade;
    }

    private function attachFileToSubmission(Submission $submission): void
    {
        $dummyFilePath = public_path('dummy/pdf-sample_0.pdf');

        if (! file_exists($dummyFilePath)) {
            echo "⚠️  Dummy file not found: {$dummyFilePath}\n";

            return;
        }

        try {
            $submissionFile = \Modules\Learning\Models\SubmissionFile::create([
                'submission_id' => $submission->id,
            ]);

            $submissionFile->addMedia($dummyFilePath)
                ->preservingOriginal()
                ->usingName('submission-'.$submission->id)
                ->usingFileName('submission-'.$submission->id.'.pdf')
                ->toMediaCollection('file', 'do');
        } catch (\Exception $e) {
            echo "⚠️  Failed to attach file for submission {$submission->id}: ".$e->getMessage()."\n";
        }
    }

    private function createGradeForSubmission(Submission $submission, Assignment $assignment, string $status, ?float $score): void
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

        $gradeStatus = $status === 'submitted' ? 'pending' : 'graded';
        $gradedAt = $status === 'graded' ? now()->subDays(rand(0, 7))->toDateTimeString() : null;

        \DB::table('grades')->insertOrIgnore([
            'source_type' => 'assignment',
            'source_id' => $assignment->id,
            'user_id' => $submission->user_id,
            'submission_id' => $submission->id,
            'graded_by' => $instructorIds[array_rand($instructorIds)],
            'score' => $score,
            'max_score' => $assignment->max_score,
            'feedback' => $status === 'graded' ? $faker->paragraph(1) : null,
            'status' => $gradeStatus,
            'graded_at' => $gradedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
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

        \DB::table('quiz_answers')->delete();
        echo "  ✓ Cleaned quiz answers\n";

        \DB::table('media')->where('collection_name', 'file')->whereIn('model_id', function ($query) {
            $query->select('id')->from('submission_files');
        })->delete();
        echo "  ✓ Cleaned submission files media\n";

        \DB::table('submission_files')->delete();
        echo "  ✓ Cleaned submission files\n";

        \DB::table('submissions')->delete();
        echo "  ✓ Cleaned submissions\n";

        \DB::table('quiz_submissions')->delete();
        echo "  ✓ Cleaned quiz submissions\n";

        \DB::table('lesson_progress')->delete();
        echo "  ✓ Cleaned lesson progress\n";

        echo "✅ Existing progress data cleaned!\n\n";
    }
}
