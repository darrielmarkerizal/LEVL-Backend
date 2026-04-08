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
        
        // Check if 'elements' is requested in include
        $requestedIncludes = array_filter(explode(',', $includeParam));
        $hasElementsInclude = in_array('elements', $requestedIncludes);
        
        // If elements is requested, replace it with lessons, quizzes, assignments
        if ($hasElementsInclude) {
            $requestedIncludes = array_diff($requestedIncludes, ['elements']);
            $requestedIncludes = array_merge($requestedIncludes, ['lessons', 'quizzes', 'assignments']);
            $requestedIncludes = array_unique($requestedIncludes);
            
            // Override the include parameter for Spatie
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

            // Get max order for this course
            $maxOrder = Unit::where('course_id', $courseId)->max('order') ?? 0;

            if (isset($attributes['order'])) {
                // Normalize order if it exceeds max + 1 (prevent gaps)
                if ($attributes['order'] > $maxOrder + 1) {
                    $attributes['order'] = $maxOrder + 1;
                }

                // Shift existing units if inserting in between
                Unit::where('course_id', $courseId)
                    ->where('order', '>=', $attributes['order'])
                    ->increment('order');
            } else {
                // Auto-assign next order
                $attributes['order'] = $maxOrder + 1;
            }

            // Handle custom slug if provided, otherwise let model auto-generate
            if (empty($attributes['slug'])) {
                $attributes = Arr::except($attributes, ['slug']);
            }

            // Remove course_slug from attributes (only used for routing)
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

            // Allow slug to be updated
            // $attributes = Arr::except($attributes, ['slug']);

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
        // Get XP sources for rewards
        $xpSources = \Modules\Gamification\Models\XpSource::whereIn('code', [
            'lesson_completed',
            'quiz_passed',
            'assignment_submitted',
            'perfect_score',
        ])->get()->keyBy('code');

        $lessonIds = $unit->lessons()->where('status', 'published')->pluck('id');

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

        $lessons = $unit->lessons()
            ->where('status', 'published')
            ->select('id', 'unit_id', 'title', 'slug', 'description', 'order', 'status', 'created_at')
            ->orderBy('order')
            ->get();

        $quizzes = \Modules\Learning\Models\Quiz::where('unit_id', $unit->id)
            ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            ->select('id', 'title', 'description', 'status', 'max_score', 'passing_grade', 'created_at', 'order', 'unit_id')
            ->get();

        $assignments = \Modules\Learning\Models\Assignment::where('unit_id', $unit->id)
            ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            ->select('id', 'title', 'description', 'status', 'max_score', 'submission_type', 'created_at', 'order', 'unit_id')
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
                // First item is never locked, subsequent items locked if previous not completed
                $isLocked = $user && $contents->isNotEmpty() ? ! $previousContentCompleted : false;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'lesson',
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'order' => $item->order,
                    'sequence' => $unit->order . '.' . $item->order,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'is_completed' => $isCompleted,
                    'is_locked' => $isLocked,
                    'xp_reward' => $xpSources['lesson_completed']->xp_amount ?? 0,
                ]);

                $previousContentCompleted = $isCompleted;
            } elseif ($type === 'quiz') {
                $submission = $submissionsByQuiz[$item->id] ?? null;
                $isPassed = $submission ? ($submission->score >= $item->passing_grade) : false;
                // First item is never locked, subsequent items locked if previous not completed
                $isLocked = $user && $contents->isNotEmpty() ? ! $previousContentCompleted : false;

                // Quiz gives XP for passing + bonus for perfect score
                $baseXp = $xpSources['quiz_passed']->xp_amount ?? 0;
                $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'quiz',
                    'title' => $item->title,
                    'description' => $item->description,
                    'order' => $item->order,
                    'sequence' => $unit->order . '.' . $item->order,
                    'status' => $item->status->value,
                    'max_score' => $item->max_score,
                    'passing_grade' => $item->passing_grade,
                    'created_at' => $item->created_at,
                    'submission_status' => $submission ? $submission->status->value : null,
                    'score' => $submission?->score,
                    'is_passed' => $isPassed,
                    'is_locked' => $isLocked,
                    'xp_reward' => $baseXp,
                    'xp_perfect_bonus' => $perfectScoreXp,
                ]);

                $previousContentCompleted = $isPassed;
            } elseif ($type === 'assignment') {
                $submission = $submissionsByAssignment[$item->id] ?? null;
                $isPassed = $submission && $submission->status->value === 'graded' && $submission->score >= ($item->max_score * 0.6);
                // First item is never locked, subsequent items locked if previous not completed
                $isLocked = $user && $contents->isNotEmpty() ? ! $previousContentCompleted : false;

                // Assignment gives XP for submission + bonus for perfect score
                $baseXp = $xpSources['assignment_submitted']->xp_amount ?? 0;
                $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

                $contents->push([
                    'id' => $item->id,
                    'type' => 'assignment',
                    'title' => $item->title,
                    'description' => $item->description,
                    'order' => $item->order,
                    'sequence' => $unit->order . '.' . $item->order,
                    'status' => $item->status->value,
                    'max_score' => $item->max_score,
                    'submission_type' => $item->submission_type->value,
                    'created_at' => $item->created_at,
                    'submission_status' => $submission ? $submission->status->value : null,
                    'score' => $submission?->score,
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

        // Build base query with access control
        $query = Unit::query();
        
        // Apply role-based filtering
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

        // Check if 'elements' is requested in include
        $includeParam = request()->query('include', '');
        $requestedIncludes = array_filter(explode(',', $includeParam));
        $hasElementsInclude = in_array('elements', $requestedIncludes);
        
        // If elements is requested, replace it with lessons, quizzes, assignments
        if ($hasElementsInclude) {
            $requestedIncludes = array_diff($requestedIncludes, ['elements']);
            $requestedIncludes = array_merge($requestedIncludes, ['lessons', 'quizzes', 'assignments']);
            $requestedIncludes = array_unique($requestedIncludes);
            
            // Override the include parameter for Spatie
            request()->merge(['include' => implode(',', $requestedIncludes)]);
        }

        // Manually handle includes with ordering based on user role
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

        // Handle search manually before QueryBuilder
        $searchQuery = request()->query('search');
        if ($searchQuery && trim((string) $searchQuery) !== '') {
            $query->search($searchQuery);
        }

        // Build QueryBuilder with Spatie (without includes, we handle them manually above)
        $queryBuilder = \Spatie\QueryBuilder\QueryBuilder::for($query)
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::exact('status'),
                \Spatie\QueryBuilder\AllowedFilter::callback('course_slug', function ($query, $value) {
                    $query->whereHas('course', function ($q) use ($value) {
                        $q->where('slug', $value);
                    });
                }),
                \Spatie\QueryBuilder\AllowedFilter::callback('search', function ($query, $value) {
                    // This is just to allow the filter, actual search is handled above
                    // to avoid double search execution
                }),
            ])
            ->allowedSorts(['order', 'title', 'created_at', 'updated_at'])
            ->defaultSort('-created_at')
            ->with('course:id,slug,title'); // Always load course for course_slug and course_name

        return $queryBuilder->paginate($perPage);
    }

    public function getContentOrder(Unit $unit): array
    {
        $lessons = $unit->lessons()->orderBy('order')->get(['id', 'title', 'order', 'status']);
        $assignments = \Modules\Learning\Models\Assignment::where('unit_id', $unit->id)
            ->orderBy('order')
            ->get(['id', 'title', 'order', 'status']);
        $quizzes = \Modules\Learning\Models\Quiz::where('unit_id', $unit->id)
            ->orderBy('order')
            ->get(['id', 'title', 'order', 'status']);

        $content = collect();

        foreach ($lessons as $lesson) {
            $content->push([
                'type' => 'lesson',
                'id' => $lesson->id,
                'title' => $lesson->title,
                'order' => $lesson->order,
                'status' => $lesson->status,
            ]);
        }

        foreach ($assignments as $assignment) {
            $content->push([
                'type' => 'assignment',
                'id' => $assignment->id,
                'title' => $assignment->title,
                'order' => $assignment->order,
                'status' => $assignment->status->value,
            ]);
        }

        foreach ($quizzes as $quiz) {
            $content->push([
                'type' => 'quiz',
                'id' => $quiz->id,
                'title' => $quiz->title,
                'order' => $quiz->order,
                'status' => $quiz->status->value,
            ]);
        }

        return $content->sortBy('order')->values()->toArray();
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
        return \Illuminate\Support\Facades\DB::transaction(function () use ($unit, $contentOrder) {
            foreach ($contentOrder as $item) {
                $type = $item['type'] ?? null;
                $id = $item['id'] ?? null;
                $order = $item['order'] ?? null;

                if (! $type || ! $id || ! $order) {
                    continue;
                }

                match ($type) {
                    'lesson' => \Modules\Schemes\Models\Lesson::where('id', $id)
                        ->where('unit_id', $unit->id)
                        ->update(['order' => $order]),
                    'assignment' => \Modules\Learning\Models\Assignment::where('id', $id)
                        ->where('unit_id', $unit->id)
                        ->update(['order' => $order]),
                    'quiz' => \Modules\Learning\Models\Quiz::where('id', $id)
                        ->where('unit_id', $unit->id)
                        ->update(['order' => $order]),
                    default => null,
                };
            }

            return $this->getContentOrder($unit);
        });
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
