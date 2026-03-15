<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Common\Http\Resources\LevelConfigResource;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Services\LevelService;
use Modules\Gamification\Services\Support\PointManager;

class LevelController extends Controller
{
    use ApiResponse;

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
        
        $levels = \Spatie\QueryBuilder\QueryBuilder::for(LevelConfig::class)
            ->with('milestoneBadge')
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::exact('level'),
                \Spatie\QueryBuilder\AllowedFilter::callback('level_min', function ($query, $value) {
                    $query->where('level', '>=', (int) $value);
                }),
                \Spatie\QueryBuilder\AllowedFilter::callback('level_max', function ($query, $value) {
                    $query->where('level', '<=', (int) $value);
                }),
                \Spatie\QueryBuilder\AllowedFilter::callback('xp_min', function ($query, $value) {
                    $query->where('xp_required', '>=', (int) $value);
                }),
                \Spatie\QueryBuilder\AllowedFilter::callback('xp_max', function ($query, $value) {
                    $query->where('xp_required', '<=', (int) $value);
                }),
            ])
            ->allowedSorts(['level', 'xp_required', 'bonus_xp'])
            ->defaultSort('level')
            ->paginate($perPage);

        // Transform using resource
        $levels->getCollection()->transform(fn($level) => new LevelConfigResource($level));

        return $this->paginateResponse(
            $levels,
            'messages.levels_retrieved'
        );
    }

    /**
     * Get level progression table
     */
    public function progression(Request $request): JsonResponse
    {
        $startLevel = max(1, (int) $request->get('start', 1));
        $endLevel = min(100, (int) $request->get('end', 20));

        $table = $this->levelService->getLevelProgressionTable($startLevel, $endLevel);

        return $this->success(
            $table,
            'messages.level_progression_retrieved'
        );
    }

    /**
     * Get user's current level info
     */
    public function userLevel(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return $this->unauthorized('messages.unauthorized');
        }

        $stats = $user->gamificationStats;
        $totalXp = $stats?->total_xp ?? 0;

        $levelInfo = $this->levelService->getLevelProgress($totalXp);

        return $this->success(
            $levelInfo,
            'messages.level_info_retrieved'
        );
    }

    /**
     * Calculate level from XP (utility endpoint)
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'xp' => 'required|integer|min:0',
        ]);

        $xp = (int) $validated['xp'];
        $levelInfo = $this->levelService->getLevelProgress($xp);

        return $this->success(
            $levelInfo,
            'messages.level_calculated'
        );
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

        return $this->success(
            [
                'synced_count' => $synced,
                'start_level' => $startLevel,
                'end_level' => $endLevel,
            ],
            'messages.levels_synced',
            ['count' => $synced]
        );
    }

    /**
     * Update specific level config (Admin only)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage-gamification');

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'xp_required' => 'sometimes|integer|min:0',
            'rewards' => 'sometimes|array',
            'milestone_badge_id' => 'sometimes|nullable|exists:badges,id',
            'bonus_xp' => 'sometimes|integer|min:0',
        ]);

        $levelConfig = LevelConfig::findOrFail($id);
        $levelConfig->update($validated);
        $levelConfig->load('milestoneBadge');

        // Clear cache
        cache()->forget('gamification.level_configs');

        return $this->success(
            new LevelConfigResource($levelConfig),
            'messages.level_updated'
        );
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

        return $this->success(
            $stats,
            'messages.level_statistics_retrieved'
        );
    }
    
    /**
     * Get user's daily XP stats
     */
    public function dailyXpStats(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return $this->unauthorized('messages.unauthorized');
        }

        $days = min((int) $request->get('days', 7), 30); // Max 30 days
        $stats = $this->pointManager->getDailyXpStats($user->id, $days);

        return $this->success(
            $stats,
            'messages.daily_xp_stats_retrieved'
        );
    }
}
