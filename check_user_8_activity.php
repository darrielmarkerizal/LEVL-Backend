<?php

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;
use Modules\Enrollments\Models\LessonProgress;

$userId = 8;

echo "=== CHECKING USER ID: {$userId} ===\n\n";

// 1. Check if user exists
$user = User::find($userId);
if (! $user) {
    echo "❌ User with ID {$userId} NOT FOUND!\n";
    exit;
}

echo "✅ User found: {$user->name} ({$user->email})\n";
echo '   Role: '.$user->roles->pluck('name')->implode(', ')."\n\n";

// 2. Check enrollments
echo "--- ENROLLMENTS ---\n";
$enrollments = Enrollment::where('user_id', $userId)->get();
echo 'Total enrollments: '.$enrollments->count()."\n";

if ($enrollments->count() > 0) {
    foreach ($enrollments as $enrollment) {
        echo "  - Course ID: {$enrollment->course_id}\n";
        echo "    Course: {$enrollment->course->title}\n";
        echo "    Status: {$enrollment->status}\n";
        echo "    Enrolled at: {$enrollment->enrolled_at}\n";
        echo "    Updated at: {$enrollment->updated_at}\n\n";
    }
} else {
    echo "  ❌ No enrollments found\n\n";
}

// 3. Check lesson completions
echo "--- LESSON COMPLETIONS ---\n";
$lessonCompletions = LessonProgress::query()
    ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
    ->where('enrollments.user_id', $userId)
    ->where('lesson_progress.status', 'completed')
    ->select('lesson_progress.*')
    ->get();
echo 'Total lesson completions: '.$lessonCompletions->count()."\n";

if ($lessonCompletions->count() > 0) {
    foreach ($lessonCompletions->take(5) as $completion) {
        $lesson = \Modules\Schemes\Models\Lesson::find($completion->lesson_id);
        echo "  - Lesson ID: {$completion->lesson_id}\n";
        echo "    Lesson: {$lesson->title}\n";
        echo "    Completed at: {$completion->completed_at}\n";
        echo "    Updated at: {$completion->updated_at}\n\n";
    }
    if ($lessonCompletions->count() > 5) {
        echo '  ... and '.($lessonCompletions->count() - 5)." more\n\n";
    }
} else {
    echo "  ❌ No lesson completions found\n\n";
}

// 4. Check assignment submissions
echo "--- ASSIGNMENT SUBMISSIONS ---\n";
$submissions = Submission::where('user_id', $userId)->get();
echo 'Total submissions: '.$submissions->count()."\n";

if ($submissions->count() > 0) {
    foreach ($submissions->take(5) as $submission) {
        echo "  - Assignment ID: {$submission->assignment_id}\n";
        echo "    Status: {$submission->status}\n";
        echo "    Submitted at: {$submission->submitted_at}\n\n";
    }
    if ($submissions->count() > 5) {
        echo '  ... and '.($submissions->count() - 5)." more\n\n";
    }
} else {
    echo "  ❌ No assignment submissions found\n\n";
}

// 5. Check quiz submissions
echo "--- QUIZ SUBMISSIONS ---\n";
$quizSubmissions = QuizSubmission::where('user_id', $userId)->get();
echo 'Total quiz submissions: '.$quizSubmissions->count()."\n";

if ($quizSubmissions->count() > 0) {
    foreach ($quizSubmissions->take(5) as $quizSub) {
        echo "  - Quiz ID: {$quizSub->quiz_id}\n";
        echo "    Status: {$quizSub->status}\n";
        echo "    Score: {$quizSub->score}\n";
        echo "    Submitted at: {$quizSub->submitted_at}\n\n";
    }
    if ($quizSubmissions->count() > 5) {
        echo '  ... and '.($quizSubmissions->count() - 5)." more\n\n";
    }
} else {
    echo "  ❌ No quiz submissions found\n\n";
}

// 6. Summary
echo "=== SUMMARY ===\n";
echo 'Enrollments: '.$enrollments->count()."\n";
echo 'Lesson Completions: '.$lessonCompletions->count()."\n";
echo 'Assignment Submissions: '.$submissions->count()."\n";
echo 'Quiz Submissions: '.$quizSubmissions->count()."\n\n";

if ($enrollments->count() === 0) {
    echo "⚠️  User has NO enrollments - cannot access any courses\n";
} elseif ($lessonCompletions->count() === 0 && $submissions->count() === 0 && $quizSubmissions->count() === 0) {
    echo "⚠️  User has enrollments but NO activity (no lessons completed, no submissions)\n";
} else {
    echo "✅ User has some learning activity\n";
}

echo "\n=== DONE ===\n";
