<?php

declare(strict_types=1);

namespace Modules\Common\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Common\Http\Resources\CourseMasterDataResource;
use Modules\Common\Http\Resources\MasterDataTypeResource;
use Modules\Common\Services\MasterDataService;

class MasterDataController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly MasterDataService $service
    ) {}

    public function types(Request $request): JsonResponse
    {
        $params = $this->service->extractQueryParams($request->query->all());
        $paginator = $this->service->getAvailableTypes($params);
        $paginator->getCollection()->transform(fn ($item) => new MasterDataTypeResource($item));

        return $this->paginateResponse($paginator, __('messages.master_data.types_retrieved'));
    }

    public function courses(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $data = $this->service->getCourses($search);

        return $this->success(
            CourseMasterDataResource::collection($data),
            __('messages.master_data.retrieved')
        );
    }

    public function get(string $type): JsonResponse
    {
        $data = $this->service->get($type);

        return $this->success($data, __('messages.master_data.retrieved'));
    }

    public function index(Request $request, string $type): JsonResponse
    {
        $data = $this->service->getAll($type, $request->query->all());

        return $this->success($data, __('messages.master_data.retrieved'));
    }

    public function show(string $type, int|string $id): JsonResponse
    {
        $item = $this->service->find($type, (int) $id);

        if (! $item) {
            return $this->error(__('messages.master_data.not_found'), [], 404);
        }

        return $this->success($item, __('messages.master_data.retrieved'));
    }

    public function store(Request $request, string $type): JsonResponse
    {
        if (! $this->service->isCrudAllowed($type)) {
            return $this->forbidden(__('messages.master_data.crud_not_allowed'));
        }

        $data = $request->validate($this->service->getValidationRules());
        $item = $this->service->create($type, $data);

        return $this->success($item, __('messages.master_data.created'));
    }

    public function update(Request $request, string $type, int|string $id): JsonResponse
    {
        if (! $this->service->isCrudAllowed($type)) {
            return $this->forbidden(__('messages.master_data.crud_not_allowed'));
        }

        $data = $request->validate($this->service->getValidationRules(true));
        $item = $this->service->update($type, (int) $id, $data);

        return $this->success($item, __('messages.master_data.updated'));
    }

    public function destroy(string $type, int|string $id): JsonResponse
    {
        if (! $this->service->isCrudAllowed($type)) {
            return $this->forbidden(__('messages.master_data.crud_not_allowed'));
        }

        $this->service->delete($type, (int) $id);

        return $this->success(null, __('messages.master_data.deleted'));
    }
}
