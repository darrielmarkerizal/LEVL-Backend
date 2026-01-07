<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Contracts\Repositories\PinnedBadgeRepositoryInterface;

class ProfileAchievementController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PinnedBadgeRepositoryInterface $pinnedBadgeRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $badges = $user->badges()->with('badge')->get();
        $pinnedBadges = $user->pinnedBadges()->with('badge')->orderBy('order')->get();

        return $this->success([
            'badges' => \Modules\Auth\Http\Resources\BadgeResource::collection($badges),
            'pinned_badges' => \Modules\Auth\Http\Resources\BadgeResource::collection($pinnedBadges),
        ]);
    }

    public function pinBadge(Request $request, int $badgeId): JsonResponse
    {
        $request->validate(['order' => 'sometimes|integer|min:0']);

        $user = $request->user();

        if (! $user->badges()->where('badge_id', $badgeId)->exists()) {
            return $this->notFound(__('messages.achievement.badge_not_owned'));
        }

        if ($this->pinnedBadgeRepository->findByUserAndBadge($user->id, $badgeId)) {
            return $this->error(__('messages.profile.badge_already_pinned'), [], 422);
        }

        $pinnedBadge = $this->pinnedBadgeRepository->create([
            'user_id' => $user->id,
            'badge_id' => $badgeId,
            'order' => $request->input('order', 0),
        ]);

        return $this->success(
            new \Modules\Auth\Http\Resources\BadgeResource($pinnedBadge->load('badge')),
            __('messages.profile.badge_pinned')
        );
    }

    public function unpinBadge(Request $request, int $badgeId): JsonResponse
    {
        $user = $request->user();
        $pinnedBadge = $this->pinnedBadgeRepository->findByUserAndBadge($user->id, $badgeId);

        if (! $pinnedBadge) {
            return $this->notFound(__('messages.achievement.badge_not_pinned'));
        }

        $this->pinnedBadgeRepository->delete($pinnedBadge);

        return $this->success(null, __('messages.profile.badge_unpinned'));
    }
}
