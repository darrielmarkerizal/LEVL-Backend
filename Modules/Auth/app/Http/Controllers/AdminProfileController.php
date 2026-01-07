<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Contracts\Services\ProfileServiceInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Contracts\Repositories\ProfileAuditLogRepositoryInterface;
use Modules\Auth\Models\User;

class AdminProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProfileServiceInterface $profileService,
        private ProfileAuditLogRepositoryInterface $auditLogRepository
    ) {
        $this->middleware('role:Admin');
    }

    public function show(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $profileData = $this->profileService->getProfileData($user, $request->user());

        return $this->success(
            new \Modules\Auth\Http\Resources\ProfileResource($profileData),
            __('messages.success')
        );
    }

    public function update(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:100',
            'email' => "sometimes|email|unique:users,email,{$userId}",
            'phone' => 'sometimes|nullable|string|max:20',
            'bio' => 'sometimes|nullable|string|max:1000',
            'account_status' => 'sometimes|in:active,suspended,deleted',
        ]);

        $user = User::findOrFail($userId);
        $admin = $request->user();
        $oldData = $user->only(['name', 'email', 'phone', 'bio', 'account_status']);

        $updatedUser = $this->profileService->updateProfile($user, $request->all());

        $this->auditLogRepository->create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'action' => 'profile_updated',
            'changes' => [
                'old' => $oldData,
                'new' => $updatedUser->only(['name', 'email', 'phone', 'bio', 'account_status']),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success(
            $this->profileService->getProfileData($updatedUser, $admin),
            __('messages.profile.updated_success')
        );
    }

    public function suspend(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $admin = $request->user();

        $user->account_status = 'suspended';
        $user->save();

        $this->auditLogRepository->create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'action' => 'account_suspended',
            'changes' => ['status' => 'suspended'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success([], __('messages.profile.suspended_success'));
    }

    public function activate(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $admin = $request->user();

        $user->account_status = 'active';
        $user->save();

        $this->auditLogRepository->create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'action' => 'account_activated',
            'changes' => ['status' => 'active'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success([], __('messages.profile.activated_success'));
    }

    public function auditLogs(Request $request, int $userId): JsonResponse
    {
        $logs = $this->auditLogRepository->findByUserId($userId, 20);

        return $this->success($logs, __('messages.success'));
    }
}
