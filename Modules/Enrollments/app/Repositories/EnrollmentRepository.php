<?php

namespace Modules\Enrollments\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Enrollments\Contracts\Repositories\EnrollmentRepositoryInterface;
use Modules\Enrollments\Models\Enrollment;

class EnrollmentRepository extends BaseRepository implements EnrollmentRepositoryInterface
{
    protected array $allowedFilters = [
        'status',
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
    ];

    protected array $allowedSorts = [
        'id',
        'created_at',
        'updated_at',
        'status',
        'enrolled_at',
        'completed_at',
        'progress_percent',
    ];

    protected string $defaultSort = '-created_at';

    protected array $with = ['user:id,name,email', 'course:id,slug,title,enrollment_type'];

    protected function model(): string
    {
        return Enrollment::class;
    }

    public function paginateByCourse(int $courseId, array $params = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()
            ->where('course_id', $courseId)
            ->with(['user:id,name,email']);

        $searchQuery = $params['search'] ?? request('filter.search') ?? request('search');

        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Enrollment::search($searchQuery)
                ->query(fn ($q) => $q->where('course_id', $courseId))
                ->keys()
                ->toArray();

            if (! empty($ids)) {
                $query->whereIn('id', $ids);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $this->filteredPaginate(
            $query,
            $params,
            $this->allowedFilters,
            $this->allowedSorts,
            $this->defaultSort,
            $perPage
        );
    }

    public function paginateByCourseIds(array $courseIds, array $params = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()
            ->with(['user:id,name,email', 'course:id,slug,title,enrollment_type']);

        if (! empty($courseIds)) {
            $query->whereIn('course_id', $courseIds);
        } else {
            $query->whereRaw('1 = 0');
        }

        $searchQuery = $params['search'] ?? request('filter.search') ?? request('search');

        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Enrollment::search($searchQuery)
                ->query(fn ($q) => $q->whereIn('course_id', $courseIds))
                ->keys()
                ->toArray();

            if (! empty($ids)) {
                $query->whereIn('id', $ids);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $this->filteredPaginate(
            $query,
            $params,
            $this->allowedFilters,
            $this->allowedSorts,
            $this->defaultSort,
            $perPage
        );
    }

    public function paginateByUser(int $userId, array $params = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()
            ->where('user_id', $userId)
            ->with(['course:id,slug,title,status']);

        $searchQuery = $params['search'] ?? request('filter.search') ?? request('search');

        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Enrollment::search($searchQuery)
                ->query(fn ($q) => $q->where('user_id', $userId))
                ->keys()
                ->toArray();

            if (! empty($ids)) {
                $query->whereIn('id', $ids);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $this->filteredPaginate(
            $query,
            $params,
            $this->allowedFilters,
            $this->allowedSorts,
            $this->defaultSort,
            $perPage
        );
    }

    public function findByCourseAndUser(int $courseId, int $userId): ?Enrollment
    {
        return $this->query()
            ->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();
    }
}
