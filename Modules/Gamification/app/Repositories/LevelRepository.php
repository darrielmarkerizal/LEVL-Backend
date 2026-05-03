<?php

declare(strict_types=1);

namespace Modules\Gamification\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Common\Models\LevelConfig;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LevelRepository
{
    public function getPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return QueryBuilder::for(LevelConfig::class)
            ->allowedFilters([
                AllowedFilter::exact('level'),
                AllowedFilter::callback('tier', function ($query, $value) {
                    $tier = (int) $value;
                    $startLevel = ($tier - 1) * 10 + 1;
                    $endLevel = $tier * 10;
                    $query->whereBetween('level', [$startLevel, $endLevel]);
                }),
                AllowedFilter::callback('level_min', function ($query, $value) {
                    $query->where('level', '>=', (int) $value);
                }),
                AllowedFilter::callback('level_max', function ($query, $value) {
                    $query->where('level', '<=', (int) $value);
                }),
                AllowedFilter::callback('xp_min', function ($query, $value) {
                    $query->where('xp_required', '>=', (int) $value);
                }),
                AllowedFilter::callback('xp_max', function ($query, $value) {
                    $query->where('xp_required', '<=', (int) $value);
                }),
            ])
            ->allowedSorts(['level', 'xp_required'])
            ->defaultSort('level')
            ->paginate($perPage);
    }

    public function getByTier(int $tier): Collection
    {
        $startLevel = ($tier - 1) * 10 + 1;
        $endLevel = $tier * 10;

        return LevelConfig::whereBetween('level', [$startLevel, $endLevel])
            ->orderBy('level')
            ->get();
    }

    public function getAllGroupedByTier(): array
    {
        $allLevels = LevelConfig::orderBy('level')->get();

        $tiers = [];
        for ($i = 1; $i <= 10; $i++) {
            $startLevel = ($i - 1) * 10 + 1;
            $endLevel = $i * 10;

            $tierLevels = $allLevels->filter(function ($level) use ($startLevel, $endLevel) {
                return $level->level >= $startLevel && $level->level <= $endLevel;
            })->values();

            if ($tierLevels->isNotEmpty()) {
                $tiers[] = [
                    'tier' => $i,
                    'levels' => $tierLevels,
                    'start_level' => $startLevel,
                    'end_level' => $endLevel,
                ];
            }
        }

        return $tiers;
    }

    public function findById(int $id): ?LevelConfig
    {
        return LevelConfig::find($id);
    }

    public function update(LevelConfig $levelConfig, array $data): LevelConfig
    {
        $levelConfig->update($data);

        return $levelConfig;
    }

    public function getStatistics(): array
    {
        return [
            'total_levels' => LevelConfig::count(),
            'max_level' => LevelConfig::max('level'),
            'users_by_level' => \DB::table('user_gamification_stats')
                ->select('global_level', \DB::raw('count(*) as count'))
                ->groupBy('global_level')
                ->orderBy('global_level')
                ->get(),
        ];
    }
}