<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Contracts\Repositories\QuestionRepositoryInterface;
use Modules\Learning\Contracts\Services\SubmissionServiceInterface;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;
use Modules\Learning\Services\Support\SubmissionFinder;
use Modules\Learning\Services\Support\SubmissionLifecycleProcessor;
use Modules\Schemes\Services\PrerequisiteService;

class SubmissionService implements SubmissionServiceInterface
{
    public function __construct(
        private readonly SubmissionFinder $finder,
        private readonly SubmissionLifecycleProcessor $lifecycleProcessor,
        private readonly QuestionRepositoryInterface $questionRepository,
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function create(Assignment $assignment, int $userId, array $data): Submission
    {
        return $this->lifecycleProcessor->create($assignment, $userId, $data);
    }

    public function startSubmission(int $assignmentId, int $studentId): Submission
    {
        return $this->lifecycleProcessor->startSubmission($assignmentId, $studentId);
    }

    public function update(Submission $submission, array $data): Submission
    {
        return $this->lifecycleProcessor->update($submission, $data);
    }

    public function submitAnswers(int $submissionId, array $answers): Submission
    {
        return $this->lifecycleProcessor->submitAnswers($submissionId, $answers, $this->questionRepository);
    }

    public function delete(Submission $submission): bool
    {
        return $this->lifecycleProcessor->delete($submission);
    }

    public function listForAssignment(Assignment $assignment, User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listForAssignment($assignment, $user, $filters);
    }

    public function listForAssignmentForIndex(Assignment $assignment, User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listForAssignmentForIndex($assignment, $user, $filters);
    }

    public function getSubmission(int $submissionId, array $filters = []): Submission
    {
        return $this->finder->getSubmission($submissionId, $filters);
    }

    public function getSubmissionDetail(Submission $submission, ?int $userId): array
    {
        return $this->finder->getSubmissionDetail($submission, $userId);
    }

    public function getSubmissionQuestions(Submission $submission): Collection
    {
        return $this->finder->getSubmissionQuestions($submission);
    }

    public function getSubmissionQuestionsPaginated(Submission $submission, int $perPage = 1): LengthAwarePaginator
    {
        return $this->finder->getSubmissionQuestionsPaginated($submission, $perPage);
    }

    public function searchSubmissions(string $query, array $filters = [], array $options = []): array
    {
        return $this->finder->searchSubmissions($query, $filters, $options);
    }

    public function listByAssignment(Assignment $assignment, array $filters = [])
    {
        return $this->finder->listByAssignment($assignment, $filters);
    }

    public function getHighestScoreSubmission(int $assignmentId, int $studentId): ?Submission
    {
        return $this->finder->getHighestScoreSubmission($assignmentId, $studentId);
    }

    public function getSubmissionsWithHighestMarked(int $assignmentId, int $studentId): Collection
    {
        return $this->finder->getSubmissionsWithHighestMarked($assignmentId, $studentId);
    }

    public function updateSubmissionScore(Submission $submission, float $score): Submission
    {
        return $this->lifecycleProcessor->updateSubmissionScore($submission, $score);
    }

    public function grade(Submission $submission, int $score, int $gradedBy, ?string $feedback = null): Submission
    {
        return $this->lifecycleProcessor->grade($submission, $score, $gradedBy, $feedback);
    }

    public function checkAndDispatchNewHighScore(Submission $submission): void
    {
        $this->lifecycleProcessor->checkAndDispatchNewHighScore($submission);
    }

    public function getQuestionsForStudent(Submission $submission, int $page): array
    {
        $questions = $this->finder->getSubmissionQuestions($submission);
        $total = $questions->count();

        if ($page < 1 || $page > $total) {
            throw new \InvalidArgumentException(__('messages.submissions.invalid_page'));
        }

        $question = $questions->get($page - 1);

        return [
            'question' => $question,
            'meta' => [
                'pagination' => [
                    'current_page' => $page,
                    'total' => $total,
                    'has_next' => $page < $total,
                    'has_prev' => $page > 1,
                ],
            ],
        ];
    }

    public function validateStudentAssignmentAccess(Assignment $assignment, int $userId): array
    {
        $assignment->load('unit.course');

        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $assignment->unit->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (! $enrollment) {
            return ['accessible' => false, 'reason' => 'not_enrolled'];
        }

        $prerequisiteCheck = $this->prerequisiteService->checkAssignmentAccess($assignment, $userId);

        if (! $prerequisiteCheck['accessible']) {
            return [
                'accessible' => false,
                'reason' => 'prerequisites_not_met',
                'missing_count' => count($prerequisiteCheck['missing']),
            ];
        }

        return ['accessible' => true];
    }
}
