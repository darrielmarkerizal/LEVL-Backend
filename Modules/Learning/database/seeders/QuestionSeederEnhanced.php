<?php

declare(strict_types=1);

namespace Modules\Learning\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Learning\Enums\QuestionType;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

class QuestionSeederEnhanced extends Seeder
{
    private array $multipleChoiceQuestions = [
        [
            'content' => 'What is the time complexity of binary search algorithm?',
            'options' => [
                ['id' => 'opt_1', 'label' => 'O(n)'],
                ['id' => 'opt_2', 'label' => 'O(log n)'],
                ['id' => 'opt_3', 'label' => 'O(n²)'],
                ['id' => 'opt_4', 'label' => 'O(1)'],
            ],
            'answer_key' => ['opt_2'],
        ],
        [
            'content' => 'Which HTTP method is idempotent?',
            'options' => [
                ['id' => 'opt_1', 'label' => 'POST'],
                ['id' => 'opt_2', 'label' => 'PATCH'],
                ['id' => 'opt_3', 'label' => 'PUT'],
                ['id' => 'opt_4', 'label' => 'DELETE'],
            ],
            'answer_key' => ['opt_3'],
        ],
        [
            'content' => 'What is the default port for HTTPS?',
            'options' => [
                ['id' => 'opt_1', 'label' => '80'],
                ['id' => 'opt_2', 'label' => '443'],
                ['id' => 'opt_3', 'label' => '8080'],
                ['id' => 'opt_4', 'label' => '3000'],
            ],
            'answer_key' => ['opt_2'],
        ],
    ];

    private array $checkboxQuestions = [
        [
            'content' => 'Which of the following are valid HTTP status codes for success? (Select all that apply)',
            'options' => [
                ['id' => 'opt_1', 'label' => '200 OK'],
                ['id' => 'opt_2', 'label' => '201 Created'],
                ['id' => 'opt_3', 'label' => '404 Not Found'],
                ['id' => 'opt_4', 'label' => '204 No Content'],
            ],
            'answer_key' => ['opt_1', 'opt_2', 'opt_4'],
        ],
        [
            'content' => 'Select all valid CSS display properties:',
            'options' => [
                ['id' => 'opt_1', 'label' => 'block'],
                ['id' => 'opt_2', 'label' => 'flex'],
                ['id' => 'opt_3', 'label' => 'grid'],
                ['id' => 'opt_4', 'label' => 'float'],
            ],
            'answer_key' => ['opt_1', 'opt_2', 'opt_3'],
        ],
    ];

    private array $essayQuestions = [
        'Explain the concept of dependency injection and its benefits in software development.',
        'Describe the differences between SQL and NoSQL databases. When would you use each?',
        'What are the SOLID principles? Explain each principle with a practical example.',
        'Discuss the advantages and disadvantages of microservices architecture.',
        'Explain how REST API differs from GraphQL. What are the use cases for each?',
        'Describe the Model-View-Controller (MVC) pattern and its role in web development.',
        'What is the purpose of version control systems? Explain Git workflow best practices.',
        'Discuss the importance of code testing and different types of testing strategies.',
    ];

    private array $fileUploadQuestions = [
        'Upload your completed project source code (ZIP file)',
        'Submit your design mockups (PNG, JPG, or PDF format)',
        'Upload the database schema diagram (PDF or image)',
        'Submit your API documentation (Markdown or PDF)',
        'Upload screenshots of your working application',
        'Submit your test case results (Excel or CSV)',
    ];

    public function run(): void
    {
        $this->command->info("\n╔══════════════════════════════════════════════════╗");
        $this->command->info('║      ❓ QUESTION & ANSWER SEEDER                 ║');
        $this->command->info("╚══════════════════════════════════════════════════╝\n");

        if (! Assignment::exists()) {
            $this->command->warn('⚠️  No assignments found. Skipping question seeding.');

            return;
        }

        if (! Submission::exists()) {
            $this->command->warn('⚠️  No submissions found. Skipping answer seeding.');

            return;
        }

        $questionCount = $this->createQuestions();
        $answerCount = $this->createAnswers();

        $this->updateSubmissionStates();

        $this->command->info("\n╔══════════════════════════════════════════════════╗");
        $this->command->info('║   ✅ QUESTION SEEDING COMPLETED!                 ║');
        $this->command->info("║   Questions: {$questionCount}                               ║");
        $this->command->info("║   Answers: {$answerCount}                              ║");
        $this->command->info("╚══════════════════════════════════════════════════╝\n");
    }

