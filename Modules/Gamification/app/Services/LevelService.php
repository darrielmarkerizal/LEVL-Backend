<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Common\Http\Resources\LevelConfigResource;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Repositories\LevelRepository;

class LevelService
{
    public function __construct(
        private readonly LevelRepository $levelRepository
    ) {}

    public function getTierName(int $tier): string
    {
        $startLevel = ($tier - 1) * 10 + 1;
        $firstLevel = $this->levelRepository->findById($startLevel);
        
        if ($firstLevel && $firstLevel->name) {
            return preg_replace('/\s+\d+$/', '', $firstLevel->name);
        }
        
        return 'Unknown';
    }

    public function getPaginatedLevels(int $perPage = 20): LengthAwarePaginator
    {
        $levels = $this->levelRepository->getPaginated($perPage);
        
        $levels->getCollection()->transform(fn ($level) => new LevelConfigResource($level));
        
        return $levels;
    }

    public function getLevelsByTier(int $tier): array
    {
        $levels = $this->levelRepository->getByTier($tier);
        $startLevel = ($tier - 1) * 10 + 1;
        $endLevel = $tier * 10;

        return [
            'tier' => $tier,
            'tier_name' => $this->getTierName($tier),
            'level_range' => [
                'start' => $startLevel,
                'end' => $endLevel,
            ],
            'levels' => $levels->map(fn ($level) => new LevelConfigResource($level)),
        ];
    }

    public function getAllLevelsGroupedByTier(): array
    {
        $tiers = $this->levelRepository->getAllGroupedByTier();

        $result = array_map(function ($tier) {
            return [
                'tier' => $tier['tier'],
                'tier_name' => $this->getTierName($tier['tier']),
                'level_range' => [
                    'start' => $tier['start_level'],
                    'end' => $tier['end_level'],
                ],
                'levels' => $tier['levels']->map(fn ($level) => new LevelConfigResource($level)),
            ];
        }, $tiers);

        return [
            'tiers' => $result,
            'total_tiers' => count($result),
        ];
    }

    public function updateLevelConfig(int $id, array $data): LevelConfig
    {
        $levelConfig = $this->levelRepository->findById($id);
        
        if (!$levelConfig) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Level config not found');
        }

        $updated = $this->levelRepository->update($levelConfig, $data);

        Cache::forget('gamification.level_configs');

        return $updated;
    }

    public function getLevelStatistics(): array
    {
        $stats = $this->levelRepository->getStatistics();
        
        return [
            'total_levels' => $stats['total_levels'],
            'max_level' => $stats['max_level'],
            'total_xp_to_max' => $this->calculateTotalXpForLevel(100),
            'users_by_level' => $stats['users_by_level'],
        ];
    }

    public function calculateXpForLevel(int $level): int
    {
        if ($level <= 0) {
            return 0;
        }

        return (int) round(100 * pow($level, 1.6));
    }

    public function calculateLevelFromXp(int $totalXp): int
    {
        if ($totalXp <= 0) {
            return 0;
        }

        $low = 0;
        $high = 100;
        $result = 0;

        while ($low <= $high) {
            $mid = (int) floor(($low + $high) / 2);
            $xpRequired = $this->calculateTotalXpForLevel($mid);

            if ($xpRequired <= $totalXp) {
                $result = $mid;
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return $result;
    }

    public function calculateTotalXpForLevel(int $level): int
    {
        if ($level <= 0) {
            return 0;
        }

        $total = 0;
        for ($i = 1; $i <= $level; $i++) {
            $total += $this->calculateXpForLevel($i);
        }

        return $total;
    }

    public function getLevelProgress(int $totalXp): array
    {
        $currentLevel = $this->calculateLevelFromXp($totalXp);
        $currentLevelTotalXp = $this->calculateTotalXpForLevel($currentLevel);
        $nextLevelTotalXp = $this->calculateTotalXpForLevel($currentLevel + 1);

        $currentLevelXp = $totalXp - $currentLevelTotalXp;
        $xpToNextLevel = $nextLevelTotalXp - $totalXp;
        $xpRequiredForNextLevel = $nextLevelTotalXp - $currentLevelTotalXp;

        $progress = $xpRequiredForNextLevel > 0
            ? ($currentLevelXp / $xpRequiredForNextLevel) * 100
            : 0;

        return [
            'current_level' => $currentLevel,
            'total_xp' => $totalXp,
            'current_level_xp' => $currentLevelXp,
            'xp_to_next_level' => $xpToNextLevel,
            'xp_required_for_next_level' => $xpRequiredForNextLevel,
            'progress_percentage' => round($progress, 2),
        ];
    }

    public function generateLevelConfigs(int $startLevel = 1, int $endLevel = 100): Collection
    {
        $configs = collect();

        for ($level = $startLevel; $level <= $endLevel; $level++) {
            $xpRequired = $this->calculateXpForLevel($level);

            $configs->push([
                'level' => $level,
                'name' => $this->getLevelName($level),
                'xp_required' => $xpRequired,
                'rewards' => $this->getDefaultRewards($level),
            ]);
        }

        return $configs;
    }

    public function syncLevelConfigs(int $startLevel = 1, int $endLevel = 100): int
    {
        $configs = $this->generateLevelConfigs($startLevel, $endLevel);
        $synced = 0;

        foreach ($configs as $config) {
            LevelConfig::updateOrCreate(
                ['level' => $config['level']],
                $config
            );
            $synced++;
        }

        Cache::forget('gamification.level_configs');

        return $synced;
    }

    private function getLevelName(int $level): string
    {
        $tiers = [
            1 => 'Beginner',
            11 => 'Novice',
            21 => 'Competent',
            31 => 'Intermediate',
            41 => 'Proficient',
            51 => 'Advanced',
            61 => 'Expert',
            71 => 'Master',
            81 => 'Grand Master',
            91 => 'Legendary',
        ];

        $tierStart = (int) (floor(($level - 1) / 10) * 10) + 1;
        $tierName = $tiers[$tierStart] ?? 'Unknown';
        $tierNumber = (($level - 1) % 10) + 1;

        return "{$tierName} {$tierNumber}";
    }

    private function getDefaultRewards(int $level): array
    {
        return [];
    }

    public function updateTierName(int $tier, string $baseTierName): int
    {
        $startLevel = ($tier - 1) * 10 + 1;
        $endLevel = $tier * 10;
        
        $updated = 0;
        
        for ($level = $startLevel; $level <= $endLevel; $level++) {
            $tierNumber = (($level - 1) % 10) + 1;
            $newName = "{$baseTierName} {$tierNumber}";
            
            LevelConfig::where('level', $level)->update([
                'name' => $newName,
            ]);
            
            $updated++;
        }
        
        Cache::forget('gamification.level_configs');
        
        return $updated;
    }

    public function getLevelConfigs(): Collection
    {
        return Cache::remember('gamification.level_configs', 3600, function () {
            return LevelConfig::orderBy('level')->get();
        });
    }

    public function getLevelConfig(int $level): ?LevelConfig
    {
        return $this->getLevelConfigs()->firstWhere('level', $level);
    }

    public function getLevelProgressionTable(int $startLevel = 1, int $endLevel = 20): array
    {
        $table = [];

        for ($level = $startLevel; $level <= $endLevel; $level++) {
            $table[] = [
                'level' => $level,
                'xp_required' => $this->calculateXpForLevel($level),
                'total_xp' => $this->calculateTotalXpForLevel($level),
                'name' => $this->getLevelName($level),
            ];
        }

        return $table;
    }
}