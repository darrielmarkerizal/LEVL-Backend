<?php

declare(strict_types=1);

namespace Modules\Common\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

use Modules\Common\Contracts\Services\LevelConfigServiceInterface;
use Modules\Common\Models\LevelConfig;
use Modules\Common\Repositories\LevelConfigRepository;
use Modules\Gamification\Models\Badge;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LevelConfigService implements LevelConfigServiceInterface
{
    public function __construct(
        private readonly LevelConfigRepository $repository,
        private readonly BadgeService $badgeService,
    ) {}

    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = request()->get('page', 1);
        $search = $params['search'] ?? request('search');
        $sort = request('sort', 'level');

        return cache()->tags(['common', 'levels'])->remember(
            "common:levels:paginate:{$perPage}:{$page}:{$search}:{$sort}",
            300,
            function () use ($perPage, $search) {
                $query = LevelConfig::query();

                if ($search && trim($search) !== '') {
                    $query->search($search);
                }

                return QueryBuilder::for($query)
                    ->allowedFilters([
                        AllowedFilter::exact('id'),
                        AllowedFilter::exact('level'),
                        AllowedFilter::partial('name'),
                        AllowedFilter::callback('search', fn ($q, $v) => $q->search($v)),
                    ])
                    ->allowedSorts(['id', 'level', 'name', 'xp_required', 'created_at', 'updated_at'])
                    ->defaultSort('level')
                    ->paginate($perPage);
            }
        );
    }

    public function create(array $data): LevelConfig
    {
        return DB::transaction(function () use ($data) {
            $levelConfig = $this->repository->create($data);
            $this->syncBadgesFromRewards($levelConfig->id, $data['rewards'] ?? []);
            cache()->tags(['common', 'levels'])->flush();
            return $levelConfig->fresh();
        });
    }

    public function find(int $id): ?LevelConfig
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): ?LevelConfig
    {
        $config = $this->repository->findById($id);

        if (! $config) {
            return null;
        }

        return DB::transaction(function () use ($config, $data) {
            $updated = $this->repository->update($config, $data);
            $this->syncBadgesFromRewards($updated->id, $data['rewards'] ?? []);
            cache()->tags(['common', 'levels'])->flush();
            return $updated->fresh();
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $config = $this->repository->findById($id);
            if (! $config) {
                return false;
            }
            $result = $this->repository->delete($config);
            cache()->tags(['common', 'levels'])->flush();
            return $result;
        });
    }

    private function syncBadgesFromRewards(int $levelConfigId, array $rewards): void
    {
        if (empty($rewards)) {
            return;
        }

        foreach ($rewards as $reward) {
            if ($reward['type'] !== 'badge') {
                continue;
            }

            $badgeCode = $reward['value'] ?? null;
            if (! $badgeCode) {
                continue;
            }

            $this->badgeService->createOrFind($badgeCode, [
                'description' => 'Badge earned upon reaching level milestone',
                'type' => 'milestone',
            ]);
        }
    }
}
