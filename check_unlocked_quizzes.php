<?php

// Script to find students with unlocked quizzes that haven't been attempted yet

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizSubmission;
use Modules\Schemes\Services\PrerequisiteService;

echo "=== Finding Students with Unlocked Quizzes ===\n\n";

// Get all active students
$students = User::whereHas('roles', function ($q) {
    $q->where('name', 'Student');
})->where('status', 'active')->get();

echo 'Total active students: '.$students->count()."\n\n";

$prerequisiteService = app(PrerequisiteService::class);
$results = [];

foreach ($students as $student) {
    echo "Checking student: {$student->name} (ID: {$student->id})\n";

    // Get student's enrollments
    $enrollments = Enrollment::where('user_id', $student->id)
        ->where('status', 'active')
        ->with('course')
        ->get();

    foreach ($enrollments as $enrollment) {
        $course = $enrollment->course;

        // Get all published quizzes in this course
        $quizzes = Quiz::whereHas('lesson.unit', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })
            ->where('status', 'published')
            ->with(['lesson.unit'])
            ->get();

        foreach ($quizzes as $quiz) {
            // Check if student has attempted this quiz
            $hasAttempt = QuizSubmission::where('quiz_id', $quiz->id)
                ->where('user_id', $student->id)
                ->exists();

            if (! $hasAttempt) {
                // Check if quiz is unlocked
                $accessCheck = $prerequisiteService->checkQuizAccess($quiz, $student->id);

                if ($accessCheck['accessible']) {
                    $results[] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'student_email' => $student->email,
                        'course_id' => $course->id,
                        'course_name' => $course->title,
                        'quiz_id' => $quiz->id,
                        'quiz_title' => $quiz->title,
                        'lesson_title' => $quiz->lesson->title ?? 'N/A',
                        'unit_title' => $quiz->lesson->unit->title ?? 'N/A',
                    ];

                    echo "  ✓ Found unlocked quiz: {$quiz->title} (ID: {$quiz->id})\n";
                }
            }
        }
    }

    echo "\n";
}

echo "\n=== SUMMARY ===\n";
echo 'Total unlocked quizzes not attempted: '.count($results)."\n\n";

if (count($results) > 0) {
    echo "Details:\n";
    echo str_repeat('-', 100)."\n";
    printf("%-20s %-30s %-30s %-20s\n", 'Student', 'Course', 'Quiz', 'Unit/Lesson');
    echo str_repeat('-', 100)."\n";

    foreach ($results as $result) {
        printf(
            "%-20s %-30s %-30s %-20s\n",
            substr($result['student_name'], 0, 18),
            substr($result['course_name'], 0, 28),
            substr($result['quiz_title'], 0, 28),
            substr($result['unit_title'], 0, 18)
        );
    }

    echo str_repeat('-', 100)."\n\n";

    // Show first result details for testing
    if (count($results) > 0) {
        $first = $results[0];
        echo "\n=== FIRST RESULT (For Testing) ===\n";
        echo "Student ID: {$first['student_id']}\n";
        echo "Student Name: {$first['student_name']}\n";
        echo "Student Email: {$first['student_email']}\n";
        echo "Quiz ID: {$first['quiz_id']}\n";
        echo "Quiz Title: {$first['quiz_title']}\n";
        echo "Course: {$first['course_name']}\n";
        echo "\nYou can test with this student and quiz!\n";
    }
} else {
    echo "No unlocked quizzes found that haven't been attempted.\n";
}

echo "\n=== END ===\n";
