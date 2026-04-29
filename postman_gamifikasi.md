# 🎮 Gamifikasi – Panduan Endpoint

> **Base URL:** `{{url}}/api/v1`

---

## Tentang Token per Role

| Folder | Token yang Digunakan |
|--------|----------------------|
| Admin (CRUD Badge & Level) | `{{access_token_admin}}` |
| Semua user (Lihat badge, leaderboard, dll) | `{{access_token_student}}` / `{{access_token_admin}}` / `{{access_token_instructor}}` |
| Asesi (data gamifikasi diri sendiri) | `{{access_token_student}}` |

---

## Alur Gamifikasi

```
=== ADMIN — MANAJEMEN BADGE ===
1. Lihat daftar semua badge                     → GET    /badges
2. Lihat detail badge spesifik                  → GET    /badges/:badge_id
3. Buat badge baru                              → POST   /badges
4. Perbarui badge                               → PUT    /badges/:badge_id
5. Hapus badge                                  → DELETE /badges/:badge_id

=== ADMIN — MANAJEMEN LEVEL ===
6. Lihat daftar semua level (paginasi)          → GET  /levels
7. Lihat semua level dikelompokkan per tier     → GET  /levels/tiers
8. Lihat level dalam tier tertentu              → GET  /levels/tiers/:tier
9. Lihat tabel progresi level (range)           → GET  /levels/progression
10. Hitung level berdasarkan XP                 → POST /levels/calculate

=== SHARED (Semua User) ===
11. Lihat papan peringkat (Leaderboard)         → GET /leaderboards
12. Cek peringkat saya saat ini                 → GET /user/rank

=== ASESI — DATA GAMIFIKASI DIRI SENDIRI ===
13. Lihat ringkasan gamifikasi saya             → GET /user/gamification-summary
14. Lihat badge yang saya miliki                → GET /user/badges
15. Lihat badge yang tersedia untuk diraih      → GET /badges/available
16. Lihat riwayat poin/XP saya                 → GET /user/points-history
17. Lihat milestones / pencapaian saya          → GET /user/milestones
18. Lihat info level saya saat ini              → GET /user/level
19. Lihat statistik XP harian saya             → GET /user/daily-xp-stats
20. Lihat progress gamifikasi per unit kursus  → GET /user/levels/:course_slug
```

---

## ── ADMIN — MANAJEMEN BADGE ──

---

## 1. [GET] Daftar Semua Badge

**Endpoint:**
```
GET {{url}}/api/v1/badges
```

**Authorization:** Bearer `{{access_token_admin}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman |
| `filter[type]` | `achievement` | Filter berdasarkan tipe badge |
| `filter[rarity]` | `rare` | Filter berdasarkan rarity badge |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "name": "Quiz Master",
      "description": "Diberikan kepada pengguna yang menyelesaikan 10 kuis dengan nilai ≥ 80.",
      "icon_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/badges/quiz-master.png",
      "type": "achievement",
      "rarity": "rare",
      "xp_reward": 100,
      "created_at": "2026-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Fast Learner",
      "description": "Diberikan kepada pengguna yang menyelesaikan kursus pertama.",
      "icon_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/badges/fast-learner.png",
      "type": "achievement",
      "rarity": "common",
      "xp_reward": 50,
      "created_at": "2026-01-01T00:00:00.000000Z"
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

## 2. [GET] Detail Badge Spesifik

**Endpoint:**
```
GET {{url}}/api/v1/badges/:badge_id
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `badge_id` | `1` | ID badge |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1,
    "name": "Quiz Master",
    "description": "Diberikan kepada pengguna yang menyelesaikan 10 kuis dengan nilai ≥ 80.",
    "icon_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/badges/quiz-master.png",
    "type": "achievement",
    "rarity": "rare",
    "xp_reward": 100,
    "rules": [
      {
        "id": 1,
        "event_type": "quiz_completed",
        "threshold": 10,
        "condition": "score_gte:80"
      }
    ]
  },
  "meta": null,
  "errors": null
}
```

---

## 3. [POST] Buat Badge Baru

**Endpoint:**
```
POST {{url}}/api/v1/badges
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (multipart/form-data):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `name` | string | ✅ | Nama badge |
| `description` | string | ❌ | Deskripsi badge |
| `type` | string | ✅ | Tipe badge |
| `rarity` | string | ✅ | Rarity: `common`, `rare`, `epic`, `legendary` |
| `xp_reward` | integer | ❌ | Jumlah XP yang diberikan saat badge diraih |
| `icon` | file | ❌ | File gambar ikon badge |

