<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Common\Http\Resources\LevelConfigResource;
use Modules\Gamification\Services\LevelService;
use Modules\Gamification\Services\Support\PointManager;

class LevelController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LevelService $levelService,
        private readonly PointManager $pointManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        
        $levels = $this->levelService->getPaginatedLevels($perPage);

        return $this->paginateResponse(
            $levels,
            'messages.levels_retrieved'
        );
    }

    public function allTiers(): JsonResponse
    {
        $data = $this->levelService->getAllLevelsGroupedByTier();

        return $this->success(
            $data,
            'messages.levels_retrieved'
        );
    }

    public function tierLevels(int $tier): JsonResponse
    {
        if ($tier < 1 || $tier > 10) {
            return $this->error(
                'Tier must be between 1 and 10',
                422
            );
        }

        $data = $this->levelService->getLevelsByTier($tier);

        return $this->success(
            $data,
            'messages.levels_retrieved'
        );
    }

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



    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage-gamification');

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'xp_required' => 'sometimes|integer|min:0',
            'rewards' => 'sometimes|array',
        ]);

        $levelConfig = $this->levelService->updateLevelConfig($id, $validated);

        return $this->success(
            new LevelConfigResource($levelConfig),
            'messages.level_updated'
        );
    }

    public function updateTier(Request $request, int $tier): JsonResponse
    {
        $this->authorize('manage-gamification');

        if ($tier < 1 || $tier > 10) {
            return $this->error(
                'Tier must be between 1 and 10',
                422
            );
        }

        $validated = $request->validate([
            'base_tier_name' => 'required|string|max:255',
        ]);

        $updated = $this->levelService->updateTierName($tier, $validated['base_tier_name']);

        return $this->success(
            [
                'tier' => $tier,
                'base_tier_name' => $validated['base_tier_name'],
                'updated_count' => $updated,
            ],
            'messages.tier_updated',
            ['count' => $updated]
        );
    }

    public function statistics(): JsonResponse
    {
        $this->authorize('manage-gamification');

        $stats = $this->levelService->getLevelStatistics();

        return $this->success(
            $stats,
            'messages.level_statistics_retrieved'
        );
    }

    public function dailyXpStats(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->unauthorized('messages.unauthorized');
        }

        $days = min((int) $request->get('days', 7), 30);
        $stats = $this->pointManager->getDailyXpStats($user->id, $days);

        return $this->success(
            $stats,
            'messages.daily_xp_stats_retrieved'
        );
    }
}