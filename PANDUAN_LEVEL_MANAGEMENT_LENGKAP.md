# Panduan Lengkap Level Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap API Level Management untuk kebutuhan gamification system. Level menggunakan formula `XP(level) = 100 × level^1.6` untuk progression yang smooth dan engaging.

---

## Daftar Isi

1. [Level Overview](#1-level-overview)
2. [List Level Configurations](#2-list-level-configurations)
3. [Get Level Progression Table](#3-get-level-progression-table)
4. [Get User Current Level](#4-get-user-current-level)
5. [Calculate Level from XP](#5-calculate-level-from-xp)
6. [Sync Level Configurations (Admin)](#6-sync-level-configurations-admin)
7. [Update Level Configuration (Admin)](#7-update-level-configuration-admin)
8. [Get Level Statistics (Admin)](#8-get-level-statistics-admin)
9. [Level Up Event System](#9-level-up-event-system)
10. [XP Source Management](#10-xp-source-management)
11. [Authorization Matrix](#11-authorization-matrix)
12. [UI/UX Implementation Notes](#12-uiux-implementation-notes)

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

## 6. SYNC LEVEL CONFIGURATIONS (Admin)

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

## 7. UPDATE LEVEL CONFIGURATION (Admin)

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

## 8. GET LEVEL STATISTICS (Admin)

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

## 9. LEVEL UP EVENT SYSTEM

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

## 10. XP SOURCE MANAGEMENT

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

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple |
|------|----|---------:|------------:|-------------:|:--------------:|
| lesson_completed | 50 | 10s | - | 5,000 | ✅ |
| assignment_submitted | 100 | - | - | - | ❌ |
| quiz_passed | 80 | - | - | - | ❌ |
| unit_completed | 200 | - | - | - | ❌ |
| course_completed | 500 | - | - | - | ❌ |

#### Engagement Activities

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple |
|------|----|---------:|------------:|-------------:|:--------------:|
| daily_login | 10 | 24h | 1 | 10 | ✅ |
| streak_7_days | 200 | - | - | - | ✅ |
| streak_30_days | 1,000 | - | - | - | ✅ |

#### Social Activities

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple |
|------|----|---------:|------------:|-------------:|:--------------:|
| forum_post_created | 20 | 60s | 10 | 200 | ✅ |
| forum_reply_created | 10 | 30s | 20 | 200 | ✅ |
| forum_liked | 5 | - | - | 100 | ✅ |

#### Quality Activities

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple |
|------|----|---------:|------------:|-------------:|:--------------:|
| perfect_score | 50 | - | - | - | ✅ |
| first_submission | 30 | - | - | - | ✅ |

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
use Modules\Gamification\Services\Support\PointManager;

// Award XP with automatic source config lookup
$pointManager->awardXp(
    userId: $user->id,
    points: 0, // Will be overridden by XP source config
    reason: 'lesson_completed', // XP source code
    sourceType: 'lesson',
    sourceId: $lesson->id
);

// System automatically:
// 1. Looks up 'lesson_completed' in xp_sources table
// 2. Uses configured XP amount (50)
// 3. Applies cooldown (10 seconds)
// 4. Checks daily XP cap (5,000)
// 5. Awards XP if all checks pass
// 6. Triggers level up event if applicable
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

## 11. AUTHORIZATION MATRIX

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| List Levels | ✅ | ✅ | ✅ | ✅ |
| Get Progression Table | ✅ | ✅ | ✅ | ✅ |
| Get User Level | ✅ (own) | ✅ (own) | ✅ (own) | ✅ (own) |
| Calculate Level | ✅ | ✅ | ✅ | ✅ |
| Sync Levels | ❌ | ❌ | ❌ | ✅ |
| Update Level Config | ❌ | ❌ | ❌ | ✅ |
| Get Statistics | ❌ | ❌ | ❌ | ✅ |

---

## 12. UI/UX IMPLEMENTATION NOTES

### 12.1 User Level Display

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

### 12.2 Level Progression Table

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

### 12.3 Level Calculator (Utility)

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

### 12.4 Admin Level Management

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

### 12.5 Level Statistics Dashboard

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

### 12.6 Level Up Notification

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

### 12.7 Responsive Design

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

### 12.8 Accessibility

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

### 12.9 Performance Optimization

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

### 12.10 Error Handling

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

**Versi**: 1.2  
**Terakhir Update**: 14 Maret 2026  
**Kontak**: Backend Team
