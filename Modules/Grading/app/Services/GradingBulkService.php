<?php

declare(strict_types=1);

namespace Modules\Grading\Services;

use Illuminate\Support\Facades\DB;
use Modules\Grading\DTOs\BulkOperationDTO;
use Modules\Grading\Jobs\BulkApplyFeedbackJob;
use Modules\Grading\Jobs\BulkReleaseGradesJob;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Models\Submission;
use Modules\Grading\Services\GradingEntryService;

class GradingBulkService
{
    public function __construct(
        private readonly GradingEntryService $entryService
    ) {}

    public function handleBulkRelease(BulkOperationDTO $data): void
    {
        $this->validateBulkReleaseGrades($data->submissionIds);

        if ($data->async) {
            BulkReleaseGradesJob::dispatch($data->submissionIds, $data->performerId);
        } else {
            $this->bulkReleaseGrades($data->submissionIds, $data->performerId);
        }
    }

    public function handleBulkFeedback(BulkOperationDTO $data): void
    {
        $this->validateBulkApplyFeedback($data->submissionIds);

        if ($data->async) {
            BulkApplyFeedbackJob::dispatch(
                $data->submissionIds, 
                (string) $data->feedback, 
                $data->performerId
            );
        } else {
            $this->bulkApplyFeedback(
                $data->submissionIds, 
                (string) $data->feedback, 
                $data->performerId
            );
        }
    }

    public function bulkReleaseGrades(array $submissionIds, ?int $performerId): int
    {
        $count = 0;
        foreach ($submissionIds as $id) {
            try {
                $this->entryService->releaseGrade((int) $id);
                $count++;
            } catch (\Exception $e) {
                continue;
            }
        }
        return $count;
    }

    public function bulkApplyFeedback(array $submissionIds, string $feedback, ?int $performerId): int
    {
        $count = 0;
        foreach ($submissionIds as $id) {
            try {
                $submission = Submission::with('grade')->find($id);
                if ($submission && $submission->grade) {
                    $submission->grade->update(['feedback' => $feedback]);
                    $count++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return $count;
    }

    private function validateBulkReleaseGrades(array $submissionIds): void
    {
        $invalidCount = Submission::whereIn('id', $submissionIds)
            ->where(function ($q) {
                $q->where('state', '!=', SubmissionState::Graded->value)
                  ->where('state', '!=', SubmissionState::Released->value); 
            })
            ->count();

        if ($invalidCount > 0) {
            throw new \InvalidArgumentException(__('messages.grading.bulk_release_invalid_state'));
        }
        
        $draftCount = \Modules\Grading\Models\Grade::whereIn('submission_id', $submissionIds)
            ->where('is_draft', true)
            ->count();
            
        if ($draftCount > 0) {
            throw new \InvalidArgumentException(__('messages.grading.bulk_release_draft_grades'));
        }
    }

    private function validateBulkApplyFeedback(array $submissionIds): void
    {
        $count = Submission::whereIn('id', $submissionIds)->count();
        if ($count !== count($submissionIds)) {
             throw new \InvalidArgumentException(__('messages.grading.invalid_submission_ids'));
        }
    }
}
