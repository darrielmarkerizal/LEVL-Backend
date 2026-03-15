<?php

declare(strict_types=1);

namespace Modules\Gamification\Services\Support;

use Illuminate\Support\Facades\DB;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Repositories\GamificationRepository;

class BadgeManager
{
    public function __construct(
        private readonly GamificationRepository $repository
    ) {}

    public function awardBadge(
        int $userId,
        string $code,
        string $name,
        ?string $description = null
    ): ?UserBadge {
        return DB::transaction(function () use ($userId, $code, $name, $description) {
            $badge = $this->repository->firstOrCreateBadge($code, [
                'name' => $name,
                'description' => $description,
            ]);

            // Check if badge is repeatable
            if (!$badge->is_repeatable) {
                $existing = $this->repository->findUserBadge($userId, $badge->id);
                if ($existing) {
                    return null; // Non-repeatable badge already awarded
                }
            }

            // Check max_awards_per_user limit for repeatable badges
            if ($badge->is_repeatable && $badge->max_awards_per_user) {
                $awardCount = $this->repository->countUserBadgesByBadgeId($userId, $badge->id);
                if ($awardCount >= $badge->max_awards_per_user) {
                    return null; // Max awards limit reached
                }
            }

            return $this->repository->createUserBadge([
                'user_id' => $userId,
                'badge_id' => $badge->id,
                'awarded_at' => now(),
                'description' => $description,
            ]);
        });
    }

    public function countUserBadges(int $userId): int
    {
        return $this->repository->countByUserId($userId);
    }

    public function getUserBadges(int $userId): \Illuminate\Support\Collection
    {
        return $this->repository->findByUserId($userId);
    }

    public function getUserBadgesPaginated(int $userId, int $perPage = 15, $request = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        
        return \Spatie\QueryBuilder\QueryBuilder::for(UserBadge::class)
            ->where('user_id', $userId)
            ->with(['badge', 'badge.media'])
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::callback('type', function ($query, $value) {
                    $query->whereHas('badge', function ($q) use ($value) {
                        $q->where('type', $value);
                    });
                }),
                \Spatie\QueryBuilder\AllowedFilter::callback('rarity', function ($query, $value) {
                    $query->whereHas('badge', function ($q) use ($value) {
                        $q->where('rarity', $value);
                    });
                }),
            ])
            ->allowedSorts(['earned_at', 'progress'])
            ->defaultSort('-earned_at')
            ->paginate($perPage);
    }
}
