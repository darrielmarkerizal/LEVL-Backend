<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Http\Requests\ChangePasswordRequest;
use Modules\Auth\Services\PasswordService;

class ProfilePasswordController extends Controller
{
    use ApiResponse;

    public function __construct(
        private \App\Contracts\Services\ProfileServiceInterface $profileService
    ) {}

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->profileService->changePassword(
            $user,
            $request->input('current_password'),
            $request->input('new_password')
        );

        return $this->success(null, __('messages.auth.password_changed'));
    }

    /**
     * Alias for changePassword to match route expectations
     */
    public function update(ChangePasswordRequest $request): JsonResponse
    {
        return $this->changePassword($request);
    }
}
