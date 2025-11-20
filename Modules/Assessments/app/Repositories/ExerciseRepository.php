<?php

namespace Modules\Assessments\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Assessments\Models\Exercise;

class ExerciseRepository
{
    public function query(): Builder
    {
        return Exercise::query();
    }

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);

        return $this->applyFilters($this->query(), $filters)
            ->paginate($perPage)
            ->appends($filters);
    }

    public function create(array $attributes): Exercise
    {
        return Exercise::create($attributes);
    }

    public function update(Exercise $exercise, array $attributes): Exercise
    {
        $exercise->fill($attributes)->save();

        return $exercise;
    }

    public function delete(Exercise $exercise): bool
    {
        return $exercise->delete();
    }

    public function questionCount(Exercise $exercise): int
    {
        return $exercise->questions()->count();
    }

    public function questionsWithOptions(Exercise $exercise): Collection
    {
        return $exercise->questions()
            ->with('options')
            ->orderBy('order')
            ->get();
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $scopeType = $filters['scope_type'] ?? ($filters['filter']['scope_type'] ?? null);
        $scopeId = $filters['scope_id'] ?? ($filters['filter']['scope_id'] ?? null);
        if ($scopeType && $scopeId) {
            $query->where('scope_type', $scopeType)
                ->where('scope_id', $scopeId);
        }

        $status = $filters['status'] ?? ($filters['filter']['status'] ?? null);
        if ($status) {
            $query->where('status', $status);
        }

        $authorId = $filters['author_id'] ?? ($filters['filter']['author_id'] ?? null);
        if ($authorId) {
            $query->where('created_by', $authorId);
        }

        return $query;
    }
}
