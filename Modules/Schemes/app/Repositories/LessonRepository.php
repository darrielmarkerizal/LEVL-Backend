<?php

namespace Modules\Schemes\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\Models\Lesson;

class LessonRepository implements LessonRepositoryInterface
{
    public function findByUnit(int $unitId, array $params = []): LengthAwarePaginator
    {
        $query = Lesson::where('unit_id', $unitId);

        if (! empty($params['filter']['status'])) {
            $query->where('status', $params['filter']['status']);
        }

        if (! empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('markdown_content', 'like', "%{$search}%");
            });
        }

        $sortField = $params['sort'] ?? 'order';
        $sortDirection = 'asc';
        if (str_starts_with($sortField, '-')) {
            $sortField = substr($sortField, 1);
            $sortDirection = 'desc';
        }

        $allowedSortFields = ['id', 'title', 'order', 'status', 'duration_minutes', 'created_at', 'updated_at', 'published_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('order', 'asc');
        }

        $perPage = isset($params['per_page']) ? max(1, (int) $params['per_page']) : 15;

        return $query->paginate($perPage);
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
