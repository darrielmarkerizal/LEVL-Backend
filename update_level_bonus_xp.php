<?php

/**
 * Script to update all level configs with dynamic bonus XP
 * Formula: bonus_xp = round(10 × level^1.3)
 * 
 * This follows the same progressive pattern as xp_required but with smaller values
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Common\Models\LevelConfig;

echo "Updating level configs with dynamic bonus XP...\n\n";

$levels = LevelConfig::orderBy('level')->get();
$updated = 0;

foreach ($levels as $levelConfig) {
    $level = $levelConfig->level;
    
    // Calculate bonus XP using formula: 10 × level^1.3
    $bonusXp = (int) round(10 * pow($level, 1.3));
    
    // Update the level config
    $levelConfig->bonus_xp = $bonusXp;
    $levelConfig->save();
    
    $updated++;
    
    echo sprintf(
        "Level %3d: bonus_xp = %4d XP (xp_required = %5d)\n",
        $level,
        $bonusXp,
        $levelConfig->xp_required
    );
}

echo "\n✓ Successfully updated {$updated} level configurations with dynamic bonus XP\n";
echo "\nBonus XP Formula: 10 × level^1.3\n";
echo "This provides progressive rewards that scale with level difficulty\n";
