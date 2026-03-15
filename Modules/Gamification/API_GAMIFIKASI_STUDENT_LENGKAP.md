# DOKUMENTASI API GAMIFIKASI STUDENT - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Gamification - Student Engagement  
**Platform**: Mobile & Web Student

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [XP System](#xp-system)
4. [Level System](#level-system)
5. [Badge System](#badge-system)
6. [Leaderboard](#leaderboard)
7. [Statistics & Progress](#statistics--progress)
8. [Complete Use Case](#complete-use-case)

---

## 🎯 RINGKASAN

Dokumentasi ini menjelaskan sistem gamifikasi lengkap untuk meningkatkan engagement student:
1. **XP (Experience Points)** - Poin yang didapat dari aktivitas belajar
2. **Level System** - Sistem level berdasarkan total XP
3. **Badges** - Lencana achievement untuk pencapaian tertentu
4. **Leaderboard** - Ranking student berdasarkan XP
5. **Statistics** - Statistik pembelajaran dan progress

### Fitur Utama
- ✅ Automatic XP award untuk setiap aktivitas
- ✅ Level progression dengan milestone rewards
- ✅ Badge system dengan berbagai kategori
- ✅ Real-time leaderboard dengan filtering
- ✅ Detailed statistics dan analytics
- ✅ XP transaction history
- ✅ Achievement tracking

---

## 🌐 BASE URL & HEADERS

### Base URL
```
Development:  http://localhost:8000/api/v1
Staging:      https://staging-api.levl.id/api/v1
Production:   https://api.levl.id/api/v1
```

### Headers Standar
```http
Content-Type: application/json
Accept: application/json
Accept-Language: id
Authorization: Bearer {{auth_token}}
```

---

## ⭐ XP SYSTEM

### XP Sources & Rewards

| Activity | XP Reward | Bonus | Description |
|----------|-----------|-------|-------------|
| Lesson Complete | +10 XP | - | Menyelesaikan materi |
| Assignment Submit | +20 XP | +15 XP (score ≥80) | Submit tugas |
| Quiz Start | +5 XP | - | Memulai kuis |
| Quiz Complete | +30 XP | +10 XP (score ≥80) | Menyelesaikan kuis |
| Perfect Score | - | +20 XP (score = 100) | Nilai sempurna |
| Forum Post | +5 XP | - | Membuat thread forum |
| Forum Reply | +2 XP | - | Membalas thread |
| Reaction Received | +1 XP | - | Mendapat reaction |

### XP Info Response Structure

Setiap endpoint yang memberikan XP akan include `xp_info` di response:

```json
{
  "xp_info": {
    "awarded": true,
    "amount": 10,
    "source": "lesson_completed",
    "description": "Menyelesaikan materi: Introduction",
    "total_xp": 860,
    "level_info": {
      "current_level": 5,
      "level_name": "Apprentice",
      "xp_for_current_level": 800,
      "xp_for_next_level": 1000,
      "xp_progress": 60,
      "xp_needed": 140,
      "progress_percentage": 30
    }
  }
}
```

---

### 1.1. GET [Mobile] XP - Riwayat Transaksi XP

Melihat riwayat semua transaksi XP yang diterima dengan fitur filtering dan sorting lengkap.

#### Endpoint
```
GET /user/points-history
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[source_type]` | string | ❌ No | - | Filter by source type: `lesson`, `assignment`, `course`, `unit` |
| `filter[reason]` | string | ❌ No | - | Filter by reason: `lesson_completed`, `assignment_submitted`, etc |
| `filter[period]` | string | ❌ No | - | Filter by period: `today`, `this_week`, `this_month`, `this_year` |
| `filter[month]` | string | ❌ No | - | Filter by specific month (YYYY-MM): `2026-01`, `2026-02`, etc |
| `filter[date_from]` | date | ❌ No | - | Filter dari tanggal (Y-m-d) |
| `filter[date_to]` | date | ❌ No | - | Filter sampai tanggal (Y-m-d) |
| `filter[points_min]` | integer | ❌ No | - | Filter minimal poin |
| `filter[points_max]` | integer | ❌ No | - | Filter maksimal poin |
| `sort` | string | ❌ No | -created_at | Sorting: `created_at`, `points`, `source_type`, `reason` (prefix `-` untuk desc) |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[source_type]**:
- `lesson` - XP dari lesson
- `assignment` - XP dari assignment
- `course` - XP dari course
- `unit` - XP dari unit
- `grade` - XP dari nilai
- `attempt` - XP dari percobaan

**filter[reason]**:
- `lesson_completed` - Menyelesaikan pelajaran
- `assignment_submitted` - Mengumpulkan tugas
- `quiz_completed` - Menyelesaikan kuis
- `quiz_passed` - Lulus kuis
- `perfect_score` - Nilai sempurna
- `first_attempt` - Percobaan pertama
- `first_submission` - Pengumpulan pertama
- `daily_streak` - Streak harian
- `forum_post` - Posting forum
- `forum_reply` - Balasan forum
- `reaction_received` - Menerima reaksi
- `engagement` - Engagement (forum/reaction)
- `bonus` - Bonus
- `penalty` - Penalti
- `completion` - Penyelesaian (legacy)
- `score` - Skor (legacy)

**filter[period]**:
- `today` - Hari ini
- `this_week` - Minggu ini (Senin - Minggu)
- `this_month` - Bulan ini (tanggal 1 sampai hari ini di bulan berjalan)
- `this_year` - Tahun ini (1 Januari sampai hari ini di tahun berjalan)

**filter[month]** (Format: YYYY-MM):
- `2026-01` - Januari 2026 (1-31 Januari)
- `2026-02` - Februari 2026 (1-28/29 Februari)
- `2026-03` - Maret 2026 (1-31 Maret)
- dst.

**Catatan Penting**:
- `this_month` = dari tanggal 1 bulan ini sampai hari ini (bukan 30 hari terakhir)
- `filter[month]` = untuk melihat data bulan spesifik secara penuh
- Jika `filter[month]` digunakan, `filter[period]` akan diabaikan

**sort** (Available Fields):
- `created_at` / `-created_at` - Urutkan berdasarkan tanggal (default: `-created_at`)
- `points` / `-points` - Urutkan berdasarkan jumlah poin
- `source_type` / `-source_type` - Urutkan berdasarkan tipe sumber
- `reason` / `-reason` - Urutkan berdasarkan alasan

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Riwayat poin berhasil diambil.",
  "data": [
    {
      "id": 8,
      "points": 100,
      "source_type": "assignment",
      "source_type_label": "Tugas",
      "reason": "assignment_submitted",
      "reason_label": "Mengumpulkan Tugas",
      "description": "Submitted assignment: Quisquam porro ex rerum distinctio natus hic numquam.",
      "context": {
        "assignment": {
          "id": 263,
          "title": "Quisquam porro ex rerum distinctio natus hic numquam."
        },
        "unit": {
          "id": 129,
          "title": "Getting Started"
        },
        "course": {
          "id": 43,
          "title": "Laravel PHP Framework Masterclass"
        }
      },
      "created_at": "2026-03-15T03:09:10.000000Z"
    },
    {
      "id": 7,
      "points": 50,
      "source_type": "lesson",
      "source_type_label": "Pelajaran",
      "reason": "lesson_completed",
      "reason_label": "Menyelesaikan Pelajaran",
      "description": "Completed lesson: Best Practices for Data Structures",
      "context": {
        "lesson": {
          "id": 597,
          "title": "Best Practices for Data Structures"
        },
        "unit": {
          "id": 129,
          "title": "Getting Started"
        },
        "course": {
          "id": 43,
          "title": "Laravel PHP Framework Masterclass"
        }
      },
      "created_at": "2026-03-15T03:02:01.000000Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 6,
      "last_page": 1,
      "from": 1,
      "to": 6,
      "has_next": false,
      "has_prev": false
    }
  },
  "errors": null
}
```

#### Postman Example
```javascript
// Query Params - All Transactions (Default)
per_page: 15
page: 1
sort: -created_at

// Query Params - Filter by Source Type
filter[source_type]: lesson
per_page: 20

// Query Params - Filter by Reason
filter[reason]: lesson_completed
sort: -points

// Query Params - Filter by Period (This Month)
filter[period]: this_month
per_page: 20

// Query Params - Filter by Specific Month
filter[month]: 2026-01
per_page: 20

// Query Params - Filter by Specific Month (February 2026)
filter[month]: 2026-02
per_page: 20

// Query Params - Filter by Date Range
filter[date_from]: 2026-03-01
filter[date_to]: 2026-03-15
sort: -created_at

// Query Params - Filter by Points Range
filter[points_min]: 50
filter[points_max]: 100

// Query Params - Kombinasi Multiple Filters
filter[source_type]: lesson
filter[period]: this_week
sort: -points
per_page: 20

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has transactions", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has pagination", () => {
    const meta = pm.response.json().meta;
    pm.expect(meta).to.have.property('pagination');
    pm.expect(meta.pagination).to.have.property('total');
});
pm.test("Has context", () => {
    const data = pm.response.json().data;
    if (data.length > 0) {
        pm.expect(data[0]).to.have.property('context');
        pm.expect(data[0]).to.have.property('source_type_label');
        pm.expect(data[0]).to.have.property('reason_label');
    }
});
```

---

## 🎖️ LEVEL SYSTEM

### Level Progression

Level ditentukan berdasarkan total XP yang dikumpulkan. Setiap level memiliki threshold XP tertentu.

| Level | Name | XP Required | XP Range |
|-------|------|-------------|----------|
| 1 | Newbie | 0 | 0 - 49 |
| 2 | Novice | 50 | 50 - 149 |
| 3 | Learner | 150 | 150 - 299 |
| 4 | Student | 300 | 300 - 499 |
| 5 | Apprentice | 500 | 500 - 799 |
| 6 | Practitioner | 800 | 800 - 1199 |
| 7 | Skilled | 1200 | 1200 - 1699 |
| 8 | Proficient | 1700 | 1700 - 2299 |
| 9 | Expert | 2300 | 2300 - 2999 |
| 10 | Master | 3000 | 3000+ |

### 2.1. GET [Mobile] Level - Informasi Level Saya

Melihat informasi level saat ini dan progress ke level berikutnya.

#### Endpoint
```
GET /user/level
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Informasi level berhasil diambil",
  "data": {
    "current_level": 5,
    "level_name": "Apprentice",
    "level_description": "You're making great progress!",
    "total_xp": 860,
    "xp_for_current_level": 500,
    "xp_for_next_level": 800,
    "xp_progress": 360,
    "xp_needed": 440,
    "progress_percentage": 45,
    "next_level": {
      "level": 6,
      "name": "Practitioner",
      "xp_required": 800
    },
    "milestones": [
      {
        "level": 5,
        "name": "Apprentice",
        "unlocked_at": "2026-03-10T14:30:00.000000Z"
      },
      {
        "level": 10,
        "name": "Master",
        "unlocked_at": null
      }
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/my-level

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has level info", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('current_level');
    pm.expect(data).to.have.property('level_name');
    pm.expect(data).to.have.property('progress_percentage');
});
```

---

### 2.2. GET [Mobile] Level - Daftar Semua Level

Melihat daftar semua level yang tersedia beserta requirement-nya dengan filtering dan sorting.

#### Endpoint
```
GET /levels
```

#### Authorization
```
Bearer Token Required
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[level]` | integer | ❌ No | - | Filter by exact level |
| `filter[level_min]` | integer | ❌ No | - | Filter by minimum level |
| `filter[level_max]` | integer | ❌ No | - | Filter by maximum level |
| `filter[xp_min]` | integer | ❌ No | - | Filter by minimum XP required |
| `filter[xp_max]` | integer | ❌ No | - | Filter by maximum XP required |
| `sort` | string | ❌ No | level | Sorting: `level`, `xp_required`, `bonus_xp` |
| `per_page` | integer | ❌ No | 20 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar level berhasil diambil",
  "data": [
    {
      "level": 1,
      "name": "Newbie",
      "description": "Welcome to your learning journey!",
      "xp_required": 0,
      "xp_range": "0 - 49",
      "bonus_xp": 0,
      "icon_url": "https://api.levl.id/storage/levels/newbie.png",
      "is_unlocked": true,
      "unlocked_at": "2026-03-01T10:00:00.000000Z"
    },
    {
      "level": 2,
      "name": "Novice",
      "description": "You're getting started!",
      "xp_required": 50,
      "xp_range": "50 - 149",
      "bonus_xp": 10,
      "icon_url": "https://api.levl.id/storage/levels/novice.png",
      "is_unlocked": true,
      "unlocked_at": "2026-03-02T11:30:00.000000Z"
    },
    {
      "level": 6,
      "name": "Practitioner",
      "description": "You're becoming skilled!",
      "xp_required": 800,
      "xp_range": "800 - 1199",
      "bonus_xp": 50,
      "icon_url": "https://api.levl.id/storage/levels/practitioner.png",
      "is_unlocked": false,
      "unlocked_at": null
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100,
      "last_page": 5,
      "from": 1,
      "to": 20,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

#### Postman Example
```javascript
// Query Params - All Levels (Default)
per_page: 20
sort: level
page: 1

// Query Params - Filter by Level Range
filter[level_min]: 1
filter[level_max]: 10
per_page: 20

// Query Params - Filter by XP Range
filter[xp_min]: 0
filter[xp_max]: 500
sort: xp_required

// Query Params - Filter by Exact Level
filter[level]: 5

// Query Params - Sort by Bonus XP
sort: -bonus_xp
per_page: 20

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has levels", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has pagination", () => {
    const meta = pm.response.json().meta;
    pm.expect(meta).to.have.property('pagination');
});
```

---

## 🏅 BADGE SYSTEM

### Badge Categories

| Category | Description | Examples |
|----------|-------------|----------|
| Learning | Aktivitas pembelajaran | First Lesson, Course Complete |
| Assessment | Tugas dan kuis | Perfect Score, Quiz Master |
| Engagement | Partisipasi | Forum Active, Helpful |
| Achievement | Pencapaian khusus | Fast Learner, Dedicated |
| Milestone | Milestone tertentu | Level 5, 100 XP |

### 3.1. GET [Mobile] Lencana - Lencana Saya

Melihat semua badge yang sudah didapatkan dengan pagination dan filtering.

#### Endpoint
```
GET /user/badges
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[type]` | string | ❌ No | - | Filter by type |
| `filter[rarity]` | string | ❌ No | - | Filter by rarity |
| `sort` | string | ❌ No | -earned_at | Sorting: `earned_at`, `progress` |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[type]**:
- `completion` - Badge penyelesaian
- `quality` - Badge kualitas
- `speed` - Badge kecepatan
- `habit` - Badge kebiasaan
- `social` - Badge sosial
- `milestone` - Badge pencapaian milestone
- `hidden` - Badge tersembunyi

**filter[rarity]**:
- `common` - Badge umum
- `uncommon` - Badge tidak umum
- `rare` - Badge langka
- `epic` - Badge epik
- `legendary` - Badge legendaris

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Lencana berhasil diambil",
  "data": [
    {
      "id": 1,
      "name": "First Steps",
      "slug": "first-steps",
      "description": "Menyelesaikan lesson pertama",
      "category": "learning",
      "icon_url": "https://api.levl.id/storage/badges/first-steps.png",
      "rarity": "common",
      "earned_at": "2026-03-01T10:30:00.000000Z",
      "progress": {
        "current": 1,
        "target": 1,
        "percentage": 100
      }
    },
    {
      "id": 5,
      "name": "Quiz Champion",
      "slug": "quiz-champion",
      "description": "Menyelesaikan 10 kuis dengan nilai ≥80",
      "category": "assessment",
      "icon_url": "https://api.levl.id/storage/badges/quiz-champion.png",
      "rarity": "rare",
      "earned_at": "2026-03-10T14:30:00.000000Z",
      "progress": {
        "current": 10,
        "target": 10,
        "percentage": 100
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 12,
      "last_page": 1,
      "from": 1,
      "to": 12,
      "has_next": false,
      "has_prev": false
    },
    "summary": {
      "total_badges": 12,
      "by_category": {
        "learning": 5,
        "assessment": 3,
        "engagement": 2,
        "achievement": 1,
        "milestone": 1
      },
      "by_rarity": {
        "common": 6,
        "uncommon": 3,
        "rare": 2,
        "epic": 1,
        "legendary": 0
      }
    }
  }
}
```

#### Postman Example
```javascript
// Query Params - All My Badges
per_page: 15
sort: -earned_at
page: 1

// Query Params - Filter by Type
filter[type]: milestone
sort: -earned_at

// Query Params - Filter by Rarity
filter[rarity]: rare
sort: -earned_at

// Query Params - Combine Filters
filter[type]: social
filter[rarity]: uncommon
sort: -earned_at

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has badges", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has pagination", () => {
    const meta = pm.response.json().meta;
    pm.expect(meta).to.have.property('pagination');
});
```

---

### 3.2. GET [Mobile] Lencana - Semua Lencana (Available)

Melihat semua badge yang tersedia, termasuk yang belum didapatkan dengan status earned dan progress.

#### Endpoint
```
GET /badges/available
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[type]` | string | ❌ No | - | Filter by type |
| `filter[rarity]` | string | ❌ No | - | Filter by rarity |
| `filter[earned]` | boolean | ❌ No | - | Filter earned/not earned |
| `search` | string | ❌ No | - | Search by name, code, description |
| `sort` | string | ❌ No | name | Sorting: `name`, `rarity`, `xp_reward`, `created_at` |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[type]**:
- `completion` - Badge penyelesaian
- `quality` - Badge kualitas
- `speed` - Badge kecepatan
- `habit` - Badge kebiasaan
- `social` - Badge sosial
- `milestone` - Badge pencapaian milestone
- `hidden` - Badge tersembunyi

**filter[rarity]**:
- `common` - Badge umum
- `uncommon` - Badge tidak umum
- `rare` - Badge langka
- `epic` - Badge epik
- `legendary` - Badge legendaris

**filter[earned]**:
- `true` - Hanya badge yang sudah didapat
- `false` - Hanya badge yang belum didapat

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar lencana berhasil diambil",
  "data": [
    {
      "id": 1,
      "name": "First Steps",
      "slug": "first-steps",
      "description": "Menyelesaikan lesson pertama",
      "category": "learning",
      "icon_url": "https://api.levl.id/storage/badges/first-steps.png",
      "rarity": "common",
      "is_earned": true,
      "earned_at": "2026-03-01T10:30:00.000000Z",
      "progress": {
        "current": 1,
        "target": 1,
        "percentage": 100
      }
    },
    {
      "id": 15,
      "name": "Master Learner",
      "slug": "master-learner",
      "description": "Menyelesaikan 100 lessons",
      "category": "learning",
      "icon_url": "https://api.levl.id/storage/badges/master-learner.png",
      "rarity": "epic",
      "is_earned": false,
      "earned_at": null,
      "progress": {
        "current": 45,
        "target": 100,
        "percentage": 45
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 50,
      "last_page": 4,
      "from": 1,
      "to": 15,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

#### Postman Example
```javascript
// Query Params - All Available Badges
per_page: 15
sort: name
page: 1

// Query Params - Filter by Type
filter[type]: milestone
sort: name

// Query Params - Filter by Rarity
filter[rarity]: epic
sort: -xp_reward

// Query Params - Combine Type and Rarity
filter[type]: social
filter[rarity]: rare
sort: name

// Query Params - Only Earned Badges
filter[earned]: true
sort: -earned_at

// Query Params - Only Not Earned Badges
filter[earned]: false
sort: name

// Query Params - Search Badges
search: master
per_page: 20

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has badges", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has pagination", () => {
    const meta = pm.response.json().meta;
    pm.expect(meta).to.have.property('pagination');
});
pm.test("Has earned status", () => {
    const data = pm.response.json().data;
    if (data.length > 0) {
        pm.expect(data[0]).to.have.property('is_earned');
        pm.expect(data[0]).to.have.property('progress');
    }
});
```

---

## 🏆 LEADERBOARD

### 4.1. GET [Mobile] Leaderboard - Global

Melihat ranking global semua student berdasarkan XP.

#### Endpoint
```
GET /leaderboards
```

#### Authorization
```
Bearer Token Required
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[period]` | string | ❌ No | all_time | Period: `today`, `this_week`, `this_month`, `this_year`, `all_time` |
| `filter[month]` | string | ❌ No | - | Filter by specific month (YYYY-MM): `2026-01`, `2026-02`, etc |
| `search` | string | ❌ No | - | Search by user name |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[period]**:
- `today` - Leaderboard hari ini
- `this_week` - Leaderboard minggu ini (Senin - Minggu)
- `this_month` - Leaderboard bulan ini (tanggal 1 sampai hari ini)
- `this_year` - Leaderboard tahun ini (1 Januari sampai hari ini)
- `all_time` - Leaderboard sepanjang waktu (default)

**filter[month]** (Format: YYYY-MM):
- `2026-01` - Leaderboard Januari 2026
- `2026-02` - Leaderboard Februari 2026
- `2026-03` - Leaderboard Maret 2026
- dst.

**Catatan**:
- Jika `filter[month]` digunakan, `filter[period]` akan diabaikan
- `this_month` menghitung dari tanggal 1 bulan berjalan sampai hari ini

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Leaderboard berhasil diambil",
  "data": {
    "leaderboard": [
      {
        "rank": 1,
        "user": {
          "id": 10,
          "name": "Ahmad Rizki",
          "avatar_url": "https://api.levl.id/storage/avatars/ahmad.jpg"
        },
        "total_xp": 2850,
        "level": 9,
        "level_name": "Expert",
        "badges_count": 25,
        "courses_completed": 5
      },
      {
        "rank": 2,
        "user": {
          "id": 15,
          "name": "Siti Nurhaliza",
          "avatar_url": "https://api.levl.id/storage/avatars/siti.jpg"
        },
        "total_xp": 2650,
        "level": 8,
        "level_name": "Proficient",
        "badges_count": 22,
        "courses_completed": 4
      },
      {
        "rank": 3,
        "user": {
          "id": 5,
          "name": "Budi Santoso",
          "avatar_url": "https://api.levl.id/storage/avatars/budi.jpg"
        },
        "total_xp": 2450,
        "level": 8,
        "level_name": "Proficient",
        "badges_count": 20,
        "courses_completed": 4
      }
    ],
    "my_rank": {
      "rank": 15,
      "user": {
        "id": 25,
        "name": "Current User",
        "avatar_url": "https://api.levl.id/storage/avatars/me.jpg"
      },
      "total_xp": 860,
      "level": 5,
      "level_name": "Apprentice",
      "badges_count": 12,
      "courses_completed": 1
    }
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 150,
      "last_page": 8
    },
    "scope": "global",
    "period": "all_time"
  }
}
```

#### Postman Example
```javascript
// Query Params - Global Leaderboard (All Time)
filter[period]: all_time
per_page: 20

// Query Params - This Month Leaderboard
filter[period]: this_month
per_page: 20

// Query Params - Specific Month Leaderboard (January 2026)
filter[month]: 2026-01
per_page: 20

// Query Params - Specific Month Leaderboard (February 2026)
filter[month]: 2026-02
per_page: 20

// Query Params - Search User
search: Ahmad
filter[period]: all_time
per_page: 20

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has leaderboard", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('leaderboard');
    pm.expect(data.leaderboard).to.be.an('array');
});
pm.test("Has my rank", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('my_rank');
    pm.expect(data.my_rank).to.have.property('rank');
});
```

---

### 4.2. GET [Mobile] Leaderboard - Ranking Saya

Melihat ranking saya di leaderboard.

#### Endpoint
```
GET /user/rank
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[period]` | string | ❌ No | all_time | Period: `today`, `this_week`, `this_month`, `this_year`, `all_time` |
| `filter[month]` | string | ❌ No | - | Filter by specific month (YYYY-MM): `2026-01`, `2026-02`, etc |

#### Valid Values

**filter[period]**:
- `today` - Ranking hari ini
- `this_week` - Ranking minggu ini (Senin - Minggu)
- `this_month` - Ranking bulan ini (tanggal 1 sampai hari ini)
- `this_year` - Ranking tahun ini (1 Januari sampai hari ini)
- `all_time` - Ranking sepanjang waktu (default)

**filter[month]** (Format: YYYY-MM):
- `2026-01` - Ranking Januari 2026
- `2026-02` - Ranking Februari 2026
- `2026-03` - Ranking Maret 2026
- dst.

**Catatan**:
- Jika `filter[month]` digunakan, `filter[period]` akan diabaikan
- `this_month` menghitung dari tanggal 1 bulan berjalan sampai hari ini

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Ranking berhasil diambil",
  "data": {
    "rank": 15,
    "user": {
      "id": 25,
      "name": "Current User",
      "avatar_url": "https://api.levl.id/storage/avatars/me.jpg"
    },
    "total_xp": 860,
    "level": 5,
    "level_name": "Apprentice",
    "badges_count": 12,
    "total_students": 150,
    "percentile": 90
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL - All Time Rank (Default)
{{base_url}}/user/rank?filter[period]=all_time

// URL - This Month Rank
{{base_url}}/user/rank?filter[period]=this_month

// URL - Specific Month Rank (January 2026)
{{base_url}}/user/rank?filter[month]=2026-01

// URL - Specific Month Rank (February 2026)
{{base_url}}/user/rank?filter[month]=2026-02

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has rank data", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('rank');
    pm.expect(data).to.have.property('total_xp');
    pm.expect(data).to.have.property('level');
});
```

---

## 📊 STATISTICS & PROGRESS

### 5.1. GET [Mobile] Statistik - Dashboard Gamifikasi

Melihat ringkasan statistik gamifikasi lengkap dengan filter period dan month.

#### Endpoint
```
GET /user/gamification-summary
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[period]` | string | ❌ No | all_time | Period: `today`, `this_week`, `this_month`, `this_year`, `all_time` |
| `filter[month]` | string | ❌ No | - | Filter by specific month (YYYY-MM): `2026-01`, `2026-02`, etc |

#### Valid Values

**filter[period]**:
- `today` - Statistik hari ini
- `this_week` - Statistik minggu ini (Senin - Minggu)
- `this_month` - Statistik bulan ini (tanggal 1 sampai hari ini)
- `this_year` - Statistik tahun ini (1 Januari sampai hari ini)
- `all_time` - Statistik sepanjang waktu (default)

**filter[month]** (Format: YYYY-MM):
- `2026-01` - Statistik Januari 2026
- `2026-02` - Statistik Februari 2026
- `2026-03` - Statistik Maret 2026
- dst.

**Catatan**:
- Jika `filter[month]` digunakan, `filter[period]` akan diabaikan
- `this_month` menghitung dari tanggal 1 bulan berjalan sampai hari ini

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Dashboard gamifikasi berhasil diambil",
  "data": {
    "xp": {
      "total": 860,
      "today": 45,
      "this_week": 180,
      "this_month": 520,
      "period": 520
    },
    "level": {
      "current": 5,
      "name": "Apprentice",
      "progress_percentage": 45,
      "xp_to_next_level": 440
    },
    "badges": {
      "total_earned": 12,
      "period_earned": 3
    },
    "leaderboard": {
      "global_rank": 15,
      "total_students": 150
    },
    "activity": {
      "current_streak": 7,
      "longest_streak": 14
    }
  },
  "xp_info": {
    "awarded": false,
    "total_xp": 860,
    "level_info": {
      "current_level": 5,
      "level_name": "Apprentice",
      "xp_for_current_level": 500,
      "xp_for_next_level": 800,
      "xp_progress": 360,
      "xp_needed": 440,
      "progress_percentage": 45
    }
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL - All Time Summary (Default)
{{base_url}}/user/gamification-summary

// URL - This Month Summary
{{base_url}}/user/gamification-summary?filter[period]=this_month

// URL - Specific Month Summary (January 2026)
{{base_url}}/user/gamification-summary?filter[month]=2026-01

// URL - Specific Month Summary (February 2026)
{{base_url}}/user/gamification-summary?filter[month]=2026-02

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has summary data", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('xp');
    pm.expect(data).to.have.property('level');
    pm.expect(data).to.have.property('badges');
    pm.expect(data).to.have.property('leaderboard');
});
pm.test("Has XP info", () => {
    const xpInfo = pm.response.json().xp_info;
    pm.expect(xpInfo).to.have.property('total_xp');
    pm.expect(xpInfo).to.have.property('level_info');
});
pm.test("Has period data", () => {
    const data = pm.response.json().data;
    pm.expect(data.xp).to.have.property('period');
    pm.expect(data.badges).to.have.property('period_earned');
});
```

---

### 5.2. GET [Mobile] Statistik - Progress Harian

Melihat progress XP harian untuk grafik.

#### Endpoint
```
GET /user/daily-xp-stats
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `days` | integer | ❌ No | 7 | Jumlah hari (max: 30) |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Progress harian berhasil diambil",
  "data": {
    "daily_xp": [
      {
        "date": "2026-03-09",
        "xp_earned": 45,
        "activities": {
          "lessons": 3,
          "assignments": 1,
          "quizzes": 0
        }
      },
      {
        "date": "2026-03-10",
        "xp_earned": 80,
        "activities": {
          "lessons": 5,
          "assignments": 1,
          "quizzes": 1
        }
      }
    ],
    "summary": {
      "total_xp": 520,
      "average_per_day": 74,
      "most_active_day": "2026-03-10",
      "streak_days": 7
    }
  },
  "xp_info": {
    "awarded": false,
    "total_xp": 860,
    "level_info": {
      "current_level": 5,
      "level_name": "Apprentice",
      "xp_for_current_level": 500,
      "xp_for_next_level": 800,
      "xp_progress": 360,
      "xp_needed": 440,
      "progress_percentage": 45
    }
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL - Last 7 days
{{base_url}}/user/daily-xp-stats?days=7

// URL - Last 30 days
{{base_url}}/user/daily-xp-stats?days=30

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has daily stats", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('daily_xp');
    pm.expect(data.daily_xp).to.be.an('array');
    pm.expect(data).to.have.property('summary');
});
```

---

## 📖 COMPLETE USE CASE: GAMIFICATION JOURNEY

```javascript
// ============================================
// SCENARIO: Student tracking gamification progress
// ============================================