**Contoh Respons (201 Created):**
```json
{
  "success": true,
  "message": "Badge berhasil dibuat.",
  "data": {
    "id": 10,
    "name": "First Assignment",
    "description": "Menyelesaikan tugas pertama.",
    "icon_url": null,
    "type": "achievement",
    "rarity": "common",
    "xp_reward": 30
  },
  "meta": null,
  "errors": null
}
```

---

## 4. [PUT] Perbarui Badge

**Endpoint:**
```
PUT {{url}}/api/v1/badges/:badge_id
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `badge_id` | `10` | ID badge yang ingin diperbarui |

**Body (multipart/form-data):** sama dengan endpoint Buat Badge

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Badge berhasil diperbarui.",
  "data": {
    "id": 10,
    "name": "First Assignment Updated",
    "xp_reward": 50
  },
  "meta": null,
  "errors": null
}
```

---

## 5. [DELETE] Hapus Badge

**Endpoint:**
```
DELETE {{url}}/api/v1/badges/:badge_id
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `badge_id` | `10` | ID badge yang ingin dihapus |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Badge berhasil dihapus.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## ── ADMIN — MANAJEMEN LEVEL ──

---

## 6. [GET] Daftar Semua Level (Paginasi)

**Endpoint:**
```
GET {{url}}/api/v1/levels
```

**Authorization:** Bearer `{{access_token_admin}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `per_page` | `20` | Jumlah data per halaman (maks 100) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "level": 1,
      "tier": 1,
      "name": "Pemula I",
      "xp_required": 0
    },
    {
      "id": 2,
      "level": 2,
      "tier": 1,
      "name": "Pemula II",
      "xp_required": 200
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

## 7. [GET] Semua Level Dikelompokkan per Tier

**Endpoint:**
```
GET {{url}}/api/v1/levels/tiers
```

**Authorization:** Bearer `{{access_token_student}}`

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "tier_1": {
      "name": "Pemula",
      "levels": [
        { "level": 1, "xp_required": 0 },
        { "level": 2, "xp_required": 200 }
      ]
    },
    "tier_2": {
      "name": "Pelajar",
      "levels": [
        { "level": 11, "xp_required": 2000 },
        { "level": 12, "xp_required": 2500 }
      ]
    }
  },
  "meta": null,
  "errors": null
}
```

---

## 8. [GET] Level dalam Tier Tertentu

**Endpoint:**
```
GET {{url}}/api/v1/levels/tiers/:tier
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `tier` | `1` | Nomor tier (1–10) |

---

## 9. [GET] Tabel Progresi Level

**Endpoint:**
```
GET {{url}}/api/v1/levels/progression
```

**Authorization:** Bearer `{{access_token_student}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `start` | `1` | Level awal (default 1, min 1) |
| `end` | `20` | Level akhir (default 20, maks 100) |

---

## 10. [POST] Hitung Level Berdasarkan XP

**Endpoint:**
```
POST {{url}}/api/v1/levels/calculate
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
```json
{
  "xp": 1500
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `xp` | integer | ✅ | Jumlah XP yang ingin dihitung levelnya |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "current_level": 7,
    "tier": 1,
    "level_name": "Pemula VII",
    "xp_at_level_start": 1400,
    "xp_to_next_level": 200,
    "progress_percentage": 50
  },
  "meta": null,
  "errors": null
}
```

---

## ── SHARED (Semua User) ──

---

## 11. [GET] Papan Peringkat (Leaderboard)

