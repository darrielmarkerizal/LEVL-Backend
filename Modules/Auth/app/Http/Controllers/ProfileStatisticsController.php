<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\ProfileStatisticsService;

class ProfileStatisticsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProfileStatisticsService $statisticsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $statistics = $this->statisticsService->getStatistics($user);

        return $this->success(new \Modules\Auth\Http\Resources\ProfileStatisticsResource($statistics));
    }
}
