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
            // Optimized search: use ILIKE for basic matching, similarity for ordering
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->whereRaw("COALESCE(metadata->>'title', '') ILIKE ?", ["%{$search}%"])
                    ->orWhereHas('deletedByUser', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('username', 'ILIKE', "%{$search}%");
                    });
            });
            
            // Add similarity-based ordering for better relevance
            $query->orderByRaw("similarity(COALESCE(metadata->>'title', ''), ?) DESC", [$search]);
        }

        return $query
            ->with(['deletedByUser:id,name,username'])
            ->paginate($perPage)
            ->appends($params);
    }

    public function findByIdOrFail(int $id): TrashBin
    {
        return TrashBin::query()->findOrFail($id);
    }

    public function findManyByIds(array $ids): Collection
    {
        return TrashBin::query()->whereIn('id', $ids)->orderBy('id')->get();
    }

    public function countByGroupUuid(string $groupUuid): int
    {
        return TrashBin::query()->where('group_uuid', $groupUuid)->count();
    }

    public function getSourceTypes(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'trash_bins:source_types',
            now()->addHours(1),
            fn () => TrashBin::query()
                ->select('resource_type')
                ->distinct()
                ->orderBy('resource_type')
                ->pluck('resource_type')
                ->values()
                ->toArray()
        );
    }

    public function getSourceTypesForAccess(int $actorId, array $accessibleCourseIds): array
    {
        // Don't cache user-specific data
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

    public function getAllForAccess(?int $actorId, bool $isSuperadmin, array $accessibleCourseIds, ?string $resourceType = null): Collection
    {
        $query = TrashBin::query();

        if (! $isSuperadmin && $actorId !== null) {
            $query->where(function ($sub) use ($actorId, $accessibleCourseIds): void {
                $sub->where('deleted_by', $actorId);

                if (! empty($accessibleCourseIds)) {
                    $sub->orWhereIn(DB::raw("(metadata->>'course_id')::bigint"), $accessibleCourseIds);
                }
            });
        }

        if ($resourceType !== null) {
            $query->where('resource_type', $resourceType);
        }

        return $query->orderBy('id')->get();
    }
}
