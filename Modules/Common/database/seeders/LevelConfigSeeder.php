<?php

namespace Modules\Common\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Common\Models\LevelConfig;

class LevelConfigSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding level configurations...');

        // Define levels 1 to 100
        $levels = [];

        for ($i = 1; $i <= 100; $i++) {
            // XP Required Formula: 100 × level^1.6
            $xpRequired = (int) round(100 * pow($i, 1.6));
            
            // Bonus XP Formula: 10 × level^1.3 (smaller exponent for balanced rewards)
            $bonusXp = (int) round(10 * pow($i, 1.3));

            $levels[] = [
                'level' => $i,
                'name' => $this->getLevelName($i),
                'xp_required' => $xpRequired,
                'bonus_xp' => $bonusXp,
                'rewards' => [], // Empty array, rewards managed via bonus_xp and milestone_badge_id
            ];
        }

        foreach ($levels as $level) {
            LevelConfig::updateOrCreate(
                ['level' => $level['level']],
                $level
            );
        }

        $this->command->info('✅ Successfully seeded 100 level configurations with dynamic bonus XP');
    }

    /**
     * Get tier-based level name
     */
    private function getLevelName(int $level): string
    {
        $tiers = [
            ['min' => 1, 'max' => 10, 'name' => 'Beginner'],
            ['min' => 11, 'max' => 20, 'name' => 'Novice'],
            ['min' => 21, 'max' => 30, 'name' => 'Competent'],
            ['min' => 31, 'max' => 40, 'name' => 'Intermediate'],
            ['min' => 41, 'max' => 50, 'name' => 'Proficient'],
            ['min' => 51, 'max' => 60, 'name' => 'Advanced'],
            ['min' => 61, 'max' => 70, 'name' => 'Expert'],
            ['min' => 71, 'max' => 80, 'name' => 'Master'],
            ['min' => 81, 'max' => 90, 'name' => 'Grand Master'],
            ['min' => 91, 'max' => 100, 'name' => 'Legendary'],
        ];

        foreach ($tiers as $tier) {
            if ($level >= $tier['min'] && $level <= $tier['max']) {
                $tierLevel = $level - $tier['min'] + 1;
                return "{$tier['name']} {$tierLevel}";
            }
        }

        return "Level {$level}";
    }
}
