<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
  use ApiResponse;

  public function __construct(private ActivityLogService $service) {}

  public function index(Request $request): JsonResponse
  {
    $params = [
      'page' => (int) $request->query('page', 1),
      'per_page' => $request->query('per_page'),
      'search' => $request->query('search'),
      'sort' => $request->query('sort'),
      'filter' => $request->query('filter', []),
    ];

    $result = $this->service->paginate($params);

    return $this->paginateResponse(
      $result['paginator'],
      __("messages.activity_logs.retrieved"),
      200,
      $result['metadata'],
    );
  }

  /**
   * Detail Log Aktivitas
   *
   * @authenticated
   */
  public function show(int $id): JsonResponse
  {
    $activity = $this->service->find($id);

    if (!$activity) {
      return $this->notFound(__("messages.activity_logs.not_found"));
    }

    return $this->success($activity, __("messages.activity_logs.item_retrieved"));
  }
}
