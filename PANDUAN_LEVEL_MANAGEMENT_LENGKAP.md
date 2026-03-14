# Panduan Lengkap Level Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap API Level Management untuk kebutuhan gamification system. Level menggunakan formula `XP(level) = 100 × level^1.6` untuk progression yang smooth dan engaging.

---

## Daftar Isi

1. [Level Overview](#1-level-overview)
2. [List Level Configurations](#2-list-level-configurations)
3. [Get Level Progression Table](#3-get-level-progression-table)
4. [Get User Current Level](#4-get-user-current-level)
5. [Calculate Level from XP](#5-calculate-level-from-xp)
6. [Get Daily XP Stats](#6-get-daily-xp-stats)
7. [Sync Level Configurations (Admin)](#7-sync-level-configurations-admin)
8. [Update Level Configuration (Admin)](#8-update-level-configuration-admin)
9. [Get Level Statistics (Admin)](#9-get-level-statistics-admin)
10. [Level Up Event System](#10-level-up-event-system)
11. [XP Source Management](#11-xp-source-management)
12. [XP Transaction Log](#12-xp-transaction-log)
13. [Global Daily XP Cap](#13-global-daily-xp-cap)
14. [Authorization Matrix](#14-authorization-matrix)
15. [UI/UX Implementation Notes](#15-uiux-implementation-notes)

---

## 1. LEVEL OVERVIEW

### Base Endpoint
- Public Level API: `/api/v1/levels`
- User Level API: `/api/v1/user/level`
- Admin Level API: `/api/v1/levels` (dengan middleware Superadmin)

### Authentication
Semua endpoint membutuhkan token Bearer:
```
Authorization: Bearer {token}
```

### Level Formula
```
XP(level) = 100 × level^1.6
```

**Contoh Perhitungan**:
- Level 1: 100 XP
- Level 10: 3,981 XP (Total: 20,433 XP)
- Level 25: 22,097 XP (Total: 206,145 XP)
- Level 50: 78,446 XP (Total: 1,197,126 XP)
- Level 100: 264,575 XP (Total: 6,985,922 XP)

### Level Fields (Core)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID level config |
| `level` | integer | Nomor level (1-100) |
| `name` | string | Nama tier level |
| `xp_required` | integer | XP yang dibutuhkan untuk level ini |
| `rewards` | json | Rewards yang didapat saat mencapai level |
| `created_at` | datetime | Waktu dibuat |
| `updated_at` | datetime | Waktu diupdate |

### Level Tiers (Nama Level)

| Level Range | Name |
|-------------|------|
| 1-9 | Beginner |
| 10-19 | Novice |
| 20-29 | Competent |
| 30-39 | Intermediate |
| 40-49 | Proficient |
| 50-59 | Advanced |
| 60-69 | Expert |
| 70-79 | Master |
| 80-89 | Grand Master |
| 90-100 | Legendary Master |

---

## 2. LIST LEVEL CONFIGURATIONS

### Endpoint
```
GET /api/v1/levels
```

### Authorization
- Authenticated user (semua role)

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `per_page` | integer | ❌ Tidak | 20 | Jumlah item per halaman (min 1, max 100) |
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |

### Contoh Request

#### 1. Get All Levels (Default)
```
GET /api/v1/levels
```

#### 2. Get Levels with Custom Pagination
```
GET /api/v1/levels?per_page=50&page=1
```

### Response Format

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "level": 1,
        "name": "Beginner",
        "xp_required": 100,
        "rewards": {},
        "created_at": "2026-03-14T10:00:00.000000Z",
        "updated_at": "2026-03-14T10:00:00.000000Z"
      },
      {
        "id": 2,
        "level": 2,
        "name": "Beginner",
        "xp_required": 303,
        "rewards": {},
        "created_at": "2026-03-14T10:00:00.000000Z",
        "updated_at": "2026-03-14T10:00:00.000000Z"
      },
      {
        "id": 10,
        "level": 10,
        "name": "Novice",
        "xp_required": 3981,
        "rewards": {
          "badge": "level_10_milestone",
          "bonus_xp": 100
        },
        "created_at": "2026-03-14T10:00:00.000000Z",
        "updated_at": "2026-03-14T10:00:00.000000Z"
      }
    ],
    "first_page_url": "http://localhost/api/v1/levels?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://localhost/api/v1/levels?page=5",
    "next_page_url": "http://localhost/api/v1/levels?page=2",
    "path": "http://localhost/api/v1/levels",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 100
  }
}
```

### Catatan Penting
- Data di-paginate secara default
- Total 100 level configurations (level 1-100)
- Rewards berisi JSON object dengan milestone rewards

---

## 3. GET LEVEL PROGRESSION TABLE

### Endpoint
```
GET /api/v1/levels/progression
```

### Authorization
- Authenticated user (semua role)

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `start` | integer | ❌ Tidak | 1 | Level awal (min: 1) |
| `end` | integer | ❌ Tidak | 20 | Level akhir (max: 100) |

### Contoh Request

#### 1. Get Progression (Level 1-20)
```
GET /api/v1/levels/progression
```

#### 2. Get Progression (Level 1-50)
```
GET /api/v1/levels/progression?start=1&end=50
```

#### 3. Get Progression (Level 50-100)
```
GET /api/v1/levels/progression?start=50&end=100
```

### Response Format

```json
{
  "success": true,
  "data": [
    {
      "level": 1,
      "xp_required": 100,
      "total_xp": 100,
      "name": "Beginner"
    },
    {
      "level": 2,
      "xp_required": 303,
      "total_xp": 403,
      "name": "Beginner"
    },
    {
      "level": 5,
      "xp_required": 1148,
      "total_xp": 3524,
      "name": "Beginner"
    },
    {
      "level": 10,
      "xp_required": 3981,
      "total_xp": 20433,
      "name": "Novice"
    },
    {
      "level": 20,
      "xp_required": 14568,
      "total_xp": 117486,
      "name": "Competent"
    }
  ]
}
```

### Field Explanation

| Field | Deskripsi |
|-------|-----------|
| `level` | Nomor level |
| `xp_required` | XP yang dibutuhkan untuk naik ke level ini |
| `total_xp` | Total XP kumulatif untuk mencapai level ini |
| `name` | Nama tier level |

### Catatan Penting
- `xp_required` adalah XP yang dibutuhkan untuk naik dari level sebelumnya
- `total_xp` adalah total XP kumulatif dari level 1
- Berguna untuk menampilkan tabel progression di UI

---

## 4. GET USER CURRENT LEVEL

### Endpoint
```
GET /api/v1/user/level
```

### Authorization
- Authenticated user (semua role)
- User hanya bisa melihat level sendiri

### Query Parameters
Tidak ada.

### Contoh Request

```
GET /api/v1/user/level
```

### Response Format

```json
{
  "success": true,
  "data": {
    "current_level": 14,
    "total_xp": 50000,
    "current_level_xp": 1582,
    "xp_to_next_level": 6903,
    "xp_required_for_next_level": 8485,
    "progress_percentage": 18.64
  },
  "gamification": {
    "current_xp": 50000,
    "current_level": 14,
    "latest_xp_award": {
      "xp_awarded": 100,
      "reason": "assignment_submitted",
      "description": "Submitted assignment: Introduction to PHP",
      "xp_source_code": "assignment_submitted",
      "leveled_up": false,
      "old_level": 14,
      "new_level": 14,
      "awarded_at": "2026-03-14T10:30:00Z"
    }
  }
}
```

### Field Explanation

| Field | Deskripsi |
|-------|-----------|
| `current_level` | Level user saat ini |
| `total_xp` | Total XP yang dimiliki user |
| `current_level_xp` | XP yang sudah dikumpulkan di level saat ini |
| `xp_to_next_level` | XP yang masih dibutuhkan untuk naik level |
| `xp_required_for_next_level` | Total XP yang dibutuhkan untuk naik level |
| `progress_percentage` | Persentase progress ke level berikutnya (0-100) |
| `gamification` | **NEW**: Automatic XP info (added by middleware) |
| `gamification.latest_xp_award` | Latest XP award (if within last 5 seconds) |

### Contoh Perhitungan

Jika user punya 50,000 XP:
- Current level: 14
- Total XP untuk level 14: 48,418 XP
- Total XP untuk level 15: 56,903 XP
- Current level XP: 50,000 - 48,418 = 1,582 XP
- XP to next level: 56,903 - 50,000 = 6,903 XP
- XP required for next level: 56,903 - 48,418 = 8,485 XP
- Progress: (1,582 / 8,485) × 100 = 18.64%

### Catatan Penting
- Endpoint ini untuk menampilkan level progress di user profile/dashboard
- Progress percentage berguna untuk progress bar
- Data real-time berdasarkan total XP user

---

## 5. CALCULATE LEVEL FROM XP

### Endpoint
```
POST /api/v1/levels/calculate
```

### Authorization
- Authenticated user (semua role)

### Content-Type
`application/json`

### Request Body

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `xp` | integer | ✅ Ya | min:0 | Total XP untuk dihitung levelnya |

### Contoh Request

```json
{
  "xp": 50000
}
```

### Response Format

```json
{
  "success": true,
  "data": {
    "current_level": 14,
    "total_xp": 50000,
    "current_level_xp": 1582,
    "xp_to_next_level": 6903,
    "xp_required_for_next_level": 8485,
    "progress_percentage": 18.64
  }
}
```

### Error Response (422)

```json
{
  "success": false,
  "message": "Data yang Anda kirim tidak valid.",
  "errors": {
    "xp": ["The xp field is required."]
  }
}
```

### Catatan Penting
- Endpoint ini untuk utility/calculator
- Berguna untuk preview level sebelum award XP
- Response format sama dengan endpoint user level

---

## 6. GET DAILY XP STATS

### Endpoint
```
GET /api/v1/user/daily-xp-stats
```

### Authorization
- Authenticated user (semua role)
- User hanya bisa melihat stats sendiri

### Query Parameters
Tidak ada.

### Contoh Request

```
GET /api/v1/user/daily-xp-stats
```

### Response Format

```json
{
  "success": true,
  "data": {
    "total_xp_earned": 3500,
    "global_daily_cap": 10000,
    "remaining_xp": 6500,
    "cap_reached": false,
    "cap_reached_at": null,
    "xp_by_source": {
      "lesson_completed": 1500,
      "assignment_submitted": 1000,
      "quiz_passed": 800,
      "forum_post_created": 200
    }
  }
}
```

### Field Explanation

| Field | Deskripsi |
|-------|-----------|
| `total_xp_earned` | Total XP yang sudah earned hari ini |
| `global_daily_cap` | Batas maksimal XP per hari (default: 10,000) |
| `remaining_xp` | Sisa XP yang bisa di-earn hari ini |
| `cap_reached` | Boolean, true jika sudah mencapai cap |
| `cap_reached_at` | Timestamp saat mencapai cap (null jika belum) |
| `xp_by_source` | Breakdown XP per source hari ini |

### Catatan Penting
- Data di-reset setiap hari (00:00)
- Berguna untuk menampilkan daily progress
- Warning user saat mendekati cap
- Mencegah user grinding XP berlebihan

---

## 7. SYNC LEVEL CONFIGURATIONS (Admin)

### Endpoint
```
POST /api/v1/levels/sync
```

### Authorization
- **Superadmin only**

### Content-Type
`application/json`

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `start` | integer | ❌ Tidak | 1 | Level awal untuk sync (min: 1) |
| `end` | integer | ❌ Tidak | 100 | Level akhir untuk sync (max: 100) |

### Contoh Request

#### 1. Sync All Levels (1-100)
```
POST /api/v1/levels/sync
```

#### 2. Sync Specific Range
```
POST /api/v1/levels/sync?start=1&end=50
```

### Response Format

```json
{
  "success": true,
  "message": "Successfully synced 100 level configurations",
  "data": {
    "synced_count": 100,
    "start_level": 1,
    "end_level": 100
  }
}
```

### Error Response (403)

```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

### Business Rules
- Hanya Superadmin yang bisa sync level configs
- Sync akan create/update level configurations berdasarkan formula
- Existing level configs akan di-update (tidak di-delete)
- Cache akan di-clear otomatis setelah sync

### Catatan Penting
- Gunakan endpoint ini saat pertama kali setup atau update formula
- Sync bersifat idempotent (aman dijalankan multiple kali)
- Proses sync cepat (< 1 detik untuk 100 levels)

---

## 8. UPDATE LEVEL CONFIGURATION (Admin)

### Endpoint
```
PUT /api/v1/levels/{id}
```

### Authorization
- **Superadmin only**

### Content-Type
`application/json`

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `id` | integer | ✅ Ya | ID level config |

### Request Body

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `name` | string | ❌ Tidak | max:255 | Nama level (override default) |
| `xp_required` | integer | ❌ Tidak | min:0 | XP required (override formula) |
| `rewards` | json | ❌ Tidak | valid JSON | Rewards untuk level ini |

### Contoh Request

#### 1. Update Level Name
```json
{
  "name": "Elite Master"
}
```

#### 2. Update XP Required
```json
{
  "xp_required": 5000
}
```

#### 3. Update Rewards
```json
{
  "rewards": {
    "badge": "elite_50",
    "bonus_xp": 2000,
    "title": "Elite Master",
    "unlock_features": ["premium_content"]
  }
}
```

#### 4. Update Multiple Fields
```json
{
  "name": "Elite Master",
  "xp_required": 5000,
  "rewards": {
    "badge": "elite_50",
    "bonus_xp": 2000,
    "title": "Elite Master"
  }
}
```

### Response Format

```json
{
  "success": true,
  "message": "Level configuration updated successfully",
  "data": {
    "id": 50,
    "level": 50,
    "name": "Elite Master",
    "xp_required": 5000,
    "rewards": {
      "badge": "elite_50",
      "bonus_xp": 2000,
      "title": "Elite Master",
      "unlock_features": ["premium_content"]
    },
    "created_at": "2026-03-14T10:00:00.000000Z",
    "updated_at": "2026-03-14T11:30:00.000000Z"
  }
}
```

### Error Response (404)

```json
{
  "success": false,
  "message": "Level configuration not found",
  "errors": null
}
```

### Rewards Structure (Recommended)

```json
{
  "badge": "level_milestone_badge_code",
  "bonus_xp": 500,
  "title": "Special Title",
  "unlock_features": ["feature1", "feature2"],
  "custom_field": "custom_value"
}
```

### Catatan Penting
- Update bersifat partial (hanya field yang dikirim yang di-update)
- Cache akan di-clear otomatis setelah update
- Rewards bisa berisi custom fields sesuai kebutuhan
- XP required yang di-update akan override formula calculation

---

## 9. GET LEVEL STATISTICS (Admin)

### Endpoint
```
GET /api/v1/levels/statistics
```

### Authorization
- **Superadmin only**

### Query Parameters
Tidak ada.

### Contoh Request

```
GET /api/v1/levels/statistics
```

### Response Format

```json
{
  "success": true,
  "data": {
    "total_levels": 100,
    "max_level": 100,
    "total_xp_to_max": 6985922,
    "users_by_level": [
      {
        "global_level": 1,
        "count": 150
      },
      {
        "global_level": 2,
        "count": 120
      },
      {
        "global_level": 5,
        "count": 80
      },
      {
        "global_level": 10,
        "count": 45
      },
      {
        "global_level": 15,
        "count": 25
      },
      {
        "global_level": 20,
        "count": 12
      }
    ]
  }
}
```

### Field Explanation

| Field | Deskripsi |
|-------|-----------|
| `total_levels` | Total level configurations yang ada |
| `max_level` | Level maksimal yang tersedia |
| `total_xp_to_max` | Total XP yang dibutuhkan untuk mencapai max level |
| `users_by_level` | Distribusi user per level |

### Catatan Penting
- Endpoint ini untuk monitoring dan analytics
- `users_by_level` menunjukkan distribusi user di setiap level
- Berguna untuk melihat engagement dan progression rate
- Data bisa digunakan untuk adjust rewards atau XP formula

---

## 10. LEVEL UP EVENT SYSTEM

### Overview

Sistem Level Up Event secara otomatis di-trigger saat user naik level. Event ini di-broadcast real-time ke frontend untuk menampilkan notifikasi dan animasi.

### Event Flow

```
User earns XP (lesson completed, assignment submitted, etc)
   ↓
System calculates new level
   ↓
If level increased:
   ↓
Trigger UserLeveledUp Event
   ↓
Broadcast to user channel
   ↓
Award milestone rewards (badge, bonus XP, etc)
   ↓
Frontend shows level up notification
```

### Event Data Structure

```json
{
  "event": "level_up",
  "user_id": 123,
  "old_level": 14,
  "new_level": 15,
  "total_xp": 50000,
  "rewards": {
    "badge": "level_15_milestone",
    "bonus_xp": 200,
    "title": "Intermediate Master"
  },
  "timestamp": "2026-03-14T10:30:00Z"
}
```

### Broadcasting Channel

Event di-broadcast ke channel: `user.{userId}` dengan nama event: `level.up`

### Frontend Implementation (Laravel Echo)

```javascript
// Subscribe to user channel
Echo.channel(`user.${userId}`)
    .listen('.level.up', (event) => {
        console.log('User leveled up!', event);
        
        // Show level up notification
        showLevelUpNotification({
            oldLevel: event.old_level,
            newLevel: event.new_level,
            rewards: event.rewards
        });
        
        // Play celebration animation
        playLevelUpAnimation();
        
        // Confetti effect
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
        
        // Refresh user stats
        queryClient.invalidateQueries(['user-level']);
        queryClient.invalidateQueries(['user-stats']);
    });
```

### Automatic Reward Processing

Saat user level up, sistem otomatis:

1. **Award Milestone Badge** - Jika level config memiliki badge di rewards
2. **Award Bonus XP** - Jika level config memiliki bonus_xp di rewards
3. **Log Event** - Untuk monitoring dan analytics
4. **Broadcast Event** - Ke frontend untuk real-time notification

### Example Rewards by Level

| Level | Badge | Bonus XP | Title |
|-------|-------|----------|-------|
| 10 | level_10_milestone | 100 | - |
| 20 | level_20_milestone | 200 | - |
| 25 | level_25_milestone | 500 | Competent |
| 50 | level_50_milestone | 1000 | Advanced |
| 75 | level_75_milestone | 1500 | Master |
| 100 | level_100_milestone | 2000 | Legendary Master |

---

## 11. XP SOURCE MANAGEMENT

### Overview

Sistem XP Source Management mengatur semua sumber XP di platform dengan konfigurasi yang fleksibel dan anti-abuse mechanism built-in.

### XP Sources Table

Semua sumber XP dikonfigurasi di tabel `xp_sources` dengan field:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `code` | string | Unique identifier (e.g., 'lesson_completed') |
| `name` | string | Display name |
| `description` | text | Deskripsi aktivitas |
| `xp_amount` | integer | XP yang diberikan |
| `cooldown_seconds` | integer | Cooldown antar action yang sama |
| `daily_limit` | integer | Max berapa kali per hari (null = unlimited) |
| `daily_xp_cap` | integer | Max XP per hari dari source ini |
| `allow_multiple` | boolean | Bisa earn multiple times dari source_id yang sama |
| `is_active` | boolean | Active/inactive |
| `metadata` | json | Additional config |

### Default XP Sources

#### Learning Activities

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple | Status |
|------|----|---------:|------------:|-------------:|:--------------:|:------:|
| lesson_completed | 50 | 10s | - | 5,000 | ✅ | ✅ Integrated |
| assignment_submitted | 100 | - | - | - | ❌ | ✅ Integrated |
| quiz_passed | 80 | - | - | - | ❌ | ✅ Integrated |
| unit_completed | 200 | - | - | - | ❌ | ✅ Integrated |
| course_completed | 500 | - | - | - | ❌ | ✅ Integrated |

#### Engagement Activities

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple | Status |
|------|----|---------:|------------:|-------------:|:--------------:|:------:|
| daily_login | 10 | 24h | 1 | 10 | ✅ | ✅ Integrated |
| streak_7_days | 200 | - | - | - | ✅ | ✅ Integrated |
| streak_30_days | 1,000 | - | - | - | ✅ | ✅ Integrated |

#### Social Activities

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple | Status |
|------|----|---------:|------------:|-------------:|:--------------:|:------:|
| forum_post_created | 20 | 60s | 10 | 200 | ✅ | ✅ Integrated |
| forum_reply_created | 10 | 30s | 20 | 200 | ✅ | ✅ Integrated |
| forum_liked | 5 | - | - | 100 | ✅ | ✅ Integrated |

#### Quality Activities

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple | Status |
|------|----|---------:|------------:|-------------:|:--------------:|:------:|
| perfect_score | 50 | - | - | - | ✅ | ✅ Integrated |
| first_submission | 30 | - | - | - | ✅ | ✅ Integrated |

### Anti-Abuse Mechanisms

#### 1. Cooldown System
Mencegah spam dengan membatasi frekuensi action yang sama.

**Example:**
- `lesson_completed` memiliki cooldown 10 detik
- User tidak bisa earn XP dari lesson lain dalam 10 detik

#### 2. Daily Limit
Membatasi berapa kali user bisa earn XP dari source yang sama per hari.

**Example:**
- `daily_login` memiliki daily limit 1
- User hanya bisa earn XP login sekali per hari

#### 3. Daily XP Cap
Membatasi total XP yang bisa didapat dari source tertentu per hari.

**Example:**
- `lesson_completed` memiliki daily XP cap 5,000
- Setelah earn 5,000 XP dari lesson, tidak bisa earn lagi hari itu

#### 4. Allow Multiple
Mengatur apakah user bisa earn XP multiple times dari source_id yang sama.

**Example:**
- `assignment_submitted` allow_multiple = false
- User hanya bisa earn XP sekali per assignment

### XP Award Flow

```
1. User completes activity
   ↓
2. System checks XP source config
   ↓
3. Validate anti-abuse rules:
   - Check cooldown
   - Check daily limit
   - Check daily XP cap
   - Check allow_multiple
   ↓
4. If all checks pass:
   - Award XP
   - Check level up
   - Trigger events
   ↓
5. If any check fails:
   - Reject XP award
   - Log attempt (optional)
```

### Backend Usage

```php
use Modules\Gamification\Services\GamificationService;

// Award XP with automatic source config lookup
$gamification->awardXp(
    userId: $user->id,
    points: 0, // Will be overridden by XP source config
    reason: 'lesson_completed', // XP source code
    sourceType: 'lesson',
    sourceId: $lesson->id,
    options: [
        'description' => 'Completed lesson: Introduction to PHP',
        'allow_multiple' => true, // Optional override
    ]
);

// System automatically:
// 1. Looks up 'lesson_completed' in xp_sources table
// 2. Uses configured XP amount (50)
// 3. Applies cooldown (10 seconds)
// 4. Checks daily XP cap (5,000)
// 5. Checks global daily cap (10,000)
// 6. Awards XP if all checks pass
// 7. Logs transaction with IP & user agent
// 8. Triggers level up event if applicable
// 9. Returns XP info in API response
```

### Event-Driven Integration

Sistem menggunakan event-driven architecture untuk automatic XP awarding:

```php
// Example: Quiz Completion
// File: Levl-BE/Modules/Learning/app/Services/QuizSubmissionService.php

public function submit(QuizSubmission $submission, int $actorId): QuizSubmission
{
    return DB::transaction(function () use ($submission) {
        // ... grading logic ...
        
        $gradedSubmission = $this->autoGrade($submission);
        
        // Auto-dispatch QuizCompleted event
        if ($gradedSubmission->grading_status === QuizGradingStatus::Graded) {
            event(new \Modules\Learning\Events\QuizCompleted($gradedSubmission));
        }
        
        return $gradedSubmission;
    });
}

// Listener automatically awards XP
// File: Levl-BE/Modules/Gamification/app/Listeners/AwardXpForQuizPassed.php
public function handle(QuizCompleted $event): void
{
    $submission = $event->submission;
    
    if (!$submission->isPassed()) {
        return; // Only award if passed
    }
    
    // Award 80 XP for passing quiz
    $this->gamification->awardXp(
        userId: $submission->user_id,
        points: 0, // Uses xp_sources config
        reason: 'quiz_passed',
        sourceType: 'quiz',
        sourceId: $submission->quiz_id
    );
    
    // Award bonus for perfect score
    if ($submission->final_score >= 100) {
        $this->gamification->awardXp(
            userId: $submission->user_id,
            points: 0,
            reason: 'perfect_score',
            sourceType: 'quiz',
            sourceId: $submission->quiz_id
        );
    }
}
```

### Customizing XP Sources

Admin dapat mengubah konfigurasi XP sources melalui database:

```sql
-- Increase XP for lesson completion
UPDATE xp_sources 
SET xp_amount = 75 
WHERE code = 'lesson_completed';

-- Remove daily cap for assignments
UPDATE xp_sources 
SET daily_xp_cap = NULL 
WHERE code = 'assignment_submitted';

-- Disable forum XP temporarily
UPDATE xp_sources 
SET is_active = false 
WHERE code LIKE 'forum_%';
```

### Monitoring XP Sources

Track XP distribution by source:

```sql
-- XP earned by source (last 30 days)
SELECT 
    reason,
    COUNT(*) as times_earned,
    SUM(points) as total_xp,
    AVG(points) as avg_xp
FROM points
WHERE created_at >= NOW() - INTERVAL '30 days'
GROUP BY reason
ORDER BY total_xp DESC;
```

---

## 12. XP TRANSACTION LOG

### Overview

Sistem XP Transaction Log menyimpan complete audit trail dari semua XP transactions untuk keperluan audit, analytics, dan rollback.

### Enhanced Points Table

Tabel `points` telah di-enhance dengan field tambahan:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | Transaction ID |
| `user_id` | integer | User yang earn XP |
| `points` | integer | Jumlah XP |
| `source_type` | string | Tipe source (lesson, assignment, system, dll) |
| `source_id` | integer | ID dari source |
| `reason` | string | Reason code (lesson_completed, dll) |
| `xp_source_code` | string | Code dari xp_sources table |
| `old_level` | integer | Level sebelum earn XP |
| `new_level` | integer | Level setelah earn XP |
| `triggered_level_up` | boolean | True jika transaction ini trigger level up |
| `metadata` | json | Additional data |
| `ip_address` | string | IP address user |
| `user_agent` | string | Browser/app user agent |
| `created_at` | datetime | Timestamp transaction |

### Transaction Log Example

```json
{
  "id": 1002,
  "user_id": 123,
  "points": 100,
  "source_type": "assignment",
  "source_id": 456,
  "reason": "assignment_submitted",
  "xp_source_code": "assignment_submitted",
  "old_level": 14,
  "new_level": 15,
  "triggered_level_up": true,
  "metadata": {
    "assignment_title": "Final Project",
    "score": 95
  },
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2026-03-14T10:30:00Z"
}
```

### Use Cases

#### 1. Audit Trail
Tracking semua XP transactions untuk compliance dan security:

```sql
-- Get user's complete XP history
SELECT 
    created_at,
    points as xp,
    reason,
    source_type,
    old_level,
    new_level,
    triggered_level_up,
    ip_address
FROM points
WHERE user_id = 123
ORDER BY created_at DESC;
```

#### 2. Analytics
Analyze XP distribution dan user behavior:

```sql
-- XP earned by source (last 30 days)
SELECT 
    xp_source_code,
    COUNT(*) as transactions,
    SUM(points) as total_xp,
    AVG(points) as avg_xp
FROM points
WHERE created_at >= NOW() - INTERVAL '30 days'
GROUP BY xp_source_code
ORDER BY total_xp DESC;

-- Level up frequency
SELECT 
    DATE(created_at) as date,
    COUNT(*) as level_ups
FROM points
WHERE triggered_level_up = true
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

#### 3. Fraud Detection
Detect suspicious patterns:

```sql
-- Users with unusual XP patterns
SELECT 
    user_id,
    COUNT(*) as transactions,
    SUM(points) as total_xp,
    COUNT(DISTINCT ip_address) as unique_ips
FROM points
WHERE created_at >= CURRENT_DATE
GROUP BY user_id
HAVING COUNT(*) > 100  -- More than 100 transactions per day
   OR COUNT(DISTINCT ip_address) > 5  -- Multiple IPs
ORDER BY transactions DESC;
```

#### 4. Rollback (if needed)
Reverse specific transaction:

```sql
-- Rollback transaction
BEGIN;

-- Get transaction details
SELECT * FROM points WHERE id = 1002;

-- Reverse XP
UPDATE user_gamification_stats
SET total_xp = total_xp - 100,
    global_level = 14  -- Restore old level
WHERE user_id = 123;

-- Mark as reversed
UPDATE points
SET metadata = jsonb_set(
    COALESCE(metadata, '{}'::jsonb),
    '{reversed}',
    'true'::jsonb
)
WHERE id = 1002;

COMMIT;
```

### Benefits

1. **Complete Audit Trail** - Semua XP transactions tercatat
2. **IP Tracking** - Detect multi-account abuse
3. **Level Change History** - Track progression
4. **Analytics Ready** - Data untuk business intelligence
5. **Rollback Capability** - Undo transactions jika needed

---

## 13. GLOBAL DAILY XP CAP

### Overview

Global Daily XP Cap membatasi total XP yang bisa di-earn user per hari untuk mencegah grinding dan menjaga fair progression.

### Configuration

**Default Cap**: 10,000 XP per day

Set di environment variable:
```env
GAMIFICATION_GLOBAL_DAILY_XP_CAP=10000
```

Atau di config file:
```php
// config/gamification.php
'global_daily_cap' => 10000,
```

### xp_daily_caps Table

Tracking daily XP per user:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `user_id` | integer | User ID |
| `date` | date | Tanggal (unique per user) |
| `total_xp_earned` | integer | Total XP earned hari ini |
| `global_daily_cap` | integer | Cap limit (default: 10,000) |
| `cap_reached` | boolean | True jika sudah reach cap |
| `cap_reached_at` | datetime | Timestamp saat reach cap |
| `xp_by_source` | json | Breakdown XP per source |

### How It Works

```
1. User earns XP
   ↓
2. Check today's total XP
   ↓
3. If (current + new) > cap:
   - Reject XP award
   - Log attempt
   - Return null
   ↓
4. If within cap:
   - Award XP
   - Update daily total
   - Track by source
   ↓
5. If cap reached:
   - Set cap_reached = true
   - Record timestamp
```

### API Endpoint

Get user's daily XP stats:

```
GET /api/v1/user/daily-xp-stats
```

Response:
```json
{
  "total_xp_earned": 8500,
  "global_daily_cap": 10000,
  "remaining_xp": 1500,
  "cap_reached": false,
  "xp_by_source": {
    "lesson_completed": 3500,
    "assignment_submitted": 3000,
    "quiz_passed": 1600,
    "forum_post_created": 400
  }
}
```

### Frontend Integration

#### Daily XP Progress Bar

```typescript
interface DailyXpProgressProps {
  earned: number;
  cap: number;
  bySource: Record<string, number>;
}

function DailyXpProgress({ earned, cap, bySource }: DailyXpProgressProps) {
  const percentage = (earned / cap) * 100;
  const remaining = cap - earned;
  
  return (
    <div>
      <h3>Daily XP Progress</h3>
      <ProgressBar value={earned} max={cap} />
      <p>{earned.toLocaleString()} / {cap.toLocaleString()} XP</p>
      
      {remaining < 1000 && (
        <Alert variant="warning">
          Only {remaining} XP remaining today!
        </Alert>
      )}
      
      {earned >= cap && (
        <Alert variant="info">
          Daily XP cap reached! Come back tomorrow.
        </Alert>
      )}
      
      <h4>XP by Source</h4>
      <PieChart data={Object.entries(bySource)} />
    </div>
  );
}
```

#### Real-time Updates

```typescript
// Fetch daily stats
const { data: dailyStats } = useQuery({
  queryKey: ['daily-xp-stats'],
  queryFn: () => fetch('/api/v1/user/daily-xp-stats').then(r => r.json()),
  refetchInterval: 60000, // Refetch every minute
});

// Listen to XP awards
Echo.channel(`user.${userId}`)
  .listen('.xp.awarded', () => {
    queryClient.invalidateQueries(['daily-xp-stats']);
  });
```

### Customization

#### Per-User Custom Cap

```sql
-- Set higher cap for VIP users
UPDATE xp_daily_caps
SET global_daily_cap = 20000
WHERE user_id IN (SELECT id FROM users WHERE is_vip = true)
  AND date = CURRENT_DATE;
```

#### Temporary Cap Adjustment

```sql
-- Double XP event (increase cap for all users)
UPDATE xp_daily_caps
SET global_daily_cap = 20000
WHERE date = '2026-03-15';  -- Event date
```

### Benefits

1. **Fair Progression** - Prevents power users from advancing too quickly
2. **Engagement Pacing** - Encourages daily return visits
3. **Anti-Grinding** - Limits XP farming
4. **Server Load** - Reduces excessive API calls
5. **Game Balance** - Maintains competitive fairness

### Monitoring

```sql
-- Users who reached cap today
SELECT 
    u.id,
    u.name,
    xdc.total_xp_earned,
    xdc.cap_reached_at
FROM xp_daily_caps xdc
JOIN users u ON u.id = xdc.user_id
WHERE xdc.date = CURRENT_DATE
  AND xdc.cap_reached = true
ORDER BY xdc.cap_reached_at;

-- Average daily XP (last 30 days)
SELECT 
    date,
    AVG(total_xp_earned) as avg_xp,
    MAX(total_xp_earned) as max_xp,
    COUNT(CASE WHEN cap_reached THEN 1 END) as users_reached_cap
FROM xp_daily_caps
WHERE date >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY date
ORDER BY date DESC;
```

---

## 14. AUTHORIZATION MATRIX

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| List Levels | ✅ | ✅ | ✅ | ✅ |
| Get Progression Table | ✅ | ✅ | ✅ | ✅ |
| Get User Level | ✅ (own) | ✅ (own) | ✅ (own) | ✅ (own) |
| Get Daily XP Stats | ✅ (own) | ✅ (own) | ✅ (own) | ✅ (own) |
| Calculate Level | ✅ | ✅ | ✅ | ✅ |
| Sync Levels | ❌ | ❌ | ❌ | ✅ |
| Update Level Config | ❌ | ❌ | ❌ | ✅ |
| Get Statistics | ❌ | ❌ | ❌ | ✅ |

---

## 15. UI/UX IMPLEMENTATION NOTES

### 15.1 User Level Display

#### Level Badge Component
```typescript
interface LevelBadgeProps {
  level: number;
  name: string;
  size?: 'sm' | 'md' | 'lg';
}

// Display: "Level 14 • Competent"
```

**Rekomendasi**:
- Tampilkan level number dengan badge
- Tampilkan level name sebagai subtitle
- Gunakan warna berbeda per tier (Beginner=gray, Novice=blue, Expert=purple, Master=gold)

#### Level Progress Bar
```typescript
interface LevelProgressProps {
  currentLevelXp: number;
  xpRequiredForNextLevel: number;
  progressPercentage: number;
}

// Display: Progress bar dengan label "1,582 / 8,485 XP (18.64%)"
```

**Rekomendasi**:
- Progress bar dengan gradient color
- Tooltip menunjukkan XP to next level
- Animasi smooth saat XP bertambah
- Celebration animation saat level up

---

### 15.2 Level Progression Table

#### Table View
```typescript
interface LevelProgressionRow {
  level: number;
  xpRequired: number;
  totalXp: number;
  name: string;
}
```

**Columns**:
- Level (dengan badge)
- XP Required (formatted number)
- Total XP (formatted number)
- Tier Name

**Features**:
- Highlight current user level
- Sticky header saat scroll
- Pagination atau infinite scroll
- Filter by tier name

---

### 15.3 Level Calculator (Utility)

#### Calculator Form
```typescript
interface CalculatorForm {
  xp: number;
}

// Input: XP amount
// Output: Level info (level, progress, etc)
```

**Use Cases**:
- Preview level sebelum award XP
- Simulasi progression
- Educational tool untuk user

---

### 15.4 Admin Level Management

#### Sync Levels Interface
```typescript
interface SyncLevelsForm {
  startLevel: number;
  endLevel: number;
}

// Button: "Sync Levels"
// Confirmation modal before sync
```

**Features**:
- Range selector (start-end)
- Preview table sebelum sync
- Progress indicator during sync
- Success/error notification

#### Update Level Config Form
```typescript
interface UpdateLevelForm {
  name?: string;
  xpRequired?: number;
  rewards?: {
    badge?: string;
    bonusXp?: number;
    title?: string;
    unlockFeatures?: string[];
  };
}
```

**Features**:
- Inline edit di table
- Modal form untuk detailed edit
- JSON editor untuk rewards
- Preview rewards structure

---

### 15.5 Level Statistics Dashboard

#### Charts & Visualizations
1. **User Distribution Chart** (Bar Chart)
   - X-axis: Level
   - Y-axis: User count
   - Highlight: Current average level

2. **Progression Curve** (Line Chart)
   - X-axis: Level
   - Y-axis: XP required
   - Show: Formula curve

3. **Level Tiers** (Pie Chart)
   - Segments: Tier names
   - Values: User count per tier

**Metrics Cards**:
- Total Users
- Average Level
- Highest Level User
- Total XP Awarded

---

### 15.6 Level Up Notification

#### Notification Component
```typescript
interface LevelUpNotification {
  oldLevel: number;
  newLevel: number;
  rewards?: {
    badge?: string;
    bonusXp?: number;
    title?: string;
  };
}
```

**Display**:
- Modal/Toast dengan celebration animation
- Show old level → new level
- List rewards yang didapat
- CTA: "Continue" atau "View Profile"

**Animation**:
- Confetti effect
- Level badge animation
- Sound effect (optional)

**Real-time Implementation:**
```typescript
// Listen to level up event
useEffect(() => {
  const channel = Echo.channel(`user.${userId}`);
  
  channel.listen('.level.up', (event) => {
    // Show notification modal
    setLevelUpModal({
      show: true,
      oldLevel: event.old_level,
      newLevel: event.new_level,
      rewards: event.rewards
    });
    
    // Play confetti
    confetti({
      particleCount: 100,
      spread: 70,
      origin: { y: 0.6 }
    });
    
    // Play sound
    playSound('/sounds/level-up.mp3');
  });
  
  return () => channel.stopListening('.level.up');
}, [userId]);
```

---

### 15.7 Daily XP Progress Display

#### Daily XP Progress Component
```typescript
interface DailyXpProgressProps {
  totalXpEarned: number;
  globalDailyCap: number;
  remainingXp: number;
  capReached: boolean;
  xpBySource: Record<string, number>;
}

function DailyXpProgress(props: DailyXpProgressProps) {
  const percentage = (props.totalXpEarned / props.globalDailyCap) * 100;
  
  return (
    <Card>
      <h3>Daily XP Progress</h3>
      
      {/* Progress Bar */}
      <ProgressBar 
        value={props.totalXpEarned}
        max={props.globalDailyCap}
        color={percentage > 90 ? 'red' : percentage > 70 ? 'yellow' : 'green'}
      />
      
      {/* Stats */}
      <div className="stats">
        <span>{props.totalXpEarned.toLocaleString()} XP earned</span>
        <span>{props.remainingXp.toLocaleString()} XP remaining</span>
      </div>
      
      {/* Warnings */}
      {props.remainingXp < 1000 && !props.capReached && (
        <Alert variant="warning">
          ⚠️ You're approaching your daily XP cap! 
          Only {props.remainingXp} XP remaining today.
        </Alert>
      )}
      
      {props.capReached && (
        <Alert variant="info">
          🎯 Daily XP cap reached! You've earned the maximum XP for today.
          Come back tomorrow to continue your progress!
        </Alert>
      )}
      
      {/* XP Breakdown */}
      <div className="xp-breakdown">
        <h4>XP by Activity</h4>
        {Object.entries(props.xpBySource).map(([source, xp]) => (
          <div key={source} className="source-item">
            <span>{formatSourceName(source)}</span>
            <span>{xp} XP</span>
          </div>
        ))}
      </div>
      
      {/* Pie Chart */}
      <PieChart 
        data={Object.entries(props.xpBySource).map(([name, value]) => ({
          name: formatSourceName(name),
          value
        }))}
      />
    </Card>
  );
}
```

**Rekomendasi**:
- Tampilkan di dashboard utama
- Update real-time saat user earn XP
- Warning saat mendekati cap (< 1000 XP remaining)
- Celebration message saat reach cap
- Breakdown XP per source dengan chart

#### Real-time Updates

```typescript
// Fetch daily stats with auto-refresh
const { data: dailyStats, refetch } = useQuery({
  queryKey: ['daily-xp-stats'],
  queryFn: fetchDailyXpStats,
  refetchInterval: 60000, // Refetch every minute
});

// Listen to XP awards
useEffect(() => {
  const channel = Echo.channel(`user.${userId}`);
  
  channel.listen('.xp.awarded', (event) => {
    // Refetch daily stats
    refetch();
    
    // Show toast notification
    toast.success(`+${event.xp} XP earned!`);
  });
  
  return () => channel.stopListening('.xp.awarded');
}, [userId, refetch]);
```

---

### 15.8 Responsive Design

#### Mobile View
- Compact level badge
- Simplified progress bar
- Swipeable progression table
- Bottom sheet untuk level details

#### Desktop View
- Full level badge dengan name
- Detailed progress bar dengan tooltip
- Full table dengan all columns
- Sidebar untuk level info

---

### 15.9 Accessibility

#### Screen Reader Support
- ARIA labels untuk level badge
- ARIA live region untuk level up
- Keyboard navigation untuk table
- Focus management untuk modals

#### High Contrast Mode
- Clear level badge borders
- High contrast progress bar
- Readable tier colors
- Sufficient color contrast

---

### 15.10 Performance Optimization

#### Caching Strategy
```typescript
// Cache user level for 5 minutes
const { data: userLevel } = useQuery({
  queryKey: ['user-level'],
  queryFn: fetchUserLevel,
  staleTime: 5 * 60 * 1000,
});

// Cache level configs for 1 hour
const { data: levelConfigs } = useQuery({
  queryKey: ['level-configs'],
  queryFn: fetchLevelConfigs,
  staleTime: 60 * 60 * 1000,
});
```

#### Lazy Loading
- Load progression table on demand
- Infinite scroll untuk large tables
- Lazy load statistics charts

---

### 15.11 Error Handling

#### Common Errors

**1. User Level Not Found**
```typescript
// Fallback: Show level 0 atau "No level yet"
if (!userLevel) {
  return <EmptyState message="Start learning to gain XP!" />;
}
```

**2. Sync Failed**
```typescript
// Show error message dengan retry button
<ErrorAlert 
  message="Failed to sync levels" 
  onRetry={handleRetry}
/>
```

**3. Invalid XP Input**
```typescript
// Validation error di calculator
<FormError field="xp" message="XP must be a positive number" />
```

---

## Workflow Rekomendasi

### User Workflow

```
1. User earns XP (lesson completed, assignment submitted, etc)
   ↓
2. System calculates new level
   ↓
3. If level up:
   - Show level up notification
   - Award milestone rewards
   - Update user profile
   ↓
4. User views level progress in profile/dashboard
```

### Admin Workflow

```
1. Initial Setup:
   - Sync all levels (1-100)
   - Verify level configs
   ↓
2. Customization (Optional):
   - Update specific level names
   - Customize rewards
   - Adjust XP requirements
   ↓
3. Monitoring:
   - View level statistics
   - Analyze user distribution
   - Adjust if needed
```

---

## HTTP Status Codes

- `200` - Success (GET, POST, PUT)
- `401` - Unauthorized (token invalid/tidak login)
- `403` - Forbidden (role tidak punya akses)
- `404` - Not Found (level config tidak ditemukan)
- `422` - Validation Error (input tidak valid)
- `500` - Server Error

---

## Tips Implementasi

### 1. Real-time Level Update
```typescript
// Listen to XP changes
useEffect(() => {
  const channel = pusher.subscribe(`user.${userId}`);
  channel.bind('xp-awarded', (data) => {
    // Refetch user level
    queryClient.invalidateQueries(['user-level']);
  });
}, [userId]);
```

### 2. Level Progress Animation
```typescript
// Animate progress bar
<motion.div
  initial={{ width: 0 }}
  animate={{ width: `${progressPercentage}%` }}
  transition={{ duration: 0.5, ease: "easeOut" }}
/>
```

### 3. Level Badge Styling
```css
/* Tier colors */
.level-beginner { background: #9CA3AF; }
.level-novice { background: #3B82F6; }
.level-competent { background: #10B981; }
.level-intermediate { background: #F59E0B; }
.level-proficient { background: #EF4444; }
.level-advanced { background: #8B5CF6; }
.level-expert { background: #EC4899; }
.level-master { background: #F59E0B; }
.level-grandmaster { background: #DC2626; }
.level-legendary { background: linear-gradient(45deg, #F59E0B, #DC2626); }
```

---

## Changelog

### Version 2.0 (14 Maret 2026) - 100% Integration Complete! 🎉
- **MAJOR UPDATE**: Achieved 100% XP source integration (13/13)
- Added Quiz Completion Integration
  - Created `QuizCompleted` event
  - Implemented `AwardXpForQuizPassed` listener
  - Auto-dispatch on quiz submission
  - Awards 80 XP + 50 XP bonus for perfect score
- Added Daily Login & Streak System
  - Created `UserLoggedIn` event
  - Implemented `AwardXpForDailyLogin` listener
  - Created `TrackDailyLogin` middleware
  - Awards 10 XP daily + streak bonuses (200 XP for 7 days, 1000 XP for 30 days)
- Enhanced XP Info in API Responses
  - Created `IncludesXpInfo` trait for controllers
  - Created `XpAwardResource` for consistent formatting
  - Created `AppendXpInfoToResponse` middleware for automatic XP info
  - All API responses now include gamification data
- Event-Driven Architecture
  - All XP sources now use event-listener pattern
  - Automatic XP awarding on user actions
  - Real-time level up notifications
  - Complete integration with Learning, Grading, Forums modules
- Updated documentation with:
  - 100% integration status
  - Event-driven examples
  - Real-time implementation guides
  - Complete XP source status table

### Version 1.3 (14 Maret 2026)
- Added XP Transaction Log system
  - Enhanced points table with transaction metadata
  - IP address and user agent tracking
  - Level change tracking
  - Complete audit trail
- Added Global Daily XP Cap (10,000 XP/day)
  - xp_daily_caps table
  - Daily XP stats API endpoint
  - XP breakdown by source
  - Cap reached notifications
- Added Daily XP Stats endpoint (`GET /api/v1/user/daily-xp-stats`)
- Enhanced documentation with transaction log and daily cap sections

### Version 1.2 (14 Maret 2026)
- Added Level Up Event System with real-time broadcasting
- Added XP Source Management system
- Added comprehensive anti-abuse mechanisms:
  - Cooldown system
  - Daily limits
  - Daily XP caps
  - Allow multiple controls
- Enhanced documentation with event flow and XP source tables

### Version 1.1 (14 Maret 2026)
- Removed description column reference (not in database schema)
- Clarified rewards column usage and structure

### Version 1.0 (14 Maret 2026)
- Initial release
- Formula: `XP(level) = 100 × level^1.6`
- 7 public endpoints
- 3 admin endpoints
- Complete UI/UX guidelines
- Level tiers (10 tiers)
- Milestone rewards system

---

**Versi**: 2.0 - 100% Complete  
**Terakhir Update**: 14 Maret 2026  
**Status**: ✅ Production Ready  
**Coverage**: 100% (13/13 XP sources integrated)  
**Kontak**: Backend Team

---

## Integration Status Summary

### ✅ Fully Integrated Modules (100%)

| Module | Events | XP Sources | Status |
|--------|--------|------------|--------|
| Schemes | 3 | lesson_completed, unit_completed, course_completed | ✅ Complete |
| Learning | 2 | assignment_submitted, quiz_passed, first_submission | ✅ Complete |
| Grading | 2 | perfect_score | ✅ Complete |
| Forums | 3 | forum_post_created, forum_reply_created, forum_liked | ✅ Complete |
| Gamification | 2 | daily_login, streak_7_days, streak_30_days | ✅ Complete |

**Total**: 13/13 XP sources (100%) ✅

### 🎯 Key Features

- ✅ Event-driven architecture
- ✅ Real-time XP notifications
- ✅ Automatic level up detection
- ✅ Complete anti-abuse system
- ✅ Transaction logging with IP tracking
- ✅ Global daily XP cap (10,000)
- ✅ XP info in all API responses
- ✅ Streak system for engagement
- ✅ Perfect score bonuses
- ✅ First submission bonuses

### 📚 Related Documentation

- `INTEGRATION_ANALYSIS_REPORT.md` - Complete integration analysis
- `100_PERCENT_INTEGRATION_COMPLETE.md` - Phase 3 implementation summary
- `COMPLETE_INTEGRATION_GUIDE.md` - Full integration guide
- `XP_QUICK_REFERENCE.md` - Quick reference for developers
