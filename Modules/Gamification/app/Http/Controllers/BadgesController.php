<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use App\Support\Traits\ProvidesMetadata;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Gamification\Http\Requests\BadgeStoreRequest;
use Modules\Gamification\Http\Requests\BadgeUpdateRequest;
use Modules\Gamification\Http\Resources\BadgeResource;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Services\BadgeService;

class BadgesController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;
    use HandlesFiltering;
    use ProvidesMetadata;

    public function __construct(private readonly BadgeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Badge::class);
        $params = $this->extractFilterParams($request);
        $perPage = (int) ($params['per_page'] ?? 15);
        $paginator = $this->service->paginate($perPage, $params);
        $paginator->getCollection()->transform(fn ($item) => new BadgeResource($item));

        return $this->paginateResponse($paginator);
    }

    public function store(BadgeStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Badge::class);
        $data = $request->validated();
        $files = $request->hasFile('icon') ? ['icon' => $request->file('icon')] : [];
        $badge = $this->service->create($data, $files);

        return $this->created(new BadgeResource($badge), __('messages.badges.created'));
    }

    public function show(int $badge): JsonResponse
    {
        $model = $this->service->find($badge);

        if (! $model) {
            return $this->error(__('messages.badges.not_found'), 404);
        }

        $this->authorize('view', $model);

        $model->load('rules');

        return $this->success(new BadgeResource($model));
    }

    public function update(BadgeUpdateRequest $request, int $badge): JsonResponse
    {
        $model = $this->service->find($badge);

        if (! $model) {
            return $this->error(__('messages.badges.not_found'), 404);
        }

        $this->authorize('update', $model);

        $data = $request->validated();
        $files = $request->hasFile('icon') ? ['icon' => $request->file('icon')] : [];
        $updated = $this->service->update($badge, $data, $files);

        return $this->success(new BadgeResource($updated), __('messages.badges.updated'));
    }

    public function destroy(int $badge): JsonResponse
    {
        $model = $this->service->find($badge);

        if (! $model) {
            return $this->error(__('messages.badges.not_found'), 404);
        }

        $this->authorize('delete', $model);

        $deleted = $this->service->delete($badge);

        if (! $deleted) {
            return $this->error(__('messages.badges.not_found'), 404);
        }

        return $this->success([], __('messages.badges.deleted'));
    }

    /**
     * Get available badges for student (with earned status and progress)
     */
    public function available(Request $request): JsonResponse
    {
        $userId = auth('api')->user()->id;
        $perPage = min((int) $request->get('per_page', 15), 100);

        $badges = $this->service->getAvailableBadgesForStudent($userId, $perPage, $request);
        $badges->appends($request->query());

        $badges->getCollection()->transform(fn ($item) => new BadgeResource($item));

        return $this->paginateResponse($badges, __('messages.badges.retrieved'));
    }
}
