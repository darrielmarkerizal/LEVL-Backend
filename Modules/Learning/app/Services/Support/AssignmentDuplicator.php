<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Illuminate\Support\Facades\DB;
use Modules\Learning\Contracts\Repositories\AssignmentRepositoryInterface;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Exceptions\AssignmentException;
use Modules\Learning\Models\Assignment;

class AssignmentDuplicator
{
    public function __construct(
        private readonly AssignmentRepositoryInterface $repository
    ) {
    }

    public function duplicateAssignment(int $assignmentId, int $userId, array $overrides = []): Assignment
    {
        return DB::transaction(function () use ($assignmentId, $userId, $overrides) {
            $overrides['created_by'] = $userId;
            $original = $this->repository->findForDuplication($assignmentId);

            if (!$original) {
                throw AssignmentException::notFound();
            }

            $assignmentData = $this->prepareAssignmentDataForDuplication($original, $overrides);

            $newAssignment = $this->repository->create($assignmentData);

            $this->duplicateAttachments($original, $newAssignment);

            return $newAssignment->fresh(['unit', 'unit.course', 'creator', 'media']);
        });
    }

    private function prepareAssignmentDataForDuplication(Assignment $original, ?array $overrides): array
    {
        return [
            'unit_id' => $overrides['unit_id'] ?? $original->unit_id,
            'created_by' => $overrides['created_by'] ?? $original->created_by,
            'title' => $overrides['title'] ?? $original->title . ' (Copy)',
            'description' => $overrides['description'] ?? $original->description,
            'submission_type' => $original->submission_type?->value ?? $original->getRawOriginal('submission_type'),
            'max_score' => $overrides['max_score'] ?? $original->max_score,
            'passing_grade' => $original->passing_grade,
            'review_mode' => $overrides['review_mode'] ?? ($original->review_mode?->value ?? $original->getRawOriginal('review_mode')),
            'status' => $overrides['status'] ?? AssignmentStatus::Draft->value,
            'order' => $original->order,
        ];
    }

    private function duplicateAttachments(Assignment $original, Assignment $newAssignment): void
    {
        $attachments = $original->getMedia('attachments');

        foreach ($attachments as $media) {
            $media->copy($newAssignment, 'attachments');
        }
    }
}

