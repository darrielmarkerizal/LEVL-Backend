// USAGE: Change the student ID below
$studentId = 15; // Change this to the student ID you want to check

$student = \Modules\Auth\Models\User::role('Student')->find($studentId);

if (!$student) {
    echo "Student with ID {$studentId} not found!\n";
    exit;
}

$prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);

echo "=== ASSESSMENT STATUS FOR STUDENT ===\n";
echo "Name: {$student->name}\n";
echo "Email: {$student->email}\n";
echo "ID: {$student->id}\n";
echo str_repeat('=', 60) . "\n\n";

$enrollments = $student->enrollments()
    ->where('status', 'active')
    ->with(['course.units' => function($q) {
        $q->orderBy('order')
          ->with(['assignments' => function($q) {
              $q->where('status', 'published')->orderBy('order');
          }, 'quizzes' => function($q) {
              $q->where('status', 'published')->orderBy('order');
          }]);
    }])
    ->get();

if ($enrollments->isEmpty()) {
    echo "No active enrollments found.\n";
    exit;
}

foreach ($enrollments as $enrollment) {
    $course = $enrollment->course;
    echo "COURSE: {$course->title}\n";
    echo str_repeat('-', 60) . "\n";
    
    foreach ($course->units as $unit) {
        $isLocked = $prerequisiteService->checkUnitAccess($unit, $student);
        $lockStatus = $isLocked ? '🔒 LOCKED' : '🔓 UNLOCKED';
        
        echo "\n  Unit {$unit->order}: {$unit->title} [{$lockStatus}]\n";
        
        if ($isLocked) {
            $incompleteness = $prerequisiteService->getUnitIncompleteness($unit, $student);
            if (!empty($incompleteness)) {
                echo "    Prerequisites needed:\n";
                foreach ($incompleteness as $item) {
                    $type = ucfirst($item['type']);
                    $required = $item['passing_required'] ? ' (MUST PASS)' : '';
                    echo "      - {$type}: {$item['title']}{$required}\n";
                }
            }
        }
        
        // Show assignments
        if ($unit->assignments->isNotEmpty()) {
            echo "    Assignments:\n";
            foreach ($unit->assignments as $assignment) {
                $status = $isLocked ? '🔒' : '✅';
                echo "      {$status} {$assignment->title} (ID: {$assignment->id})\n";
                
                // Check if student has submission
                $submission = $assignment->submissions()
                    ->where('user_id', $student->id)
                    ->latest()
                    ->first();
                
                if ($submission) {
                    echo "         Status: {$submission->status}";
                    if ($submission->score !== null) {
                        echo " | Score: {$submission->score}/{$assignment->max_score}";
                    }
                    echo "\n";
                }
            }
        }
        
        // Show quizzes
        if ($unit->quizzes->isNotEmpty()) {
            echo "    Quizzes:\n";
            foreach ($unit->quizzes as $quiz) {
                $status = $isLocked ? '🔒' : '✅';
                echo "      {$status} {$quiz->title} (ID: {$quiz->id})\n";
                
                // Check if student has submission
                $submission = $quiz->submissions()
                    ->where('user_id', $student->id)
                    ->latest()
                    ->first();
                
                if ($submission) {
                    echo "         Status: {$submission->status}";
                    if ($submission->final_score !== null) {
                        echo " | Score: {$submission->final_score}/{$quiz->max_score}";
                        $passed = $submission->final_score >= $quiz->passing_grade ? 'PASSED' : 'FAILED';
                        echo " | {$passed}";
                    }
                    echo "\n";
                }
            }
        }
    }
    
    echo "\n" . str_repeat('=', 60) . "\n\n";
}

// Summary
$unlockedCount = 0;
$lockedCount = 0;

foreach ($enrollments as $enrollment) {
    foreach ($enrollment->course->units as $unit) {
        $isLocked = $prerequisiteService->checkUnitAccess($unit, $student);
        $assessmentCount = $unit->assignments->count() + $unit->quizzes->count();
        
        if ($isLocked) {
            $lockedCount += $assessmentCount;
        } else {
            $unlockedCount += $assessmentCount;
        }
    }
}

echo "SUMMARY:\n";
echo "  Unlocked Assessments: {$unlockedCount}\n";
echo "  Locked Assessments: {$lockedCount}\n";
echo "  Total Assessments: " . ($unlockedCount + $lockedCount) . "\n";
