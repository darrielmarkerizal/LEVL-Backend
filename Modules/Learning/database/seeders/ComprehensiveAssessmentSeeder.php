<?php

declare(strict_types=1);

namespace Modules\Learning\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Enums\AssignmentType;
use Modules\Learning\Enums\QuestionType;
use Modules\Learning\Enums\QuizGradingStatus;
use Modules\Learning\Enums\QuizQuestionType;
use Modules\Learning\Enums\QuizSubmissionStatus;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Enums\SubmissionStatus;

class ComprehensiveAssessmentSeeder extends Seeder
{
    private array $pregenSentences = [];

    private array $pregenParagraphs = [];

    private array $pregenWords = [];

    private array $pregenUuids = [];

    private string $createdAt;

    private array $userIds = [];

    private array $instructorIds = [];

    public function run(): void
    {
        DB::connection()->disableQueryLog();
        ini_set('memory_limit', '1536M');

        echo "\n🎯 Comprehensive Assessment Seeder (Refactored)\n";
        echo '='.str_repeat('=', 50)."\n";

        $this->pregenerateFakeData();
        $this->createdAt = now()->toDateTimeString();

        $this->userIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Student')
            ->where('model_has_roles.model_type', 'Modules\\Auth\\Models\\User')
            ->limit(50)
            ->pluck('users.id')
            ->toArray();

        $this->instructorIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['Instructor', 'Admin'])
            ->where('model_has_roles.model_type', 'Modules\\Auth\\Models\\User')
            ->pluck('users.id')
            ->toArray();

        if (empty($this->userIds)) {
            echo "⚠️  No users found. Please run user seeders first.\n";

            return;
        }

        if (empty($this->instructorIds)) {
            $this->instructorIds = DB::table('users')->limit(5)->pluck('id')->toArray();
        }

        $courses = DB::table('courses')->select('id', 'title')->get();

        if ($courses->isEmpty()) {
            echo "⚠️  No courses found. Please run course seeders first.\n";

            return;
        }

        echo "� Found {$courses->count()} courses\n\n";

        $totalAssignments = 0;
        $totalQuizzes = 0;
        $totalQuestions = 0;
        $totalSubmissions = 0;
        $totalAnswers = 0;
        $totalGrades = 0;

        foreach ($courses as $index => $course) {
            echo '📘 Course '.($index + 1)."/{$courses->count()}: {$course->title}\n";

            $units = DB::table('units')
                ->where('course_id', $course->id)
                ->select('id', 'title')
                ->get();

            if ($units->isEmpty()) {
                echo "   ⚠️ No units found, skipping...\n";

                continue;
            }

            foreach ($units as $unit) {
                $result = $this->createMixedAssessments($course->id, $unit->id);
                $totalAssignments += $result['assignments'];
                $totalQuizzes += $result['quizzes'];
                $totalQuestions += $result['questions'];
                $totalSubmissions += $result['submissions'];
                $totalAnswers += $result['answers'];
                $totalGrades += $result['grades'];
            }

            echo "   ✓ Created data for course\n";

            if (($index + 1) % 5 === 0) {
                gc_collect_cycles();
            }
        }

        echo "\n".str_repeat('=', 50)."\n";
        echo "✅ Comprehensive Assessment Seeding Completed!\n";
        echo "   📊 Assignments: {$totalAssignments}\n";
        echo "   📊 Quizzes: {$totalQuizzes}\n";
        echo "   📊 Questions: {$totalQuestions}\n";
        echo "   📊 Submissions: {$totalSubmissions}\n";
        echo "   📊 Answers: {$totalAnswers}\n";
        echo "   📊 Grades: {$totalGrades}\n";

