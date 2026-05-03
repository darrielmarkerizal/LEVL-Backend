<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Services\PrerequisiteService;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonFinder
{
    use \App\Support\Traits\BuildsQueryBuilderRequest;

    public function __construct(
        private readonly LessonRepositoryInterface $repository,
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function paginate(int $unitId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        $user = auth('api')->user();
        $userKey = $user ? $user->id . ':' . implode(',', $user->getRoleNames()->toArray()) : 'guest';

        return cache()->tags(['schemes', 'lessons'])->remember(
            "schemes:lessons:unit:{$unitId}:{$perPage}:{$userKey}:".request('page', 1).':'.md5(json_encode($filters)),
            300,
            function () use ($unitId, $filters, $perPage) {
                return QueryBuilder::for(Lesson::class, $this->buildQueryBuilderRequest($filters))
                    ->where('unit_id', $unitId)
                    ->allowedFilters([
                        AllowedFilter::exact('content_type'),
                        AllowedFilter::exact('status'),
                    ])
                    ->allowedIncludes([
                        'unit',
                        \Spatie\QueryBuilder\AllowedInclude::callback('blocks', function ($query) {
                            $query->orderBy('order');
                        }),
                    ])
                    ->allowedSorts(['order', 'title', 'created_at'])
                    ->defaultSort('order')
                    ->paginate($perPage);
            }
        );
    }

    public function find(int $id): ?Lesson
    {
        return $this->repository->findById($id);
    }

    public function findOrFail(int $id): Lesson
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function getLessonForUser(Lesson $lesson, Course $course, ?User $user): Lesson
    {
        if ($user?->hasRole('Student')) {
            $enrollment = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereIn('status', [\Modules\Enrollments\Enums\EnrollmentStatus::Active, \Modules\Enrollments\Enums\EnrollmentStatus::Completed])
                ->first();

            if (! $enrollment) {
                throw new \App\Exceptions\BusinessException(__('messages.enrollments.not_enrolled'), [], 403);
            }

            // Lesson yang sudah diselesaikan selalu bisa diakses kembali
            if (! $lesson->isCompletedBy($user->id)) {
                $unit = $lesson->unit()->first();
                if (! $unit) {
                    throw new \App\Exceptions\BusinessException(__('messages.lessons.locked_prerequisite'), [], 403);
                }

                $unitAccess = $this->prerequisiteService->checkUnitAccess($unit, $user->id);
                if (! ($unitAccess['accessible'] ?? false)) {
                    throw new \App\Exceptions\BusinessException(__('messages.lessons.locked_prerequisite'), [], 403);
                }

                $lessonAccess = $this->prerequisiteService->checkLessonAccess($lesson, $user->id);
                if (! ($lessonAccess['accessible'] ?? false)) {
                    throw new \App\Exceptions\BusinessException(__('messages.lessons.locked_prerequisite'), [], 403);
                }
            }
        }

        return $lesson->load(['blocks' => fn($q) => $q->orderBy('order')]);
    }

    public function paginateAll(array $filters = [], int $perPage = 15, ?\Modules\Auth\Models\User $user = null): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        $query = Lesson::query();

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['content_type'])) {
            $query->where('content_type', $filters['content_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if ($user && ! $user->hasRole('Superadmin')) {
            $query->whereHas('unit.course', function ($q) use ($user) {
                if ($user->hasRole(['Admin', 'Instructor'])) {
                    $q->where('instructor_id', $user->id);
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

        if (isset($filters['unit_slug'])) {
            $query->whereHas('unit', function ($q) use ($filters) {
                $q->where('slug', $filters['unit_slug']);
            });
        }

        if (isset($filters['course_slug'])) {
            $query->whereHas('unit.course', function ($q) use ($filters) {
                $q->where('slug', $filters['course_slug']);
            });
        }

        if (isset($filters['include'])) {
            $includes = is_array($filters['include']) ? $filters['include'] : explode(',', $filters['include']);
            $allowedIncludes = ['unit', 'unit.course', 'blocks'];
            $validIncludes = array_intersect($includes, $allowedIncludes);
            if (! empty($validIncludes)) {
                foreach ($validIncludes as $include) {
                    if ($include === 'blocks') {
                        $query->with(['blocks' => fn($q) => $q->orderBy('order')]);
                    } else {
                        $query->with($include);
                    }
                }
            }
        }

        $sortField = $filters['sort'] ?? '-created_at';
        $sortDirection = 'asc';
        if (str_starts_with($sortField, '-')) {
            $sortDirection = 'desc';
            $sortField = substr($sortField, 1);
        }

        $allowedSorts = ['order', 'title', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }
}
