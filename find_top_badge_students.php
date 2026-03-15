<?php

/**
 * Script untuk mencari student yang paling banyak memiliki badge
 * 
 * Usage:
 * php artisan tinker < find_top_badge_students.php
 * 
 * Or run directly in tinker:
 * php artisan tinker
 * > include 'find_top_badge_students.php';
 */

use Modules\Auth\Models\User;
use Modules\Gamification\Models\UserBadge;

echo "\n=== TOP STUDENTS BY BADGE COUNT ===\n\n";

// Method 1: Using withCount (Recommended - Most Efficient)
echo "Method 1: Using withCount (Recommended)\n";
echo str_repeat("-", 50) . "\n";

$topStudents = User::role('Student')
    ->withCount('badges')
    ->orderByDesc('badges_count')
    ->limit(10)
    ->get(['id', 'name', 'email']);

foreach ($topStudents as $index => $student) {
    echo sprintf(
        "#%d - %s (%s)\n    Badges: %d\n",
        $index + 1,
        $student->name,
        $student->email,
        $student->badges_count
    );
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Method 2: Using Query Builder with JOIN
echo "Method 2: Using Query Builder with JOIN\n";
echo str_repeat("-", 50) . "\n";

$topStudentsQuery = DB::table('users')
    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
    ->leftJoin('user_badges', 'users.id', '=', 'user_badges.user_id')
    ->where('roles.name', 'Student')
    ->where('model_has_roles.model_type', 'Modules\\Auth\\Models\\User')
    ->whereNull('users.deleted_at')
    ->select(
        'users.id',
        'users.name',
        'users.email',
        DB::raw('COUNT(user_badges.id) as badges_count')
    )
    ->groupBy('users.id', 'users.name', 'users.email')
    ->orderByDesc('badges_count')
    ->limit(10)
    ->get();

foreach ($topStudentsQuery as $index => $student) {
    echo sprintf(
        "#%d - %s (%s)\n    Badges: %d\n",
        $index + 1,
        $student->name,
        $student->email,
        $student->badges_count
    );
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Method 3: Get detailed badge information for top student
echo "Method 3: Detailed Badge Information for Top Student\n";
echo str_repeat("-", 50) . "\n";

$topStudent = User::role('Student')
    ->withCount('badges')
    ->orderByDesc('badges_count')
    ->first();

if ($topStudent) {
    echo sprintf(
        "Top Student: %s (%s)\n",
        $topStudent->name,
        $topStudent->email
    );
    echo sprintf("Total Badges: %d\n\n", $topStudent->badges_count);
    
    // Get all badges with details
    $badges = UserBadge::where('user_id', $topStudent->id)
        ->with(['badge' => function($query) {
            $query->select('id', 'name', 'code', 'category', 'rarity', 'type');
        }])
        ->orderBy('earned_at', 'desc')
        ->get();
    
    echo "Badge List:\n";
    foreach ($badges as $index => $userBadge) {
        echo sprintf(
            "  %d. %s (%s)\n     Category: %s | Rarity: %s | Type: %s\n     Earned: %s\n",
            $index + 1,
            $userBadge->badge->name ?? 'Unknown',
            $userBadge->badge->code ?? 'N/A',
            $userBadge->badge->category ?? 'N/A',
            $userBadge->badge->rarity?->value ?? 'N/A',
            $userBadge->badge->type?->value ?? 'N/A',
            $userBadge->earned_at?->format('Y-m-d H:i:s') ?? 'N/A'
        );
    }
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Method 4: Badge Statistics by Category
echo "Method 4: Badge Statistics by Category for Top Student\n";
echo str_repeat("-", 50) . "\n";

if ($topStudent) {
    $badgesByCategory = UserBadge::where('user_id', $topStudent->id)
        ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
        ->select('badges.category', DB::raw('COUNT(*) as count'))
        ->groupBy('badges.category')
        ->orderByDesc('count')
        ->get();
    
    echo sprintf("Badge breakdown for %s:\n", $topStudent->name);
    foreach ($badgesByCategory as $stat) {
        echo sprintf(
            "  - %s: %d badges\n",
            ucfirst($stat->category ?? 'Unknown'),
            $stat->count
        );
    }
    
    echo "\n";
    
    // Badge by rarity
    $badgesByRarity = UserBadge::where('user_id', $topStudent->id)
        ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
        ->select('badges.rarity', DB::raw('COUNT(*) as count'))
        ->groupBy('badges.rarity')
        ->orderByDesc('count')
        ->get();
    
    echo "Badge breakdown by rarity:\n";
    foreach ($badgesByRarity as $stat) {
        echo sprintf(
            "  - %s: %d badges\n",
            ucfirst($stat->rarity ?? 'Unknown'),
            $stat->count
        );
    }
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Method 5: Compare Top 5 Students
echo "Method 5: Comparison of Top 5 Students\n";
echo str_repeat("-", 50) . "\n";

$top5Students = User::role('Student')
    ->withCount('badges')
    ->with(['gamificationStats' => function($query) {
        $query->select('user_id', 'total_xp', 'global_level');
    }])
    ->orderByDesc('badges_count')
    ->limit(5)
    ->get(['id', 'name', 'email']);

echo sprintf(
    "%-4s %-25s %-10s %-10s %-10s\n",
    "Rank",
    "Name",
    "Badges",
    "Level",
    "Total XP"
);
echo str_repeat("-", 70) . "\n";

foreach ($top5Students as $index => $student) {
    echo sprintf(
        "%-4s %-25s %-10d %-10s %-10s\n",
        "#" . ($index + 1),
        substr($student->name, 0, 24),
        $student->badges_count,
        $student->gamificationStats?->global_level ?? 'N/A',
        $student->gamificationStats?->total_xp ?? 'N/A'
    );
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Summary Statistics
echo "Summary Statistics\n";
echo str_repeat("-", 50) . "\n";

$totalStudents = User::role('Student')->count();
$studentsWithBadges = User::role('Student')
    ->has('badges')
    ->count();
$totalBadgesAwarded = UserBadge::count();
$averageBadgesPerStudent = $studentsWithBadges > 0 
    ? round($totalBadgesAwarded / $studentsWithBadges, 2) 
    : 0;

echo sprintf("Total Students: %d\n", $totalStudents);
echo sprintf("Students with Badges: %d (%.1f%%)\n", 
    $studentsWithBadges, 
    $totalStudents > 0 ? ($studentsWithBadges / $totalStudents * 100) : 0
);
echo sprintf("Total Badges Awarded: %d\n", $totalBadgesAwarded);
echo sprintf("Average Badges per Student (with badges): %.2f\n", $averageBadgesPerStudent);

echo "\n" . str_repeat("=", 50) . "\n";
echo "Script completed successfully!\n\n";
