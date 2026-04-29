# 🏆 Gamifikasi – Panduan Endpoint

> **Base URL:** `{{url}}/api/v1`
> **Token Asesi:** `{{asesi_token}}` | **Token Admin:** `{{admin_token}}`

---

## Alur Gamifikasi

```
1. Lihat ringkasan XP & level saya         → GET /user/gamification-summary
2. Lihat level saya saat ini               → GET /user/level
3. Lihat statistik XP harian              → GET /user/daily-xp-stats
4. Lihat lencana yang saya miliki         → GET /user/badges
5. Lihat riwayat perolehan poin           → GET /user/points-history
6. Lihat pencapaian / milestone           → GET /user/milestones
7. Lihat peringkat saya                   → GET /user/rank
8. Lihat papan peringkat global           → GET /leaderboards
9. Lihat level per unit (per skema)       → GET /user/levels/:slug
```

---

## 1. [GET] Ringkasan Gamifikasi Saya

**Endpoint:**
```
GET {{url}}/api/v1/user/gamification-summary
```

**Authorization:** Bearer `{{asesi_token}}`

**Query Params (opsional):**

| Key | Value Contoh | Deskripsi |
|-----|-------------|-----------|
| `filter[period]` | `all_time` / `this_month` | Filter periode XP |
| `filter[month]` | `2026-04` | Filter bulan spesifik (format: YYYY-MM) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Ringkasan gamifikasi berhasil diambil.",
  "data": {
    "total_xp": 1250,
    "global_level": 5,
    "current_streak": 3,
    "longest_streak": 7,
    "badges_count": 4,
    "rank": 12,
    "xp_this_period": 320
  },
  "meta": null,
  "errors": null
}
```

---

## 2. [GET] Level Saya Saat Ini

**Endpoint:**
```
GET {{url}}/api/v1/user/level
```

**Authorization:** Bearer `{{asesi_token}}`

**Catatan:** Mengembalikan informasi level pengguna saat ini beserta progres menuju level berikutnya. Middleware `xp.info` otomatis mengkalkulasi data XP terkini.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Informasi level berhasil diambil.",
  "data": {
    "current_level": 5,
    "level_name": "Intermediate",
    "tier": 1,
    "total_xp": 1250,
    "xp_required_for_current": 1000,
    "xp_required_for_next": 1500,
    "xp_in_current_level": 250,
    "xp_needed_for_next": 500,
    "progress_percentage": 50.0,
    "next_level": 6,
    "next_level_name": "Advanced Beginner"
  },
  "meta": null,
  "errors": null
}
```

---

## 3. [GET] Statistik XP Harian

**Endpoint:**
```
GET {{url}}/api/v1/user/daily-xp-stats
```

**Authorization:** Bearer `{{asesi_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `days` | `7` | Jumlah hari ke belakang (max: 30, default: 7) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Statistik XP harian berhasil diambil.",
  "data": [
    { "date": "2026-04-23", "xp": 0 },
    { "date": "2026-04-24", "xp": 75 },
    { "date": "2026-04-25", "xp": 150 },
    { "date": "2026-04-26", "xp": 50 },
    { "date": "2026-04-27", "xp": 200 },
    { "date": "2026-04-28", "xp": 25 },
    { "date": "2026-04-29", "xp": 100 }
  ],
  "meta": null,
  "errors": null
}
```

---

## 4. [GET] Lencana yang Saya Miliki

**Endpoint:**
```
GET {{url}}/api/v1/user/badges
```

**Authorization:** Bearer `{{asesi_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `per_page` | `15` | Jumlah data per halaman (default: 15) |
| `page` | `1` | Nomor halaman |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar lencana berhasil diambil.",
  "data": [
    {
      "id": 3,
      "code": "FIRST_QUIZ",
      "name": "Quiz Master Pemula",
      "description": "Berhasil menyelesaikan kuis pertama.",
      "icon_url": "https://cdn.example.com/badges/first-quiz.png",
      "type": "achievement",
      "awarded_at": "2026-04-15T09:30:00.000000Z"
    },
    {
      "id": 7,
      "code": "STREAK_7",
      "name": "Semangat 7 Hari",
      "description": "Belajar selama 7 hari berturut-turut.",
      "icon_url": "https://cdn.example.com/badges/streak-7.png",
      "type": "streak",
      "awarded_at": "2026-04-22T18:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2
  },
  "errors": null
}
```