// 1. Check current level and XP
GET /user/level
// Response: Level 5 "Apprentice", 860 XP, 45% to next level

// 2. View XP transaction history
GET /user/points-history?per_page=20&sort=-created_at
// Response: List of all XP earned with sources

// 3. Filter XP by source (if supported by backend)
GET /user/points-history?filter[source]=quiz_completed
// Response: Only XP from quizzes

// 4. View my badges
GET /user/badges
// Response: 12 badges earned

// 5. View all available badges
GET /badges?filter[earned]=false
// Response: 38 badges not yet earned, with progress

// 6. Check global leaderboard
GET /leaderboards?filter[period]=all_time&per_page=20
// Response: Top 20 students, my rank included in meta

// 7. Check my rank
GET /user/rank?filter[period]=all_time
// Response: My rank #15 with details

// 8. View gamification dashboard
GET /user/gamification-summary
// Response: Complete overview of XP, level, badges, rank

// 9. View daily progress
GET /user/daily-xp-stats?days=7
// Response: XP earned per day for last 7 days

// 10. Complete a lesson (automatic XP)
POST /lessons/introduction/complete
// Response includes xp_info:
// - awarded: true
// - amount: 10
// - total_xp: 870
// - level_info: still level 5, now 46% progress

// 11. Complete a quiz (automatic XP + bonus)
POST /quiz-submissions/1/submit
// Response includes xp_info:
// - awarded: true
// - amount: 40 (30 base + 10 bonus for score ≥80)
// - total_xp: 910
// - level_info: Level up! Now level 6 "Practitioner"

