<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Illuminate\Support\Facades\DB;
use Modules\Enrollments\Contracts\Repositories\EnrollmentRepositoryInterface;
use Modules\Learning\Contracts\Repositories\SubmissionRepositoryInterface;
use Modules\Learning\Contracts\Services\QuestionServiceInterface;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Enums\SubmissionStatus;
use Modules\Learning\Events\SubmissionCreated;
use Modules\Learning\Exceptions\SubmissionException;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

class SubmissionCreationProcessor
{
    public function __construct(
        private readonly SubmissionRepositoryInterface $repository,
        private readonly EnrollmentRepositoryInterface $enrollmentRepository,
        private readonly ?QuestionServiceInterface $questionService = null,
        private readonly ?\Modules\Schemes\Services\PrerequisiteService $prerequisiteService = null,
    ) {}

    public function create(Assignment $assignment, int $userId, array $data): Submission
    {
        if ($this->prerequisiteService) {
            $accessCheck = $this->prerequisiteService->checkAssignmentAccess($assignment, $userId);
            if (! $accessCheck['accessible']) {
                throw SubmissionException::notAllowed(__('messages.assignments.locked_cannot_submit'));
            }
        }

        return DB::transaction(function () use ($assignment, $userId, $data) {
            $assignment->loadMissing('unit.course');

            if (! $assignment->unit || ! $assignment->unit->course) {
                throw SubmissionException::notAllowed(__('messages.submissions.assignment_no_unit'));
            }

            $enrollment = $this->enrollmentRepository->findActiveByUserAndCourse($userId, $assignment->unit->course->id);
            if (! $enrollment) {
                throw SubmissionException::notAllowed(__('messages.submissions.not_enrolled'));
            }

            if (! $assignment->isAvailable()) {
                throw SubmissionException::notAllowed(__('messages.submissions.assignment_unavailable'));
            }

            $existing = Submission::query()
                ->where('assignment_id', $assignment->id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status === SubmissionStatus::Draft) {
                    throw SubmissionException::draftExists($existing->id);
                }
                if ($existing->status === SubmissionStatus::Submitted) {
                    throw SubmissionException::notAllowed(__('messages.submissions.pending_grading_exists'));
                }
                if ($existing->state !== SubmissionState::Released) {
                    throw SubmissionException::notAllowed(__('messages.submissions.grading_not_released'));
                }
            }

            $attemptNumber = $this->repository->countAttempts($userId, $assignment->id) + 1;

            $questionSet = $assignment->isQuiz() ? $this->generateQuestionSet($assignment->id) : null;

            $submission = $this->repository->create([
                'assignment_id' => $assignment->id,
                'user_id' => $userId,
                'enrollment_id' => $enrollment->id,
                'answer_text' => $data['answer_text'] ?? null,
                'status' => SubmissionStatus::Draft->value,
                'state' => SubmissionState::InProgress->value,
                'attempt_number' => $attemptNumber,
                'question_set' => $questionSet,
            ]);

            if (isset($data['files']) && is_array($data['files'])) {
                foreach ($data['files'] as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $submission->addMedia($file)
                            ->toMediaCollection('submission_files');
                    }
                }
            }

            SubmissionCreated::dispatch($submission);

            return $submission->fresh(['assignment', 'user', 'enrollment', 'grade', 'media']);
        });
    }

    public function startSubmission(
        int $assignmentId,
        int $studentId
    ): Submission {
        $assignment = Assignment::findOrFail($assignmentId);

        if ($this->prerequisiteService) {
            $accessCheck = $this->prerequisiteService->checkAssignmentAccess($assignment, $studentId);
            if (! $accessCheck['accessible']) {
                throw SubmissionException::notAllowed(__('messages.assignments.locked_cannot_submit'));
            }
        }

        $pendingSubmission = Submission::where('assignment_id', $assignmentId)
            ->where('user_id', $studentId)
            ->whereIn('status', [SubmissionStatus::Draft->value, SubmissionStatus::Submitted->value])
            ->first();

        if ($pendingSubmission) {
            if ($pendingSubmission->status === SubmissionStatus::Draft->value) {
                throw SubmissionException::draftExists($pendingSubmission->id);
            }

            throw SubmissionException::notAllowed(__('messages.submissions.pending_grading_exists'));
        }

        $questionSet = $assignment->isQuiz() ? $this->generateQuestionSet($assignmentId) : null;
        $attemptNumber = $this->repository->countAttempts($studentId, $assignmentId) + 1;

        return DB::transaction(function () use ($assignmentId, $studentId, $questionSet, $attemptNumber) {
            $submission = $this->repository->create([
                'assignment_id' => $assignmentId,
                'user_id' => $studentId,
                'state' => SubmissionState::InProgress->value,
                'status' => SubmissionStatus::Draft->value,
                'question_set' => $questionSet,
                'attempt_number' => $attemptNumber,
            ]);

            return $submission->fresh(['assignment', 'user']);
        });
    }

    private function generateQuestionSet(int $assignmentId): ?array
    {
        if ($this->questionService) {
            $seed = random_int(1, PHP_INT_MAX);
            $questions = $this->questionService->generateQuestionSet($assignmentId, $seed);

            return $questions->pluck('id')->toArray();
        }

        return null;
    }
}
