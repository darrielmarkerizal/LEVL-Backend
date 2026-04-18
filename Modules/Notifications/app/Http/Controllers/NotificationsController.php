<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Notifications\Services\NotificationService;

/**
 * @tags Notifikasi
 */
class NotificationsController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly NotificationService $service) {}

    /**
     * Menampilkan daftar data
     *
     *
     * @summary Menampilkan daftar data
     *
     * @response 200 scenario="Success" {"success":true,"message":"Success","data":[{"id":1,"name":"Example Notifications"}],"meta":{"current_page":1,"last_page":5,"per_page":15,"total":75},"links":{"first":"...","last":"...","prev":null,"next":"..."}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     * @authenticated
     */
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

    /**
     * Menampilkan form untuk membuat data baru
     *
     *
     * @summary Menampilkan form untuk membuat data baru
     *
     * @response 200 scenario="Success" {"success":true,"message":"Success","data":{"id":1,"name":"Example Notifications"}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     * @authenticated
     */
    public function create()
    {
        return $this->error('messages.feature_unavailable', status: 501);
    }

    /**
     * Menyimpan data baru
     *
     *
     * @summary Menyimpan data baru
     *
     * @response 201 scenario="Success" {"success":true,"message":"Notifications berhasil dibuat.","data":{"id":1,"name":"New Notifications"}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     * @response 422 scenario="Validation Error" {"success":false,"message":"Validasi gagal.","errors":{"field":["Field wajib diisi."]}}
     * @response 501 scenario="Not Implemented" {"success":false,"message":"Fitur belum tersedia."}
     *
     * @authenticated
     */
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

    /**
     * Menampilkan data tertentu
     *
     *
     * @summary Menampilkan data tertentu
     *
     * @response 200 scenario="Success" {"success":true,"message":"Success","data":{"id":1,"name":"Example Notifications"}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     * @response 404 scenario="Not Found" {"success":false,"message":"Notifications tidak ditemukan."}
     *
     * @authenticated
     */
    public function show($id)
    {
        $userId = (int) auth('api')->id();
        $notification = $this->service->findForUser($userId, (int) $id);
        if (! $notification) {
            return $this->notFound('messages.not_found');
        }

        return $this->success($this->service->toPayload($notification, $userId), 'messages.data_retrieved');
    }

    /**
     * Menampilkan form untuk edit data
     *
     *
     * @summary Menampilkan form untuk edit data
     *
     * @response 200 scenario="Success" {"success":true,"message":"Success","data":{"id":1,"name":"Example Notifications"}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     * @authenticated
     */
    public function edit($id)
    {
        return $this->error('messages.feature_unavailable', status: 501);
    }

    /**
     * Memperbarui data
     *
     *
     * @summary Memperbarui data
     *
     * @response 200 scenario="Success" {"success":true,"message":"Notifications berhasil diperbarui.","data":{"id":1,"name":"Updated Notifications"}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     * @response 404 scenario="Not Found" {"success":false,"message":"Notifications tidak ditemukan."}
     * @response 422 scenario="Validation Error" {"success":false,"message":"Validasi gagal.","errors":{"field":["Field wajib diisi."]}}
     * @response 501 scenario="Not Implemented" {"success":false,"message":"Fitur belum tersedia."}
     *
     * @authenticated
     */
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

    /**
     * Menghapus data
     *
     *
     * @summary Menghapus data
     *
     * @response 200 scenario="Success" {"success":true,"message":"Notifications berhasil dihapus.","data":[]}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     * @response 404 scenario="Not Found" {"success":false,"message":"Notifications tidak ditemukan."}
     * @response 501 scenario="Not Implemented" {"success":false,"message":"Fitur belum tersedia."}
     *
     * @authenticated
     */
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