// 12. Check if new badge earned
GET /user/badges?sort=-earned_at&per_page=5
// Response: New badge "Quiz Master" earned!

// 13. View updated leaderboard position
GET /user/rank?filter[period]=all_time
// Response: Moved up to rank #12!

// 14. View all levels
GET /levels
// Response: List of all 100 levels with requirements
```

---

## 🎯 KEY POINTS

### Automatic XP Award
- XP diberikan otomatis saat menyelesaikan aktivitas
- Setiap response yang memberikan XP include `xp_info` object
- XP langsung ditambahkan ke total user
- Level progression otomatis saat mencapai threshold

### Badge System
- Badge earned otomatis berdasarkan rules
- Progress tracking untuk badge yang belum earned
- Badge memiliki rarity: common, uncommon, rare, epic, legendary
- Badge dikelompokkan per category untuk mudah filtering

### Leaderboard
- Real-time ranking berdasarkan XP
- Support multiple scopes: global, course, monthly
- Always include user's own rank
- Pagination untuk performa optimal

### Statistics
- Dashboard overview untuk quick insights
- Daily progress untuk tracking consistency
- Activity breakdown per type
- Streak tracking untuk engagement

---

## 📊 RESPONSE FORMAT

### Success Response with XP Info
```json
{
  "success": true,
  "message": "Success message",
  "data": { },
  "xp_info": {
    "awarded": true,
    "amount": 10,
    "source": "lesson_completed",
    "description": "Menyelesaikan materi: Introduction",
    "total_xp": 860,
    "level_info": {
      "current_level": 5,
      "level_name": "Apprentice",
      "xp_for_current_level": 500,
      "xp_for_next_level": 800,
      "xp_progress": 360,
      "xp_needed": 440,
      "progress_percentage": 45
    }
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error detail"]
  }
}
```

---

## ⚠️ ERROR CODES

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request berhasil |
| 401 | Unauthorized | Token invalid/expired |
| 403 | Forbidden | Tidak memiliki akses |
| 404 | Not Found | Resource tidak ditemukan |
| 422 | Validation Error | Input tidak valid |

---

## 🔗 INTEGRATION WITH LEARNING API

Gamification terintegrasi penuh dengan Learning API:

1. **Lesson Complete** → +10 XP (automatic)
2. **Assignment Submit** → +20 XP (automatic)
3. **Quiz Start** → +5 XP (automatic)
4. **Quiz Complete** → +30 XP + bonus (automatic)
5. **Perfect Score** → +20 XP bonus (automatic)

Setiap endpoint pembelajaran yang memberikan XP akan include `xp_info` di response.

---

**Dokumentasi ini mencakup complete gamification system untuk student engagement.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
