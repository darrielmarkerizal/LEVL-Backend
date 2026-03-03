<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Contracts\Services\AssignmentServiceInterface;
use Modules\Learning\DTOs\PrerequisiteCheckResult;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Enums\RandomizationType;
use Modules\Learning\Enums\ReviewMode;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Override;
use Modules\Learning\Repositories\AssignmentRepository;
use Modules\Learning\Services\Support\AssignmentDuplicator;
use Modules\Learning\Services\Support\AssignmentFinder;
use Modules\Learning\Services\Support\AssignmentOverrideProcessor;
use Modules\Learning\Services\Support\AssignmentPrerequisiteProcessor;

class AssignmentService implements AssignmentServiceInterface
{
    public function __construct(
        private readonly AssignmentRepository $repository,
        private readonly AssignmentFinder $finder,
        private readonly AssignmentPrerequisiteProcessor $prerequisiteProcessor,
        private readonly AssignmentOverrideProcessor $overrideProcessor,
        private readonly AssignmentDuplicator $duplicator
    ) {}

    public function resolveCourseFromScope(string $assignableType, int $assignableId): ?\Modules\Schemes\Models\Course
    {
        return $this->finder->resolveCourseFromScope($assignableType, $assignableId);
    }

    public function resolveCourseFromScopeOrFail(?array $scope): \Modules\Schemes\Models\Course
    {
        return $this->finder->resolveCourseFromScopeOrFail($scope);
    }

    public function list(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listForIndex($course, $filters);
    }

    public function listForIndex(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listForIndex($course, $filters);
    }

    public function listByLesson(\Modules\Schemes\Models\Lesson $lesson, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listByLesson($lesson, $filters);
    }

    public function listByLessonForIndex(\Modules\Schemes\Models\Lesson $lesson, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listByLessonForIndex($lesson, $filters);
    }

    public function listByUnit(\Modules\Schemes\Models\Unit $unit, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listByUnit($unit, $filters);
    }

    public function listByUnitForIndex(\Modules\Schemes\Models\Unit $unit, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listByUnitForIndex($unit, $filters);
    }

    public function listByCourseForIndex(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listByCourseForIndex($course, $filters);
    }

    public function listByCourse(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listByCourse($course, $filters);
    }

    public function listIncomplete(\Modules\Schemes\Models\Course $course, int $studentId, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listIncomplete($course, $studentId, $filters);
    }

    public function listByScope(string $scopeType, int $scopeId, array $filters = []): LengthAwarePaginator
    {
        switch ($scopeType) {
            case 'course':
                if ($course = \Modules\Schemes\Models\Course::find($scopeId)) {
                    return $this->finder->listByCourse($course, $filters);
                }
                break;
            case 'unit':
                if ($unit = \Modules\Schemes\Models\Unit::find($scopeId)) {
                    return $this->finder->listByUnit($unit, $filters);
                }
                break;
            case 'lesson':
                if ($lesson = \Modules\Schemes\Models\Lesson::find($scopeId)) {
                    return $this->finder->listByLesson($lesson, $filters);
                }
                break;
        }

        $perPage = (int) data_get($filters, 'per_page', 15);

        return $this->repository->paginate($perPage);
    }

