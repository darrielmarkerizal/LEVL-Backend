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
use Modules\Trash\Http\Resources\TrashBinResource;

class TrashBinController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TrashBinManagementServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate(auth('api')->user(), $request->query());
        $paginator->setCollection(TrashBinResource::collection($paginator->getCollection())->collection);

        return $this->paginateResponse($paginator, 'messages.trash_bins.list_retrieved');
    }

    public function restore(int $trashBinId): JsonResponse
    {
        $result = $this->service->restore(auth('api')->user(), $trashBinId);

        if (($result['queued'] ?? false) === true) {
            return $this->success($result, 'messages.trash_bins.restore_queued', [], 202);
        }

        return $this->success(null, 'messages.trash_bins.restored');
    }

    public function restoreAll(Request $request): JsonResponse
    {
        $resourceType = $request->query('resource_type');
        $result = $this->service->restoreAll(
            auth('api')->user(),
            is_string($resourceType) && $resourceType !== '' ? $resourceType : null
        );

        return $this->success($result, 'messages.trash_bins.restore_all_queued', [], 202);
    }

    public function bulkRestore(BulkRestoreTrashBinsRequest $request): JsonResponse
    {
        $result = $this->service->bulkRestore(auth('api')->user(), $request->validated('ids'));

        return $this->success($result, 'messages.trash_bins.bulk_restore_queued', [], 202);
    }

    public function forceDelete(int $trashBinId): JsonResponse
    {
        $result = $this->service->forceDelete(auth('api')->user(), $trashBinId);

        if (($result['queued'] ?? false) === true) {
            return $this->success($result, 'messages.trash_bins.force_delete_queued', [], 202);
        }

        return $this->success(null, 'messages.trash_bins.force_deleted');
    }

    public function forceDeleteAll(Request $request): JsonResponse
    {
        $resourceType = $request->query('resource_type');

        $result = $this->service->forceDeleteAll(
            auth('api')->user(),
            is_string($resourceType) && $resourceType !== '' ? $resourceType : null
        );

        return $this->success($result, 'messages.trash_bins.force_delete_all_queued', [], 202);
    }

    public function bulkForceDelete(BulkForceDeleteTrashBinsRequest $request): JsonResponse
    {
        $result = $this->service->bulkForceDelete(auth('api')->user(), $request->validated('ids'));

        return $this->success($result, 'messages.trash_bins.bulk_force_delete_queued', [], 202);
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
