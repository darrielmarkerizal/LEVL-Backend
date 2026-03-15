<?php

return [
    'quiz_question_type' => [
        'multiple_choice' => 'Pilihan Ganda',
        'checkbox' => 'Kotak Centang',
        'true_false' => 'Benar/Salah',
        'essay' => 'Esai',
    ],

    'quiz_submission_status' => [
        'draft' => 'Draft',
        'submitted' => 'Dikumpulkan',
        'graded' => 'Dinilai',
    ],

    'quiz_grading_status' => [
        'pending' => 'Menunggu',
        'waiting_for_grading' => 'Menunggu Penilaian',
        'partially_graded' => 'Dinilai Sebagian',
        'graded' => 'Dinilai',
    ],

    'quiz_status' => [
        'draft' => 'Draft',
        'published' => 'Dipublikasikan',
        'archived' => 'Diarsipkan',
    ],

    'assignment_status' => [
        'draft' => 'Draft',
        'published' => 'Dipublikasikan',
        'archived' => 'Diarsipkan',
    ],

    'assignment_type' => [
        'assignment' => 'Tugas',
        'quiz' => 'Kuis',
    ],

    'submission_type' => [
        'file' => 'File',
        'text' => 'Teks',
        'both' => 'File dan Teks',
    ],

    'submission_status' => [
        'draft' => 'Draft',
        'submitted' => 'Dikumpulkan',
        'graded' => 'Dinilai',
        'late' => 'Terlambat',
        'missing' => 'Tidak Dikumpulkan',
    ],

    'review_mode' => [
        'immediate' => 'Segera',
        'after_due_date' => 'Setelah Batas Waktu',
        'after_graded' => 'Setelah Dinilai',
        'never' => 'Tidak Pernah',
    ],

    'randomization_type' => [
        'static' => 'Statis',
        'random' => 'Acak',
        'random_from_bank' => 'Acak dari Bank Soal',
    ],

    'point_reason' => [
        'lesson_completed' => 'Menyelesaikan Pelajaran',
        'assignment_submitted' => 'Mengumpulkan Tugas',
        'quiz_completed' => 'Menyelesaikan Kuis',
        'quiz_passed' => 'Lulus Kuis',
        'perfect_score' => 'Nilai Sempurna',
        'first_attempt' => 'Percobaan Pertama',
        'first_submission' => 'Pengumpulan Pertama',
        'daily_streak' => 'Streak Harian',
        'forum_post' => 'Posting Forum',
        'forum_reply' => 'Balasan Forum',
        'reaction_received' => 'Menerima Reaksi',
        'engagement' => 'Engagement',
        'bonus' => 'Bonus',
        'penalty' => 'Penalti',
        'completion' => 'Penyelesaian',
        'score' => 'Skor',
    ],

    'point_source_type' => [
        'lesson' => 'Pelajaran',
        'assignment' => 'Tugas',
        'attempt' => 'Percobaan',
        'challenge' => 'Tantangan',
        'system' => 'Sistem',
        'grade' => 'Nilai',
    ],

    'badge_type' => [
        'completion' => 'Penyelesaian',
        'quality' => 'Kualitas',
        'speed' => 'Kecepatan',
        'habit' => 'Kebiasaan',
        'social' => 'Sosial',
        'milestone' => 'Pencapaian',
        'hidden' => 'Tersembunyi',
    ],
];
