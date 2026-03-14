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
                $query = Badge::with('rules');

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
}
