echo "=== Testing Dashboard Endpoint ===" . PHP_EOL . PHP_EOL;

echo "1. Checking Gamification Stats..." . PHP_EOL;
$statsCount = \Modules\Gamification\Models\UserGamificationStat::count();
echo "   Total User Stats: {$statsCount}" . PHP_EOL;

$withStreaks = \Modules\Gamification\Models\UserGamificationStat::where('current_streak', '>', 0)->count();
echo "   Users with active streaks: {$withStreaks}" . PHP_EOL . PHP_EOL;

echo "2. Checking Badges/Achievements..." . PHP_EOL;
$badgeCount = \Modules\Gamification\Models\Badge::count();
echo "   Total Badges: {$badgeCount}" . PHP_EOL;

$userBadgeCount = \Modules\Gamification\Models\UserBadge::count();
echo "   Total User Badges earned: {$userBadgeCount}" . PHP_EOL . PHP_EOL;

echo "3. Finding Students with Streaks and Achievements..." . PHP_EOL;
$topStudents = \Modules\Gamification\Models\UserGamificationStat::where('current_streak', '>', 0)
    ->orderByDesc('current_streak')
    ->limit(5)
    ->get();

if ($topStudents->count() > 0) {
    echo "   Top Students with Streaks:" . PHP_EOL;
    foreach ($topStudents as $stat) {
        $user = \Modules\Auth\Models\User::find($stat->user_id);
        $badgesCount = \Modules\Gamification\Models\UserBadge::where('user_id', $user->id)->count();
        $enrollmentsCount = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)->count();
        
        echo "   - {$user->name} (ID: {$user->id})" . PHP_EOL;
        echo "     Streak: {$stat->current_streak} days | XP: {$stat->total_xp} | Level: {$stat->global_level}" . PHP_EOL;
        echo "     Badges: {$badgesCount} | Enrollments: {$enrollmentsCount}" . PHP_EOL;
    }
} else {
    echo "   No students with active streaks found." . PHP_EOL;
}

echo PHP_EOL . "4. Testing Dashboard API for a Student..." . PHP_EOL;

$studentWithData = \Modules\Auth\Models\User::role('Student')
    ->whereHas('gamificationStats', function($q) {
        $q->where('current_streak', '>', 0);
    })
    ->first();

if ($studentWithData) {
    echo "   Testing with: {$studentWithData->name} (ID: {$studentWithData->id})" . PHP_EOL;
    
    try {
        $service = app(\Modules\Dashboard\Contracts\Services\DashboardServiceInterface::class);
        $dashboardData = $service->getDashboardData($studentWithData);
        
        echo "   ✓ Dashboard data retrieved successfully!" . PHP_EOL;
        echo "   - Gamification Stats: " . json_encode($dashboardData['gamification_stats']) . PHP_EOL;
        echo "   - Recent Achievements: " . count($dashboardData['recent_achievements']) . " badges" . PHP_EOL;
        echo "   - Recommended Courses: " . count($dashboardData['recommended_courses']) . " courses" . PHP_EOL;
        echo "   - Latest Activity: " . ($dashboardData['latest_learning_activity'] ? 'Yes' : 'No') . PHP_EOL;
    } catch (\Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . PHP_EOL;
        echo "   Trace: " . $e->getTraceAsString() . PHP_EOL;
    }
} else {
    echo "   No student with streak found. Testing with any student..." . PHP_EOL;
    
    $anyStudent = \Modules\Auth\Models\User::role('Student')->first();
    if ($anyStudent) {
        echo "   Testing with: {$anyStudent->name} (ID: {$anyStudent->id})" . PHP_EOL;
        
        try {
            $service = app(\Modules\Dashboard\Contracts\Services\DashboardServiceInterface::class);
            $dashboardData = $service->getDashboardData($anyStudent);
            
            echo "   ✓ Dashboard data retrieved successfully!" . PHP_EOL;
            echo "   - Gamification Stats: " . json_encode($dashboardData['gamification_stats']) . PHP_EOL;
            echo "   - Recent Achievements: " . count($dashboardData['recent_achievements']) . " badges" . PHP_EOL;
            echo "   - Recommended Courses: " . count($dashboardData['recommended_courses']) . " courses" . PHP_EOL;
            echo "   - Latest Activity: " . ($dashboardData['latest_learning_activity'] ? 'Yes' : 'No') . PHP_EOL;
        } catch (\Exception $e) {
            echo "   ✗ Error: " . $e->getMessage() . PHP_EOL;
            echo "   Trace: " . $e->getTraceAsString() . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
