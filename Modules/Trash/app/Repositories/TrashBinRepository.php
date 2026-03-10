<?php

declare(strict_types=1);

namespace Modules\Trash\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Modules\Trash\Contracts\Repositories\TrashBinRepositoryInterface;
use Modules\Trash\Models\TrashBin;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TrashBinRepository implements TrashBinRepositoryInterface
{
    public function paginateForAccess(int $actorId, bool $isSuperadmin, array $accessibleCourseIds, array $params): LengthAwarePaginator
    {
        $perPage = max(1, min((int) ($params['per_page'] ?? 15), 100));
        $search = isset($params['search']) && is_string($params['search']) ? trim($params['search']) : null;

        $query = QueryBuilder::for(TrashBin::query())
            ->allowedFilters([
                AllowedFilter::exact('resource_type'),
                AllowedFilter::exact('trashable_type'),
                AllowedFilter::exact('group_uuid'),
                AllowedFilter::exact('deleted_by'),
                AllowedFilter::exact('root_resource_type'),
                AllowedFilter::exact('root_resource_id'),
            ])
            ->allowedSorts([
                'id',
                'resource_type',
                'trashable_type',
                'deleted_at',
                'expires_at',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-deleted_at');

        if (! $isSuperadmin) {
            $query->where(function ($sub) use ($actorId, $accessibleCourseIds): void {
                $sub->where('deleted_by', $actorId);

                if (! empty($accessibleCourseIds)) {
                    $sub->orWhereIn(DB::raw("(metadata->>'course_id')::bigint"), $accessibleCourseIds);
                }
            });
        }

        if ($search) {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->search($search)
                    ->orWhereRaw("COALESCE(metadata->>'title', '') ILIKE ?", ["%{$search}%"]);
            });
        }

        return $query->paginate($perPage)->appends($params);
    }

    public function findByIdOrFail(int $id): TrashBin
    {
        return TrashBin::query()->findOrFail($id);
    }

    public function findManyByIds(array $ids): Collection
    {
        return TrashBin::query()->whereIn('id', $ids)->orderBy('id')->get();
    }

    public function getSourceTypes(): array
    {
        return TrashBin::query()
            ->select('resource_type')
            ->distinct()
            ->orderBy('resource_type')
            ->pluck('resource_type')
            ->values()
            ->toArray();
    }

    public function getSourceTypesForAccess(int $actorId, array $accessibleCourseIds): array
    {
        return TrashBin::query()
            ->where(function ($sub) use ($actorId, $accessibleCourseIds): void {
                $sub->where('deleted_by', $actorId);

                if (! empty($accessibleCourseIds)) {
                    $sub->orWhereIn(DB::raw("(metadata->>'course_id')::bigint"), $accessibleCourseIds);
                }
            })
            ->select('resource_type')
            ->distinct()
            ->orderBy('resource_type')
            ->pluck('resource_type')
            ->values()
            ->toArray();
    }
}
