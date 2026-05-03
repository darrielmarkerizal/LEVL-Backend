<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use App\Support\CodeGenerator;
use BackedEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Modules\Learning\Contracts\Services\AssignmentServiceInterface;
use Modules\Learning\Contracts\Services\QuizServiceInterface;
use Modules\Learning\Enums\AssignmentType;
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
        private readonly UnitIncludeAuthorizer $includeAuthorizer,
        private readonly LessonService $lessonService,
        private readonly QuizServiceInterface $quizService,
        private readonly AssignmentServiceInterface $assignmentService,
        private readonly UnitContentSyncService $syncService,
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

        $user = auth('api')->user();
        $accessLevel = 'public';

        if ($user) {
            if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
                $accessLevel = 'admin';
            } elseif ($user->hasRole('Instructor')) {
                $course = \Modules\Schemes\Models\Course::find($courseId);
                if ($course && $course->hasInstructorAssignment($user)) {
                    $accessLevel = 'admin';
                }
            }
        }

        return cache()->tags(['schemes', 'units'])->remember(
            "schemes:units:course:{$courseId}:{$accessLevel}:{$perPage}:".request('page', 1).':'.md5(json_encode($filters)),
            300,
            function () use ($courseId, $filters, $perPage, $accessLevel) {
                $query = QueryBuilder::for(Unit::class, $this->buildQueryBuilderRequest($filters))
                    ->where('course_id', $courseId);

                if ($accessLevel === 'public') {
                    $query->where('status', 'published');
                }

                $query->allowedFilters([
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

        $requestedIncludes = array_filter(explode(',', $includeParam));
        $hasElementsInclude = in_array('elements', $requestedIncludes);

        if ($hasElementsInclude) {
            $requestedIncludes = array_diff($requestedIncludes, ['elements']);
            $requestedIncludes = array_merge($requestedIncludes, ['lessons', 'quizzes', 'assignments']);
            $requestedIncludes = array_unique($requestedIncludes);

            request()->merge(['include' => implode(',', $requestedIncludes)]);
        }

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

            $maxOrder = Unit::where('course_id', $courseId)->max('order') ?? 0;

            if (isset($attributes['order'])) {

                if ($attributes['order'] > $maxOrder + 1) {
                    $attributes['order'] = $maxOrder + 1;
                }

                Unit::where('course_id', $courseId)
                    ->where('order', '>=', $attributes['order'])
                    ->increment('order');
            } else {

                $attributes['order'] = $maxOrder + 1;
            }

            if (empty($attributes['slug'])) {
                $attributes = Arr::except($attributes, ['slug']);
            }

            $attributes = Arr::except($attributes, ['course_slug']);

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

        $xpSources = \Modules\Gamification\Models\XpSource::whereIn('code', [
            'lesson_completed',
            'quiz_passed',
            'assignment_submitted',
            'perfect_score',
        ])->get()->keyBy('code');

        $mustFilterPublished = true;
        if ($user) {
            if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
                $mustFilterPublished = false;
            } elseif ($user->hasRole('Instructor')) {
                $unit->loadMissing('course');
                if ($unit->course && $unit->course->hasInstructorAssignment($user)) {
                    $mustFilterPublished = false;
                }
            }
        }

        $lessonQuery = $unit->lessons();
        if ($mustFilterPublished) {
            $lessonQuery->where('status', 'published');
        }
        $lessonIds = clone $lessonQuery;
        $lessonIds = $lessonIds->pluck('id');

        $completedLessonIds = [];
        if ($user) {
            $completedLessonIds = \Modules\Enrollments\Models\LessonProgress::query()
                ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
                ->where('enrollments.user_id', $user->id)
                ->where('lesson_progress.status', 'completed')
                ->whereIn('lesson_progress.lesson_id', $lessonIds)
                ->pluck('lesson_progress.lesson_id')
                ->toArray();
        }

        $lessons = $lessonQuery
            ->select('id', 'unit_id', 'title', 'slug', 'description', 'order', 'status', 'created_at')
            ->orderBy('order')
            ->get();

        $quizQuery = \Modules\Learning\Models\Quiz::where('unit_id', $unit->id);
        if ($mustFilterPublished) {
            $quizQuery->where('status', \Modules\Learning\Enums\QuizStatus::Published);
        }
        $quizzes = $quizQuery
            ->select('id', 'title', 'description', 'status', 'max_score', 'passing_grade', 'created_at', 'order', 'unit_id')
            ->get();

        $assignmentQuery = \Modules\Learning\Models\Assignment::where('unit_id', $unit->id);
        if ($mustFilterPublished) {
            $assignmentQuery->where('status', \Modules\Learning\Enums\AssignmentStatus::Published);
        }
        $assignments = $assignmentQuery
            ->select('id', 'title', 'description', 'status', 'max_score', 'passing_grade', 'submission_type', 'created_at', 'order', 'unit_id')
            ->get();

        $submissionsByQuiz = [];
        $submissionsByAssignment = [];

        if ($user && $quizzes->isNotEmpty()) {
            $quizIds = $quizzes->pluck('id');

            $submissionsByQuiz = \Modules\Learning\Models\QuizSubmission::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds)
                ->get()
                ->groupBy('quiz_id')
                ->map(fn ($submissions) => $submissions->sortByDesc('submitted_at')->first());
        }

        if ($user && $assignments->isNotEmpty()) {
            $assignmentIds = $assignments->pluck('id');

            $submissionsByAssignment = \Modules\Learning\Models\Submission::where('user_id', $user->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->get()
                ->groupBy('assignment_id')
                ->map(fn ($submissions) => $submissions->sortByDesc('submitted_at')->first());
        }

        $unitContentsMap = \Modules\Schemes\Models\UnitContent::where('unit_id', $unit->id)
            ->get()
            ->mapWithKeys(fn ($uc) => [$uc->contentable_type.'_'.$uc->contentable_id => $uc->order]);

        $getCanonicalOrder = fn (string $type, int $id): int => $unitContentsMap->get($type.'_'.$id, PHP_INT_MAX);

        $allContent = collect();

        foreach ($lessons as $lesson) {
            $allContent->push([
                'type' => 'lesson',
                'order' => $getCanonicalOrder('lesson', $lesson->id),
                'data' => $lesson,
            ]);
        }

        foreach ($quizzes as $quiz) {
            $allContent->push([
                'type' => 'quiz',
                'order' => $getCanonicalOrder('quiz', $quiz->id),
                'data' => $quiz,
            ]);
        }

        foreach ($assignments as $assignment) {
            $allContent->push([
                'type' => 'assignment',
                'order' => $getCanonicalOrder('assignment', $assignment->id),
                'data' => $assignment,
            ]);
        }

        $allContent = $allContent->sortBy('order')->values();

        $contents = collect();
        $previousContentCompleted = true;

        foreach ($allContent as $contentItem) {
            $type = $contentItem['type'];
            $item = $contentItem['data'];
            $canonicalOrder = $contentItem['order'];

            if ($type === 'lesson') {
                $isCompleted = in_array($item->id, $completedLessonIds);

                $isLocked = $user && $contents->isNotEmpty() ? ! $previousContentCompleted : false;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'lesson',
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'order' => $canonicalOrder,
                    'sequence' => $unit->order.'.'.$canonicalOrder,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'is_completed' => $isCompleted,
                    'is_locked' => $isLocked,
                    'xp_reward' => $xpSources['lesson_completed']->xp_amount ?? 0,
                ]);

                $previousContentCompleted = $isCompleted;
            } elseif ($type === 'quiz') {
                $submission = $submissionsByQuiz[$item->id] ?? null;
                $finalScore = $submission ? ($submission->final_score ?? $submission->score) : null;
                $isPassed = $submission && $submission->status->value === 'graded' && $finalScore !== null && $finalScore >= $item->passing_grade;

                $isLocked = $user && $contents->isNotEmpty() ? ! $previousContentCompleted : false;

                $baseXp = $xpSources['quiz_passed']->xp_amount ?? 0;
                $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'quiz',
                    'title' => $item->title,
                    'description' => $item->description,
                    'order' => $canonicalOrder,
                    'sequence' => $unit->order.'.'.$canonicalOrder,
                    'status' => $item->status->value,
                    'max_score' => $item->max_score,
                    'passing_grade' => $item->passing_grade,
                    'created_at' => $item->created_at,
                    'submission_status' => $submission ? $submission->status->value : null,
                    'score' => $submission?->score,
                    'is_completed' => $isPassed,
                    'is_locked' => $isLocked,
                    'xp_reward' => $baseXp,
                    'xp_perfect_bonus' => $perfectScoreXp,
                ]);

                $previousContentCompleted = $isPassed;
            } elseif ($type === 'assignment') {
                $submission = $submissionsByAssignment[$item->id] ?? null;
                $isPassed = $submission && $submission->status->value === 'graded' && $submission->score >= $item->passing_grade;

                $isLocked = $user && $contents->isNotEmpty() ? ! $previousContentCompleted : false;

                $baseXp = $xpSources['assignment_submitted']->xp_amount ?? 0;
                $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'assignment',
                    'title' => $item->title,
                    'description' => $item->description,
                    'order' => $canonicalOrder,
                    'sequence' => $unit->order.'.'.$canonicalOrder,
                    'status' => $item->status->value,
                    'max_score' => $item->max_score,
                    'passing_grade' => $item->passing_grade,
                    'submission_type' => $item->submission_type->value,
                    'created_at' => $item->created_at,
                    'submission_status' => $submission ? $submission->status->value : null,
                    'score' => $submission?->score,
                    'is_completed' => $isPassed,
                    'is_locked' => $isLocked,
                    'xp_reward' => $baseXp,
                    'xp_perfect_bonus' => $perfectScoreXp,
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

        if ($user && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin')) {
            $query->whereHas('course', function ($q) use ($user) {
                if ($user->hasRole('Instructor')) {
                    $q->whereHas('instructors', function ($instructorQuery) use ($user) {
                        $instructorQuery->where('user_id', $user->id);
                    });
                } elseif ($user->hasRole('Student')) {
                    $q->whereHas('enrollments', function ($enrollmentQuery) use ($user) {
                        $enrollmentQuery
                            ->where('user_id', $user->id)
                            ->whereIn('status', ['active', 'completed']);
                    });
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        $includeParam = request()->query('include', '');
        $requestedIncludes = array_filter(explode(',', $includeParam));
        $hasElementsInclude = in_array('elements', $requestedIncludes);

        if ($hasElementsInclude) {
            $requestedIncludes = array_diff($requestedIncludes, ['elements']);
            $requestedIncludes = array_merge($requestedIncludes, ['lessons', 'quizzes', 'assignments']);
            $requestedIncludes = array_unique($requestedIncludes);

            request()->merge(['include' => implode(',', $requestedIncludes)]);
        }

        if (in_array('lessons', $requestedIncludes)) {
            if ($user && $user->hasRole('Student')) {
                $query->with(['lessons' => fn ($q) => $q->where('status', 'published')->orderBy('order', 'asc')]);
            } else {
                $query->with(['lessons' => fn ($q) => $q->orderBy('order', 'asc')]);
            }
        }

        if (in_array('quizzes', $requestedIncludes)) {
            if ($user && $user->hasRole('Student')) {
                $query->with(['quizzes' => function ($q) use ($user) {
                    $q->where('status', 'published')
                        ->whereHas('unit.course', function ($courseQuery) use ($user) {
                            $courseQuery->where('status', 'published')
                                ->whereHas('enrollments', function ($enrollmentQuery) use ($user) {
                                    $enrollmentQuery->where('user_id', $user->id)
                                        ->whereIn('status', ['active', 'completed']);
                                });
                        })
                        ->orderBy('order', 'asc');
                }]);
            } else {
                $query->with(['quizzes' => fn ($q) => $q->orderBy('order', 'asc')]);
            }
        }

        if (in_array('assignments', $requestedIncludes)) {
            if ($user && $user->hasRole('Student')) {
                $query->with(['assignments' => function ($q) use ($user) {
                    $q->where('status', 'published')
                        ->whereHas('unit.course', function ($courseQuery) use ($user) {
                            $courseQuery->where('status', 'published')
                                ->whereHas('enrollments', function ($enrollmentQuery) use ($user) {
                                    $enrollmentQuery->where('user_id', $user->id)
                                        ->whereIn('status', ['active', 'completed']);
                                });
                        })
                        ->orderBy('order', 'asc');
                }]);
            } else {
                $query->with(['assignments' => fn ($q) => $q->orderBy('order', 'asc')]);
            }
        }

        $searchQuery = request()->query('search');
        if ($searchQuery && trim((string) $searchQuery) !== '') {
            $query->search($searchQuery);
        }

        $queryBuilder = \Spatie\QueryBuilder\QueryBuilder::for($query)
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::exact('status'),
                \Spatie\QueryBuilder\AllowedFilter::callback('course_slug', function ($query, $value) {
                    $query->whereHas('course', function ($q) use ($value) {
                        $q->where('slug', $value);
                    });
                }),
                \Spatie\QueryBuilder\AllowedFilter::callback('search', function ($query, $value) {}),
            ])
            ->allowedSorts(['order', 'title', 'created_at', 'updated_at'])
            ->defaultSort('-created_at')
            ->with('course:id,slug,title');

        return $queryBuilder->paginate($perPage);
    }

    public function getContentOrder(Unit $unit): array
    {
        $unitContents = \Modules\Schemes\Models\UnitContent::where('unit_id', $unit->id)
            ->orderBy('order')
            ->get();

        $grouped = $unitContents->groupBy('contentable_type');
        $models = [];

        foreach ($grouped as $type => $items) {
            $ids = $items->pluck('contentable_id')->toArray();
            $modelClass = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($type);

            if ($modelClass) {
                $models[$type] = $modelClass::withoutGlobalScopes()
                    ->whereIn('id', $ids)
                    ->whereNull('deleted_at')
                    ->get(['id', 'title', 'status'])
                    ->keyBy('id');
            }
        }

        return $unitContents->map(function ($uc) use ($models) {
            $model = ($models[$uc->contentable_type] ?? collect())->get($uc->contentable_id);
            if (! $model) {
                return null;
            }

            $status = $model->status;
            if ($status instanceof \BackedEnum) {
                $status = $status->value;
            }

            return [
                'type' => $uc->contentable_type,
                'id' => $uc->contentable_id,
                'title' => $model->title,
                'order' => $uc->order,
                'status' => $status,
            ];
        })->filter()->values()->toArray();
    }

    public function createContentElement(Unit $unit, array $data, int $createdBy): array
    {
        $type = $data['type'];
        $title = trim((string) $data['title']);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($unit, $type, $title, $createdBy) {
            $created = match ($type) {
                'lesson' => $this->lessonService->create($unit->id, [
                    'title' => $title,
                ]),
                'quiz' => $this->quizService->create([
                    'unit_id' => $unit->id,
                    'title' => $title,
                ], $createdBy),
                'assignment' => $this->assignmentService->create([
                    'unit_id' => $unit->id,
                    'type' => AssignmentType::Assignment->value,
                    'title' => $title,
                ], $createdBy),
                default => throw new \InvalidArgumentException('Invalid content type.'),
            };

            $status = $created->status;
            if ($status instanceof BackedEnum) {
                $status = $status->value;
            }

            return [
                'id' => $created->id,
                'type' => $type,
                'unit_id' => $unit->id,
                'title' => $created->title,
                'order' => $created->order,
                'status' => $status,
                'next_action' => 'Use PUT endpoint to complete the element data.',
            ];
        });
    }

    public function reorderContent(Unit $unit, array $contentOrder): array
    {
        $this->syncService->reorder($unit->id, $contentOrder);

        return $this->getContentOrder($unit);
    }

    public function generateUniqueSlug(string $title): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Unit::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
