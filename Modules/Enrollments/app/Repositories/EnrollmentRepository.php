<?php

namespace Modules\Enrollments\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Enrollments\Contracts\Repositories\EnrollmentRepositoryInterface;
use Modules\Enrollments\Models\Enrollment;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    protected array $with = ['user:id,name,email', 'course:id,slug,title,enrollment_type'];

    /**
     * Paginate enrollments by course with Spatie Query Builder + Scout search.
     *
     * Supports:
     * - filter[search] (Meilisearch), filter[status], filter[user_id], filter[enrolled_at], filter[completed_at]
     * - sort: id, created_at, updated_at, status, enrolled_at, completed_at, progress_percent
     */
    public function paginateByCourse(int $courseId, int $perPage = 15): LengthAwarePaginator
    {
        $searchQuery = request('filter.search');

        $builder = QueryBuilder::for(Enrollment::class)
            ->where('course_id', $courseId)
            ->with(['user:id,name,email']);

        // Use Scout/Meilisearch for full-text search if available
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Enrollment::search($searchQuery)
                ->query(fn ($q) => $q->where('course_id', $courseId))
                ->keys()
                ->toArray();
            $builder->whereIn('id', $ids);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('enrolled_at'),
                AllowedFilter::exact('completed_at'),
            ])
            ->allowedSorts(['id', 'created_at', 'updated_at', 'status', 'enrolled_at', 'completed_at', 'progress_percent'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Paginate enrollments by multiple course IDs with Spatie Query Builder + Scout.
     */
    public function paginateByCourseIds(array $courseIds, int $perPage = 15): LengthAwarePaginator
    {
        $searchQuery = request('filter.search');

        $builder = QueryBuilder::for(Enrollment::class)
            ->with(['user:id,name,email', 'course:id,slug,title,enrollment_type']);

        if (! empty($courseIds)) {
            $builder->whereIn('course_id', $courseIds);
        } else {
            $builder->whereRaw('1 = 0');
        }

        // Use Scout/Meilisearch for full-text search if available
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Enrollment::search($searchQuery)
                ->query(fn ($q) => $q->whereIn('course_id', $courseIds))
                ->keys()
                ->toArray();
            $builder->whereIn('id', $ids);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('course_id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('enrolled_at'),
                AllowedFilter::exact('completed_at'),
            ])
            ->allowedSorts(['id', 'created_at', 'updated_at', 'status', 'enrolled_at', 'completed_at', 'progress_percent'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Paginate enrollments by user with Spatie Query Builder + Scout.
     */
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        $searchQuery = request('filter.search');

        $builder = QueryBuilder::for(Enrollment::class)
            ->where('user_id', $userId)
            ->with(['course:id,slug,title,status']);

        // Use Scout/Meilisearch for full-text search if available
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Enrollment::search($searchQuery)
                ->query(fn ($q) => $q->where('user_id', $userId))
                ->keys()
                ->toArray();
            $builder->whereIn('id', $ids);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('course_id'),
                AllowedFilter::exact('enrolled_at'),
                AllowedFilter::exact('completed_at'),
            ])
            ->allowedSorts(['id', 'created_at', 'updated_at', 'status', 'enrolled_at', 'completed_at', 'progress_percent'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    public function findByCourseAndUser(int $courseId, int $userId): ?Enrollment
    {
        return Enrollment::query()
            ->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findById(int $id): ?Enrollment
    {
        return Enrollment::find($id);
    }

    public function create(array $attributes): Enrollment
    {
        return Enrollment::create($attributes);
    }

    public function update(Enrollment $enrollment, array $attributes): Enrollment
    {
        $enrollment->fill($attributes)->save();

        return $enrollment;
    }

    public function delete(Enrollment $enrollment): bool
    {
        return $enrollment->delete();
    }
}
