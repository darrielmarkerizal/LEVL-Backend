<?php

declare(strict_types=1);

namespace Modules\Trash\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Trash\Contracts\Services\TrashBinManagementServiceInterface;
use Modules\Trash\Http\Requests\BulkForceDeleteTrashBinsRequest;
use Modules\Trash\Http\Requests\BulkRestoreTrashBinsRequest;

class TrashBinController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TrashBinManagementServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate(auth('api')->user(), $request->query());

        return $this->paginateResponse($paginator, 'messages.trash_bins.list_retrieved');
    }

    public function restore(int $trashBinId): JsonResponse
    {
        $this->service->restore(auth('api')->user(), $trashBinId);

        return $this->success(null, 'messages.trash_bins.restored');
    }

    public function restoreAll(Request $request): JsonResponse
    {
        $resourceType = $request->query('resource_type');
        $count = $this->service->restoreAll(
            auth('api')->user(),
            is_string($resourceType) && $resourceType !== '' ? $resourceType : null
        );

        return $this->success(
            ['restored' => $count],
            'messages.trash_bins.restore_all',
            ['count' => $count]
        );
    }

    public function bulkRestore(BulkRestoreTrashBinsRequest $request): JsonResponse
    {
        $count = $this->service->bulkRestore(auth('api')->user(), $request->validated('ids'));

        return $this->success(
            ['restored' => $count],
            'messages.trash_bins.bulk_restored',
            ['count' => $count]
        );
    }

    public function forceDelete(int $trashBinId): JsonResponse
    {
        $this->service->forceDelete(auth('api')->user(), $trashBinId);

        return $this->success(null, 'messages.trash_bins.force_deleted');
    }

    public function forceDeleteAll(Request $request): JsonResponse
    {
        $resourceType = $request->query('resource_type');

        $count = $this->service->forceDeleteAll(
            auth('api')->user(),
            is_string($resourceType) && $resourceType !== '' ? $resourceType : null
        );

        return $this->success(
            ['deleted' => $count],
            'messages.trash_bins.force_deleted_all',
            ['count' => $count]
        );
    }

    public function bulkForceDelete(BulkForceDeleteTrashBinsRequest $request): JsonResponse
    {
        $count = $this->service->bulkForceDelete(auth('api')->user(), $request->validated('ids'));

        return $this->success(
            ['deleted' => $count],
            'messages.trash_bins.bulk_force_deleted',
            ['count' => $count]
        );
    }

    public function sourceTypes(): JsonResponse
    {
        $types = $this->service->getSourceTypes(auth('api')->user());

        return $this->success($types, 'messages.trash_bins.source_types_retrieved');
    }

    public function masterSourceTypes(): JsonResponse
    {
        $types = $this->service->getMasterSourceTypes();

        return $this->success($types, 'messages.trash_bins.source_types_retrieved');
    }
}
