<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Contracts\Repositories\UserBulkRepositoryInterface;
use Modules\Auth\Contracts\Services\UserBulkServiceInterface;
use Modules\Auth\Http\Requests\BulkActivateRequest;
use Modules\Auth\Http\Requests\BulkDeactivateRequest;
use Modules\Auth\Http\Requests\BulkDeleteRequest;
use Modules\Auth\Http\Requests\BulkExportRequest;

class UserBulkController extends Controller
{
  use ApiResponse;

  public function __construct(
    private UserBulkServiceInterface $bulkService,
    private UserBulkRepositoryInterface $bulkRepository
  ) {
    $this->middleware('role:Superadmin,Admin');
  }

  public function exportToEmail(BulkExportRequest $request): JsonResponse
  {
    $this->bulkService->exportToEmail(
      $request->input('user_ids'),
      $request->input('email')
    );

    return $this->success(null, __("messages.users.bulk_export_queued"));
  }

  public function bulkActivate(BulkActivateRequest $request): JsonResponse
  {
    $count = $this->bulkService->bulkActivate(
      $request->input('user_ids'),
      $request->user()->id
    );

    return $this->success(
      ['activated_count' => $count],
      __("messages.users.bulk_activated", ['count' => $count])
    );
  }

  public function bulkDeactivate(BulkDeactivateRequest $request): JsonResponse
  {
      $count = $this->bulkService->bulkDeactivate(
        $request->input('user_ids'),
        $request->user()->id,
        $request->user()->id
      );

      return $this->success(
        ['deactivated_count' => $count],
        __("messages.users.bulk_deactivated", ['count' => $count])
      );
  }

  public function bulkDelete(BulkDeleteRequest $request): JsonResponse
  {
      $count = $this->bulkService->bulkDelete(
        $request->input('user_ids'),
        $request->user()->id
      );

      return $this->success(
        ['deleted_count' => $count],
        __("messages.users.bulk_deleted", ['count' => $count])
      );
  }
}