        $this->cleanup();
        DB::connection()->enableQueryLog();
    }

    private function createMixedAssessments(int $courseId, int $unitId): array
    {
        $stats = ['assignments' => 0, 'quizzes' => 0, 'questions' => 0, 'submissions' => 0, 'answers' => 0, 'grades' => 0];

        $numAssignments = rand(1, 3);
        $numQuizzes = rand(1, 3);

        $maxLessonOrder = DB::table('lessons')->where('unit_id', $unitId)->max('order') ?? 0;
        $currentOrder = $maxLessonOrder + 1;

        $assignments = [];
        $quizzes = [];

        for ($i = 0; $i < $numAssignments; $i++) {
            $result = $this->createAssignmentWithSubmissions($courseId, $unitId, $currentOrder);
            $assignments[] = DB::table('assignments')->where('unit_id', $unitId)->latest('id')->first();
            $stats['assignments'] += $result['assignments'];
            $stats['submissions'] += $result['submissions'];
            $stats['grades'] += $result['grades'];
            $currentOrder++;
        }

        for ($i = 0; $i < $numQuizzes; $i++) {
            $result = $this->createQuizWithSubmissions($courseId, $unitId, $currentOrder);
            $quizzes[] = DB::table('quizzes')->where('unit_id', $unitId)->latest('id')->first();
            $stats['quizzes'] += $result['quizzes'];
            $stats['questions'] += $result['questions'];
            $stats['submissions'] += $result['submissions'];
            $stats['answers'] += $result['answers'];
            $currentOrder++;
        }

        return $stats;
    }

    private function createAssignmentWithSubmissions(int $courseId, int $unitId, int $order): array
    {
        $stats = ['assignments' => 0, 'submissions' => 0, 'grades' => 0];

        $assignmentId = DB::table('assignments')->insertGetId([
            'unit_id' => $unitId,
            'order' => $order,
            'created_by' => $this->instructorIds[array_rand($this->instructorIds)],
            'title' => $this->pregenSentences[array_rand($this->pregenSentences)],
            'description' => $this->pregenParagraphs[array_rand($this->pregenParagraphs)],
            'type' => AssignmentType::Assignment->value,
            'submission_type' => 'file',
            'max_score' => 100,
            'passing_grade' => rand(60, 80),
            'review_mode' => 'manual',
            'status' => 'published',
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);
        $stats['assignments']++;

        if (rand(0, 1)) {
            $this->createAssignmentAttachments($assignmentId);
        }

        $selectedUsers = array_slice($this->userIds, 0, min(5, count($this->userIds)));

        foreach ($selectedUsers as $userId) {
            $submissionResult = $this->createSubmissionWithGrade($assignmentId, $userId, $courseId);
            $stats['submissions'] += $submissionResult['submissions'];
            $stats['grades'] += $submissionResult['grades'];
        }

        return $stats;
    }

    private function createQuizWithSubmissions(int $courseId, int $unitId, int $order): array
    {
        $stats = ['quizzes' => 0, 'questions' => 0, 'submissions' => 0, 'answers' => 0];

        $quizId = DB::table('quizzes')->insertGetId([
            'unit_id' => $unitId,
            'order' => $order,
            'created_by' => $this->instructorIds[array_rand($this->instructorIds)],
            'title' => 'Quiz: '.$this->pregenSentences[array_rand($this->pregenSentences)],
            'description' => $this->pregenParagraphs[array_rand($this->pregenParagraphs)],
            'passing_grade' => rand(60, 80),
            'auto_grading' => true,
            'max_score' => 100,
            'time_limit_minutes' => rand(30, 90),
            'randomization_type' => 'static',
            'review_mode' => 'immediate',
            'status' => 'published',
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);
        $stats['quizzes']++;

        $questionTypes = [
            QuizQuestionType::MultipleChoice->value,
            QuizQuestionType::TrueFalse->value,
            QuizQuestionType::Checkbox->value,
            QuizQuestionType::Essay->value,
        ];

        $questions = [];
        foreach ($questionTypes as $order => $type) {
            $questionId = $this->createQuizQuestion($quizId, $type, $order + 1);
            $questions[] = ['id' => $questionId, 'type' => $type, 'weight' => 25];
            $stats['questions']++;
        }

        $selectedUsers = array_slice($this->userIds, 0, min(5, count($this->userIds)));

        foreach ($selectedUsers as $userId) {
            $submissionResult = $this->createQuizSubmissionWithAnswers($quizId, $userId, $questions);
            $stats['submissions'] += $submissionResult['submissions'];
            $stats['answers'] += $submissionResult['answers'];
        }

        return $stats;
    }

    private function createAssignmentQuestion(int $assignmentId, string $type, int $order): int
    {
        $questionData = [
            'assignment_id' => $assignmentId,
            'type' => $type,
            'content' => $this->getQuestionContent($type),
            'weight' => 1.0,
            'order' => $order,
            'max_score' => 50,
            'options' => null,
            'answer_key' => null,
            'max_file_size' => null,
            'allowed_file_types' => null,
            'allow_multiple_files' => false,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];

        if ($type === QuestionType::FileUpload->value) {
            $questionData['max_file_size'] = 10485760;
            $questionData['allowed_file_types'] = json_encode(['pdf', 'docx', 'xlsx', 'zip']);
            $questionData['allow_multiple_files'] = true;
        }

        return DB::table('assignment_questions')->insertGetId($questionData);
    }

    private function createSubmissionWithGrade(int $assignmentId, int $userId, int $courseId): array
    {
        $stats = ['submissions' => 0, 'grades' => 0];

        $assignment = DB::table('assignments')->where('id', $assignmentId)->first();
        $passingGrade = $assignment->passing_grade ?? 75;

        $states = [SubmissionState::Graded->value, SubmissionState::PendingManualGrading->value, SubmissionState::Released->value];
        $state = $states[array_rand($states)];

        $status = match ($state) {
            SubmissionState::PendingManualGrading->value => SubmissionStatus::Submitted->value,
            default => SubmissionStatus::Graded->value,
        };

        $totalScore = in_array($state, [SubmissionState::Graded->value, SubmissionState::Released->value])
            ? rand((int) $passingGrade, 100)
            : null;

        $submissionId = DB::table('submissions')->insertGetId([
            'assignment_id' => $assignmentId,
            'user_id' => $userId,
            'status' => $status,
            'state' => $state,
            'submitted_at' => now()->subDays(rand(1, 14))->toDateTimeString(),
            'attempt_number' => 1,
            'score' => $totalScore,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);
        $stats['submissions']++;

        if (in_array($state, [SubmissionState::Graded->value, SubmissionState::Released->value])) {
            DB::table('grades')->insertOrIgnore([
                'submission_id' => $submissionId,
                'source_type' => 'assignment',
                'source_id' => $assignmentId,
                'user_id' => $userId,
                'graded_by' => $this->instructorIds[array_rand($this->instructorIds)],
                'score' => $totalScore,
                'max_score' => 100,
                'feedback' => $this->pregenParagraphs[array_rand($this->pregenParagraphs)],
                'is_draft' => false,
                'graded_at' => now()->subDays(rand(0, 7))->toDateTimeString(),
                'released_at' => $state === SubmissionState::Released->value ? now()->toDateTimeString() : null,
                'created_at' => $this->createdAt,
                'updated_at' => $this->createdAt,
            ]);
            $stats['grades']++;
        }

        return $stats;
    }

    private function createQuizQuestion(int $quizId, string $type, int $order): int
    {
        $questionData = [
            'quiz_id' => $quizId,
            'type' => $type,
            'content' => $this->getQuizQuestionContent($type),
            'weight' => 25,
            'order' => $order,
            'max_score' => 25,
            'options' => null,
            'answer_key' => null,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];

        switch ($type) {
            case QuizQuestionType::MultipleChoice->value:
                $options = ['Option A', 'Option B', 'Option C', 'Option D'];
                $questionData['options'] = json_encode($options);
                $questionData['answer_key'] = json_encode([0]);
                break;

            case QuizQuestionType::TrueFalse->value:
                $questionData['options'] = json_encode(['True', 'False']);
                $questionData['answer_key'] = json_encode([0]);
                break;

            case QuizQuestionType::Checkbox->value:
                $options = ['Option 1', 'Option 2', 'Option 3', 'Option 4'];
                $questionData['options'] = json_encode($options);
                $questionData['answer_key'] = json_encode([0, 2]);
                break;
        }

        return DB::table('quiz_questions')->insertGetId($questionData);
    }

    private function createSubmissionWithAnswersAndGrade(int $assignmentId, int $userId, array $questions, int $courseId): array
    {
        $stats = ['submissions' => 0, 'answers' => 0, 'grades' => 0];

        $states = [SubmissionState::Graded->value, SubmissionState::PendingManualGrading->value, SubmissionState::Released->value];
        $state = $states[array_rand($states)];

        $status = match ($state) {
            SubmissionState::PendingManualGrading->value => SubmissionStatus::Submitted->value,
            default => SubmissionStatus::Graded->value,
        };

        $submissionId = DB::table('submissions')->insertGetId([
            'assignment_id' => $assignmentId,
            'user_id' => $userId,
            'status' => $status,
            'state' => $state,
            'submitted_at' => now()->subDays(rand(1, 14))->toDateTimeString(),
            'attempt_number' => 1,
            'is_late' => false,
            'score' => null,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);
        $stats['submissions']++;

        $totalScore = 0;
        foreach ($questions as $question) {
            $answerResult = $this->createAnswer($submissionId, $question, $state);
            $stats['answers']++;
            $totalScore += $answerResult['score'] ?? 0;
        }

        if (in_array($state, [SubmissionState::Graded->value, SubmissionState::Released->value])) {
            DB::table('submissions')->where('id', $submissionId)->update(['score' => $totalScore]);

            DB::table('grades')->insertOrIgnore([
                'submission_id' => $submissionId,
                'source_type' => 'assignment',
                'source_id' => $assignmentId,
                'user_id' => $userId,
                'graded_by' => $this->instructorIds[array_rand($this->instructorIds)],
                'score' => $totalScore,
                'max_score' => 100,
                'feedback' => $this->pregenParagraphs[array_rand($this->pregenParagraphs)],
                'is_draft' => false,
                'graded_at' => now()->subDays(rand(0, 7))->toDateTimeString(),
                'released_at' => $state === SubmissionState::Released->value ? now()->toDateTimeString() : null,
                'created_at' => $this->createdAt,
                'updated_at' => $this->createdAt,
            ]);
            $stats['grades']++;
        }

        return $stats;
    }

    private function createQuizSubmissionWithAnswers(int $quizId, int $userId, array $questions): array
    {
        $stats = ['submissions' => 0, 'answers' => 0];

        $quiz = DB::table('quizzes')->where('id', $quizId)->first();
        $passingGrade = (float) ($quiz->passing_grade ?? 75);

        $submissionId = DB::table('quiz_submissions')->insertGetId([
            'quiz_id' => $quizId,
            'user_id' => $userId,
            'status' => QuizSubmissionStatus::Graded->value,
            'grading_status' => QuizGradingStatus::Graded->value,
            'score' => null,
            'final_score' => null,
            'submitted_at' => now()->subDays(rand(1, 14))->toDateTimeString(),
            'started_at' => now()->subDays(rand(1, 15))->toDateTimeString(),
            'attempt_number' => 1,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);
        $stats['submissions']++;

        $totalScore = 0;
        foreach ($questions as $question) {
            $score = $this->createQuizAnswer($submissionId, $question, $passingGrade);
            $stats['answers']++;
            $totalScore += $score;
        }

        if ($totalScore < $passingGrade) {
            $totalScore = rand((int) $passingGrade, 100);
        }

        DB::table('quiz_submissions')->where('id', $submissionId)->update([
            'score' => $totalScore,
            'final_score' => $totalScore,
        ]);

        return $stats;
    }

    private function createAnswer(int $submissionId, array $question, string $state): array
    {
        $answerData = [
            'submission_id' => $submissionId,
            'question_id' => $question['id'],
            'content' => null,
            'selected_options' => null,
            'file_paths' => null,
            'score' => null,
            'is_auto_graded' => false,
            'feedback' => null,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];

        switch ($question['type']) {
            case QuestionType::Essay->value:
                $answerData['content'] = $this->pregenParagraphs[array_rand($this->pregenParagraphs)];
                if ($state !== SubmissionState::PendingManualGrading->value) {
                    $answerData['score'] = rand(30, $question['max_score']);
                    $answerData['feedback'] = $this->pregenSentences[array_rand($this->pregenSentences)];
                }
                break;

            case QuestionType::FileUpload->value:
                $answerData['file_paths'] = json_encode([
                    'uploads/dummy_'.rand(1000, 9999).'.pdf',
                    'uploads/dummy_'.rand(1000, 9999).'.docx',
                ]);
                if ($state !== SubmissionState::PendingManualGrading->value) {
                    $answerData['score'] = rand(35, $question['max_score']);
                    $answerData['feedback'] = $this->pregenSentences[array_rand($this->pregenSentences)];
                }
                break;
        }

        DB::table('answers')->insertOrIgnore($answerData);

        return ['score' => $answerData['score']];
    }

    private function createQuizAnswer(int $submissionId, array $question, float $passingGrade): float
    {
        $answerData = [
            'quiz_submission_id' => $submissionId,
            'quiz_question_id' => $question['id'],
            'content' => null,
            'selected_options' => null,
            'score' => 0,
            'is_auto_graded' => true,
            'feedback' => null,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];

        $isCorrect = rand(0, 100) > 20;

        switch ($question['type']) {
            case QuizQuestionType::MultipleChoice->value:
            case QuizQuestionType::TrueFalse->value:
                $answerData['selected_options'] = json_encode([$isCorrect ? 0 : 1]);
                $answerData['score'] = $isCorrect ? $question['weight'] : 0;
                break;

            case QuizQuestionType::Checkbox->value:
                $answerData['selected_options'] = json_encode($isCorrect ? [0, 2] : [0, 1]);
                $answerData['score'] = $isCorrect ? $question['weight'] : 0;
                break;

            case QuizQuestionType::Essay->value:
                $answerData['content'] = $this->pregenParagraphs[array_rand($this->pregenParagraphs)];
                $answerData['is_auto_graded'] = false;
                $answerData['score'] = rand((int) ($question['weight'] * 0.7), $question['weight']);
                $answerData['feedback'] = $this->pregenSentences[array_rand($this->pregenSentences)];
                break;
        }

        DB::table('quiz_answers')->insertOrIgnore($answerData);

        return $answerData['score'];
    }

    private function getQuestionContent(string $type): string
    {
        $templates = [
            QuestionType::Essay->value => [
                'Explain the importance of code testing and describe different types of testing strategies.',
                'Discuss the Model-View-Controller (MVC) pattern and its role in web applications.',
                'Compare REST API with GraphQL and explain when to use each.',
            ],
            QuestionType::FileUpload->value => [
                'Upload your completed project source code as a ZIP file.',
                'Submit your database schema diagram in PDF format.',
                'Upload your test case results in Excel or CSV format.',
            ],
        ];

        $options = $templates[$type] ?? $templates[QuestionType::Essay->value];

        return $options[array_rand($options)];
    }

    private function getQuizQuestionContent(string $type): string
    {
        $templates = [
            QuizQuestionType::MultipleChoice->value => 'Which of the following best describes dependency injection?',
            QuizQuestionType::TrueFalse->value => 'Laravel uses the MVC architectural pattern.',
            QuizQuestionType::Checkbox->value => 'Select all valid HTTP methods:',
            QuizQuestionType::Essay->value => 'Explain the concept of middleware in web applications.',
        ];

        return $templates[$type] ?? 'Sample question';
    }

    private function createAssignmentAttachments(int $assignmentId): void
    {
        $numAttachments = rand(1, 3);

        for ($i = 0; $i < $numAttachments; $i++) {
            DB::table('media')->insert([
                'model_type' => 'Modules\\Learning\\Models\\Assignment',
                'model_id' => $assignmentId,
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'collection_name' => 'attachments',
                'name' => 'assignment-attachment-'.$i,
                'file_name' => 'document-'.rand(1000, 9999).'.pdf',
                'mime_type' => 'application/pdf',
                'disk' => 'do',
                'conversions_disk' => 'do',
                'size' => rand(100000, 5000000),
                'manipulations' => '[]',
                'custom_properties' => '[]',
                'generated_conversions' => '[]',
                'responsive_images' => '[]',
                'order_column' => $i + 1,
                'created_at' => $this->createdAt,
                'updated_at' => $this->createdAt,
            ]);
        }
    }

    private function pregenerateFakeData(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        for ($i = 0; $i < 100; $i++) {
            $this->pregenSentences[] = $faker->sentence(8);
            $this->pregenParagraphs[] = $faker->paragraph(3);
            $this->pregenWords[] = $faker->word();
            $this->pregenUuids[] = $faker->uuid();
        }

        unset($faker);
    }

    private function cleanup(): void
    {
        $this->pregenSentences = [];
        $this->pregenParagraphs = [];
        $this->pregenWords = [];
        $this->pregenUuids = [];
        gc_collect_cycles();
    }
}
