<?php

use Modules\Auth\app\Models\User;
use Modules\Schemes\app\Models\Enrollment;
use Modules\Schemes\app\Services\PrerequisiteService;


$students = User::role('Student')->get();

echo "=== CHECKING UNLOCKED ASSESSMENTS FOR ALL STUDENTS ===\n\n";

$prerequisiteService = app(PrerequisiteService::class);

foreach ($students as $student) {
    echo "Student: {$student->name} (ID: {$student->id})\n";
    echo str_repeat('-', 60)."\n";

    
    $enrollments = Enrollment::where('user_id', $student->id)
        ->where('status', 'active')
        ->with('course.units.assignments', 'course.units.quizzes')
        ->get();

    if ($enrollments->isEmpty()) {
        echo "  No active enrollments\n\n";

        continue;
    }

    foreach ($enrollments as $enrollment) {
        $course = $enrollment->course;
        echo "  Course: {$course->title}\n";

        
        foreach ($course->units as $unit) {
            
            foreach ($unit->assignments()->where('status', 'published')->get() as $assignment) {
                $isLocked = $prerequisiteService->checkUnitAccess($unit, $student);

                if (! $isLocked) {
                    echo "    ✓ Assignment UNLOCKED: {$assignment->title} (Unit: {$unit->title})\n";
                } else {
                    echo "    ✗ Assignment LOCKED: {$assignment->title} (Unit: {$unit->title})\n";
                }
            }

            
            foreach ($unit->quizzes()->where('status', 'published')->get() as $quiz) {
                $isLocked = $prerequisiteService->checkUnitAccess($unit, $student);

                if (! $isLocked) {
                    echo "    ✓ Quiz UNLOCKED: {$quiz->title} (Unit: {$unit->title})\n";
                } else {
                    echo "    ✗ Quiz LOCKED: {$quiz->title} (Unit: {$unit->title})\n";
                }
            }
        }
        echo "\n";
    }
}

echo "\n=== SUMMARY: STUDENTS WITH UNLOCKED ASSESSMENTS ===\n\n";


$summary = [];

foreach ($students as $student) {
    $enrollments = Enrollment::where('user_id', $student->id)
        ->where('status', 'active')
        ->with('course.units.assignments', 'course.units.quizzes')
        ->get();

    $unlockedAssignments = [];
    $unlockedQuizzes = [];

    foreach ($enrollments as $enrollment) {
        $course = $enrollment->course;

        foreach ($course->units as $unit) {
            $isLocked = $prerequisiteService->checkUnitAccess($unit, $student);

            if (! $isLocked) {
                
                foreach ($unit->assignments()->where('status', 'published')->get() as $assignment) {
                    $unlockedAssignments[] = [
                        'course' => $course->title,
                        'unit' => $unit->title,
                        'title' => $assignment->title,
                        'id' => $assignment->id,
                    ];
                }

                
                foreach ($unit->quizzes()->where('status', 'published')->get() as $quiz) {
                    $unlockedQuizzes[] = [
                        'course' => $course->title,
                        'unit' => $unit->title,
                        'title' => $quiz->title,
                        'id' => $quiz->id,
                    ];
                }
            }
        }
    }

    if (! empty($unlockedAssignments) || ! empty($unlockedQuizzes)) {
        $summary[] = [
            'student' => $student,
            'assignments' => $unlockedAssignments,
            'quizzes' => $unlockedQuizzes,
        ];
    }
}


foreach ($summary as $data) {
    $student = $data['student'];
    echo "Student: {$student->name} (ID: {$student->id}, Email: {$student->email})\n";

    if (! empty($data['assignments'])) {
        echo '  Unlocked Assignments ('.count($data['assignments'])."):\n";
        foreach ($data['assignments'] as $assignment) {
            echo "    - [{$assignment['course']}] {$assignment['unit']} > {$assignment['title']} (ID: {$assignment['id']})\n";
        }
    }

    if (! empty($data['quizzes'])) {
        echo '  Unlocked Quizzes ('.count($data['quizzes'])."):\n";
        foreach ($data['quizzes'] as $quiz) {
            echo "    - [{$quiz['course']}] {$quiz['unit']} > {$quiz['title']} (ID: {$quiz['id']})\n";
        }
    }

    echo "\n";
}

echo 'Total students with unlocked assessments: '.count($summary)."\n";
