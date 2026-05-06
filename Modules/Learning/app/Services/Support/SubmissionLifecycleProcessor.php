<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

class SubmissionLifecycleProcessor
{
    public function __construct(
        private readonly SubmissionCreationProcessor $creationProcessor,
        private readonly SubmissionCompletionProcessor $completionProcessor
    ) {}

    public function create(Assignment $assignment, int $userId, array $data): Submission
    {
        return $this->creationProcessor->create($assignment, $userId, $data);
    }

    public function startSubmission(
        int $assignmentId,
        int $studentId
    ): Submission {
        return $this->creationProcessor->startSubmission($assignmentId, $studentId);
    }

    public function update(Submission $submission, array $data): Submission
    {
        return $this->completionProcessor->update($submission, $data);
    }

    public function grade(Submission $submission, int $score, int $gradedBy, ?string $feedback = null): Submission
    {
        return $this->completionProcessor->grade($submission, $score, $gradedBy, $feedback);
    }

    public function submitAnswers(int $submissionId, array $answers): Submission
    {
        return $this->completionProcessor->submitAnswers($submissionId, $answers);
    }

    public function updateSubmissionScore(Submission $submission, float $score): Submission
    {
        return $this->completionProcessor->updateSubmissionScore($submission, $score);
    }

    public function delete(Submission $submission): bool
    {
        return $this->completionProcessor->delete($submission);
    }

    public function checkAndDispatchNewHighScore(Submission $submission): void
    {
        $this->completionProcessor->checkAndDispatchNewHighScore($submission);
    }
}
