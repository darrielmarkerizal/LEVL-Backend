<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use App\Support\CodeGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Modules\Schemes\Contracts\Repositories\UnitRepositoryInterface;
use Modules\Schemes\DTOs\CreateUnitDTO;
use Modules\Schemes\DTOs\UpdateUnitDTO;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Services\Support\UnitIncludeAuthorizer;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UnitService
{
    use \App\Support\Traits\BuildsQueryBuilderRequest;

    public function __construct(
        private readonly UnitRepositoryInterface $repository,
        private readonly SchemesCacheService $cacheService,
        private readonly UnitIncludeAuthorizer $includeAuthorizer
    ) {}

    public function validateHierarchy(int $courseId, int $unitId): void
    {
        $unit = Unit::findOrFail($unitId);

        if ((int) $unit->course_id !== $courseId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(__('messages.units.not_in_course'));
        }
    }

    public function paginate(int $courseId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        return cache()->tags(['schemes', 'units'])->remember(
            "schemes:units:course:{$courseId}:{$perPage}:".request('page', 1).':'.md5(json_encode($filters)),
            300,
            function () use ($courseId, $filters, $perPage) {
                $query = QueryBuilder::for(Unit::class, $this->buildQueryBuilderRequest($filters))
                    ->where('course_id', $courseId)
                    ->allowedFilters([
                        AllowedFilter::exact('status'),
                    ])
                    ->allowedIncludes(['course', 'lessons'])
                    ->allowedSorts(['order', 'title', 'created_at'])
                    ->defaultSort('order');

                return $query->paginate($perPage);
            }
        );
    }

    public function find(int $id): ?Unit
    {
        return $this->repository->findById($id);
    }

    public function findOrFail(int $id): Unit
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function findWithIncludes(int $id): Unit
    {
        $unit = $this->findOrFail($id);
        $includeParam = request()->get('include', '');

        if (empty($includeParam)) {
            return $unit;
        }

        $unit->load('course');
        $user = auth('api')->user();
        $allowedIncludes = $this->includeAuthorizer->getAllowedIncludesForQueryBuilder($user, $unit);

        return QueryBuilder::for(Unit::class)
            ->where('id', $id)
            ->allowedIncludes($allowedIncludes)
            ->firstOrFail();
    }

    public function create(int $courseId, CreateUnitDTO|array $data): Unit
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($courseId, $data) {
            $attributes = $data instanceof CreateUnitDTO ? $data->toArrayWithoutNull() : $data;
            $attributes['course_id'] = $courseId;

            if (empty($attributes['code'])) {
                $attributes['code'] = CodeGenerator::generate('UNIT-', 4, Unit::class);
            }

            if (isset($attributes['order'])) {
                Unit::where('course_id', $courseId)
                    ->where('order', '>=', $attributes['order'])
                    ->increment('order');
            } else {
                $maxOrder = Unit::where('course_id', $courseId)->max('order');
                $attributes['order'] = $maxOrder ? $maxOrder + 1 : 1;
            }

            $attributes = Arr::except($attributes, ['slug']);

            $unit = $this->repository->create($attributes);
            cache()->tags(['schemes', 'units'])->flush();

            return $unit;
        });
    }

    public function update(int $id, UpdateUnitDTO|array $data): Unit
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data) {
            $unit = $this->repository->findByIdOrFail($id);
            $attributes = $data instanceof UpdateUnitDTO ? $data->toArrayWithoutNull() : $data;

            if (isset($attributes['order']) && $attributes['order'] != $unit->order) {
                $newOrder = $attributes['order'];
                $currentOrder = $unit->order;
                $courseId = $unit->course_id;

                if ($newOrder < $currentOrder) {

                    Unit::where('course_id', $courseId)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $currentOrder)
                        ->increment('order');
                } elseif ($newOrder > $currentOrder) {

                    Unit::where('course_id', $courseId)
                        ->where('order', '>', $currentOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                }
            }

            $attributes = Arr::except($attributes, ['slug']);

            $updated = $this->repository->update($unit, $attributes);
            cache()->tags(['schemes', 'units'])->flush();

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $unit = $this->repository->findByIdOrFail($id);
            $courseId = $unit->course_id;
            $deletedOrder = $unit->order;

            $deleted = $this->repository->delete($unit);

            if ($deleted) {

                Unit::where('course_id', $courseId)
                    ->where('order', '>', $deletedOrder)
                    ->decrement('order');
            }

            if ($deleted) {
                cache()->tags(['schemes', 'units'])->flush();
            }

            return $deleted;
        });
    }

    public function reorder(int $courseId, array $data): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($courseId, $data) {
            $unitIds = array_map('intval', $data['units']);

            if (count($unitIds) !== count(array_unique($unitIds))) {
                throw new \InvalidArgumentException(__('messages.units.duplicate_ids'));
            }

            $allUnits = Unit::where('course_id', $courseId)->pluck('id')->toArray();

            if (count($unitIds) !== count($allUnits) || array_diff($allUnits, $unitIds)) {
                throw new \InvalidArgumentException(__('messages.units.must_include_all'));
            }

            $count = Unit::whereIn('id', $unitIds)
                ->where('course_id', $courseId)
                ->count();

            if ($count !== count($unitIds)) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(__('messages.units.some_not_found'));
            }

            foreach ($unitIds as $index => $unitId) {
                $this->repository->updateOrder($unitId, $index + 1);
            }

            $this->cacheService->invalidateCourse($courseId);

            $this->cacheService->invalidateCourse($courseId);
            cache()->tags(['schemes', 'units'])->flush();

            return true;
        });
    }

    public function publish(int $id): Unit
    {
        $unit = $this->repository->findByIdOrFail($id);
        $unit->update(['status' => 'published']);

        $this->cacheService->invalidateCourse($unit->course_id);

        $this->cacheService->invalidateCourse($unit->course_id);
        cache()->tags(['schemes', 'units'])->flush();

        return $unit->fresh();
    }

    public function unpublish(int $id): Unit
    {
        $unit = $this->repository->findByIdOrFail($id);
        $unit->update(['status' => 'draft']);

        $this->cacheService->invalidateCourse($unit->course_id);

        $this->cacheService->invalidateCourse($unit->course_id);
        cache()->tags(['schemes', 'units'])->flush();

        return $unit->fresh();
    }

    public function getContents(Unit $unit, ?\Modules\Auth\Models\User $user = null): array
    {
        $lessonIds = $unit->lessons()->where('status', 'published')->pluck('id');

        $completedLessonIds = [];
        if ($user) {
            $completedLessonIds = \Modules\Schemes\Models\LessonCompletion::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->pluck('lesson_id')
                ->toArray();
        }

        $lessons = $unit->lessons()
            ->where('status', 'published')
            ->select('id', 'unit_id', 'title', 'slug', 'description', 'order', 'status', 'created_at')
            ->orderBy('order')
            ->get();

        $submissionsByQuiz = [];
        $submissionsByAssignment = [];

        if ($user) {
            $quizIds = \Modules\Learning\Models\Quiz::where('assignable_type', \Modules\Schemes\Models\Lesson::class)
                ->whereIn('assignable_id', $lessonIds)
                ->pluck('id');

            $submissionsByQuiz = \Modules\Learning\Models\QuizSubmission::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds)
                ->get()
                ->groupBy('quiz_id')
                ->map(fn ($submissions) => $submissions->sortByDesc('submitted_at')->first());

            $submissionsByAssignment = \Modules\Learning\Models\Submission::where('user_id', $user->id)
                ->whereIn('assignment_id', function ($query) use ($lessonIds) {
                    $query->select('id')
                        ->from('assignments')
                        ->whereIn('lesson_id', $lessonIds);
                })
                ->get()
                ->groupBy('assignment_id')
                ->map(fn ($submissions) => $submissions->sortByDesc('submitted_at')->first());
        }

        $quizzes = \Modules\Learning\Models\Quiz::where('unit_id', $unit->id)
            ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            ->select('id', 'title', 'description', 'status', 'max_score', 'passing_grade', 'created_at', 'order', 'unit_id')
            ->get();

        $assignments = \Modules\Learning\Models\Assignment::where('unit_id', $unit->id)
            ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            ->select('id', 'title', 'description', 'status', 'max_score', 'submission_type', 'created_at', 'order', 'unit_id')
            ->get();

        $allContent = collect();

        foreach ($lessons as $lesson) {
            $allContent->push([
                'type' => 'lesson',
                'order' => $lesson->order,
                'data' => $lesson,
            ]);
        }

        foreach ($quizzes as $quiz) {
            $allContent->push([
                'type' => 'quiz',
                'order' => $quiz->order,
                'data' => $quiz,
            ]);
        }

        foreach ($assignments as $assignment) {
            $allContent->push([
                'type' => 'assignment',
                'order' => $assignment->order,
                'data' => $assignment,
            ]);
        }

        $allContent = $allContent->sortBy('order')->values();

        $contents = collect();
        $previousContentCompleted = true;

        foreach ($allContent as $contentItem) {
            $type = $contentItem['type'];
            $item = $contentItem['data'];

            if ($type === 'lesson') {
                $isCompleted = in_array($item->id, $completedLessonIds);
                $isLocked = $user ? ! $previousContentCompleted : false;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'lesson',
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'order' => $item->order,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'is_completed' => $isCompleted,
                    'is_locked' => $isLocked,
                ]);

                $previousContentCompleted = $isCompleted;
            } elseif ($type === 'quiz') {
                $submission = $submissionsByQuiz[$item->id] ?? null;
                $isPassed = $submission ? ($submission->score >= $item->passing_grade) : false;
                $isLocked = $user ? ! $previousContentCompleted : false;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'quiz',
                    'title' => $item->title,
                    'description' => $item->description,
                    'order' => $item->order,
                    'status' => $item->status->value,
                    'max_score' => $item->max_score,
                    'passing_grade' => $item->passing_grade,
                    'created_at' => $item->created_at,
                    'submission_status' => $submission ? $submission->status->value : null,
                    'score' => $submission?->score,
                    'is_passed' => $isPassed,
                    'is_locked' => $isLocked,
                ]);

                $previousContentCompleted = $isPassed;
            } elseif ($type === 'assignment') {
                $submission = $submissionsByAssignment[$item->id] ?? null;
                $isPassed = $submission && $submission->status->value === 'graded' && $submission->score >= ($item->max_score * 0.6);
                $isLocked = $user ? ! $previousContentCompleted : false;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'assignment',
                    'title' => $item->title,
                    'description' => $item->description,
                    'order' => $item->order,
                    'status' => $item->status->value,
                    'max_score' => $item->max_score,
                    'submission_type' => $item->submission_type->value,
                    'created_at' => $item->created_at,
                    'submission_status' => $submission ? $submission->status->value : null,
                    'score' => $submission?->score,
                    'is_locked' => $isLocked,
                ]);

                $previousContentCompleted = $isPassed;
            }
        }

        return $contents->toArray();
    }

    public function paginateAll(array $filters = [], int $perPage = 15, ?\Modules\Auth\Models\User $user = null): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        $query = Unit::query();

        if (isset($filters['search']) && ! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if ($user && ! $user->hasRole('Superadmin')) {
            $query->whereHas('course', function ($q) use ($user) {
                if ($user->hasRole(['Admin', 'Instructor'])) {
                    $q->where('instructor_id', $user->id);
                }
            });
        }

        if (isset($filters['course_slug'])) {
            $query->whereHas('course', function ($q) use ($filters) {
                $q->where('slug', $filters['course_slug']);
            });
        }

        $allowedSorts = ['order', 'title', 'created_at'];
        $sortField = in_array($filters['sort'] ?? '', $allowedSorts) ? $filters['sort'] : 'created_at';
        $sortOrder = ($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortField, $sortOrder);

        if (isset($filters['include'])) {
            $allowedIncludes = ['course', 'lessons'];
            $includes = array_intersect(explode(',', $filters['include']), $allowedIncludes);
            if (! empty($includes)) {
                $query->with($includes);
            }
        }

        return $query->paginate($perPage);
    }
}
