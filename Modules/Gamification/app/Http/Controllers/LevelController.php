<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Services\LevelService;
use Modules\Gamification\Services\Support\PointManager;

class LevelController extends Controller
{
    public function __construct(
        private readonly LevelService $levelService,
        private readonly PointManager $pointManager
    ) {}

    /**
     * Get all level configurations
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        
        $levels = LevelConfig::orderBy('level')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }

    /**
     * Get level progression table
     */
    public function progression(Request $request): JsonResponse
    {
        $startLevel = max(1, (int) $request->get('start', 1));
        $endLevel = min(100, (int) $request->get('end', 20));

        $table = $this->levelService->getLevelProgressionTable($startLevel, $endLevel);

        return response()->json([
            'success' => true,
            'data' => $table,
        ]);
    }

    /**
     * Get user's current level info
     */
    public function userLevel(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $stats = $user->gamificationStats;
        $totalXp = $stats?->total_xp ?? 0;

        $levelInfo = $this->levelService->getLevelProgress($totalXp);

        return response()->json([
            'success' => true,
            'data' => $levelInfo,
        ]);
    }

    /**
     * Calculate level from XP (utility endpoint)
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'xp' => 'required|integer|min:0',
        ]);

        $xp = (int) $request->input('xp');
        $levelInfo = $this->levelService->getLevelProgress($xp);

        return response()->json([
            'success' => true,
            'data' => $levelInfo,
        ]);
    }

    /**
     * Sync level configurations (Admin only)
     */
    public function sync(Request $request): JsonResponse
    {
        $this->authorize('manage-gamification');

        $startLevel = max(1, (int) $request->get('start', 1));
        $endLevel = min(100, (int) $request->get('end', 100));

        $synced = $this->levelService->syncLevelConfigs($startLevel, $endLevel);

        return response()->json([
            'success' => true,
            'message' => "Successfully synced {$synced} level configurations",
            'data' => [
                'synced_count' => $synced,
                'start_level' => $startLevel,
                'end_level' => $endLevel,
            ],
        ]);
    }

    /**
     * Update specific level config (Admin only)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage-gamification');

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'xp_required' => 'sometimes|integer|min:0',
            'rewards' => 'sometimes|array',
        ]);

        $levelConfig = LevelConfig::findOrFail($id);
        $levelConfig->update($request->only(['name', 'xp_required', 'rewards']));

        // Clear cache
        cache()->forget('gamification.level_configs');

        return response()->json([
            'success' => true,
            'message' => 'Level configuration updated successfully',
            'data' => $levelConfig,
        ]);
    }

    /**
     * Get level statistics
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('manage-gamification');

        $stats = [
            'total_levels' => LevelConfig::count(),
            'max_level' => LevelConfig::max('level'),
            'total_xp_to_max' => $this->levelService->calculateTotalXpForLevel(100),
            'users_by_level' => \DB::table('user_gamification_stats')
                ->select('global_level', \DB::raw('count(*) as count'))
                ->groupBy('global_level')
                ->orderBy('global_level')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
    
    /**
     * Get user's daily XP stats
     */
    public function dailyXpStats(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $stats = $this->pointManager->getDailyXpStats($user->id);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