**Endpoint:**
```
GET {{url}}/api/v1/leaderboards
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan peringkat pengguna berdasarkan total XP. Respons juga menyertakan peringkat user yang sedang login (`my_rank`) di dalam `meta`.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman |
| `search` | `budi` | Cari pengguna berdasarkan nama |
| `filter[period]` | `all_time` | Filter periode: `all_time`, `monthly` |
| `filter[month]` | `2026-04` | Filter bulan spesifik (format: YYYY-MM), hanya berlaku jika `period=monthly` |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data papan peringkat berhasil diambil.",
  "data": [
    {
      "rank": 1,
      "user_id": 101,
      "name": "Budi Santoso",
      "avatar_url": null,
      "total_xp": 2500,
      "current_level": 12,
      "tier": 2
    },
    {
      "rank": 2,
      "user_id": 102,
      "name": "Sari Dewi",
      "avatar_url": null,
      "total_xp": 2060,
      "current_level": 11,
      "tier": 2
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "my_rank": {
      "rank": 5,
      "total_xp": 1200,
      "current_level": 7
    }
  },
  "errors": null
}
```

---

## 12. [GET] Peringkat Saya Saat Ini

**Endpoint:**
```
GET {{url}}/api/v1/user/rank
```

**Authorization:** Bearer `{{access_token_student}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `filter[period]` | `all_time` | Filter periode: `all_time`, `monthly` |
| `filter[month]` | `2026-04` | Filter bulan spesifik (format: YYYY-MM) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data peringkat berhasil diambil.",
  "data": {
    "rank": 5,
    "total_xp": 1200,
    "current_level": 7,
    "tier": 1,
    "period": "all_time"
  },
  "meta": null,
  "errors": null
}
```

---

## ── ASESI — DATA GAMIFIKASI DIRI SENDIRI ──

---

## 13. [GET] Ringkasan Gamifikasi Saya

**Endpoint:**
```
GET {{url}}/api/v1/user/gamification-summary
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan ringkasan lengkap data gamifikasi pengguna: XP, level, badge, milestone, dll.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `filter[period]` | `all_time` | Filter periode: `all_time`, `monthly` |
| `filter[month]` | `2026-04` | Filter bulan spesifik (format: YYYY-MM) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Ringkasan gamifikasi berhasil diambil.",
  "data": {
    "total_xp": 1200,
    "current_level": 7,
    "tier": 1,
    "level_name": "Pemula VII",
    "xp_to_next_level": 200,
    "progress_percentage": 50,
    "total_badges": 2,
    "total_milestones": 3,
    "rank": 5,
    "period": "all_time"
  },
  "meta": null,
  "errors": null
}
```

---

## 14. [GET] Daftar Badge yang Saya Miliki

