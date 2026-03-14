<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Common\Models\LevelConfig;

class LevelService
{
    /**
     * Calculate XP required for a specific level using formula: XP(level) = 100 × level^1.6
     */
    public function calculateXpForLevel(int $level): int
    {
        if ($level <= 0) {
            return 0;
        }

        return (int) round(100 * pow($level, 1.6));
    }

    /**
     * Calculate level from total XP
     */
    public function calculateLevelFromXp(int $totalXp): int
    {
        if ($totalXp <= 0) {
            return 0;
        }

        // Binary search untuk efisiensi
        $low = 0;
        $high = 100; // Max level
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

    /**
     * Calculate total XP required to reach a level (cumulative)
     */
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

    /**
     * Get XP progress for current level
     */
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

    /**
     * Generate level configs for a range of levels
     */
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

    /**
     * Sync level configs to database
     */
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

        // Clear cache
        Cache::forget('gamification.level_configs');

        return $synced;
    }

    /**
     * Get level name based on level number
     */
    private function getLevelName(int $level): string
    {
        return match (true) {
            $level >= 90 => 'Legendary Master',
            $level >= 80 => 'Grand Master',
            $level >= 70 => 'Master',
            $level >= 60 => 'Expert',
            $level >= 50 => 'Advanced',
            $level >= 40 => 'Proficient',
            $level >= 30 => 'Intermediate',
            $level >= 20 => 'Competent',
            $level >= 10 => 'Novice',
            default => 'Beginner',
        };
    }

    /**
     * Get default rewards for a level
     */
    private function getDefaultRewards(int $level): array
    {
        $rewards = [];

        // Milestone rewards
        if ($level % 10 === 0) {
            $rewards['badge'] = "level_{$level}_milestone";
            $rewards['bonus_xp'] = $level * 10;
        }

        // Special rewards for major milestones
        if (in_array($level, [25, 50, 75, 100])) {
            $rewards['title'] = $this->getLevelName($level);
            $rewards['bonus_xp'] = $level * 20;
        }

        return $rewards;
    }

    /**
     * Get level configs from cache or database
     */
    public function getLevelConfigs(): Collection
    {
        return Cache::remember('gamification.level_configs', 3600, function () {
            return LevelConfig::orderBy('level')->get();
        });
    }

    /**
     * Get specific level config
     */
    public function getLevelConfig(int $level): ?LevelConfig
    {
        return $this->getLevelConfigs()->firstWhere('level', $level);
    }

    /**
     * Get level progression table (for display)
     */
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
