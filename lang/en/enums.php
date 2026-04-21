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
        'released' => 'Released',
    ],

    'quiz_grading_status' => [
        'pending' => 'Pending',
        'waiting_for_grading' => 'Waiting for Grading',
        'partially_graded' => 'Partially Graded',
        'graded' => 'Graded',
        'released' => 'Released',
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
        'mixed' => 'File and Text',
        'both' => 'File and Text',
    ],

    'submission_status' => [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'graded' => 'Graded',
        'late' => 'Late',
    ],

    'submission_state' => [
        'in_progress' => 'In Progress',
        'auto_graded' => 'Auto Graded',
        'pending_manual_grading' => 'Pending Manual Grading',
        'graded' => 'Graded',
        'released' => 'Released',
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

    'badge_rarity' => [
        'common' => 'Common',
        'uncommon' => 'Uncommon',
        'rare' => 'Rare',
        'epic' => 'Epic',
        'legendary' => 'Legendary',
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

    'user_status' => [
        'pending' => 'Pending',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'banned' => 'Banned',
    ],

    'roles' => [
        'superadmin' => 'Super Admin',
        'admin' => 'Admin',
        'instructor' => 'Instructor',
        'student' => 'Student',
    ],

    'course_status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'course_types' => [
        'self_paced' => 'Self Paced',
        'instructor_led' => 'Instructor Led',
        'blended' => 'Blended',
    ],

    'course_type' => [
        'okupasi' => 'Occupation',
        'kluster' => 'Cluster',
    ],

    'enrollment_types' => [
        'open' => 'Open',
        'approval' => 'Approval Required',
        'key' => 'Enrollment Key',
        'invitation' => 'Invitation Only',
    ],

    'enrollment_type' => [
        'auto_accept' => 'Auto Accept',
        'key_based' => 'Key Based',
        'approval' => 'Approval Required',
    ],

    'level_tags' => [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'expert' => 'Expert',
    ],

    'level_tag' => [
        'dasar' => 'Basic',
        'menengah' => 'Intermediate',
        'mahir' => 'Advanced',
    ],

    'content_types' => [
        'text' => 'Text',
        'video' => 'Video',
        'audio' => 'Audio',
        'document' => 'Document',
        'interactive' => 'Interactive',
        'quiz' => 'Quiz',
        'assignment' => 'Assignment',
    ],

    'block_type' => [
        'text' => 'Text',
        'image' => 'Image',
        'video' => 'Video Upload',
        'file' => 'File',
        'link' => 'External Link',
        'youtube' => 'YouTube',
        'drive' => 'Google Drive',
        'embed' => 'Embed',
    ],

    'enrollment_status' => [
        'pending' => 'Pending',
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'progress_status' => [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ],

    'content_status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'target_types' => [
        'all' => 'All Users',
        'role' => 'By Role',
        'user' => 'Specific Users',
        'course' => 'Course Participants',
    ],

    'notification_types' => [
        'system' => 'System',
        'course' => 'Course',
        'assignment' => 'Assignment',
        'grade' => 'Grade',
        'forum' => 'Forum',
        'achievement' => 'Achievement',
    ],

    'notification_channels' => [
        'database' => 'In-App',
        'mail' => 'Email',
        'push' => 'Push Notification',
    ],

    'notification_frequencies' => [
        'instant' => 'Instant',
        'daily' => 'Daily Digest',
        'weekly' => 'Weekly Digest',
        'never' => 'Never',
    ],

    'grade_status' => [
        'pending' => 'Pending',
        'graded' => 'Graded',
        'returned' => 'Returned',
    ],

    'grade_source_types' => [
        'assignment' => 'Assignment',
        'quiz' => 'Quiz',
        'manual' => 'Manual',
    ],

    'category_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'setting_types' => [
        'string' => 'Text',
        'integer' => 'Number',
        'boolean' => 'Yes/No',
        'json' => 'JSON',
    ],
];
