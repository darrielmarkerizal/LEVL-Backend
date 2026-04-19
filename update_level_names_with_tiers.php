<?php



require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Common\Models\LevelConfig;

echo "Updating level names with tier system...\n\n";


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

function getTierName(int $level): string
{
    global $tiers;

    $tierStart = (int) (floor(($level - 1) / 10) * 10) + 1;

    return $tiers[$tierStart] ?? 'Unknown';
}

function getTierNumber(int $level): int
{
    return (($level - 1) % 10) + 1;
}

$levels = LevelConfig::orderBy('level')->get();
$updated = 0;

foreach ($levels as $levelConfig) {
    $level = $levelConfig->level;
    $tierName = getTierName($level);
    $tierNumber = getTierNumber($level);

    $newName = "{$tierName} {$tierNumber}";

    if ($levelConfig->name !== $newName) {
        $oldName = $levelConfig->name;
        $levelConfig->name = $newName;
        $levelConfig->save();

        $updated++;
        echo sprintf(
            "Level %3d: %-20s → %s\n",
            $level,
            $oldName,
            $newName
        );
    }
}

echo "\n✓ Successfully updated {$updated} level names with tier system\n";
echo "\nTier Structure:\n";
echo "  Level 1-10:   Beginner 1-10\n";
echo "  Level 11-20:  Novice 1-10\n";
echo "  Level 21-30:  Competent 1-10\n";
echo "  Level 31-40:  Intermediate 1-10\n";
echo "  Level 41-50:  Proficient 1-10\n";
echo "  Level 51-60:  Advanced 1-10\n";
echo "  Level 61-70:  Expert 1-10\n";
echo "  Level 71-80:  Master 1-10\n";
echo "  Level 81-90:  Grand Master 1-10\n";
echo "  Level 91-100: Legendary 1-10\n";
