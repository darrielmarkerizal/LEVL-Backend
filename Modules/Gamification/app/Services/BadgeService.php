<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Gamification\Contracts\Services\BadgeServiceInterface;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Repositories\BadgeRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BadgeService implements BadgeServiceInterface
{
    public function __construct(private readonly BadgeRepository $repository) {}

    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = request()->get('page', 1);
        $search = $params['search'] ?? request('search');
        $sort = request('sort', '-created_at');

        return cache()->tags(['common', 'badges'])->remember(
            "common:badges:paginate:{$perPage}:{$page}:{$search}:{$sort}",
            300,
            function () use ($perPage, $search) {
                // FIX: Eager load rules and users count
                $query = Badge::with('rules')->withCount('users');

                if ($search && trim($search) !== '') {
                    $query->search($search);
                }

                return QueryBuilder::for($query)
                    ->allowedFilters([
                        AllowedFilter::exact('id'),
                        AllowedFilter::partial('code'),
                        AllowedFilter::partial('name'),
                        AllowedFilter::exact('type'),
                        AllowedFilter::partial('category'),
                        AllowedFilter::exact('rarity'),
                        AllowedFilter::exact('active'),
                        AllowedFilter::callback('search', fn ($q, $v) => $q->search($v)),
                    ])
                    ->allowedSorts(['id', 'code', 'name', 'type', 'rarity', 'xp_reward', 'threshold', 'created_at', 'updated_at'])
                    ->allowedIncludes(['rules'])
                    ->defaultSort('-created_at')
                    ->paginate($perPage);
            }
        );
    }

    public function create(array $data, array $files = []): Badge
    {
        return DB::transaction(function () use ($data, $files) {
            $badge = $this->repository->create($data);

            if (! empty($data['rules'])) {
                $this->syncRules($badge->id, $data['rules']);
            }

            $this->handleMedia($badge, $files);

            cache()->tags(['common', 'badges'])->flush();

            return $badge->fresh();
        });
    }

    public function createOrFind(string $code, array $data = [], ?string $iconPath = null): Badge
    {
        $existingBadge = Badge::where('code', $code)->first();
        if ($existingBadge) {
            return $existingBadge;
        }

        $badgeData = array_merge([
            'code' => $code,
            'name' => ucfirst(str_replace('_', ' ', $code)),
            'description' => $data['description'] ?? 'Badge for milestone',
            'type' => $data['type'] ?? 'milestone',
            'threshold' => $data['threshold'] ?? null,
        ], $data);

        $badge = $this->repository->create($badgeData);

        if ($iconPath && file_exists($iconPath)) {
            $badge->addMedia($iconPath)->toMediaCollection('icon');
        }

        cache()->tags(['common', 'badges'])->flush();

        return $badge->fresh();
    }

    public function find(int $id): ?Badge
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data, array $files = []): ?Badge
    {
        $badge = $this->repository->findById($id);

        if (! $badge) {
            return null;
        }

        return DB::transaction(function () use ($badge, $data, $files) {
            $updated = $this->repository->update($badge, $data);

            if (isset($data['rules'])) {
                $this->syncRules($badge->id, $data['rules']);
            }

            $this->handleMedia($updated, $files);

            cache()->tags(['common', 'badges'])->flush();

            return $updated->fresh();
        });
    }

    private function handleMedia(Badge $badge, array $files): void
    {
        if (isset($files['icon'])) {
            if ($badge->media()->exists()) {
                $badge->clearMediaCollection('icon');
            }
            $badge->addMedia($files['icon'])->toMediaCollection('icon');
        }
    }

    public function delete(int $id): bool
    {
        $badge = $this->repository->findById($id);

        if (! $badge) {
            return false;
        }

        return DB::transaction(function () use ($badge) {
            $result = $this->repository->delete($badge);
            cache()->tags(['common', 'badges'])->flush();

            return $result;
        });
    }

    private function syncRules(int $badgeId, array $rules): void
    {
        \Modules\Gamification\Models\BadgeRule::where('badge_id', $badgeId)->delete();

        foreach ($rules as $rule) {
            \Modules\Gamification\Models\BadgeRule::create([
                'badge_id' => $badgeId,
                'event_trigger' => $rule['event_trigger'] ?? null,
                'conditions' => $rule['conditions'] ?? null,
                'priority' => $rule['priority'] ?? 0,
                'cooldown_seconds' => $rule['cooldown_seconds'] ?? null,
                'progress_window' => $rule['progress_window'] ?? null,
                'rule_enabled' => $rule['rule_enabled'] ?? true,
            ]);
        }
    }

    public function getAvailableBadgesForStudent(int $userId, int $perPage = 15, $request = null): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        
        // Get user's earned badges
        $earnedBadges = \Modules\Gamification\Models\UserBadge::where('user_id', $userId)
            ->get()
            ->keyBy('badge_id');
        
        $query = Badge::with(['rules', 'media'])
            ->where('active', true);
        
        $search = $request?->query('search') ?? request('search');
        if ($search && trim($search) !== '') {
            $query->search($search);
        }
        
        $badges = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('rarity'),
                AllowedFilter::callback('earned', function ($query, $value) use ($earnedBadges) {
                    $earnedBadgeIds = $earnedBadges->keys()->toArray();
                    if ($value === 'true' || $value === true || $value === '1') {
                        $query->whereIn('badges.id', $earnedBadgeIds ?: [0]);
                    } elseif ($value === 'false' || $value === false || $value === '0') {
                        $query->whereNotIn('badges.id', $earnedBadgeIds ?: [0]);
                    }
                }),
                AllowedFilter::callback('search', fn ($q, $v) => $q->search($v)),
            ])
            ->allowedSorts(['name', 'rarity', 'xp_reward', 'created_at'])
            ->defaultSort('name')
            ->paginate($perPage);
        
        // Transform to include earned status and progress
        $badges->getCollection()->transform(function ($badge) use ($earnedBadges) {
            $userBadge = $earnedBadges->get($badge->id);
            
            $badge->is_earned = $userBadge !== null;
            $badge->earned_at = $userBadge?->earned_at;
            
            // Calculate progress
            if ($userBadge) {
                $badge->progress = [
                    'current' => $userBadge->progress ?? $badge->threshold ?? 1,
                    'target' => $badge->threshold ?? 1,
                    'percentage' => $badge->threshold > 0 
                        ? round((($userBadge->progress ?? $badge->threshold) / $badge->threshold) * 100) 
                        : 100
                ];
            } else {
                // For not earned badges, try to get current progress from badge rules
                $currentProgress = $this->calculateBadgeProgress($badge, auth('api')->user()->id);
                $badge->progress = [
                    'current' => $currentProgress,
                    'target' => $badge->threshold ?? 1,
                    'percentage' => $badge->threshold > 0 
                        ? round(($currentProgress / $badge->threshold) * 100) 
                        : 0
                ];
            }
            
            return $badge;
        });
        
        return $badges;
    }

    private function calculateBadgeProgress(Badge $badge, int $userId): int
    {
        // This is a simplified version - you may need to implement more complex logic
        // based on your badge rules and tracking system
        
        // For now, return 0 for not earned badges
        // You can enhance this by checking specific metrics based on badge type
        return 0;
    }
}
