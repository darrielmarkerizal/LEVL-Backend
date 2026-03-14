# 📛 Dokumentasi Lengkap Manajemen Badge Backend

## 📋 Daftar Isi
1. [Arsitektur Sistem](#arsitektur-sistem)
2. [Struktur Database](#struktur-database)
3. [API Endpoints](#api-endpoints)
4. [Alur Kerja Badge](#alur-kerja-badge)
5. [Badge Rules Engine](#badge-rules-engine)
6. [Integrasi Event-Driven](#integrasi-event-driven)
7. [Anti-Farming Mechanisms](#anti-farming-mechanisms)
8. [Contoh Penggunaan](#contoh-penggunaan)

---

## 🏗️ Arsitektur Sistem

### Layer Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    HTTP Layer                            │
│  BadgesController → Authorization → Validation           │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│                   Service Layer                          │
│  BadgeService → BadgeManager → BadgeRuleEvaluator       │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│                 Repository Layer                         │
│  BadgeRepository → GamificationRepository                │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│                    Model Layer                           │
│  Badge → BadgeRule → UserBadge                          │
└─────────────────────────────────────────────────────────┘
```

### Komponen Utama

#### 1. **BadgesController**
- Path: `app/Http/Controllers/BadgesController.php`
- Fungsi: Handle HTTP requests untuk CRUD badge
- Authorization: Menggunakan BadgePolicy

#### 2. **BadgeService**
- Path: `app/Services/BadgeService.php`
- Fungsi: Business logic untuk badge management
- Fitur:
  - Pagination dengan caching (5 menit)
  - Create/Update badge dengan media handling
  - Sync badge rules
  - Cache invalidation otomatis

#### 3. **BadgeManager**
- Path: `app/Services/Support/BadgeManager.php`
- Fungsi: Core logic untuk award badge ke user
- Fitur:
  - Auto-create badge jika belum ada
  - Prevent duplicate badge (unique constraint)
  - Transaction-safe

#### 4. **BadgeRuleEvaluator**
- Path: `app/Services/Support/BadgeRuleEvaluator.php`
- Fungsi: Evaluasi kondisi badge secara dinamis
- Fitur:
  - Rule caching (1 jam)
  - Conditional evaluation
  - Multi-criteria support

---

## 🗄️ Struktur Database

### Tabel: `badges`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| code | string(100) | Unique identifier (e.g., `first_step`) |
| name | string(255) | Nama badge (e.g., "Langkah Pertama") |
| description | text | Deskripsi badge |
| icon_path | string(255) | Path icon (deprecated, pakai media library) |
| type | enum | `completion`, `quality`, `speed`, `habit`, `social`, `hidden` |
| threshold | integer | Jumlah pencapaian yang dibutuhkan |
| created_at | timestamp | - |
| updated_at | timestamp | - |
| deleted_at | timestamp | Soft delete |

**Indexes:**
- `code` (unique)
- `type`


### Tabel: `badge_rules`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| badge_id | bigint | Foreign key ke `badges` |
| event_trigger | string | Event yang memicu evaluasi (e.g., `lesson_completed`) |
| conditions | json | Kondisi yang harus dipenuhi |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Contoh `conditions` JSON:**
```json
{
  "course_slug": "laravel-101",
  "min_score": 90,
  "max_attempts": 1,
  "is_weekend": true,
  "min_streak_days": 7
}
```

**Indexes:**
- `badge_id`
- `event_trigger`

### Tabel: `user_badges`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key ke `users` |
| badge_id | bigint | Foreign key ke `badges` |
| earned_at | timestamp | Waktu badge didapat |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Indexes:**
- `user_id, badge_id` (unique)
- `user_id`
- `badge_id`

---

## 🔌 API Endpoints

### 1. List Badges (Public)


```http
GET /api/v1/badges
Authorization: Bearer {token}
```

**Query Parameters:**
- `per_page` (int): Items per page (default: 15, max: 100)
- `page` (int): Page number
- `search` (string): Full-text search
- `filter[type]` (string): Filter by type
- `filter[code]` (string): Filter by code (partial)
- `filter[name]` (string): Filter by name (partial)
- `sort` (string): Sort field (e.g., `-created_at`, `name`)
- `include` (string): Include relations (e.g., `rules`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "first_step",
      "name": "Langkah Pertama",
      "description": "Bagian dari permulaan perjalanan LMS Anda.",
      "type": "completion",
      "threshold": 1,
      "icon_url": "https://cdn.example.com/badges/first_step.svg",
      "icon_thumb_url": "https://cdn.example.com/badges/first_step_thumb.svg",
      "rules": [
        {
          "id": 1,
          "event_trigger": "account_created",
          "conditions": null
        }
      ],
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

### 2. Show Badge Detail


```http
GET /api/v1/badges/{id}
Authorization: Bearer {token}
```

**Response:** Same as list, single object with `rules` loaded.

### 3. Create Badge (Superadmin Only)

```http
POST /api/v1/badges
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
  "code": "speed_runner",
  "name": "Flash Learner",
  "description": "Finish a course < 3 days",
  "type": "speed",
  "threshold": 1,
  "icon": <file>,
  "rules": [
    {
      "event_trigger": "course_completed",
      "conditions": {
        "max_duration_days": 3
      }
    }
  ]
}
```

**Validation Rules:**
- `code`: required, string, max:50, unique
- `name`: required, string, max:255
- `description`: nullable, string, max:1000
- `type`: required, enum (completion, quality, speed, habit, social, hidden)
- `threshold`: nullable, integer, min:1
- `icon`: required, file, mimes:jpeg,png,svg,webp, max:2048KB
- `rules`: nullable, array
- `rules.*.event_trigger`: required, string
- `rules.*.conditions`: nullable, json

**Response:**
```json
{
  "success": true,
  "data": { /* BadgeResource */ },
  "message": "Badge created successfully"
}
```


### 4. Update Badge (Superadmin Only)

```http
PUT /api/v1/badges/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:** Same as create, all fields optional (partial update).

### 5. Delete Badge (Superadmin Only)

```http
DELETE /api/v1/badges/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [],
  "message": "Badge deleted successfully"
}
```

### 6. Get User Badges

```http
GET /api/v1/user/badges
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "badge": {
        "id": 1,
        "code": "first_step",
        "name": "Langkah Pertama",
        "icon_url": "...",
        "type": "completion"
      },
      "earned_at": "2025-01-15T10:30:00.000000Z"
    }
  ]
}
```

---

## 🔄 Alur Kerja Badge

### 1. Badge Creation Flow


```
User Request
    ↓
BadgesController::store()
    ↓
BadgePolicy::create() → Check Superadmin
    ↓
BadgeStoreRequest::validate()
    ↓
BadgeService::create()
    ↓
DB::transaction {
    BadgeRepository::create()
    ↓
    BadgeService::syncRules() → Create BadgeRule records
    ↓
    BadgeService::handleMedia() → Upload icon to DigitalOcean Spaces
    ↓
    Cache::tags(['common', 'badges'])->flush()
}
    ↓
Return BadgeResource
```

### 2. Badge Award Flow (Automatic)

```
Event Triggered (e.g., LessonCompleted)
    ↓
EventServiceProvider dispatches to Listener
    ↓
AwardXpForLessonCompleted::handle()
    ↓
GamificationService::awardXp() → Award XP
    ↓
BadgeRuleEvaluator::evaluate()
    ↓
Cache::remember('gamification.badge_rules') → Get all rules
    ↓
Filter rules by event_trigger
    ↓
For each rule:
    BadgeRuleEvaluator::isConditionMet()
    ↓
    If true:
        BadgeManager::awardBadge()
        ↓
        DB::transaction {
            GamificationRepository::firstOrCreateBadge()
            ↓
            Check if user already has badge
            ↓
            If not: GamificationRepository::createUserBadge()
        }
```


### 3. Badge Manual Award Flow

```php
// Dalam kode aplikasi
use Modules\Gamification\Services\GamificationService;

$gamification = app(GamificationService::class);

$gamification->awardBadge(
    userId: 123,
    code: 'custom_achievement',
    name: 'Custom Achievement',
    description: 'Completed special task'
);
```

---

## ⚙️ Badge Rules Engine

### Event Triggers yang Tersedia

| Event Trigger | Dipanggil Dari | Status |
|---------------|----------------|--------|
| `lesson_completed` | AwardXpForLessonCompleted | ✅ Aktif |
| `unit_completed` | AwardXpForUnitCompleted | ⚠️ Tidak ada evaluator |
| `course_completed` | AwardBadgeForCourseCompleted | ✅ Aktif |
| `assignment_graded` | AwardXpForGradeReleased | ❌ Bug (evaluator tidak di-inject) |
| `assignment_submitted` | - | ❌ Belum ada |
| `quiz_graded` | - | ❌ Belum ada |
| `login` | AuthSessionProcessor | ✅ Aktif |
| `forum_post_created` | - | ❌ Belum ada |
| `forum_reply_created` | - | ❌ Belum ada |
| `forum_liked` | - | ❌ Belum ada |
| `account_created` | - | ❌ Belum ada |
| `profile_updated` | - | ❌ Belum ada |
| `bug_reported` | - | ❌ Belum ada |

### Conditions yang Didukung

#### 1. Target Matching


```json
{
  "course_slug": "laravel-101"  // Badge hanya untuk course tertentu
}
```

#### 2. Quality Scoring

```json
{
  "min_score": 90,              // Nilai minimal
  "max_attempts": 1,            // Maksimal percobaan
  "is_passed": true             // Harus lulus
}
```

#### 3. Speed Validation

```json
{
  "max_duration_days": 3,       // Selesai dalam 3 hari
  "is_first_submission": true   // Pengumpulan pertama
}
```

#### 4. Habit Validation

```json
{
  "is_weekend": true,           // Hanya weekend
  "min_streak_days": 7,         // Streak minimal 7 hari
  "time_before": "06:00:00",    // Sebelum jam 6 pagi
  "time_after": "00:00:00"      // Setelah jam 12 malam
}
```

### Contoh Badge Rules

#### Badge: "Perfect Assignment"
```json
{
  "code": "perfect_assignment",
  "name": "Nilai Sempurna",
  "type": "quality",
  "threshold": 1,
  "rules": [
    {
      "event_trigger": "assignment_graded",
      "conditions": {
        "min_score": 100
      }
    }
  ]
}
```


#### Badge: "Morning Bird"
```json
{
  "code": "morning_bird",
  "name": "Burung Pagi",
  "type": "habit",
  "threshold": 5,
  "rules": [
    {
      "event_trigger": "login",
      "conditions": {
        "time_before": "06:00:00"
      }
    }
  ]
}
```

#### Badge: "Speed Runner"
```json
{
  "code": "speed_runner",
  "name": "Flash Learner",
  "type": "speed",
  "threshold": 1,
  "rules": [
    {
      "event_trigger": "course_completed",
      "conditions": {
        "max_duration_days": 3
      }
    }
  ]
}
```

#### Badge: "UI/UX Master"
```json
{
  "code": "uiux_master",
  "name": "UI/UX Design Master",
  "type": "completion",
  "threshold": 1,
  "rules": [
    {
      "event_trigger": "course_completed",
      "conditions": {
        "course_slug": "ui-ux-design"
      }
    }
  ]
}
```

---

## 🔗 Integrasi Event-Driven

### Event Listeners yang Terdaftar


**File:** `app/Providers/EventServiceProvider.php`

```php
protected $listen = [
    \Modules\Schemes\Events\LessonCompleted::class => [
        \Modules\Gamification\Listeners\AwardXpForLessonCompleted::class,
    ],
    \Modules\Schemes\Events\CourseCompleted::class => [
        \Modules\Gamification\Listeners\AwardBadgeForCourseCompleted::class,
    ],
    \Modules\Grading\Events\GradesReleased::class => [
        \Modules\Gamification\Listeners\AwardXpForGradeReleased::class,
    ],
    \Modules\Schemes\Events\UnitCompleted::class => [
        \Modules\Gamification\Listeners\AwardXpForUnitCompleted::class,
    ],
];
```

### Payload yang Dikirim ke Evaluator

#### LessonCompleted
```php
$payload = [
    'lesson_id' => $lesson->id,
    'course_id' => $lesson->unit->course_id,
    'is_weekend' => now()->isWeekend(),
];
$evaluator->evaluate($user, 'lesson_completed', $payload);
```

#### CourseCompleted
```php
$payload = [
    'course_id' => $course->id,
    'course_slug' => $course->slug,
    'duration_days' => $enrollment->created_at->diffInDays($enrollment->completed_at),
];
$evaluator->evaluate($user, 'course_completed', $payload);
```

#### AssignmentGraded (Bug: evaluator tidak di-inject)
```php
$payload = [
    'assignment_id' => $assignment->id,
    'course_id' => $assignment->course_id,
    'score' => $grade->effective_score,
    'attempts' => $submission->attempt,
    'is_first_submission' => $submission->attempt === 1,
    'time' => $submission->created_at->format('H:i:s'),
];
// $this->evaluator->evaluate($user, 'assignment_graded', $payload); // Bug!
```


#### Login
```php
$payload = [
    'time' => now()->format('H:i:s'),
];
$badgeEvaluator->evaluate($user, 'login', $payload);
```

---

## 🛡️ Anti-Farming Mechanisms

### 1. Cooldown System

**Implementasi:** `PointManager::checkCooldown()`

```php
// Lesson completion: 10 detik cooldown
if ($sourceType === 'lesson' && $reason === 'completion') {
    $lastPoint = Point::where('user_id', $userId)
        ->where('source_type', $sourceType)
        ->where('reason', $reason)
        ->latest()
        ->first();

    if ($lastPoint && $lastPoint->created_at->diffInSeconds(now()) < 10) {
        return false; // Reject
    }
}
```

### 2. Daily Cap

**Implementasi:** `PointManager::checkDailyCap()`

```php
// Lesson: Max 5000 XP per hari
if ($sourceType === 'lesson') {
    $todayKey = 'gamification.daily_cap.'.$userId.'.'.Carbon::today()->format('Y-m-d');
    $currentDailyXp = Cache::get($todayKey, 0);

    if ($currentDailyXp + $points > 5000) {
        return false; // Reject
    }

    Cache::increment($todayKey, $points);
    Cache::put($todayKey, $points, Carbon::tomorrow()->addHour());
}
```

### 3. Unique Constraint

**Database Level:**
```sql
ALTER TABLE user_badges ADD UNIQUE KEY (user_id, badge_id);
ALTER TABLE points ADD UNIQUE KEY (user_id, source_type, source_id, reason);
```


**Application Level:**
```php
// BadgeManager::awardBadge()
$existing = $this->repository->findUserBadge($userId, $badge->id);
if ($existing) {
    return null; // User sudah punya badge ini
}

// PointManager::awardXp()
if (!$allowMultiple && $this->repository->pointExists($userId, $sourceType, $sourceId, $reason)) {
    return null; // XP sudah pernah diberikan
}
```

### 4. Transaction Safety

Semua operasi badge menggunakan database transaction:

```php
DB::transaction(function () use ($userId, $code, $name, $description) {
    $badge = $this->repository->firstOrCreateBadge($code, [
        'name' => $name,
        'description' => $description,
    ]);

    $existing = $this->repository->findUserBadge($userId, $badge->id);
    if ($existing) {
        return null;
    }

    return $this->repository->createUserBadge([
        'user_id' => $userId,
        'badge_id' => $badge->id,
        'awarded_at' => now(),
    ]);
});
```

---

## 💡 Contoh Penggunaan

### 1. Seeding Badges

**File:** `database/seeders/BadgeSeeder.php`

```php
php artisan db:seed --class=Modules\\Gamification\\Database\\Seeders\\BadgeSeeder
```

Akan membuat 100 badge dengan:
- Icon dari DiceBear API (SVG)
- Rules yang sudah terdefinisi
- Media library integration


### 2. Manual Award Badge

```php
use Modules\Gamification\Services\GamificationService;

$gamification = app(GamificationService::class);

// Award badge langsung
$userBadge = $gamification->awardBadge(
    userId: auth()->id(),
    code: 'special_event_2025',
    name: 'Peserta Event 2025',
    description: 'Mengikuti event spesial tahun 2025'
);

if ($userBadge) {
    // Badge berhasil diberikan
} else {
    // User sudah punya badge ini
}
```

### 3. Cek Badge User

```php
use Modules\Gamification\Services\GamificationService;

$gamification = app(GamificationService::class);

// Get semua badge user
$badges = $gamification->getUserBadges(auth()->id());

// Count badge user
$count = $gamification->countUserBadges(auth()->id());
```

### 4. Create Badge via API

```bash
curl -X POST https://api.example.com/api/v1/badges \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: multipart/form-data" \
  -F "code=night_owl" \
  -F "name=Kelelawar Malam" \
  -F "description=Mengumpulkan tugas di atas jam 12 malam" \
  -F "type=hidden" \
  -F "threshold=5" \
  -F "icon=@/path/to/icon.svg" \
  -F 'rules[0][event_trigger]=assignment_submitted' \
  -F 'rules[0][conditions][time_after]=00:00:00' \
  -F 'rules[0][conditions][time_before]=04:00:00'
```

### 5. Query Badges dengan Filter

```bash
# Get completion badges
GET /api/v1/badges?filter[type]=completion&sort=-created_at

# Search badges
GET /api/v1/badges?search=master&per_page=20

# Get badge dengan rules
GET /api/v1/badges?include=rules
```


---

## 🐛 Known Issues & Limitations

### 1. Bug di AwardXpForGradeReleased

**Problem:** Property `$evaluator` tidak di-inject di constructor

**File:** `app/Listeners/AwardXpForGradeReleased.php`

```php
// Current (Bug)
public function __construct(
    private readonly GamificationService $gamification
) {}

public function handle(GradesReleased $event): void {
    // ...
    if ($user && $this->evaluator) { // $this->evaluator tidak ada!
        $this->evaluator->evaluate($user, 'assignment_graded', $payload);
    }
}
```

**Fix:**
```php
public function __construct(
    private readonly GamificationService $gamification,
    private readonly \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
) {}
```

### 2. Threshold Tidak Diimplementasi

**Problem:** Badge dengan `threshold > 1` tidak menghitung akumulasi

**Contoh:**
- Badge "Quality Assured 5x" dengan threshold: 5
- Seharusnya: User harus dapat nilai 85+ sebanyak 5 kali
- Realita: Badge diberikan setiap kali, tidak ada counter

**Solusi:** Perlu tabel `user_badge_progress` untuk tracking.

### 3. Missing Event Triggers

Badge yang tidak akan pernah ter-trigger karena event belum ada:
- `forum_liked`, `forum_post_created`, `forum_reply_created`
- `quiz_graded`
- `assignment_submitted`
- `account_created`, `profile_updated`
- `bug_reported`


### 4. Tidak Ada Badge Progress Tracking

User tidak bisa melihat progress badge yang belum didapat (misal: "3/5 completed").

### 5. Streak Reset Tidak Otomatis

Command `ResetInactiveStreaks` ada tapi tidak dijadwalkan di scheduler.

---

## 🔐 Authorization & Security

### Badge Policy

**File:** `app/Policies/BadgePolicy.php`

| Action | Permission |
|--------|------------|
| viewAny | Public (semua user) |
| view | Public (semua user) |
| create | Superadmin atau Admin |
| update | Superadmin atau Admin |
| delete | Superadmin only |

### Middleware Protection

```php
// routes/api.php
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::prefix('badges')->group(function () {
        Route::get('/', [BadgesController::class, 'index']); // Public
        Route::get('/{badge}', [BadgesController::class, 'show']); // Public
        
        Route::middleware(['role:Superadmin'])->group(function () {
            Route::post('/', [BadgesController::class, 'store']);
            Route::put('/{badge}', [BadgesController::class, 'update']);
            Route::delete('/{badge}', [BadgesController::class, 'destroy']);
        });
    });
});
```

### Input Validation

- Code: Max 50 karakter, unique
- Name: Max 255 karakter
- Description: Max 1000 karakter
- Icon: Max 2MB, format: jpeg, png, svg, webp
- Type: Enum validation
- Rules: Array validation dengan nested conditions


---

## 📊 Caching Strategy

### 1. Badge List Cache

**Key:** `common:badges:paginate:{perPage}:{page}:{search}:{sort}`

**TTL:** 5 menit (300 detik)

**Tags:** `['common', 'badges']`

**Invalidation:** Otomatis saat create/update/delete badge

```php
cache()->tags(['common', 'badges'])->remember(
    "common:badges:paginate:{$perPage}:{$page}:{$search}:{$sort}",
    300,
    function () use ($perPage, $search) {
        // Query badges...
    }
);
```

### 2. Badge Rules Cache

**Key:** `gamification.badge_rules`

**TTL:** 1 jam (3600 detik)

**Invalidation:** Manual atau restart aplikasi

```php
$rules = Cache::remember('gamification.badge_rules', 3600, function () {
    return BadgeRule::with('badge')->get();
});
```

**Note:** Cache ini sangat penting untuk performa karena dipanggil setiap kali ada event.

### 3. Cache Invalidation

```php
// Saat create/update/delete badge
cache()->tags(['common', 'badges'])->flush();

// Saat update badge rules
Cache::forget('gamification.badge_rules');
```

---

## 🎯 Badge Types

### 1. Completion (Penyelesaian)
Badge untuk menyelesaikan sesuatu (course, unit, lesson).

**Contoh:**
- "Langkah Pertama" - Menyelesaikan lesson pertama
- "UI/UX Master" - Menyelesaikan course UI/UX
- "Penyelesai Modul Level 5" - Menyelesaikan 5 unit

### 2. Quality (Kualitas)
Badge untuk performa berkualitas tinggi.

**Contoh:**
- "Nilai Sempurna" - Mendapat nilai 100
- "Kuis Akurat" - Mendapat nilai 100 pada kuis
- "Quality Assured 5x" - Konsisten nilai 85+ sebanyak 5 kali


### 3. Speed (Kecepatan)
Badge untuk menyelesaikan dengan cepat.

**Contoh:**
- "Flash Learner" - Menyelesaikan course < 3 hari
- "Pertama Mengumpul" - Orang pertama mengumpulkan assignment
- "Penyetor Cepat" - Mengumpulkan dalam 1 hari

### 4. Habit (Kebiasaan)
Badge untuk konsistensi dan kebiasaan baik.

**Contoh:**
- "Konsisten 7 Hari" - Login 7 hari berturut-turut
- "Dedikasi Bulanan" - Login 30 hari berturut-turut
- "Burung Pagi" - Login sebelum jam 6 pagi
- "Pejuang Akhir Pekan" - Belajar di weekend

### 5. Social (Sosial)
Badge untuk interaksi sosial di platform.

**Contoh:**
- "Sangat Disukai" - Mendapat 10 likes di forum
- "Banyak Bicara" - Membuat 20 postingan forum
- "Pahlawan Forum" - Membalas 5 pertanyaan tak terjawab

### 6. Hidden (Tersembunyi)
Badge rahasia yang tidak ditampilkan sampai didapat.

**Contoh:**
- "Kelelawar Malam" - Mengumpulkan tugas jam 12-4 pagi
- "Pemburu Kutu" - Melaporkan bug

---

## 📈 Performance Considerations

### 1. Database Indexes

Pastikan index berikut ada:
```sql
-- badges table
CREATE INDEX idx_badges_code ON badges(code);
CREATE INDEX idx_badges_type ON badges(type);

-- badge_rules table
CREATE INDEX idx_badge_rules_badge_id ON badge_rules(badge_id);
CREATE INDEX idx_badge_rules_event_trigger ON badge_rules(event_trigger);

-- user_badges table
CREATE UNIQUE INDEX idx_user_badges_unique ON user_badges(user_id, badge_id);
CREATE INDEX idx_user_badges_user_id ON user_badges(user_id);
CREATE INDEX idx_user_badges_badge_id ON user_badges(badge_id);
```


### 2. N+1 Query Prevention

```php
// Good: Eager loading
$badges = Badge::with('rules')->paginate(15);

// Bad: N+1 problem
$badges = Badge::paginate(15);
foreach ($badges as $badge) {
    $rules = $badge->rules; // Query per badge!
}
```

### 3. Cache Warming

Untuk production, warm cache saat deployment:

```php
// Warm badge rules cache
Artisan::call('tinker', [
    '--execute' => 'Cache::remember("gamification.badge_rules", 3600, fn() => \Modules\Gamification\Models\BadgeRule::with("badge")->get());'
]);
```

### 4. Queue untuk Heavy Operations

Jika badge evaluation menjadi bottleneck, pertimbangkan queue:

```php
// Dispatch ke queue
dispatch(function () use ($user, $event, $payload) {
    app(BadgeRuleEvaluator::class)->evaluate($user, $event, $payload);
})->afterResponse();
```

---

## 🧪 Testing

### Unit Test Example

```php
use Tests\TestCase;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Services\BadgeService;

class BadgeServiceTest extends TestCase
{
    public function test_can_create_badge()
    {
        $service = app(BadgeService::class);
        
        $badge = $service->create([
            'code' => 'test_badge',
            'name' => 'Test Badge',
            'description' => 'Test description',
            'type' => 'completion',
            'threshold' => 1,
        ]);
        
        $this->assertInstanceOf(Badge::class, $badge);
        $this->assertEquals('test_badge', $badge->code);
    }
}
```


### Feature Test Example

```php
use Tests\TestCase;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\Badge;

class BadgeApiTest extends TestCase
{
    public function test_superadmin_can_create_badge()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadmin');
        
        $response = $this->actingAs($superadmin, 'api')
            ->postJson('/api/v1/badges', [
                'code' => 'new_badge',
                'name' => 'New Badge',
                'type' => 'completion',
                'threshold' => 1,
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'code', 'name'],
                'message'
            ]);
    }
    
    public function test_student_cannot_create_badge()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');
        
        $response = $this->actingAs($student, 'api')
            ->postJson('/api/v1/badges', [
                'code' => 'new_badge',
                'name' => 'New Badge',
                'type' => 'completion',
            ]);
        
        $response->assertStatus(403);
    }
}
```

---

## 📚 Related Documentation

- [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) - Complete API reference
- [BadgeSeeder.php](./database/seeders/BadgeSeeder.php) - Badge seeding examples
- [BadgeRuleEvaluatorTest.php](./tests/Unit/Services/Support/BadgeRuleEvaluatorTest.php) - Rule evaluation tests

---

## 🔄 Changelog

### v1.0.0 (Current)
- ✅ CRUD badge management
- ✅ Dynamic badge rules engine
- ✅ Event-driven badge awarding
- ✅ Anti-farming mechanisms
- ✅ Media library integration
- ✅ Caching strategy
- ✅ 100 pre-defined badges

### Known Issues
- ❌ Bug: AwardXpForGradeReleased evaluator not injected
- ❌ Threshold system not implemented
- ❌ Missing event triggers (forum, quiz, etc.)
- ❌ No badge progress tracking
- ❌ Streak reset not scheduled

---

**Last Updated:** March 14, 2026
**Maintainer:** Gamification Module Team
