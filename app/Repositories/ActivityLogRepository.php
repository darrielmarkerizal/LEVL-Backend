<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogRepository
{
    /**
     * Get paginated activity logs using Spatie Query Builder.
     *
     * Supports:
     * - filter[log_name], filter[event], filter[subject_type], filter[subject_id]
     * - filter[causer_type], filter[causer_id]
     * - sort: id, created_at, event, log_name (prefix with - for desc)
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildQuery()->paginate($perPage);
    }

    /**
     * Find activity log by ID.
     */
    public function find(int $id): ?Activity
    {
        return Activity::with(['causer', 'subject'])->find($id);
    }

    /**
     * Build query with Spatie Query Builder.
     */
    private function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for(Activity::class)
            ->allowedFilters([
                AllowedFilter::exact('log_name'),
                AllowedFilter::exact('event'),
                AllowedFilter::exact('subject_type'),
                AllowedFilter::exact('subject_id'),
                AllowedFilter::exact('causer_type'),
                AllowedFilter::exact('causer_id'),
            ])
            ->allowedSorts(['id', 'created_at', 'event', 'log_name'])
            ->defaultSort('-created_at')
            ->with(['causer', 'subject']);
    }
}
