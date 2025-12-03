<?php

namespace Modules\Schemes\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Contracts\Repositories\UnitRepositoryInterface;
use Modules\Schemes\Models\Unit;

class UnitRepository implements UnitRepositoryInterface
{
    public function findByCourse(int $courseId, array $params = []): LengthAwarePaginator
    {
        $query = Unit::where('course_id', $courseId);

        if (! empty($params['filter']['status'])) {
            $query->where('status', $params['filter']['status']);
        }

        if (! empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $sortField = $params['sort'] ?? 'order';
        $sortDirection = 'asc';
        if (str_starts_with($sortField, '-')) {
            $sortField = substr($sortField, 1);
            $sortDirection = 'desc';
        }

        $allowedSortFields = ['id', 'code', 'title', 'order', 'status', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('order', 'asc');
        }

        $perPage = isset($params['per_page']) ? max(1, (int) $params['per_page']) : 15;

        return $query->paginate($perPage);
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