    private function createQuestions(): int
    {
        $this->command->info("🔨 Creating questions for assignments...\n");

        $questionCount = 0;
        $questions = [];

        Assignment::withCount('questions')->chunkById(1000, function ($assignments) use (&$questionCount, &$questions) {
            foreach ($assignments as $assignment) {
                $numQuestions = rand(3, 8);

                for ($i = 0; $i < $numQuestions; $i++) {
                    $questionType = $this->getWeightedQuestionType();
                    $order = $i + 1;

                    $questionData = $this->buildQuestionData($questionType, $assignment->id, $order);

                    $questions[] = $questionData;
                    $questionCount++;

                    if (count($questions) >= 1000) {
                        DB::table('assignment_questions')->insertOrIgnore($questions);
                        $this->command->info("  ✅ Inserted {$questionCount} questions");
                        $questions = [];
                    }
                }
            }
        });

        if (! empty($questions)) {
            DB::table('assignment_questions')->insertOrIgnore($questions);
            $this->command->info("  ✅ Inserted {$questionCount} questions (final batch)");
        }

        $typeDistribution = DB::table('assignment_questions')
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $this->command->info("\n📊 Question Type Distribution:");
        foreach ($typeDistribution as $type => $count) {
            $percent = round(($count / $questionCount) * 100, 1);
            $this->command->info("  - {$type}: {$count} ({$percent}%)");
        }

        return $questionCount;
    }

    private function createAnswers(): int
    {
        $this->command->info("\n📝 Creating student answers...\n");

        $answerCount = 0;
        $answers = [];

        Submission::with('assignment.questions')->chunkById(1000, function ($submissions) use (&$answerCount, &$answers) {
            foreach ($submissions as $submission) {
                if (! $submission->assignment || $submission->assignment->questions->isEmpty()) {
                    continue;
                }

                foreach ($submission->assignment->questions as $question) {
                    if (rand(1, 100) <= 20) {
                        continue;
                    }

                    $answerData = $this->buildAnswerData($question, $submission->id);

                    $answers[] = $answerData;
                    $answerCount++;

                    if (count($answers) >= 1000) {
                        DB::table('answers')->insertOrIgnore($answers);
                        $this->command->info("  ✅ Inserted {$answerCount} answers");
                        $answers = [];
                    }
                }
            }
        });

        if (! empty($answers)) {
            DB::table('answers')->insertOrIgnore($answers);
            $this->command->info("  ✅ Inserted {$answerCount} answers (final batch)");
        }

        $this->command->info("\n📊 Answer Summary:");
        $this->command->info("  Total Answers: {$answerCount}");
        $this->command->info('  Completion Rate: ~80% (20% skipped for realism)');

        return $answerCount;
    }

