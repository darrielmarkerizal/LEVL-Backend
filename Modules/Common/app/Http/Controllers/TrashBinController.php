<?php

declare(strict_types=1);

namespace Modules\Common\Http\Controllers;

use App\Models\TrashBin;
use App\Services\Trash\TrashBinService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TrashBinController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TrashBinService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $search = $request->query('search');

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

        if (is_string($search) && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($subQuery) use ($term): void {
                $subQuery->search($term)
                    ->orWhereRaw("COALESCE(metadata->>'title', '') ILIKE ?", ["%{$term}%"]);
            });
        }

        $paginator = $query->paginate($perPage)->appends($request->query());

        return $this->paginateResponse($paginator, 'messages.trash_bins.list_retrieved');
    }

    public function restore(int $trashBinId): JsonResponse
    {
        $bin = TrashBin::query()->findOrFail($trashBinId);

        $this->service->restoreFromTrashBin($bin);

        return $this->success(null, 'messages.trash_bins.restored');
    }

    public function forceDelete(int $trashBinId): JsonResponse
    {
        $bin = TrashBin::query()->findOrFail($trashBinId);

        $this->service->forceDeleteFromTrashBin($bin);

        return $this->success(null, 'messages.trash_bins.force_deleted');
    }

    public function forceDeleteAll(Request $request): JsonResponse
    {
        $resourceType = $request->query('resource_type');

        $count = $this->service->forceDeleteAll(
            is_string($resourceType) && $resourceType !== '' ? $resourceType : null
        );

        return $this->success(
            ['deleted' => $count],
            'messages.trash_bins.force_deleted_all',
            ['count' => $count]
        );
    }
}
