<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Contracts\Services\QuizServiceInterface;
use Modules\Learning\Enums\QuizStatus;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Repositories\QuizRepository;
use Modules\Schemes\Services\UnitContentSyncService;

class QuizService implements QuizServiceInterface
{
    public function __construct(
        private readonly QuizRepository $repository,
        private readonly UnitContentSyncService $syncService,
    ) {}

    public function resolveCourseFromScope(string $assignableType, int $assignableId): ?\Modules\Schemes\Models\Course
    {
        return match ($assignableType) {
            \Modules\Schemes\Models\Course::class => \Modules\Schemes\Models\Course::find($assignableId),
            \Modules\Schemes\Models\Unit::class => \Modules\Schemes\Models\Unit::find($assignableId)?->course,
            \Modules\Schemes\Models\Lesson::class => \Modules\Schemes\Models\Lesson::find($assignableId)?->unit?->course,
            default => null,
        };
    }

    public function resolveCourseFromScopeOrFail(?array $scope): \Modules\Schemes\Models\Course
    {
        if (empty($scope['assignable_type']) || empty($scope['assignable_id'])) {
            abort(422, __('messages.quizzes.invalid_scope'));
        }

        $course = $this->resolveCourseFromScope($scope['assignable_type'], (int) $scope['assignable_id']);

        if (! $course) {
            abort(404, __('messages.quizzes.scope_not_found'));
        }

        return $course;
    }

    public function list(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator
    {
        return $this->listForIndex($course, $filters);
    }

    public function listForIndex(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->listByCourse($course->id, $filters);
    }

    public function create(array $data, int $createdBy): Quiz
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $unitId = (int) $data['unit_id'];

            if (! isset($data['order']) || $data['order'] === null) {
                $data['order'] = $this->syncService->getNextOrder($unitId);
            }

            $quiz = $this->repository->create(array_merge($data, [
                'created_by' => $createdBy,
                'status' => $data['status'] ?? QuizStatus::Draft->value,
                'passing_grade' => $data['passing_grade'] ?? 75.00,
                'max_score' => $data['max_score'] ?? 100,
                'auto_grading' => array_key_exists('auto_grading', $data) ? filter_var($data['auto_grading'], FILTER_VALIDATE_BOOLEAN) : true,
                'review_mode' => $data['review_mode'] ?? 'immediate',
            ]));

            $this->syncService->register('quiz', $quiz->id, $unitId, $data['order']);

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $quiz->addMedia($file)->toMediaCollection('attachments');
                }
            }

            return $quiz->fresh(['unit', 'creator', 'media']);
        });
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        return DB::transaction(function () use ($quiz, $data) {
            $updated = $this->repository->update($quiz, $data);

            if (isset($data['delete_attachments']) && is_array($data['delete_attachments'])) {
                $quiz->media()->whereIn('id', $data['delete_attachments'])->delete();
            }

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $quiz->addMedia($file)->toMediaCollection('attachments');
                }
            }

            return $updated->fresh(['unit', 'unit.course', 'creator', 'media', 'questions']);
        });
    }

    public function delete(Quiz $quiz): bool
    {
        return DB::transaction(function () use ($quiz) {
            $deleted = $this->repository->delete($quiz);

            if ($deleted) {
                $this->syncService->unregister('quiz', $quiz->id);
            }

            return $deleted;
        });
    }

    public function publish(Quiz $quiz): Quiz
    {
        return DB::transaction(function () use ($quiz) {
            $stats = app(\Modules\Learning\Contracts\Services\QuizQuestionServiceInterface::class)
                ->computeWeightStats($quiz->id);

            if ($stats['exceeds'] ?? false) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], [])->errors()->add('weight', __('messages.questions.weight_exceeds_max_score'))
                );
            }

            $hasNullWeight = \Modules\Learning\Models\QuizQuestion::where('quiz_id', $quiz->id)
                ->whereNull('weight')
                ->exists();

            if ($hasNullWeight) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], [])->errors()->add('weight', __('messages.questions.weight_required_to_publish'))
                );
            }

            return $this->repository->update($quiz, ['status' => QuizStatus::Published->value])
                ->fresh(['unit', 'unit.course', 'creator', 'questions']);
        });
    }

    public function unpublish(Quiz $quiz): Quiz
    {
        return DB::transaction(fn () => $this->repository->update($quiz, ['status' => QuizStatus::Draft->value])
            ->fresh(['unit', 'unit.course', 'creator', 'questions']));
    }

    public function archive(Quiz $quiz): Quiz
    {
        return DB::transaction(fn () => $this->repository->update($quiz, ['status' => QuizStatus::Archived->value])
            ->fresh(['unit', 'unit.course', 'creator', 'questions']));
    }

    public function getWithRelations(Quiz $quiz): Quiz
    {
        return $this->repository->findWithRelations($quiz);
    }

    public function listForIndexWithEnrichment(\Modules\Schemes\Models\Course $course, array $filters, ?\Modules\Auth\Models\User $user): LengthAwarePaginator
    {
        $paginator = $this->listForIndex($course, $filters);
        $enrichmentService = app(\Modules\Learning\Services\Support\QuizEnrichmentService::class);

        if ($user && $user->hasRole('Student')) {
            return $enrichmentService->enrichForStudent($paginator, $user->id);
        }

        return $enrichmentService->enrichForInstructor($paginator);
    }

    public function getWithRelationsAndEnrichment(Quiz $quiz, ?\Modules\Auth\Models\User $user): Quiz
    {
        $quizWithRelations = $this->getWithRelations($quiz);
        $enrichmentService = app(\Modules\Learning\Services\Support\QuizEnrichmentService::class);

        if ($user && $user->hasRole('Student')) {
            return $enrichmentService->enrichDetailForStudent($quizWithRelations, $user->id);
        }

        return $quizWithRelations;
    }
}
