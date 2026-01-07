<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Http\Requests\RequestAccountDeletionRequest;
use Modules\Auth\Http\Requests\ConfirmAccountDeletionRequest;
use Modules\Auth\Services\AccountDeletionService;

class ProfileAccountController extends Controller
{
  use ApiResponse;

  public function __construct(private AccountDeletionService $accountDeletionService) {}

  public function deleteRequest(RequestAccountDeletionRequest $request): JsonResponse
  {
    $uuid = $this->accountDeletionService->requestDeletion(
      $request->user(),
      $request->input('password')
    );

    return $this->success(
      ['uuid' => $uuid],
      __("messages.auth.deletion_request_sent")
    );
  }

  public function deleteConfirm(ConfirmAccountDeletionRequest $request): JsonResponse
  {
    $success = $this->accountDeletionService->confirmDeletion(
      $request->input('token'),
      $request->input('uuid')
    );

    if (!$success) {
      return $this->error(__("messages.auth.deletion_failed"), [], 422);
    }

    return $this->success(
      [],
      __("messages.auth.account_deleted_success")
    );
  }

  public function restore(): JsonResponse
  {
    $this->accountService->restoreAccount(auth()->user());

    return $this->success(null, __("messages.account.restore_success"));
  }
}
