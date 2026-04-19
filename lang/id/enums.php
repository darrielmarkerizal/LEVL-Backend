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
    ],

    'submission_state' => [
        'in_progress' => 'Sedang Dikerjakan',
        'auto_graded' => 'Dinilai Otomatis',
        'pending_manual_grading' => 'Menunggu Penilaian Manual',
        'graded' => 'Dinilai',
        'released' => 'Dirilis',
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

    'badge_rarity' => [
        'common' => 'Umum',
        'uncommon' => 'Tidak Umum',
        'rare' => 'Langka',
        'epic' => 'Epik',
        'legendary' => 'Legendaris',
    ],

    'post_category' => [
        'announcement' => 'Pengumuman',
        'information' => 'Informasi',
        'warning' => 'Peringatan',
        'system' => 'Sistem',
        'award' => 'Penghargaan',
        'gamification' => 'Gamifikasi',
    ],

    'post_status' => [
        'draft' => 'Draft',
        'scheduled' => 'Terjadwal',
        'published' => 'Dipublikasikan',
    ],

    'post_audience_role' => [
        'student' => 'Siswa',
        'instructor' => 'Instruktur',
        'admin' => 'Admin',
    ],

    'user_status' => [
        'pending' => 'Menunggu',
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
        'banned' => 'Diblokir',
    ],

    'roles' => [
        'superadmin' => 'Super Admin',
        'admin' => 'Admin',
        'instructor' => 'Instruktur',
        'student' => 'Siswa',
    ],

    'course_status' => [
        'draft' => 'Draft',
        'published' => 'Dipublikasikan',
        'archived' => 'Diarsipkan',
    ],

    'course_types' => [
        'self_paced' => 'Mandiri',
        'instructor_led' => 'Dipandu Instruktur',
        'blended' => 'Campuran',
    ],

    'course_type' => [
        'okupasi' => 'Okupasi',
        'kluster' => 'Kluster',
    ],

    'enrollment_types' => [
        'open' => 'Terbuka',
        'approval' => 'Perlu Persetujuan',
        'key' => 'Kunci Pendaftaran',
        'invitation' => 'Hanya Undangan',
    ],

    'enrollment_type' => [
        'auto_accept' => 'Otomatis Diterima',
        'key_based' => 'Berbasis Kunci',
        'approval' => 'Perlu Persetujuan',
    ],

    'level_tags' => [
        'beginner' => 'Pemula',
        'intermediate' => 'Menengah',
        'advanced' => 'Lanjutan',
        'expert' => 'Ahli',
    ],

    'level_tag' => [
        'dasar' => 'Dasar',
        'menengah' => 'Menengah',
        'mahir' => 'Mahir',
    ],

    'content_types' => [
        'text' => 'Teks',
        'video' => 'Video',
        'audio' => 'Audio',
        'document' => 'Dokumen',
        'interactive' => 'Interaktif',
        'quiz' => 'Kuis',
        'assignment' => 'Tugas',
    ],

    'block_type' => [
        'text' => 'Teks',
        'image' => 'Gambar',
        'video' => 'Video Upload',
        'file' => 'File',
        'link' => 'Link Eksternal',
        'youtube' => 'YouTube',
        'drive' => 'Google Drive',
        'embed' => 'Embed',
    ],

    'enrollment_status' => [
        'pending' => 'Menunggu',
        'active' => 'Aktif',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ],

    'progress_status' => [
        'not_started' => 'Belum Dimulai',
        'in_progress' => 'Sedang Berjalan',
        'completed' => 'Selesai',
    ],

    'content_status' => [
        'draft' => 'Draft',
        'published' => 'Dipublikasikan',
        'archived' => 'Diarsipkan',
    ],

    'priorities' => [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ],

    'target_types' => [
        'all' => 'Semua Pengguna',
        'role' => 'Berdasarkan Peran',
        'user' => 'Pengguna Tertentu',
        'course' => 'Peserta Kursus',
    ],

    'notification_types' => [
        'system' => 'Sistem',
        'course' => 'Kursus',
        'assignment' => 'Tugas',
        'grade' => 'Nilai',
        'forum' => 'Forum',
        'achievement' => 'Pencapaian',
    ],

    'notification_channels' => [
        'database' => 'Dalam Aplikasi',
        'mail' => 'Email',
        'push' => 'Notifikasi Push',
    ],

    'notification_frequencies' => [
        'instant' => 'Instan',
        'daily' => 'Ringkasan Harian',
        'weekly' => 'Ringkasan Mingguan',
        'never' => 'Tidak Pernah',
    ],

    'grade_status' => [
        'pending' => 'Menunggu',
        'graded' => 'Dinilai',
        'returned' => 'Dikembalikan',
    ],

    'grade_source_types' => [
        'assignment' => 'Tugas',
        'quiz' => 'Kuis',
        'manual' => 'Manual',
    ],

    'category_status' => [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
    ],

    'setting_types' => [
        'string' => 'Teks',
        'integer' => 'Angka',
        'boolean' => 'Ya/Tidak',
        'json' => 'JSON',
    ],
];
