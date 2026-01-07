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
        private PasswordService $passwordService
    ) {}

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->passwordService->changePassword(
            $user,
            $request->input('current_password'),
            $request->input('new_password')
        );

        return $this->success(null, __('messages.auth.password_changed'));
    }
}
