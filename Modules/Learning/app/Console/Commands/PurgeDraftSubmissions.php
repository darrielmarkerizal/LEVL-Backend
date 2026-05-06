<?php

declare(strict_types=1);

namespace Modules\Learning\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Enums\SubmissionStatus;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

class PurgeDraftSubmissions extends Command
{
    protected $signature = 'submissions:purge-drafts
                            {assignment : Assignment ID to purge draft submissions for}
                            {--dry-run : Show what would be deleted without changing data}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Delete draft submissions for a specific assignment.';

    public function handle(): int
    {
        $assignmentId = (int) $this->argument('assignment');

        $assignment = Assignment::query()->find($assignmentId);

        if (! $assignment) {
            $this->error("Assignment with ID {$assignmentId} was not found.");

            return self::FAILURE;
        }

        $query = Submission::query()
            ->where('assignment_id', $assignmentId)
            ->where('status', SubmissionStatus::Draft->value);

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info("No draft submissions found for assignment [{$assignment->id}] {$assignment->title}.");

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("[DRY RUN] Would delete {$count} draft submission(s) for assignment [{$assignment->id}] {$assignment->title}.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Delete {$count} draft submission(s) for assignment [{$assignment->id}] {$assignment->title}?")) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $deleted = 0;

        DB::transaction(function () use ($query, &$deleted): void {
            $query->orderBy('id')->chunkById(100, function ($submissions) use (&$deleted): void {
                foreach ($submissions as $submission) {
                    $submission->delete();
                    $deleted++;
                }
            });
        });

        $this->info("Deleted {$deleted} draft submission(s) for assignment [{$assignment->id}] {$assignment->title}.");

        return self::SUCCESS;
    }
}