    private function getWeightedQuestionType(): string
    {
        $weights = [
            QuestionType::MultipleChoice->value => 40,
            QuestionType::Checkbox->value => 25,
            QuestionType::Essay->value => 20,
            QuestionType::FileUpload->value => 15,
        ];

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $type;
            }
        }

        return QuestionType::Essay->value;
    }

    private function buildQuestionData(string $type, int $assignmentId, int $order): array
    {
        $baseData = [
            'assignment_id' => $assignmentId,
            'type' => $type,
            'order' => $order,
            'weight' => round(fake()->randomFloat(2, 1, 5), 2),
            'max_score' => $this->resolveMaxScoreByType($type),
            'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'updated_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ];

        $baseData['options'] = null;
        $baseData['answer_key'] = null;
        $baseData['max_file_size'] = null;
        $baseData['allowed_file_types'] = null;
        $baseData['allow_multiple_files'] = false;

        return match ($type) {
            QuestionType::MultipleChoice->value => $this->buildMultipleChoiceQuestion($baseData),
            QuestionType::Checkbox->value => $this->buildCheckboxQuestion($baseData),
            QuestionType::Essay->value => $this->buildEssayQuestion($baseData),
            QuestionType::FileUpload->value => $this->buildFileUploadQuestion($baseData),
            default => $baseData,
        };
    }

    private function buildMultipleChoiceQuestion(array $baseData): array
    {
        $questionPool = $this->multipleChoiceQuestions;
        $selected = $questionPool[array_rand($questionPool)];

        $baseData['content'] = $selected['content'];
        $baseData['options'] = json_encode($selected['options']);
        $baseData['answer_key'] = json_encode($selected['answer_key']);

        return $baseData;
    }

    private function buildCheckboxQuestion(array $baseData): array
    {
        $questionPool = $this->checkboxQuestions;
        $selected = $questionPool[array_rand($questionPool)];

        $baseData['content'] = $selected['content'];
        $baseData['options'] = json_encode($selected['options']);
        $baseData['answer_key'] = json_encode($selected['answer_key']);

        return $baseData;
    }

    private function buildEssayQuestion(array $baseData): array
    {
        $baseData['content'] = $this->essayQuestions[array_rand($this->essayQuestions)];

        return $baseData;
    }

    private function buildFileUploadQuestion(array $baseData): array
    {
        $baseData['content'] = $this->fileUploadQuestions[array_rand($this->fileUploadQuestions)];
        $baseData['max_file_size'] = 10000000;
        $baseData['allowed_file_types'] = json_encode(['pdf', 'docx', 'txt', 'png', 'jpg', 'zip']);
        $baseData['allow_multiple_files'] = (bool) rand(0, 1);

        return $baseData;
    }

    private function buildAnswerData(object $question, int $submissionId): array
    {
        $baseData = [
            'submission_id' => $submissionId,
            'question_id' => $question->id,
            'score' => null,
            'is_auto_graded' => false,
            'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            'updated_at' => SeederDate::randomPastDateTimeBetween(1, 180),
        ];

        return match ($question->type) {
            QuestionType::MultipleChoice->value => $this->buildMultipleChoiceAnswer($baseData, $question),
            QuestionType::Checkbox->value => $this->buildCheckboxAnswer($baseData, $question),
            QuestionType::Essay->value => $this->buildEssayAnswer($baseData),
            QuestionType::FileUpload->value => $this->buildFileUploadAnswer($baseData),
            default => $baseData,
        };
    }

    private function buildMultipleChoiceAnswer(array $baseData, object $question): array
    {
        $options = json_decode($question->options, true);
        if ($options) {
            $baseData['selected_options'] = json_encode([
                $options[rand(0, count($options) - 1)]['id'],
            ]);
        }

        return $baseData;
    }

    private function buildCheckboxAnswer(array $baseData, object $question): array
    {
        $options = json_decode($question->options, true);
        if ($options) {
            $numSelections = rand(1, count($options));
            $selectedKeys = array_rand($options, $numSelections);
            $selectedKeys = is_array($selectedKeys) ? $selectedKeys : [$selectedKeys];

            $selectedIds = array_map(fn ($key) => $options[$key]['id'], $selectedKeys);
            $baseData['selected_options'] = json_encode($selectedIds);
        }

        return $baseData;
    }

    private function buildEssayAnswer(array $baseData): array
    {
        $paragraphs = [
            'This concept is fundamental to modern software development. It allows developers to write more maintainable and testable code by reducing tight coupling between components.',
            'In my experience, applying this principle has significantly improved code quality. The separation of concerns makes it easier to understand and modify individual components without affecting others.',
            'From a practical standpoint, this approach offers several advantages including better code organization, easier testing, and improved scalability. However, it may introduce some complexity in smaller projects.',
            'After researching various implementations, I found that the key to success is understanding the underlying principles and adapting them to specific project requirements rather than blindly following patterns.',
        ];

        $numParagraphs = rand(2, 4);
        $selectedParagraphs = [];

        for ($i = 0; $i < $numParagraphs; $i++) {
            $selectedParagraphs[] = $paragraphs[array_rand($paragraphs)];
        }

        $baseData['content'] = implode("\n\n", $selectedParagraphs);

        return $baseData;
    }

    private function buildFileUploadAnswer(array $baseData): array
    {
        $fileTypes = ['pdf', 'docx', 'png', 'jpg', 'zip'];
        $numFiles = rand(1, 3);

        $filePaths = [];
        for ($i = 0; $i < $numFiles; $i++) {
            $ext = $fileTypes[array_rand($fileTypes)];
            $filePaths[] = Str::uuid().'.'.$ext;
        }

        $baseData['file_paths'] = json_encode($filePaths);
        $baseData['file_metadata'] = json_encode([
            'total_size' => rand(100000, 5000000),
            'file_count' => $numFiles,
        ]);

        return $baseData;
    }

    private function updateSubmissionStates(): void
    {
        $this->command->info("\n🔄 Updating submission states for grading queue...");

        $allSubmissions = DB::table('submissions')
            ->select('id', 'status')
            ->get();

        $updated = 0;
        foreach ($allSubmissions as $submission) {
            $newState = $this->resolveSubmissionState((int) $submission->id, (string) $submission->status);
            if (! $newState) {
                continue;
            }

            DB::table('submissions')
                ->where('id', $submission->id)
                ->update(['state' => $newState]);

            $updated++;
        }

        $this->command->info("  ✅ Updated {$updated} submissions with business-logic states");
    }

    private function resolveMaxScoreByType(string $type): int
    {
        return match ($type) {
            QuestionType::MultipleChoice->value => 10,
            QuestionType::Checkbox->value => 20,
            QuestionType::Essay->value => 50,
            QuestionType::FileUpload->value => 25,
            default => 10,
        };
    }

    private function resolveSubmissionState(int $submissionId, string $status): ?string
    {
        if ($status === 'draft') {
            return 'in_progress';
        }

        if ($status === 'submitted') {
            $hasManualQuestions = DB::table('answers')
                ->join('assignment_questions', 'answers.question_id', '=', 'assignment_questions.id')
                ->where('answers.submission_id', $submissionId)
                ->whereIn('assignment_questions.type', [
                    QuestionType::Essay->value,
                    QuestionType::FileUpload->value,
                ])
                ->exists();

            return $hasManualQuestions ? 'pending_manual_grading' : 'auto_graded';
        }

        if ($status === 'graded') {
            return match ($submissionId % 3) {
                0 => 'auto_graded',
                1 => 'graded',
                default => 'released',
            };
        }

        return null;
    }
}