---

## 5. [GET] Riwayat Perolehan Poin (XP)

**Endpoint:**
```
GET {{url}}/api/v1/user/points-history
```

**Authorization:** Bearer `{{asesi_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `per_page` | `15` | Jumlah data per halaman (default: 15) |
| `page` | `1` | Nomor halaman |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Riwayat poin berhasil diambil.",
  "data": [
    {
      "id": 441,
      "points": 50,
      "source_type": "quiz_submission",
      "source_type_label": "Pengumpulan Kuis",
      "reason": "quiz_completed",
      "reason_label": "Kuis Diselesaikan",
      "description": "XP dari kuis: Analisis Data Dasar",
      "context": {
        "course": { "id": 12, "title": "Analisis Data untuk Pengambilan Keputusan" }
      },
      "created_at": "2026-04-27T08:28:15.000000Z"
    },
    {
      "id": 440,
      "points": 25,
      "source_type": "lesson",
      "source_type_label": "Pelajaran",
      "reason": "lesson_completed",
      "reason_label": "Pelajaran Selesai",
      "description": "XP dari pelajaran: Pengenalan Analisis Data",
      "context": {
        "lesson": { "id": 55, "title": "Pengenalan Analisis Data" },
        "unit": { "id": 8, "title": "Fundamentals and Core Concepts" },
        "course": { "id": 12, "title": "Analisis Data untuk Pengambilan Keputusan" }
      },
      "created_at": "2026-04-26T14:10:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 38
  },
  "errors": null
}
```

---

## 6. [GET] Pencapaian / Milestone Saya

**Endpoint:**
```
GET {{url}}/api/v1/user/milestones
```

**Authorization:** Bearer `{{asesi_token}}`

