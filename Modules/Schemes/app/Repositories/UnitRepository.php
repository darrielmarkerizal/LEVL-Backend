<?php

namespace Modules\Schemes\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Contracts\Repositories\UnitRepositoryInterface;
use Modules\Schemes\Models\Unit;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UnitRepository implements UnitRepositoryInterface
{
    /**
     * Find units by course with Spatie Query Builder + Scout search.
     *
     * Supports:
     * - filter[status], filter[search] (Meilisearch)
     * - sort: id, code, title, order, status, created_at, updated_at (prefix with - for desc)
     */
    public function findByCourse(int $courseId, int $perPage = 15): LengthAwarePaginator
    {
        $searchQuery = request('filter.search');

        $builder = QueryBuilder::for(Unit::class)
            ->where('course_id', $courseId);

        // If search query exists, use Scout to get matching IDs
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Unit::search($searchQuery)
                ->query(fn ($q) => $q->where('course_id', $courseId))
                ->keys()
                ->toArray();
            $builder->whereIn('id', $ids);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['id', 'code', 'title', 'order', 'status', 'created_at', 'updated_at'])
            ->defaultSort('order')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Unit
    {
        return Unit::find($id);
    }

    public function findByCourseAndId(int $courseId, int $id): ?Unit
    {
        return Unit::where('course_id', $courseId)->find($id);
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->fresh();
    }

    public function delete(Unit $unit): bool
    {
        return $unit->delete();
    }

    public function getMaxOrderForCourse(int $courseId): int
    {
        return (int) Unit::where('course_id', $courseId)->max('order') ?? 0;
    }

    public function reorderUnits(int $courseId, array $unitOrders): void
    {
        foreach ($unitOrders as $unitId => $order) {
            Unit::where('course_id', $courseId)
                ->where('id', $unitId)
                ->update(['order' => (int) $order]);
        }
    }

    public function getAllByCourse(int $courseId): Collection
    {
        return Unit::where('course_id', $courseId)
            ->orderBy('order', 'asc')
            ->get();
    }
}
