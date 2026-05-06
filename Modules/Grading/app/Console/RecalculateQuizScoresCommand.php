<?php

declare(strict_types=1);

namespace Modules\Grading\Console;

use Illuminate\Console\Command;
use Modules\Grading\Services\GradingEntryService;
use Modules\Learning\Enums\QuizGradingStatus;
use Modules\Learning\Models\QuizSubmission;

class RecalculateQuizScoresCommand extends Command
{
    protected $signature = 'grading:recalculate-quiz-scores
                            {--submission= : Recalculate a specific submission by ID}
                            {--quiz= : Recalculate all submissions for a specific quiz ID}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Recalculate quiz submission scores to fix manually-graded score inflation bug.';

    public function __construct(private readonly GradingEntryService $gradingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $submissionId = $this->option('submission') ? (int) $this->option('submission') : null;
        $quizId       = $this->option('quiz') ? (int) $this->option('quiz') : null;

        $query = QuizSubmission::whereHas('answers', fn ($q) => $q->where('is_auto_graded', 0))
            ->whereIn('grading_status', [
                QuizGradingStatus::Graded->value,
                QuizGradingStatus::Released->value,
                QuizGradingStatus::PartiallyGraded->value,
            ]);

        if ($submissionId !== null) {
            $query->where('id', $submissionId);
        }

        if ($quizId !== null) {
            $query->where('quiz_id', $quizId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No eligible quiz submissions found.');
            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $scope = $submissionId ? "submission #{$submissionId}"
                : ($quizId ? "quiz #{$quizId}" : 'all manually-graded quiz submissions');

            $this->warn("About to recalculate scores for {$total} submission(s) ({$scope}).");

            if (! $this->confirm('Do you wish to continue?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $bar       = $this->output->createProgressBar($total);
        $fixed     = 0;
        $unchanged = 0;
        $errors    = 0;

        $query->with(['quiz.questions', 'answers.question'])
            ->chunkById(50, function ($submissions) use ($bar, &$fixed, &$unchanged, &$errors) {
                foreach ($submissions as $submission) {
                    $oldScore = (float) ($submission->score ?? 0);

                    try {
                        $updated  = $this->gradingService->recalculateQuizScore($submission);
                        $newScore = (float) ($updated->score ?? 0);

                        if (abs($oldScore - $newScore) >= 0.01) {
                            $fixed++;
                            $this->newLine();
                            $this->line(sprintf(
                                '  [Fixed] Submission #%d — score: %.2f → %.2f',
                                $submission->id,
                                $oldScore,
                                $newScore
                            ));
                        } else {
                            $unchanged++;
                        }
                    } catch (\Throwable $e) {
                        $errors++;
                        $this->newLine();
                        $this->error(sprintf(
                            '  [Error] Submission #%d — %s',
                            $submission->id,
                            $e->getMessage()
                        ));
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Fixed: {$fixed} | Unchanged: {$unchanged} | Errors: {$errors}");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