**Catatan:** Mengembalikan daftar pencapaian (achievements) yang pernah diraih maupun yang belum tercapai.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pencapaian berhasil diambil.",
  "data": [
    {
      "id": 1,
      "code": "FIRST_LOGIN",
      "name": "Selamat Datang!",
      "description": "Masuk ke platform untuk pertama kalinya.",
      "icon_url": "https://cdn.example.com/badges/welcome.png",
      "is_earned": true,
      "earned_at": "2026-03-10T07:00:00.000000Z",
      "progress": null
    },
    {
      "id": 5,
      "code": "COMPLETE_5_LESSONS",
      "name": "Rajin Belajar",
      "description": "Selesaikan 5 pelajaran.",
      "icon_url": "https://cdn.example.com/badges/learner-5.png",
      "is_earned": true,
      "earned_at": "2026-04-10T10:00:00.000000Z",
      "progress": null
    },
    {
      "id": 9,
      "code": "COMPLETE_10_QUIZZES",
      "name": "Juara Kuis",
      "description": "Selesaikan 10 kuis.",
      "icon_url": "https://cdn.example.com/badges/quiz-10.png",
      "is_earned": false,
      "earned_at": null,
      "progress": { "current": 3, "target": 10, "percentage": 30 }
    }
  ],
  "meta": null,
  "errors": null
}
```

---

## 7. [GET] Peringkat Saya

**Endpoint:**
```
GET {{url}}/api/v1/user/rank
```

**Authorization:** Bearer `{{asesi_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `filter[period]` | `all_time` / `this_month` | Filter periode (default: `all_time`) |
| `filter[month]` | `2026-04` | Filter bulan spesifik (format: YYYY-MM) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Peringkat berhasil diambil.",
  "data": {
    "rank": 12,
    "total_xp": 1250,
    "level": 5,
    "period": "all_time"
  },
  "meta": null,
  "errors": null
}
```

---

## 8. [GET] Papan Peringkat Global (Leaderboard)

**Endpoint:**
```
GET {{url}}/api/v1/leaderboards
```

**Authorization:** Bearer `{{asesi_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman (default: 15) |
| `filter[period]` | `all_time` / `this_month` | Filter periode (default: `all_time`) |
| `filter[month]` | `2026-04` | Filter bulan spesifik (format: YYYY-MM) |
| `search` | `budi` | Cari pengguna berdasarkan nama |

**Catatan:** Respons menyertakan field `my_rank` di dalam `meta` untuk menampilkan posisi pengguna yang sedang login.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Papan peringkat berhasil diambil.",
  "data": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "name": "Budi Santoso",
        "avatar_url": "https://cdn.example.com/avatars/budi.png"
      },
      "total_xp": 4500,
      "level": 18,
      "badges_count": 12
    },
    {
      "rank": 2,
      "user": {
        "id": 9,
        "name": "Sari Dewi",
        "avatar_url": null
      },
      "total_xp": 3800,
      "level": 15,
      "badges_count": 9
    },
    {
      "rank": 3,
      "user": {
        "id": 17,
        "name": "Ahmad Rizky",
        "avatar_url": "https://cdn.example.com/avatars/ahmad.png"
      },
      "total_xp": 3200,
      "level": 13,
      "badges_count": 7
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 68,
    "my_rank": {
      "rank": 12,
      "total_xp": 1250,
      "level": 5
    }
  },
  "errors": null
}
```

---

## 9. [GET] Level Saya per Unit / Skema

**Endpoint:**
```
GET {{url}}/api/v1/user/levels/:course_slug
```

**Authorization:** Bearer `{{asesi_token}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug skema/kursus |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Level unit berhasil diambil.",
  "data": [
    {
      "unit_id": 8,
      "unit_title": "Fundamentals and Core Concepts",
      "unit_level": 3,
      "unit_xp": 150
    },
    {
      "unit_id": 9,
      "unit_title": "Data Visualization",
      "unit_level": 1,
      "unit_xp": 25
    }
  ],
  "meta": null,
  "errors": null
}
```

---

## 10. [GET] Daftar Lencana Tersedia (Admin/Manajemen)

**Endpoint:**
```
GET {{url}}/api/v1/badges
```

**Authorization:** Bearer `{{admin_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `per_page` | `15` | Jumlah data per halaman |
| `filter[type]` | `achievement` | Filter berdasarkan tipe lencana |
| `filter[rarity]` | `common` | Filter berdasarkan rarity |
| `filter[active]` | `1` | Filter lencana aktif/nonaktif |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "code": "FIRST_QUIZ",
      "name": "Quiz Master Pemula",
      "description": "Berhasil menyelesaikan kuis pertama.",
      "type": "achievement",
      "type_label": "Pencapaian",
      "rarity": "common",
      "rarity_label": "Umum",
      "xp_reward": 50,
      "active": true,
      "threshold": 1,
      "is_repeatable": false,
      "max_awards_per_user": 1,
      "icon_url": "https://cdn.example.com/badges/first-quiz.png",
      "icon_thumb_url": "https://cdn.example.com/badges/first-quiz-thumb.png",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 20
  },
  "errors": null
}
```

---

## 11. [GET] Detail Lencana

**Endpoint:**
```
GET {{url}}/api/v1/badges/:badge_id
```

**Authorization:** Bearer `{{admin_token}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `badge_id` | `1` | ID lencana |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1,
    "code": "FIRST_QUIZ",
    "name": "Quiz Master Pemula",
    "description": "Berhasil menyelesaikan kuis pertama.",
    "type": "achievement",
    "type_label": "Pencapaian",
    "rarity": "common",
    "rarity_label": "Umum",
    "xp_reward": 50,
    "active": true,
    "threshold": 1,
    "is_repeatable": false,
    "max_awards_per_user": 1,
    "icon_url": "https://cdn.example.com/badges/first-quiz.png",
    "icon_thumb_url": "https://cdn.example.com/badges/first-quiz-thumb.png",
    "rules": [
      {
        "id": 3,
        "event_trigger": "quiz.completed",
        "conditions": { "min_score": 0 },
        "priority": 1,
        "cooldown_seconds": 0,
        "rule_enabled": true
      }
    ],
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-01-01T00:00:00.000000Z"
  },
  "meta": null,
  "errors": null
}
```

---

## 12. [GET] Lencana Tersedia untuk Asesi (Badge yang Bisa Diraih)

**Endpoint:**
```
GET {{url}}/api/v1/badges/available
```

**Authorization:** Bearer `{{asesi_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `per_page` | `15` | Jumlah data per halaman (max: 100) |

**Catatan:** Mengembalikan semua lencana aktif beserta informasi apakah asesi sudah mendapatkannya (`is_earned`) dan progres yang sudah dicapai (`progress`).

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar lencana berhasil diambil.",
  "data": [
    {
      "id": 1,
      "code": "FIRST_QUIZ",
      "name": "Quiz Master Pemula",
      "description": "Berhasil menyelesaikan kuis pertama.",
      "type": "achievement",
      "type_label": "Pencapaian",
      "rarity": "common",
      "rarity_label": "Umum",
      "xp_reward": 50,
      "active": true,
      "threshold": 1,
      "icon_url": "https://cdn.example.com/badges/first-quiz.png",
      "is_earned": true,
      "earned_at": "2026-04-15T09:30:00.000000Z",
      "progress": null
    },
    {
      "id": 5,
      "code": "QUIZ_10",
      "name": "Juara Kuis",
      "description": "Selesaikan 10 kuis.",
      "type": "achievement",
      "type_label": "Pencapaian",
      "rarity": "rare",
      "rarity_label": "Langka",
      "xp_reward": 200,
      "active": true,
      "threshold": 10,
      "icon_url": "https://cdn.example.com/badges/quiz-10.png",
      "is_earned": false,
      "earned_at": null,
      "progress": { "current": 3, "target": 10, "percentage": 30 }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 20
  },
  "errors": null
}
```

---

## 13. [GET] Daftar Semua Level (Admin)

**Endpoint:**
```
GET {{url}}/api/v1/levels
```

**Authorization:** Bearer `{{admin_token}}`

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `per_page` | `20` | Jumlah data per halaman (max: 100) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar level berhasil diambil.",
  "data": [
    {
      "id": 1,
      "level": 1,
      "name": "Newbie",
      "tier": 1,
      "base_tier_name": "Bronze",
      "xp_required": 0,
      "bonus_xp": 0,
      "milestone_badge_id": null
    },
    {
      "id": 2,
      "level": 2,
      "name": "Beginner",
      "tier": 1,
      "base_tier_name": "Bronze",
      "xp_required": 100,
      "bonus_xp": 10,
      "milestone_badge_id": null
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  },
  "errors": null
}
```

---

## 14. [GET] Semua Level Dikelompokkan per Tier (Admin)

**Endpoint:**
```
GET {{url}}/api/v1/levels/tiers
```

**Authorization:** Bearer `{{admin_token}}`

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar level berhasil diambil.",
  "data": {
    "1": {
      "tier": 1,
      "base_tier_name": "Bronze",
      "levels": [
        { "level": 1, "name": "Newbie", "xp_required": 0 },
        { "level": 2, "name": "Beginner", "xp_required": 100 }
      ]
    },
    "2": {
      "tier": 2,
      "base_tier_name": "Silver",
      "levels": [
        { "level": 11, "name": "Silver I", "xp_required": 2000 },
        { "level": 12, "name": "Silver II", "xp_required": 2500 }
      ]
    }
  },
  "meta": null,
  "errors": null
}
```

---

## 15. [GET] Log Gamifikasi Pengguna (Admin)

**Endpoint:**
```
GET {{url}}/api/v1/leaderboards/:user_id/gamification-log
```

**Authorization:** Bearer `{{admin_token}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `25` | ID pengguna yang akan dilihat log-nya |

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `per_page` | `15` | Jumlah data per halaman |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Riwayat poin berhasil diambil.",
  "data": [
    {
      "id": 441,
      "points": 50,
      "source_type": "quiz_submission",
      "source_type_label": "Pengumpulan Kuis",
      "reason": "quiz_completed",
      "reason_label": "Kuis Diselesaikan",
      "description": "XP dari kuis",
      "context": {
        "course": { "id": 12, "title": "Analisis Data untuk Pengambilan Keputusan" }
      },
      "created_at": "2026-04-27T08:28:15.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 38,
    "summary": {
      "total_xp": 1250,
      "total_entries": 38
    }
  },
  "errors": null
}
```

---

## 16. [GET] Export Log Gamifikasi (Admin)

**Endpoint:**
```
GET {{url}}/api/v1/leaderboards/:user_id/gamification-log/export
```

**Authorization:** Bearer `{{admin_token}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `25` | ID pengguna |

**Query Params:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `type` | `csv` / `excel` | Format file export (default: `csv`) |

**Catatan:** Respons berupa file download (bukan JSON). Gunakan fitur "Send and Download" di Postman.

---

## Referensi Nilai Enum

### Tipe Lencana (`type`)
| Value | Label |
|-------|-------|
| `achievement` | Pencapaian |
| `streak` | Streak Belajar |
| `milestone` | Milestone |
| `special` | Spesial |

### Rarity Lencana (`rarity`)
| Value | Label |
|-------|-------|
| `common` | Umum |
| `rare` | Langka |
| `epic` | Epik |
| `legendary` | Legendaris |

### Tipe Sumber Poin (`source_type`)
| Value | Keterangan |
|-------|------------|
| `lesson` | XP dari pelajaran |
| `unit` | XP dari unit selesai |
| `course` | XP dari skema selesai |
| `quiz_submission` | XP dari kuis |
| `assignment` | XP dari tugas |
| `badge` | XP dari lencana |
| `forum` | XP dari aktivitas forum |
| `streak` | XP dari streak harian |

---

## Ringkasan Semua Endpoint

| # | Method | Endpoint | Role | Deskripsi |
|---|--------|----------|------|-----------|
| 1 | `GET` | `/user/gamification-summary` | Asesi | Ringkasan XP & level saya |
| 2 | `GET` | `/user/level` | Asesi | Level saya saat ini |
| 3 | `GET` | `/user/daily-xp-stats` | Asesi | Statistik XP harian |
| 4 | `GET` | `/user/badges` | Asesi | Lencana yang saya miliki |
| 5 | `GET` | `/user/points-history` | Asesi | Riwayat perolehan XP |
| 6 | `GET` | `/user/milestones` | Asesi | Pencapaian / milestone |
| 7 | `GET` | `/user/rank` | Asesi | Peringkat saya |
| 8 | `GET` | `/leaderboards` | Shared | Papan peringkat global |
| 9 | `GET` | `/user/levels/:slug` | Asesi | Level per unit/skema |
| 10 | `GET` | `/badges` | Admin | Daftar semua lencana |
| 11 | `GET` | `/badges/:id` | Admin | Detail lencana |
| 12 | `GET` | `/badges/available` | Asesi | Lencana tersedia + status diraih |
| 13 | `GET` | `/levels` | Admin | Daftar semua level |
| 14 | `GET` | `/levels/tiers` | Admin | Level dikelompokkan per tier |
| 15 | `GET` | `/leaderboards/:user_id/gamification-log` | Admin | Log XP pengguna |
| 16 | `GET` | `/leaderboards/:user_id/gamification-log/export` | Admin | Export log XP |