    public function create(array $data, int $createdBy): Assignment
    {
        return DB::transaction(function () use ($data, $createdBy) {
            if (! isset($data['order']) || $data['order'] === null) {
                $data['order'] = $this->getNextOrderForUnit($data['unit_id']);
            }

            $isAssignment = ($data['type'] ?? null) === AssignmentType::Assignment->value || ($data['type'] ?? null) === 'assignment';

            $assignmentData = array_merge($data, [
                'created_by' => $createdBy,
                'status' => $data['status'] ?? AssignmentStatus::Draft->value,
                'submission_type' => $data['submission_type'] ?? 'text',
                'max_score' => $data['max_score'] ?? 100,
                'allow_resubmit' => data_get($data, 'allow_resubmit') !== null ? (bool) $data['allow_resubmit'] : null,
                'cooldown_minutes' => $data['cooldown_minutes'] ?? 0,
                'retake_enabled' => isset($data['retake_enabled']) ? (bool) $data['retake_enabled'] : false,
            ]);

            if ($isAssignment) {
                $assignmentData['review_mode'] = ReviewMode::Manual->value;
                unset($assignmentData['randomization_type']);
                unset($assignmentData['question_bank_count']);
            } else {
                $assignmentData['review_mode'] = $data['review_mode'] ?? ReviewMode::Immediate->value;
                $assignmentData['randomization_type'] = $data['randomization_type'] ?? RandomizationType::Static->value;
            }

            $assignment = $this->repository->create($assignmentData);

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $assignment->addMedia($file)->toMediaCollection('attachments');
                }
            }

            return $assignment->fresh(['unit', 'creator', 'media']);
        });
    }

    private function getNextOrderForUnit(int $unitId): int
    {
        $maxLessonOrder = \Modules\Schemes\Models\Lesson::where('unit_id', $unitId)->max('order') ?? 0;
        $maxAssignmentOrder = Assignment::where('unit_id', $unitId)->max('order') ?? 0;
        $maxQuizOrder = \Modules\Learning\Models\Quiz::where('unit_id', $unitId)->max('order') ?? 0;

        return max($maxLessonOrder, $maxAssignmentOrder, $maxQuizOrder) + 1;
    }

    public function update(Assignment $assignment, array $data): Assignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $updated = $this->repository->update($assignment, $data);

            if (isset($data['delete_attachments']) && is_array($data['delete_attachments'])) {
                $assignment->media()->whereIn('id', $data['delete_attachments'])->delete();
            }

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $assignment->addMedia($file)->toMediaCollection('attachments');
                }
            }

            return $updated->fresh(['lesson', 'creator', 'media']);
        });
    }

    public function publish(Assignment $assignment): Assignment
    {
        return DB::transaction(function () use ($assignment) {
            $stats = \Modules\Learning\Services\QuestionService::computeWeightStats($assignment->id);
            if ($stats['exceeds'] ?? false) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], [])->errors()->add('weight', __('messages.questions.weight_exceeds_max_score'))
                );
            }

            $wasDraft = $assignment->status === AssignmentStatus::Draft;
            $published = $this->repository->update($assignment, ['status' => AssignmentStatus::Published->value]);

            if ($wasDraft) {
                \Modules\Learning\Events\AssignmentPublished::dispatch($published);
            }

            return $published->fresh(['lesson', 'creator']);
        });
    }

    public function unpublish(Assignment $assignment): Assignment
    {
        return DB::transaction(fn () => $this->repository->update($assignment, ['status' => AssignmentStatus::Draft->value])->fresh(['lesson', 'creator']));
    }

    public function archive(Assignment $assignment): Assignment
    {
        return DB::transaction(fn () => $this->repository->update($assignment, ['status' => AssignmentStatus::Archived->value])->fresh(['lesson', 'creator']));
    }

    public function delete(Assignment $assignment): bool
    {
        return DB::transaction(fn () => $this->repository->delete($assignment));
    }

    public function getWithRelations(Assignment $assignment): Assignment
    {
        return $this->repository->findWithRelations($assignment);
    }

    public function getOverridesForAssignment(int $assignmentId): Collection
    {
        return $this->overrideProcessor->getOverridesForAssignment($assignmentId);
    }

    public function checkPrerequisites(int $assignmentId, int $studentId): PrerequisiteCheckResult
    {
        return $this->prerequisiteProcessor->checkPrerequisites($assignmentId, $studentId, $this->overrideProcessor);
    }

    public function hasCircularDependency(int $assignmentId, int $prerequisiteId): bool
    {
        return $this->prerequisiteProcessor->hasCircularDependency($assignmentId, $prerequisiteId);
    }

    public function grantOverride(int $assignmentId, int $studentId, string $overrideType, string $reason, array $value = [], ?int $grantorId = null): Override
    {
        return $this->overrideProcessor->grantOverride($assignmentId, $studentId, $overrideType, $reason, $value, $grantorId);
    }

    public function duplicateAssignment(int $assignmentId, int $userId, array $overrides = []): Assignment
    {
        return $this->duplicator->duplicateAssignment($assignmentId, $userId, $overrides);
    }
}