**Endpoint:**
```
GET {{url}}/api/v1/user/badges
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan semua badge yang sudah diraih oleh pengguna yang sedang login.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Badge berhasil diambil.",
  "data": [
    {
      "id": 1,
      "name": "Quiz Master",
      "description": "Diberikan kepada pengguna yang menyelesaikan 10 kuis dengan nilai ≥ 80.",
      "icon_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/badges/quiz-master.png",
      "type": "achievement",
      "rarity": "rare",
      "earned_at": "2026-04-25T09:00:00.000000Z",
      "xp_reward": 100
    },
    {
      "id": 2,
      "name": "Fast Learner",
      "description": "Diberikan kepada pengguna yang menyelesaikan kursus pertama.",
      "icon_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/badges/fast-learner.png",
      "type": "achievement",
      "rarity": "common",
      "earned_at": "2026-04-10T14:00:00.000000Z",
      "xp_reward": 50
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

## 15. [GET] Daftar Badge yang Tersedia untuk Diraih

**Endpoint:**
```
GET {{url}}/api/v1/badges/available
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan daftar badge yang **belum** diraih oleh asesi dan masih bisa dikejar.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman (maks 100) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Badge tersedia berhasil diambil.",
  "data": [
    {
      "id": 3,
      "name": "Assignment Champion",
      "description": "Selesaikan 5 tugas dengan nilai ≥ 85.",
      "icon_url": null,
      "type": "achievement",
      "rarity": "epic",
      "xp_reward": 150
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  },
  "errors": null
}
```

---

## 16. [GET] Riwayat Poin/XP Saya

**Endpoint:**
```
GET {{url}}/api/v1/user/points-history
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan riwayat perolehan XP/poin secara terperinci oleh pengguna yang sedang login.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Riwayat poin berhasil diambil.",
  "data": [
    {
      "id": 500,
      "amount": 50,
      "source": "quiz_completed",
      "description": "Menyelesaikan kuis: Statistik Dasar",
      "created_at": "2026-04-28T11:00:00.000000Z"
    },
    {
      "id": 499,
      "amount": 100,
      "source": "badge_earned",
      "description": "Mendapatkan badge: Quiz Master",
      "created_at": "2026-04-25T09:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  },
  "errors": null
}
```

---

## 17. [GET] Milestones / Pencapaian Saya

**Endpoint:**
```
GET {{url}}/api/v1/user/milestones
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan daftar milestone/pencapaian yang sudah diraih dan yang belum oleh pengguna.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data milestones berhasil diambil.",
  "data": {
    "achieved": [
      {
        "id": 1,
        "name": "Kursus Pertama",
        "description": "Menyelesaikan kursus pertama.",
        "achieved_at": "2026-04-10T00:00:00.000000Z"
      }
    ],
    "upcoming": [
      {
        "id": 2,
        "name": "Kursus Ketiga",
        "description": "Menyelesaikan 3 kursus.",
        "progress": 1,
        "target": 3
      }
    ]
  },
  "meta": null,
  "errors": null
}
```

---

## 18. [GET] Info Level Saya Saat Ini

**Endpoint:**
```
GET {{url}}/api/v1/user/level
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan informasi level pengguna yang sedang login berdasarkan total XP.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "current_level": 7,
    "tier": 1,
    "level_name": "Pemula VII",
    "total_xp": 1200,
    "xp_at_level_start": 1000,
    "xp_to_next_level": 200,
    "progress_percentage": 50
  },
  "meta": null,
  "errors": null
}
```

---

## 19. [GET] Statistik XP Harian Saya

**Endpoint:**
```
GET {{url}}/api/v1/user/daily-xp-stats
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan statistik perolehan XP per hari untuk beberapa hari terakhir.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `days` | `7` | Jumlah hari yang ditampilkan (default 7, maks 30) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    { "date": "2026-04-29", "xp": 150 },
    { "date": "2026-04-28", "xp": 80 },
    { "date": "2026-04-27", "xp": 0 },
    { "date": "2026-04-26", "xp": 200 },
    { "date": "2026-04-25", "xp": 100 },
    { "date": "2026-04-24", "xp": 0 },
    { "date": "2026-04-23", "xp": 50 }
  ],
  "meta": null,
  "errors": null
}
```

---

## 20. [GET] Progress Gamifikasi per Unit Kursus

**Endpoint:**
```
GET {{url}}/api/v1/user/levels/:course_slug
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan data progress/level gamifikasi pengguna dalam konteks kursus tertentu (per unit).

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data level per unit berhasil diambil.",
  "data": [
    {
      "unit_id": 40,
      "unit_title": "Pengantar Analisis Data",
      "unit_slug": "pengantar-analisis-data-7",
      "xp_earned": 300,
      "level": 3
    },
    {
      "unit_id": 41,
      "unit_title": "Teknik Visualisasi Data",
      "unit_slug": "teknik-visualisasi-data-7",
      "xp_earned": 150,
      "level": 2
    }
  ],
  "meta": null,
  "errors": null
}
```

---

## Catatan Penting

> **Endpoint `/user/*`** hanya bisa diakses oleh user yang sedang login (semua role). Data yang dikembalikan adalah data milik token yang digunakan.

> **Gamifikasi bersifat otomatis** — XP dan badge diberikan secara otomatis oleh sistem saat event terjadi (kuis selesai, tugas dikumpulkan, dll). Tidak ada endpoint manual grant XP dari admin.

> **Leaderboard** memiliki field `my_rank` di dalam `meta` yang berisi peringkat pengguna yang sedang login, berguna untuk highlight posisi sendiri di frontend.
