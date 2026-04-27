<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Notifications\Services\NotificationService;

class NotificationsController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly NotificationService $service) {}

    public function index(Request $request)
    {
        $userId = (int) auth('api')->id();
        $perPage = max(1, min(100, (int) $request->integer('per_page', 15)));
        $notifications = $this->service->listForUser($userId, $perPage);
        $notifications->setCollection($notifications->getCollection()->map(fn ($item) => $this->service->toPayload($item, $userId)));

        return $this->paginateResponse($notifications, 'messages.data_retrieved', additionalMeta: [
            'unread_count' => $this->service->unreadCount($userId),
        ]);
    }

    public function create()
    {
        return $this->error('messages.feature_unavailable', status: 501);
    }

    public function store(Request $request)
    {
        $userId = (int) auth('api')->id();
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['sometimes', 'string', 'max:255'],
            'data' => ['sometimes', 'array'],
            'action_url' => ['sometimes', 'string', 'max:255'],
        ]);

        $notification = $this->service->create([
            'user_id' => $userId,
            'type' => $payload['type'] ?? 'custom',
            'title' => $payload['title'],
            'message' => $payload['message'],
            'data' => $payload['data'] ?? null,
            'action_url' => $payload['action_url'] ?? null,
            'channel' => 'in_app',
        ])->load(['users' => fn ($query) => $query->where('users.id', $userId)]);

        return $this->created($this->service->toPayload($notification, $userId), 'messages.created');
    }

    public function show($id)
    {
        $userId = (int) auth('api')->id();
        $notification = $this->service->findForUser($userId, (int) $id);
        if (! $notification) {
            return $this->notFound('messages.not_found');
        }

        return $this->success($this->service->toPayload($notification, $userId), 'messages.data_retrieved');
    }

    public function edit($id)
    {
        return $this->error('messages.feature_unavailable', status: 501);
    }

    public function update(Request $request, string $id)
    {
        $userId = (int) auth('api')->id();
        $notification = $this->service->findForUser($userId, (int) $id);
        if (! $notification) {
            return $this->notFound('messages.not_found');
        }

        $this->service->markAsRead($notification, $userId);
        $notification = $this->service->findForUser($userId, (int) $id);

        return $this->success($this->service->toPayload($notification, $userId), 'messages.updated');
    }

    public function readAll()
    {
        $userId = (int) auth('api')->id();
        $this->service->markAllAsRead($userId);

        return $this->success([], 'messages.updated');
    }

    public function destroy(string $id)
    {
        $userId = (int) auth('api')->id();
        $notification = $this->service->findForUser($userId, (int) $id);
        if (! $notification) {
            return $this->notFound('messages.not_found');
        }

        $this->service->deleteForUser($notification, $userId);

        return $this->success([], 'messages.deleted');
    }
}
