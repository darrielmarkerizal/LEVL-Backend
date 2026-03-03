$prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);

$students = \Modules\Auth\Models\User::role('Student')
    ->whereHas('enrollments', function ($q) {
        $q->where('status', 'active');
    })
    ->with(['enrollments' => function ($q) {
        $q->where('status', 'active')
            ->with(['course.units' => function ($q) {
                $q->with([
                    'assignments' => function ($q) {
                        $q->where('status', 'published');
                    },
                    'quizzes' => function ($q) {
                        $q->where('status', 'published');
                    }
                ]);
            }]);
    }])
    ->get();

echo "=== STUDENTS WITH UNLOCKED ASSESSMENTS ===\n\n";

$totalStudents = 0;
$totalUnlockedAssignments = 0;
$totalUnlockedQuizzes = 0;

foreach ($students as $student) {

    $hasUnlocked = false;
    $studentAssignments = [];
    $studentQuizzes = [];

    foreach ($student->enrollments as $enrollment) {
        foreach ($enrollment->course->units as $unit) {

            $isLocked = $prerequisiteService->checkUnitAccess($unit, $student->id);

            if (!$isLocked) {

                foreach ($unit->assignments as $assignment) {
                    $studentAssignments[] = [
                        'course' => $enrollment->course->title,
                        'unit'   => $unit->title,
                        'title'  => $assignment->title,
                        'id'     => $assignment->id,
                    ];
                    $hasUnlocked = true;
                }

                foreach ($unit->quizzes as $quiz) {
                    $studentQuizzes[] = [
                        'course' => $enrollment->course->title,
                        'unit'   => $unit->title,
                        'title'  => $quiz->title,
                        'id'     => $quiz->id,
                    ];
                    $hasUnlocked = true;
                }
            }
        }
    }

    if ($hasUnlocked) {
        $totalStudents++;

        echo "Student #{$totalStudents}: {$student->name}\n";
        echo "  ID: {$student->id}\n";
        echo "  Email: {$student->email}\n";

        if (!empty($studentAssignments)) {
            echo "  Unlocked Assignments: " . count($studentAssignments) . "\n";
            foreach ($studentAssignments as $a) {
                echo "    • {$a['title']} (ID: {$a['id']}) - {$a['course']} > {$a['unit']}\n";
                $totalUnlockedAssignments++;
            }
        }

        if (!empty($studentQuizzes)) {
            echo "  Unlocked Quizzes: " . count($studentQuizzes) . "\n";
            foreach ($studentQuizzes as $q) {
                echo "    • {$q['title']} (ID: {$q['id']}) - {$q['course']} > {$q['unit']}\n";
                $totalUnlockedQuizzes++;
            }
        }

        echo "\n";
    }
}

echo str_repeat('=', 60) . "\n";
echo "SUMMARY:\n";
echo "  Total Students: {$totalStudents}\n";
echo "  Total Unlocked Assignments: {$totalUnlockedAssignments}\n";
echo "  Total Unlocked Quizzes: {$totalUnlockedQuizzes}\n";
echo "  Total Unlocked Assessments: " . ($totalUnlockedAssignments + $totalUnlockedQuizzes) . "\n";
