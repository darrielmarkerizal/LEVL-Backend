<?php

declare(strict_types=1);

namespace Modules\Learning\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Enums\QuestionType;
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

    public function run(): void
    {
        DB::connection()->disableQueryLog();
        ini_set('memory_limit', '1536M');

        echo "\n🎯 Comprehensive Assessment Seeder\n";
        echo "=" . str_repeat("=", 50) . "\n";

        $this->pregenerateFakeData();
        $this->createdAt = now()->toDateTimeString();

        $this->userIds = DB::table('users')->limit(50)->pluck('id')->toArray();
        if (empty($this->userIds)) {
            echo "⚠️  No users found. Please run user seeders first.\n";
            return;
        }

        $courses = DB::table('courses')
            ->select('id', 'title')
            ->get();

        if ($courses->isEmpty()) {
            echo "⚠️  No courses found. Please run course seeders first.\n";
            return;
        }

        echo "📚 Found {$courses->count()} courses\n\n";

        $totalAssignments = 0;
        $totalQuestions = 0;
        $totalSubmissions = 0;
        $totalAnswers = 0;
        $totalGrades = 0;

        foreach ($courses as $index => $course) {
            echo "📘 Course " . ($index + 1) . "/{$courses->count()}: {$course->title}\n";

            $lessons = DB::table('lessons')
                ->join('units', 'lessons.unit_id', '=', 'units.id')
                ->where('units.course_id', $course->id)
                ->select('lessons.id', 'lessons.title')
                ->get();

            if ($lessons->isEmpty()) {
                echo "   ⚠️ No lessons found, skipping...\n";
                continue;
            }

            foreach ($lessons as $lesson) {
                $result = $this->createAssignmentWithFullData($course->id, $lesson->id);
                $totalAssignments += $result['assignments'];
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

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "✅ Comprehensive Assessment Seeding Completed!\n";
        echo "   📊 Assignments: {$totalAssignments}\n";
        echo "   📊 Questions: {$totalQuestions}\n";
        echo "   📊 Submissions: {$totalSubmissions}\n";
        echo "   📊 Answers: {$totalAnswers}\n";
        echo "   📊 Grades: {$totalGrades}\n";

        $this->cleanup();
        DB::connection()->enableQueryLog();
    }

    private function createAssignmentWithFullData(int $courseId, int $lessonId): array
    {
        $stats = ['assignments' => 0, 'questions' => 0, 'submissions' => 0, 'answers' => 0, 'grades' => 0];

        $assignmentId = DB::table('assignments')->insertGetId([
            'lesson_id' => $lessonId,
            'assignable_type' => 'Modules\\Schemes\\Models\\Lesson',
            'assignable_id' => $lessonId,
            'created_by' => $this->userIds[array_rand($this->userIds)],
            'title' => $this->pregenSentences[array_rand($this->pregenSentences)],
            'description' => $this->pregenParagraphs[array_rand($this->pregenParagraphs)],
            'max_score' => 100,
            'max_attempts' => 3,
            'time_limit_minutes' => rand(30, 120),
            'status' => 'published',
            'deadline_at' => now()->addDays(rand(7, 30))->toDateTimeString(),
            'available_from' => now()->subDays(rand(1, 7))->toDateTimeString(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ]);
        $stats['assignments']++;

        $questionTypes = [
            QuestionType::MultipleChoice->value,
            QuestionType::Essay->value,
            QuestionType::FileUpload->value,
        ];

        $questions = [];
        foreach ($questionTypes as $order => $type) {
            $questionId = $this->createQuestion($assignmentId, $type, $order + 1);
            $questions[] = ['id' => $questionId, 'type' => $type, 'max_score' => 25];
            $stats['questions']++;
        }

        $selectedUsers = array_slice($this->userIds, 0, min(5, count($this->userIds)));

        foreach ($selectedUsers as $userId) {
            $submissionResult = $this->createSubmissionWithAnswersAndGrade(
                $assignmentId,
                $userId,
                $questions,
                $courseId
            );
            $stats['submissions'] += $submissionResult['submissions'];
            $stats['answers'] += $submissionResult['answers'];
            $stats['grades'] += $submissionResult['grades'];
        }

        return $stats;
    }

    private function createQuestion(int $assignmentId, string $type, int $order): int
    {
        $questionData = [
            'assignment_id' => $assignmentId,
            'type' => $type,
            'content' => $this->getQuestionContent($type),
            'weight' => 1.0,
            'order' => $order,
            'max_score' => 25,
            'options' => null,
            'answer_key' => null,
            'max_file_size' => null,
            'allowed_file_types' => null,
            'allow_multiple_files' => false,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];

        switch ($type) {
            case QuestionType::MultipleChoice->value:
                $options = [];
                for ($i = 0; $i < 4; $i++) {
                    $options[] = [
                        'id' => $this->pregenUuids[array_rand($this->pregenUuids)],
                        'label' => $this->pregenWords[array_rand($this->pregenWords)] . ' ' . $this->pregenWords[array_rand($this->pregenWords)],
                    ];
                }
                $questionData['options'] = json_encode($options);
                $questionData['answer_key'] = json_encode(['correct_option' => 0]);
                break;

            case QuestionType::Essay->value:
                $questionData['answer_key'] = json_encode(['keywords' => [$this->pregenWords[array_rand($this->pregenWords)]]]);
                break;

            case QuestionType::FileUpload->value:
                $questionData['max_file_size'] = 10485760;
                $questionData['allowed_file_types'] = json_encode(['pdf', 'docx', 'zip']);
                $questionData['allow_multiple_files'] = true;
                break;
        }

        return DB::table('assignment_questions')->insertGetId($questionData);
    }

    private function createSubmissionWithAnswersAndGrade(
        int $assignmentId,
        int $userId,
        array $questions,
        int $courseId
    ): array {
        $stats = ['submissions' => 0, 'answers' => 0, 'grades' => 0];

        $states = [
            SubmissionState::Graded->value,
            SubmissionState::PendingManualGrading->value,
            SubmissionState::Released->value,
        ];
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
            'is_late' => rand(1, 100) <= 10,
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
                'graded_by' => $this->userIds[0],
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
            case QuestionType::MultipleChoice->value:
                $answerData['selected_options'] = json_encode([$this->pregenUuids[array_rand($this->pregenUuids)]]);
                $answerData['is_auto_graded'] = true;
                if ($state !== SubmissionState::PendingManualGrading->value) {
                    $answerData['score'] = rand(0, 1) === 1 ? $question['max_score'] : 0;
                }
                break;

            case QuestionType::Essay->value:
                $answerData['content'] = $this->pregenParagraphs[array_rand($this->pregenParagraphs)] . "\n\n" .
                    $this->pregenParagraphs[array_rand($this->pregenParagraphs)];
                if ($state !== SubmissionState::PendingManualGrading->value) {
                    $answerData['score'] = rand(15, $question['max_score']);
                    $answerData['feedback'] = $this->pregenSentences[array_rand($this->pregenSentences)];
                }
                break;

            case QuestionType::FileUpload->value:
                $answerData['file_paths'] = json_encode([
                    'uploads/' . $this->pregenWords[array_rand($this->pregenWords)] . '_' . rand(1000, 9999) . '.pdf',
                    'uploads/' . $this->pregenWords[array_rand($this->pregenWords)] . '_' . rand(1000, 9999) . '.docx',
                ]);
                if ($state !== SubmissionState::PendingManualGrading->value) {
                    $answerData['score'] = rand(18, $question['max_score']);
                    $answerData['feedback'] = $this->pregenSentences[array_rand($this->pregenSentences)];
                }
                break;
        }

        DB::table('answers')->insertOrIgnore($answerData);

        return ['score' => $answerData['score']];
    }

    private function getQuestionContent(string $type): string
    {
        $templates = [
            QuestionType::MultipleChoice->value => [
                'Which of the following best describes the concept of dependency injection?',
                'What is the primary purpose of unit testing in software development?',
                'Which design pattern is most suitable for managing object creation?',
                'What does SOLID stand for in software development principles?',
            ],
            QuestionType::Essay->value => [
                'Explain the importance of code testing and describe different types of testing strategies used in modern software development.',
                'Discuss the Model-View-Controller (MVC) pattern and explain its role in web application architecture.',
                'Compare and contrast REST API with GraphQL. When would you choose one over the other?',
                'Describe the SOLID principles in object-oriented programming with practical examples.',
            ],
            QuestionType::FileUpload->value => [
                'Upload your completed project source code as a ZIP file.',
                'Submit your database schema diagram in PDF or image format.',
                'Upload your test case results in Excel or CSV format.',
                'Submit your design mockups in PNG, JPG, or PDF format.',
            ],
        ];

        $options = $templates[$type] ?? $templates[QuestionType::Essay->value];
        return $options[array_rand($options)];
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
