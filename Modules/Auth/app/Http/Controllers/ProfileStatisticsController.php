<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\ProfileStatisticsService;

/**
 * @tags Profil Pengguna
 */
class ProfileStatisticsController extends Controller
{
    public function __construct(
        private ProfileStatisticsService $statisticsService
    ) {}

    /**
     * @summary Ambil Statistik Profil
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $statistics = $this->statisticsService->getStatistics($user);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
