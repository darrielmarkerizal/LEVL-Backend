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
        'course_id',
        'user_id',
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

    public function paginateByCourse(int $courseId, array $params, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model()::query()
            ->where('course_id', $courseId)
            ->with(['user:id,name,email']);

        $filteredParams = $params;
        unset($filteredParams['filter']['course_id']);

        return $this->filteredPaginate(
            $query,
            $filteredParams,
            ['status', 'user_id', 'enrolled_at', 'completed_at'],
            $this->allowedSorts,
            $this->defaultSort,
            $perPage
        );
    }

    public function paginateByCourseIds(array $courseIds, array $params, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model()::query()
            ->with(['user:id,name,email', 'course:id,slug,title,enrollment_type']);

        if (! empty($courseIds)) {
            $query->whereIn('course_id', $courseIds);
        } else {
            $query->whereRaw('1 = 0');
        }

        $filteredParams = $params;
        unset($filteredParams['filter']['course_id']);

        return $this->filteredPaginate(
            $query,
            $filteredParams,
            $this->allowedFilters,
            $this->allowedSorts,
            $this->defaultSort,
            $perPage
        );
    }

    public function paginateByUser(int $userId, array $params, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model()::query()
            ->where('user_id', $userId)
            ->with(['course:id,slug,title,status']);

        $filteredParams = $params;
        unset($filteredParams['filter']['user_id']);

        return $this->filteredPaginate(
            $query,
            $filteredParams,
            ['status', 'course_id', 'enrolled_at', 'completed_at'],
            $this->allowedSorts,
            $this->defaultSort,
            $perPage
        );
    }

    public function findByCourseAndUser(int $courseId, int $userId): ?Enrollment
    {
        return $this->model()::query()
            ->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();
    }
}
