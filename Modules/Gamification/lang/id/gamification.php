<?php

return [
    // Badge messages
    'badge_earned' => 'Lencana Didapat!',
    'badge_earned_description' => 'Anda mendapatkan lencana :name',
    'badge_earned_title' => 'Lencana Baru: :badge',
    'badge_earned_message' => 'Selamat! Anda mendapatkan lencana ":badge" dan memperoleh :xp XP.',
    'badge_created' => 'Lencana berhasil dibuat',
    'badge_updated' => 'Lencana berhasil diperbarui',
    'badge_deleted' => 'Lencana berhasil dihapus',
    'badge_not_found' => 'Lencana tidak ditemukan',

    // Event counter messages
    'counter_incremented' => 'Penghitung berhasil ditambah',
    'counter_reset' => 'Penghitung berhasil direset',
    'counters_cleaned' => ':count penghitung kadaluarsa berhasil dibersihkan',

    // Event log messages
    'event_logged' => 'Event berhasil dicatat',
    'logs_cleaned' => ':count log event lama berhasil dibersihkan',

    // Badge version messages
    'version_created' => 'Versi lencana berhasil dibuat',
    'initial_versions_created' => ':count versi awal lencana berhasil dibuat',

    // Cache messages
    'cache_warming' => 'Memanaskan cache aturan lencana...',
    'cached_event' => 'Cache aturan untuk event: :event',
    'cache_warmed' => 'Berhasil meng-cache :count tipe event',
    'cache_cleared' => 'Cache berhasil dibersihkan',

    // Cleanup messages
    'cleaning_logs' => 'Membersihkan log event lebih lama dari :days hari...',
    'cleaning_counters' => 'Membersihkan penghitung event yang kadaluarsa...',
    'creating_versions' => 'Membuat versi awal lencana...',

    // Validation messages
    'invalid_window' => 'Tipe window tidak valid. Harus: daily, weekly, monthly, atau lifetime',
    'invalid_event_type' => 'Tipe event tidak valid',
    'threshold_required' => 'Threshold diperlukan untuk lencana ini',

    // Success messages
    'operation_successful' => 'Operasi berhasil diselesaikan',
    'data_saved' => 'Data berhasil disimpan',

    // Error messages
    'operation_failed' => 'Operasi gagal',
    'data_not_saved' => 'Gagal menyimpan data',
    'insufficient_progress' => 'Progress tidak cukup untuk mendapatkan lencana ini',

    // Metrics messages
    'metrics_collected' => 'Metrik berhasil dikumpulkan',
    'badge_evaluations_total' => 'Total Evaluasi Lencana',
    'badge_awarded_total' => 'Total Lencana Diberikan',
    'badge_awarded_last_hour' => 'Lencana Diberikan (1 Jam Terakhir)',
    'counter_increment_total' => 'Total Penambahan Penghitung',
    'active_counters' => 'Penghitung Aktif',
    'event_logs_total' => 'Total Log Event',
    'event_logs_last_hour' => 'Log Event (1 Jam Terakhir)',
    'rule_eval_duration_ms' => 'Durasi Evaluasi Aturan (ms)',
    'cache_hit_rate' => 'Tingkat Hit Cache',
    'cooldowns_active' => 'Cooldown Aktif',
    'badge_versions_active' => 'Versi Lencana Aktif',

    // Badge control messages
    'badge_repeatable' => 'Lencana ini dapat diperoleh berkali-kali',
    'badge_non_repeatable' => 'Lencana ini hanya dapat diperoleh sekali',
    'max_awards_reached' => 'Batas maksimum perolehan lencana ini telah tercapai',
    'rule_disabled' => 'Aturan ini saat ini dinonaktifkan',
    'rule_enabled' => 'Aturan ini saat ini diaktifkan',

    // Forum activity messages
    'thread_created_xp' => 'Membuat thread diskusi baru',
    'reply_created_xp' => 'Membalas diskusi',
    'reaction_received_xp' => 'Menerima reaksi pada postingan Anda',
];
