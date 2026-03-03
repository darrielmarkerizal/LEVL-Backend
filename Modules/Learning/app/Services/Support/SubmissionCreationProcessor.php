<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Enrollments\Contracts\Repositories\EnrollmentRepositoryInterface;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Contracts\Repositories\SubmissionRepositoryInterface;
use Modules\Learning\Contracts\Services\QuestionServiceInterface;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Enums\SubmissionStatus;
use Modules\Learning\Events\SubmissionCreated;
use Modules\Learning\Exceptions\SubmissionException;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Models\Lesson;

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
            $lesson = $assignment->lesson;
            if (! $lesson) {
                throw SubmissionException::notAllowed(__('messages.submissions.assignment_no_lesson'));
            }

            $enrollment = $this->getEnrollmentForLesson($lesson, $userId);
            if (! $enrollment) {
                throw SubmissionException::notAllowed(__('messages.submissions.not_enrolled'));
            }

            if (! $assignment->isAvailable()) {
                throw SubmissionException::notAllowed(__('messages.submissions.assignment_unavailable'));
            }

            $attemptNumber = $this->repository->countAttempts($userId, $assignment->id) + 1;

            $questionSet = $this->generateQuestionSet($assignment->id);

            $submission = $this->repository->create([
                'assignment_id' => $assignment->id,
                'user_id' => $userId,
                'enrollment_id' => $enrollment->id,
                'answer_text' => $data['answer_text'] ?? null,
                'status' => SubmissionStatus::Submitted->value,
                'submitted_at' => Carbon::now(),
                'attempt_number' => $attemptNumber,
                'question_set' => $questionSet,
            ]);

            $this->enrollmentRepository->incrementLessonProgress($enrollment->id, $lesson->id);

            SubmissionCreated::dispatch($submission);

            return $submission->fresh(['assignment', 'user', 'enrollment', 'files', 'grade']);
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
                throw SubmissionException::notAllowed(__('messages.submissions.draft_exists'));
            }

            throw SubmissionException::notAllowed(__('messages.submissions.pending_grading'));
        }

        $questionSet = $this->generateQuestionSet($assignmentId);

        return DB::transaction(function () use ($assignmentId, $studentId, $questionSet) {
            $submission = $this->repository->create([
                'assignment_id' => $assignmentId,
                'user_id' => $studentId,
                'state' => SubmissionState::InProgress->value,
                'status' => SubmissionStatus::Draft->value,
                'question_set' => $questionSet,
            ]);

            return $submission->fresh(['assignment', 'user']);
        });
    }

    private function getEnrollmentForLesson(Lesson $lesson, int $userId): ?Enrollment
    {
        $lesson->loadMissing('unit.course');

        if (! $lesson->unit || ! $lesson->unit->course) {
            return null;
        }

        return $this->enrollmentRepository->findActiveByUserAndCourse($userId, $lesson->unit->course->id);
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
