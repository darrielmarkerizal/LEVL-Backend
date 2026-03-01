<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Services\ProgressionService;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonFinder
{
    use \App\Support\Traits\BuildsQueryBuilderRequest;

    public function __construct(
        private readonly LessonRepositoryInterface $repository
    ) {}

    public function paginate(int $unitId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        return cache()->tags(['schemes', 'lessons'])->remember(
            "schemes:lessons:unit:{$unitId}:{$perPage}:".request('page', 1).':'.md5(json_encode($filters)),
            300,
            function () use ($unitId, $filters, $perPage) {
                return QueryBuilder::for(Lesson::class, $this->buildQueryBuilderRequest($filters))
                    ->where('unit_id', $unitId)
                    ->allowedFilters([
                        AllowedFilter::exact('content_type'),
                        AllowedFilter::exact('status'),
                    ])
                    ->allowedIncludes(['unit', 'blocks', 'assignments'])
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
            $progression = app(ProgressionService::class);
            $enrollment = $progression->getEnrollmentForCourse($course->id, $user->id);
            if (! $enrollment || ! $progression->canAccessLesson($lesson, $enrollment)) {
                throw new \App\Exceptions\BusinessException(__('messages.lessons.locked_prerequisite'), 403);
            }
        }

        return $lesson->load('blocks');
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
                $query->with($validIncludes);
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
