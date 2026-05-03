<?php

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Contracts\Services\GamificationServiceInterface;
use Modules\Gamification\Transformers\PointResource;
use Modules\Gamification\Transformers\UserBadgeResource;

class GamificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamificationServiceInterface $gamificationService
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $period = $request->input('filter.period', 'all_time');
        $month = $request->input('filter.month');

        $summary = $this->gamificationService->getSummary($userId, $period, $month);

        return $this->success($summary, __('gamification.summary_retrieved'));
    }

    public function badges(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = (int) ($request->input('per_page') ?? 15);

        $badges = $this->gamificationService->getUserBadges($userId, $perPage, $request);
        $badges->appends($request->query());

        $badges->getCollection()->transform(fn ($item) => new UserBadgeResource($item));

        return $this->paginateResponse($badges, __('gamification.badges_retrieved'));
    }

    public function pointsHistory(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = (int) ($request->input('per_page') ?? 15);
        $perPage = $perPage > 0 ? $perPage : 15;

        $points = $this->gamificationService->getPointsHistory($userId, $perPage, $request);
        $points->appends($request->query());

        $points->getCollection()->transform(fn ($item) => new PointResource($item));

        return $this->paginateResponse($points, __('gamification.points_history_retrieved'));
    }

    public function milestones(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->gamificationService->getAchievements($userId);

        return $this->success($data, __('gamification.milestones_retrieved'));
    }

    public function courseOverview(Request $request, string $slug): JsonResponse
    {
        $userId = $request->user()->id;
        $course = \Modules\Schemes\Models\Course::where('slug', $slug)->firstOrFail();

        $data = $this->gamificationService->getCourseOverview($userId, $course->id);

        return $this->success($data, __('gamification.course_progress_retrieved'));
    }

    public function level(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $stats = $this->gamificationService->getOrCreateStats($userId);

        $data = [
            'level' => $stats->global_level,
            'total_xp' => $stats->total_xp,
            'current_level_xp' => $stats->current_level_xp,
            'xp_to_next_level' => $stats->xp_to_next_level,
            'progress' => $stats->progress_to_next_level,
        ];

        return $this->success($data, __('gamification.level_retrieved'));
    }
}
