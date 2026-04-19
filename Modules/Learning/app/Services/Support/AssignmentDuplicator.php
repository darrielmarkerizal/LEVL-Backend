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
    ) {}

    public function duplicateAssignment(int $assignmentId, int $userId, array $overrides = []): Assignment
    {
        return DB::transaction(function () use ($assignmentId, $userId, $overrides) {
            $overrides['created_by'] = $userId;
            $original = $this->repository->findForDuplication($assignmentId);

            if (! $original) {
                throw AssignmentException::notFound();
            }

            $assignmentData = $this->prepareAssignmentDataForDuplication($original, $overrides);

            $newAssignment = $this->repository->create($assignmentData);

            $this->duplicateQuestions($original, $newAssignment);

            $this->duplicatePrerequisites($original, $newAssignment);

            return $newAssignment->fresh(['questions', 'prerequisites', 'lesson', 'creator']);
        });
    }

    private function prepareAssignmentDataForDuplication(Assignment $original, ?array $overrides): array
    {
        return [
            'assignable_type' => $original->assignable_type,
            'assignable_id' => $original->assignable_id,
            'created_by' => $overrides['created_by'] ?? $original->created_by,
            'title' => $overrides['title'] ?? $original->title.' (Copy)',
            'description' => $overrides['description'] ?? $original->description,
            'submission_type' => $original->submission_type?->value ?? $original->getRawOriginal('submission_type'),
            'max_score' => $overrides['max_score'] ?? $original->max_score,
            'review_mode' => $overrides['review_mode'] ?? ($original->review_mode?->value ?? $original->getRawOriginal('review_mode')),
            'status' => $overrides['status'] ?? AssignmentStatus::Draft->value,
            'time_limit_minutes' => $original->time_limit_minutes,
        ];
    }

    private function duplicateQuestions(Assignment $original, Assignment $newAssignment): void
    {
        foreach ($original->questions as $question) {
            $newAssignment->questions()->create([
                'type' => $question->type?->value ?? $question->getRawOriginal('type'),
                'content' => $question->content,
                'options' => $question->options,
                'answer_key' => $question->answer_key,
                'weight' => $question->weight,
                'order' => $question->order,
            ]);
            
            
        }
    }

    private function duplicatePrerequisites(Assignment $original, Assignment $newAssignment): void
    {
        
        
        
        $prereqIds = $original->prerequisites->pluck('id')->toArray();
        if (! empty($prereqIds)) {
            $newAssignment->prerequisites()->attach($prereqIds);
        }
    }
}
