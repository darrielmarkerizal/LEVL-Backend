echo "=== Testing Production Scenario ===" . PHP_EOL . PHP_EOL;

echo "1. Testing with student who has badges..." . PHP_EOL;
$studentWithBadge = \Modules\Auth\Models\User::role('Student')
    ->whereHas('badges')
    ->first();

if ($studentWithBadge) {
    echo "   Found: {$studentWithBadge->name} (ID: {$studentWithBadge->id})" . PHP_EOL;
    
    $badges = \Modules\Gamification\Models\UserBadge::where('user_id', $studentWithBadge->id)
        ->with('badge.media')
        ->orderByDesc('earned_at')
        ->limit(4)
        ->get();
    
    echo "   Badges count: {$badges->count()}" . PHP_EOL;
    
    foreach ($badges as $ub) {
        echo "   - Badge: " . ($ub->badge?->name ?? 'NULL') . PHP_EOL;
        echo "     Icon URL: " . ($ub->badge?->icon_url ?? 'NULL') . PHP_EOL;
        echo "     Has Media: " . ($ub->badge?->getFirstMedia('icon') ? 'Yes' : 'No') . PHP_EOL;
    }
    
    try {
        $service = app(\Modules\Dashboard\Contracts\Services\DashboardServiceInterface::class);
        $data = $service->getDashboardData($studentWithBadge);
        echo "   ✓ Dashboard call successful!" . PHP_EOL;
        echo "   Recent achievements: " . count($data['recent_achievements']) . PHP_EOL;
    } catch (\Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . PHP_EOL;
    }
} else {
    echo "   No student with badges found." . PHP_EOL;
}

echo PHP_EOL . "2. Testing Badge model icon_url accessor..." . PHP_EOL;
$badge = \Modules\Gamification\Models\Badge::first();
if ($badge) {
    echo "   Badge: {$badge->name}" . PHP_EOL;
    try {
        $iconUrl = $badge->icon_url;
        echo "   Icon URL: " . ($iconUrl ?? 'NULL') . PHP_EOL;
        echo "   ✓ Accessor works!" . PHP_EOL;
    } catch (\Exception $e) {
        echo "   ✗ Error accessing icon_url: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "3. Testing Course instructor relationship..." . PHP_EOL;
$course = \Modules\Schemes\Models\Course::with('instructor')->first();
if ($course) {
    echo "   Course: {$course->title}" . PHP_EOL;
    echo "   Instructor ID: " . ($course->instructor_id ?? 'NULL') . PHP_EOL;
    echo "   Instructor: " . ($course->instructor?->name ?? 'NULL') . PHP_EOL;
    
    try {
        $thumbnail = $course->getFirstMediaUrl('thumbnail');
        echo "   Thumbnail: " . ($thumbnail ?: 'NULL') . PHP_EOL;
        echo "   ✓ Media library works!" . PHP_EOL;
    } catch (\Exception $e) {
        echo "   ✗ Error with media: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "4. Testing recommended courses logic..." . PHP_EOL;
$student = \Modules\Auth\Models\User::role('Student')
    ->whereHas('enrollments')
    ->first();

if ($student) {
    echo "   Student: {$student->name}" . PHP_EOL;
    
    try {
        $repo = app(\Modules\Dashboard\Contracts\Repositories\DashboardRepositoryInterface::class);
        $recommended = $repo->getRecommendedCourses($student);
        echo "   ✓ Recommended courses: " . count($recommended) . PHP_EOL;
        
        foreach ($recommended as $course) {
            echo "   - {$course['title']}" . PHP_EOL;
            echo "     Instructor: " . ($course['instructor']['name'] ?? 'NULL') . PHP_EOL;
            echo "     Thumbnail: " . ($course['thumbnail'] ?? 'NULL') . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . PHP_EOL;
        echo "   Trace: " . substr($e->getTraceAsString(), 0, 500) . PHP_EOL;
    }
}

echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
