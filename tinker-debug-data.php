echo "=== DEBUGGING DATA ===\n\n";

echo "1. Checking Students with Role 'Student':\n";
$students = \Modules\Auth\Models\User::role('Student')->get();
echo "   Total Students: " . $students->count() . "\n";
if ($students->count() > 0) {
    echo "   Sample Students:\n";
    foreach ($students->take(3) as $student) {
        echo "     - ID: {$student->id}, Name: {$student->name}, Email: {$student->email}\n";
    }
}
echo "\n";

echo "2. Checking Active Enrollments:\n";
$enrollments = \Modules\Enrollments\Models\Enrollment::where('status', 'active')->get();
echo "   Total Active Enrollments: " . $enrollments->count() . "\n";
if ($enrollments->count() > 0) {
    echo "   Sample Enrollments:\n";
    foreach ($enrollments->take(3) as $enrollment) {
        $user = \Modules\Auth\Models\User::find($enrollment->user_id);
        $course = \Modules\Schemes\Models\Course::find($enrollment->course_id);
        echo "     - User: {$user->name} | Course: {$course->title}\n";
    }
}
echo "\n";

echo "3. Checking Published Assignments:\n";
$assignments = \Modules\Learning\Models\Assignment::where('status', 'published')->get();
echo "   Total Published Assignments: " . $assignments->count() . "\n";
if ($assignments->count() > 0) {
    echo "   Sample Assignments:\n";
    foreach ($assignments->take(3) as $assignment) {
        $unit = \Modules\Schemes\Models\Unit::find($assignment->unit_id);
        echo "     - ID: {$assignment->id}, Title: {$assignment->title}, Unit: {$unit->title}\n";
    }
}
echo "\n";

echo "4. Checking Published Quizzes:\n";
$quizzes = \Modules\Learning\Models\Quiz::where('status', 'published')->get();
echo "   Total Published Quizzes: " . $quizzes->count() . "\n";
if ($quizzes->count() > 0) {
    echo "   Sample Quizzes:\n";
    foreach ($quizzes->take(3) as $quiz) {
        $unit = \Modules\Schemes\Models\Unit::find($quiz->unit_id);
        echo "     - ID: {$quiz->id}, Title: {$quiz->title}, Unit: {$unit->title}\n";
    }
}
echo "\n";

echo "5. Checking Units:\n";
$units = \Modules\Schemes\Models\Unit::with('course')->get();
echo "   Total Units: " . $units->count() . "\n";
if ($units->count() > 0) {
    echo "   Sample Units:\n";
    foreach ($units->take(5) as $unit) {
        echo "     - ID: {$unit->id}, Order: {$unit->order}, Title: {$unit->title}, Course: {$unit->course->title}\n";
    }
}
echo "\n";

echo "6. Checking Students with Active Enrollments:\n";
$studentsWithEnrollments = \Modules\Auth\Models\User::role('Student')
    ->whereHas('enrollments', function($q) {
        $q->where('status', 'active');
    })
    ->get();
echo "   Total Students with Active Enrollments: " . $studentsWithEnrollments->count() . "\n";
if ($studentsWithEnrollments->count() > 0) {
    echo "   Sample:\n";
    foreach ($studentsWithEnrollments->take(3) as $student) {
        $enrollmentCount = $student->enrollments()->where('status', 'active')->count();
        echo "     - {$student->name} has {$enrollmentCount} active enrollment(s)\n";
    }
}
echo "\n";

echo "7. Testing PrerequisiteService:\n";
if ($studentsWithEnrollments->count() > 0 && $units->count() > 0) {
    $testStudent = $studentsWithEnrollments->first();
    $testUnit = $units->first();
    
    echo "   Testing with Student: {$testStudent->name} (ID: {$testStudent->id})\n";
    echo "   Testing with Unit: {$testUnit->title} (Order: {$testUnit->order})\n";
    
    try {
        $prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);
        $isLocked = $prerequisiteService->checkUnitAccess($testUnit, $testStudent->id);
        echo "   Result: " . ($isLocked ? "LOCKED" : "UNLOCKED") . "\n";
        
        if ($isLocked) {
            $incompleteness = $prerequisiteService->getUnitIncompleteness($testUnit, $testStudent->id);
            echo "   Missing Prerequisites: " . count($incompleteness) . "\n";
            if (!empty($incompleteness)) {
                foreach ($incompleteness as $item) {
                    echo "     - {$item['type']}: {$item['title']}\n";
                }
            }
        }
    } catch (\Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "   Cannot test - no students or units found\n";
}
echo "\n";

echo "=== END DEBUG ===\n";
