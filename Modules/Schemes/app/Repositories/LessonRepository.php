<?php

namespace Modules\Schemes\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\Models\Lesson;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonRepository implements LessonRepositoryInterface
{
    /**
     * Find lessons by unit with Spatie Query Builder + Scout search.
     *
     * Supports:
     * - filter[status], filter[content_type], filter[search] (Meilisearch)
     * - sort: id, title, order, status, duration_minutes, created_at, updated_at (prefix with - for desc)
     */
    public function findByUnit(int $unitId, int $perPage = 15): LengthAwarePaginator
    {
        $searchQuery = request('filter.search');

        $builder = QueryBuilder::for(Lesson::class)
            ->where('unit_id', $unitId);

        // If search query exists, use Scout to get matching IDs
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Lesson::search($searchQuery)
                ->query(fn ($q) => $q->where('unit_id', $unitId))
                ->keys()
                ->toArray();
            $builder->whereIn('id', $ids);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('content_type'),
            ])
            ->allowedSorts(['id', 'title', 'order', 'status', 'duration_minutes', 'created_at', 'updated_at', 'published_at'])
            ->defaultSort('order')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Lesson
    {
        return Lesson::find($id);
    }

    public function findByUnitAndId(int $unitId, int $id): ?Lesson
    {
        return Lesson::where('unit_id', $unitId)->find($id);
    }

    public function create(array $data): Lesson
    {
        return Lesson::create($data);
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);

        return $lesson->fresh();
    }

    public function delete(Lesson $lesson): bool
    {
        return $lesson->delete();
    }

    public function getMaxOrderForUnit(int $unitId): int
    {
        return (int) Lesson::where('unit_id', $unitId)->max('order') ?? 0;
    }

    public function getAllByUnit(int $unitId): Collection
    {
        return Lesson::where('unit_id', $unitId)
            ->orderBy('order', 'asc')
            ->get();
    }
}
