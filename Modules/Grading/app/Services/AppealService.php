<?php

declare(strict_types=1);

namespace Modules\Grading\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Grading\Contracts\Repositories\AppealRepositoryInterface;
use Modules\Grading\Contracts\Services\AppealServiceInterface;
use Modules\Grading\Enums\AppealStatus;
use Modules\Grading\Models\Appeal;
use Modules\Learning\Models\Submission;

class AppealService implements AppealServiceInterface
{
    public function __construct(
        private readonly AppealRepositoryInterface $appealRepository
    ) {}

    public function submitAppeal(int $submissionId, int $studentId, string $reason, array $files = []): Appeal
    {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException(__('messages.appeals.reason_required'));
        }

        $submission = Submission::with(['assignment', 'user'])->findOrFail($submissionId);

        if ($submission->user_id !== $studentId) {
            throw new \Illuminate\Auth\Access\AuthorizationException(__('messages.appeals.not_owner'));
        }

        $existingAppeal = $this->appealRepository->findBySubmission($submissionId);
        if ($existingAppeal) {
            throw new InvalidArgumentException(__('messages.appeals.already_exists'));
        }

        if (! $this->isEligibleForAppeal($submission)) {
            throw new InvalidArgumentException(
                __('messages.appeals.not_eligible')
            );
        }

        $documents = [];

        return DB::transaction(function () use ($submissionId, $reason, $files, $documents) {
            try {
                if (! empty($files['documents'])) {
                    foreach ($files['documents'] as $file) {
                        $path = $file->store('appeals/'.$submissionId, 'local');
                        $documents[] = [
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize(),
                        ];
                    }
                }

                $appeal = $this->appealRepository->create([
                    'submission_id' => $submissionId,
                    'student_id' => $studentId,
                    'reason' => trim($reason),
                    'supporting_documents' => $documents,
                    'status' => AppealStatus::Pending,
                    'submitted_at' => now(),
                ]);

                $this->notifyInstructorsOfAppeal($appeal);

                return $appeal;
            } catch (\Exception $e) {
                foreach ($documents as $doc) {
                    if (isset($doc['path']) && is_string($doc['path'])) {
                        Storage::disk('local')->delete($doc['path']);
                    }
                }

                throw $e;
            }
        });
    }

    public function approveAppeal(int $appealId, int $instructorId): void
    {
        $appeal = $this->appealRepository->findById($appealId);

        if (! $appeal) {
            throw new InvalidArgumentException('Appeal not found');
        }

        if ($appeal->status->isDecided()) {
            throw new InvalidArgumentException('Appeal has already been decided');
        }

        $appeal->approve($instructorId);
        $this->grantDeadlineExtension($appeal);
        $this->notifyStudentOfDecision($appeal);
    }

    public function denyAppeal(int $appealId, int $instructorId, string $reason): void
    {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException(__('messages.appeals.denial_reason_required'));
        }

        $appeal = $this->appealRepository->findById($appealId);

        if (! $appeal) {
            throw new InvalidArgumentException(__('messages.appeals.not_found'));
        }

        if ($appeal->status->isDecided()) {
            throw new InvalidArgumentException(__('messages.appeals.already_decided'));
        }

        $appeal->deny($instructorId, trim($reason));
        $this->notifyStudentOfDecision($appeal);
    }

    public function getPendingAppeals(int $instructorId): Collection
    {
        return $this->appealRepository->findPendingForInstructor($instructorId);
    }

    public function getAppeals(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return \Spatie\QueryBuilder\QueryBuilder::for(Appeal::class)
            ->with(['submission.assignment', 'student', 'reviewer'])
            ->allowedFilters([
                'status',
                'student_id',
                'submission_id',
                'reviewer_id',
                \Spatie\QueryBuilder\AllowedFilter::scope('pending'),
                \Spatie\QueryBuilder\AllowedFilter::scope('approved'),
                \Spatie\QueryBuilder\AllowedFilter::scope('denied'),
            ])
            ->allowedSorts(['submitted_at', 'decided_at', 'status'])
            ->defaultSort('-submitted_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function getAppealForUser(Appeal $appeal, int $userId): Appeal
    {
        $user = \Modules\Auth\Models\User::findOrFail($userId);
        
        $isOwner = $appeal->student_id === $userId;
        $isInstructor = $user->hasRole('Admin') ||
            $user->hasRole('Instructor') ||
            $user->hasRole('Superadmin');

        if (! $isOwner && ! $isInstructor) {
            throw new \Illuminate\Auth\Access\AuthorizationException(__('messages.appeals.not_authorized'));
        }

        $appeal->load(['submission.assignment', 'student', 'reviewer']);
        
        return $appeal;
    }

    private function isEligibleForAppeal(Submission $submission): bool
    {
        $assignment = $submission->assignment;

        if (! $assignment) {
            return false;
        }

        if (! $assignment->deadline_at) {
            return false;
        }

        if ($submission->is_late) {
            return true;
        }

        return $assignment->isPastTolerance();
    }

    private function grantDeadlineExtension(Appeal $appeal): void
    {
        $submission = $appeal->submission;

        if (! $submission) {
            return;
        }

        $submission->update([
            'is_late' => false,
        ]);
    }

    private function notifyInstructorsOfAppeal(Appeal $appeal): void
    {
        $submission = $appeal->submission;
        if (! $submission || ! $submission->assignment) {
            return;
        }

        $instructorId = $submission->assignment->created_by;

        if ($instructorId) {
            event(new \Modules\Grading\Events\AppealSubmitted($appeal, $instructorId));
        }
    }

    private function notifyStudentOfDecision(Appeal $appeal): void
    {
        event(new \Modules\Grading\Events\AppealDecided($appeal));
    }
}
