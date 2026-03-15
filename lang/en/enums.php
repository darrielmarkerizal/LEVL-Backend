<?php

return [
    'quiz_question_type' => [
        'multiple_choice' => 'Multiple Choice',
        'checkbox' => 'Checkbox',
        'true_false' => 'True/False',
        'essay' => 'Essay',
    ],

    'quiz_submission_status' => [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'graded' => 'Graded',
    ],

    'quiz_grading_status' => [
        'pending' => 'Pending',
        'waiting_for_grading' => 'Waiting for Grading',
        'partially_graded' => 'Partially Graded',
        'graded' => 'Graded',
    ],

    'quiz_status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'assignment_status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'assignment_type' => [
        'assignment' => 'Assignment',
        'quiz' => 'Quiz',
    ],

    'submission_type' => [
        'file' => 'File',
        'text' => 'Text',
        'both' => 'File and Text',
    ],

    'submission_status' => [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'graded' => 'Graded',
        'late' => 'Late',
        'missing' => 'Missing',
    ],

    'review_mode' => [
        'immediate' => 'Immediate',
        'after_due_date' => 'After Due Date',
        'after_graded' => 'After Graded',
        'never' => 'Never',
    ],

    'randomization_type' => [
        'static' => 'Static',
        'random' => 'Random',
        'random_from_bank' => 'Random from Bank',
    ],

    'point_reason' => [
        'lesson_completed' => 'Lesson Completed',
        'assignment_submitted' => 'Assignment Submitted',
        'quiz_completed' => 'Quiz Completed',
        'quiz_passed' => 'Quiz Passed',
        'perfect_score' => 'Perfect Score',
        'first_attempt' => 'First Attempt',
        'first_submission' => 'First Submission',
        'daily_streak' => 'Daily Streak',
        'forum_post' => 'Forum Post',
        'forum_reply' => 'Forum Reply',
        'reaction_received' => 'Reaction Received',
        'engagement' => 'Engagement',
        'bonus' => 'Bonus',
        'penalty' => 'Penalty',
        'completion' => 'Completion',
        'score' => 'Score',
    ],

    'point_source_type' => [
        'lesson' => 'Lesson',
        'assignment' => 'Assignment',
        'attempt' => 'Attempt',
        'challenge' => 'Challenge',
        'system' => 'System',
        'grade' => 'Grade',
    ],

    'badge_type' => [
        'completion' => 'Completion',
        'quality' => 'Quality',
        'speed' => 'Speed',
        'habit' => 'Habit',
        'social' => 'Social',
        'milestone' => 'Milestone',
        'hidden' => 'Hidden',
    ],

    'post_category' => [
        'announcement' => 'Announcement',
        'information' => 'Information',
        'warning' => 'Warning',
        'system' => 'System',
        'award' => 'Award',
        'gamification' => 'Gamification',
    ],

    'post_status' => [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'published' => 'Published',
    ],

    'post_audience_role' => [
        'student' => 'Student',
        'instructor' => 'Instructor',
        'admin' => 'Admin',
    ],
];
