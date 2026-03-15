# DOKUMENTASI API DASHBOARD STUDENT - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Dashboard - Student Overview  
**Platform**: Mobile & Web Student

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [Dashboard Overview](#dashboard-overview)
4. [Recent Learning](#recent-learning)
5. [Recent Achievements](#recent-achievements)
6. [Recommended Courses](#recommended-courses)
7. [Complete Use Case](#complete-use-case)

---

## 🎯 RINGKASAN

Dokumentasi ini menjelaskan API Dashboard Student yang menyediakan overview lengkap aktivitas pembelajaran:
1. **Dashboard Overview** - Ringkasan streak, level, dan progress
2. **Recent Learning** - Aktivitas pembelajaran terkini
3. **Recent Achievements** - Badge yang baru didapatkan
4. **Recommended Courses** - Rekomendasi kursus berdasarkan minat

### Fitur Utama
- ✅ Real-time streak tracking (current & longest)
- ✅ Level progress dengan percentage
- ✅ Recent learning dengan last accessed lesson
- ✅ Recent badges dengan rarity info
- ✅ Smart course recommendations
- ✅ Personalized based on user activity

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

## 📊 DASHBOARD OVERVIEW

### 1.1. GET [Mobile] Dashboard - Ringkasan

Melihat ringkasan dashboard student dengan informasi streak dan level.

#### Endpoint
```
GET /dashboard
```

#### Authorization
```
Bearer Token Required (Student only)
```


#### Query Parameters

Tidak ada query parameters untuk endpoint ini.

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Dashboard berhasil diambil",
  "data": {
    "streak": {
      "current": 7,
      "longest": 14
    },
    "level": {
      "current": 5,
      "name": "Apprentice",
      "current_xp": 860,
      "required_xp": 500,
      "next_level_xp": 800,
      "progress_percentage": 45.0
    },
    "xp": {
      "total": 860,
      "this_month": 520
    }
  }
}
```

#### Response Fields

**streak**:
- `current` (integer) - Streak hari ini (consecutive days)
- `longest` (integer) - Streak terpanjang yang pernah dicapai

**level**:
- `current` (integer) - Level saat ini (1-100)
- `name` (string) - Nama level (Newbie, Novice, Learner, dll)
- `current_xp` (integer) - Total XP yang dimiliki
- `required_xp` (integer) - XP yang dibutuhkan untuk level saat ini
- `next_level_xp` (integer) - XP yang dibutuhkan untuk level berikutnya
- `progress_percentage` (float) - Persentase progress ke level berikutnya (0-100)

**xp**:
- `total` (integer) - Total XP sepanjang waktu
- `this_month` (integer) - XP yang didapat bulan ini (dari tanggal 1 sampai hari ini)

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/dashboard

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has streak data", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('streak');
    pm.expect(data.streak).to.have.property('current');
    pm.expect(data.streak).to.have.property('longest');
});
pm.test("Has level data", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('level');
    pm.expect(data.level).to.have.property('current');
    pm.expect(data.level).to.have.property('name');
    pm.expect(data.level).to.have.property('progress_percentage');
});
pm.test("Has XP data", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('xp');
    pm.expect(data.xp).to.have.property('total');
    pm.expect(data.xp).to.have.property('this_month');
});
pm.test("Progress percentage valid", () => {
    const level = pm.response.json().data.level;
    pm.expect(level.progress_percentage).to.be.at.least(0);
    pm.expect(level.progress_percentage).to.be.at.most(100);
});
pm.test("XP values are non-negative", () => {
    const xp = pm.response.json().data.xp;
    pm.expect(xp.total).to.be.at.least(0);
    pm.expect(xp.this_month).to.be.at.least(0);
    pm.expect(xp.this_month).to.be.at.most(xp.total);
});
```

---

## 📚 RECENT LEARNING

### 2.1. GET [Mobile] Dashboard - Aktivitas Pembelajaran Terkini

Melihat aktivitas pembelajaran terkini dengan progress dan last accessed lesson.

#### Endpoint
```
GET /dashboard/recent-learning
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | ❌ No | 1 | Jumlah kursus (max: 10) |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Aktivitas pembelajaran terkini berhasil diambil",
  "data": [
    {
      "course": {
        "id": 43,
        "title": "Laravel PHP Framework Masterclass",
        "slug": "laravel-php-framework-masterclass",
        "thumbnail": "https://api.levl.id/storage/courses/laravel-thumb.jpg"
      },
      "progress": {
        "completed_lessons": 15,
        "total_lessons": 45,
        "percentage": 33.33
      },
      "last_lesson": {
        "id": 597,
        "title": "Best Practices for Data Structures",
        "unit_title": "Getting Started"
      },
      "last_accessed_at": "2026-03-15T03:02:01.000000Z"
    }
  ]
}
```

#### Response Fields

**course**:
- `id` (integer) - ID kursus
- `title` (string) - Judul kursus
- `slug` (string) - Slug kursus untuk URL
- `thumbnail` (string|null) - URL thumbnail kursus

**progress**:
- `completed_lessons` (integer) - Jumlah lesson yang sudah diselesaikan
- `total_lessons` (integer) - Total lesson dalam kursus
- `percentage` (float) - Persentase penyelesaian (0-100)

**last_lesson**:
- `id` (integer) - ID lesson terakhir yang diakses
- `title` (string) - Judul lesson
- `unit_title` (string|null) - Judul unit dari lesson

**last_accessed_at** (string) - Timestamp terakhir kali mengakses kursus (menggunakan `updated_at` dari enrollment)

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL - Default (1 course)
{{base_url}}/dashboard/recent-learning

// URL - Multiple courses
{{base_url}}/dashboard/recent-learning?limit=5

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has learning data", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has course info", () => {
    const data = pm.response.json().data;
    if (data.length > 0) {
        pm.expect(data[0]).to.have.property('course');
        pm.expect(data[0]).to.have.property('progress');
        pm.expect(data[0]).to.have.property('last_accessed_at');
    }
});
pm.test("Progress percentage valid", () => {
    const data = pm.response.json().data;
    if (data.length > 0) {
        const percentage = data[0].progress.percentage;
        pm.expect(percentage).to.be.at.least(0);
        pm.expect(percentage).to.be.at.most(100);
    }
});
```

---

## 🏅 RECENT ACHIEVEMENTS

### 3.1. GET [Mobile] Dashboard - Pencapaian Terkini

Melihat badge yang baru didapatkan (recent achievements).

#### Endpoint
```
GET /dashboard/recent-achievements
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | ❌ No | 4 | Jumlah badge (max: 20) |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Pencapaian terkini berhasil diambil",
  "data": [
    {
      "id": 5,
      "code": "quiz-champion",
      "name": "Quiz Champion",
      "description": "Menyelesaikan 10 kuis dengan nilai ≥80",
      "rarity": "rare",
      "type": "quality",
      "icon_url": "https://api.levl.id/storage/badges/quiz-champion.png",
      "earned_at": "2026-03-10T14:30:00.000000Z"
    },
    {
      "id": 12,
      "code": "fast-learner",
      "name": "Fast Learner",
      "description": "Menyelesaikan 5 lessons dalam 1 hari",
      "rarity": "uncommon",
      "type": "speed",
      "icon_url": "https://api.levl.id/storage/badges/fast-learner.png",
      "earned_at": "2026-03-09T18:45:00.000000Z"
    }
  ]
}
```

#### Response Fields

- `id` (integer) - ID badge
- `code` (string) - Kode unik badge
- `name` (string) - Nama badge
- `description` (string) - Deskripsi badge
- `rarity` (string) - Tingkat kelangkaan: `common`, `uncommon`, `rare`, `epic`, `legendary`
- `type` (string) - Tipe badge: `completion`, `quality`, `speed`, `habit`, `social`, `milestone`, `hidden`
- `icon_url` (string|null) - URL icon badge
- `earned_at` (string) - Timestamp saat badge didapatkan

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL - Default (4 badges)
{{base_url}}/dashboard/recent-achievements

// URL - More badges
{{base_url}}/dashboard/recent-achievements?limit=10

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has achievements data", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has badge info", () => {
    const data = pm.response.json().data;
    if (data.length > 0) {
        pm.expect(data[0]).to.have.property('id');
        pm.expect(data[0]).to.have.property('code');
        pm.expect(data[0]).to.have.property('name');
        pm.expect(data[0]).to.have.property('rarity');
        pm.expect(data[0]).to.have.property('type');
        pm.expect(data[0]).to.have.property('earned_at');
    }
});
pm.test("Valid rarity", () => {
    const data = pm.response.json().data;
    const validRarities = ['common', 'uncommon', 'rare', 'epic', 'legendary'];
    if (data.length > 0) {
        pm.expect(validRarities).to.include(data[0].rarity);
    }
});
```

---

## 🎓 RECOMMENDED COURSES

### 4.1. GET [Mobile] Dashboard - Rekomendasi Kursus

Melihat rekomendasi kursus berdasarkan kursus yang sudah diikuti (smart recommendations).

#### Endpoint
```
GET /dashboard/recommended-courses
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | ❌ No | 2 | Jumlah kursus (max: 10) |

#### Recommendation Logic

Sistem rekomendasi bekerja dengan prioritas berikut:
1. **Jika user sudah punya enrollment**: Cari kursus dengan kategori atau tag yang sama
2. **Jika tidak ada enrollment**: Tampilkan kursus populer (berdasarkan jumlah enrollment)
3. **Jika hasil kurang dari limit**: Isi dengan kursus populer

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Rekomendasi kursus berhasil diambil",
  "data": [
    {
      "id": 58,
      "title": "Advanced Laravel Techniques",
      "slug": "advanced-laravel-techniques",
      "description": "Master advanced Laravel concepts and best practices",
      "category": "programming",
      "thumbnail": "https://api.levl.id/storage/courses/advanced-laravel.jpg",
      "instructor": {
        "id": 5,
        "name": "John Doe"
      },
      "enrollments_count": 245
    },
    {
      "id": 72,
      "title": "PHP Design Patterns",
      "slug": "php-design-patterns",
      "description": "Learn essential design patterns in PHP",
      "category": "programming",
      "thumbnail": "https://api.levl.id/storage/courses/php-patterns.jpg",
      "instructor": {
        "id": 8,
        "name": "Jane Smith"
      },
      "enrollments_count": 189
    }
  ]
}
```

#### Response Fields

- `id` (integer) - ID kursus
- `title` (string) - Judul kursus
- `slug` (string) - Slug kursus untuk URL
- `description` (string|null) - Deskripsi kursus
- `category` (string|null) - Kategori kursus
- `thumbnail` (string|null) - URL thumbnail kursus
- `instructor` (object|null) - Informasi instruktur
  - `id` (integer) - ID instruktur
  - `name` (string) - Nama instruktur
- `enrollments_count` (integer) - Jumlah student yang terdaftar

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL - Default (2 courses)
{{base_url}}/dashboard/recommended-courses

// URL - More recommendations
{{base_url}}/dashboard/recommended-courses?limit=5

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has recommendations", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has course info", () => {
    const data = pm.response.json().data;
    if (data.length > 0) {
        pm.expect(data[0]).to.have.property('id');
        pm.expect(data[0]).to.have.property('title');
        pm.expect(data[0]).to.have.property('slug');
        pm.expect(data[0]).to.have.property('instructor');
        pm.expect(data[0]).to.have.property('enrollments_count');
    }
});
pm.test("Has instructor info", () => {
    const data = pm.response.json().data;
    if (data.length > 0 && data[0].instructor) {
        pm.expect(data[0].instructor).to.have.property('id');
        pm.expect(data[0].instructor).to.have.property('name');
    }
});
```

---

## 📖 COMPLETE USE CASE: DASHBOARD JOURNEY

```javascript
// ============================================
// SCENARIO: Student opens dashboard
// ============================================

// 1. Load dashboard overview
GET /dashboard
// Response: 
// - Current streak: 7 days
// - Longest streak: 14 days
// - Level 5 "Apprentice" with 45% progress
// - Total XP: 860
// - XP this month: 520

// 2. Load recent learning activity
GET /dashboard/recent-learning?limit=3
// Response: 
// - 3 most recently accessed courses
// - Each with progress percentage
// - Last accessed lesson info

// 3. Load recent achievements
GET /dashboard/recent-achievements?limit=4
// Response:
// - 4 most recently earned badges
// - With rarity and type info

// 4. Load recommended courses
GET /dashboard/recommended-courses?limit=2
// Response:
// - 2 recommended courses based on enrolled courses
// - Or popular courses if no enrollments

// ============================================
// SCENARIO: Student with no enrollments
// ============================================

// 1. Dashboard overview
GET /dashboard
// Response:
// - Streak: 0 (no activity yet)
// - Level 1 "Newbie" with 0% progress
// - Total XP: 0
// - XP this month: 0

// 2. Recent learning
GET /dashboard/recent-learning
// Response: [] (empty array)

// 3. Recent achievements
GET /dashboard/recent-achievements
// Response: [] (empty array)

// 4. Recommended courses
GET /dashboard/recommended-courses?limit=5
// Response:
// - 5 popular courses (most enrollments)
// - Since no enrolled courses to base recommendations on

// ============================================
// SCENARIO: Active student dashboard
// ============================================

// 1. Dashboard overview
GET /dashboard
// Response:
// - Current streak: 15 days
// - Longest streak: 20 days
// - Level 8 "Proficient" with 67% progress
// - Total XP: 2450
// - XP this month: 680

// 2. Recent learning (last 5 courses)
GET /dashboard/recent-learning?limit=5
// Response:
// - Course 1: 85% complete, last lesson "Advanced Concepts"
// - Course 2: 45% complete, last lesson "Introduction to APIs"
// - Course 3: 100% complete (finished)
// - Course 4: 12% complete, just started
// - Course 5: 60% complete, mid-way

// 3. Recent achievements (last 10 badges)
GET /dashboard/recent-achievements?limit=10
// Response:
// - Mix of common, uncommon, rare badges
// - Various types: completion, quality, speed, social
// - Earned over the past weeks

// 4. Recommended courses
GET /dashboard/recommended-courses?limit=3
// Response:
// - 3 courses with similar categories/tags
// - Based on enrolled courses
// - Sorted by popularity
```

---

## 🎯 KEY POINTS

### Dashboard Overview
- Real-time streak tracking untuk engagement
- Level progress dengan percentage calculation
- XP tracking: total dan bulan ini (tanggal 1 sampai hari ini)
- Minimal data untuk fast loading
- No pagination needed (single object response)

### Recent Learning
- Ordered by last_accessed_at (most recent first)
- Includes progress percentage untuk visual progress bar
- Last lesson info untuk "Continue Learning" feature
- Limit parameter untuk control jumlah data

### Recent Achievements
- Ordered by earned_at (newest first)
- Includes rarity untuk visual styling
- Includes type untuk categorization
- Icon URL untuk display badge image

### Recommended Courses
- Smart recommendations based on enrolled courses
- Falls back to popular courses if needed
- Excludes already enrolled courses
- Includes enrollment count untuk social proof

---

## 📊 RESPONSE FORMAT

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { }
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

## 🔗 INTEGRATION WITH OTHER MODULES

Dashboard terintegrasi dengan:

1. **Gamification Module**
   - Streak data dari `user_gamification_stats`
   - Level info dari `level_configs`
   - Badge data dari `user_badges`

2. **Enrollment Module**
   - Recent learning dari `enrollments`
   - Last accessed tracking

3. **Schemes Module**
   - Course data dengan units & lessons
   - Lesson completion tracking
   - Course recommendations

4. **Learning Module**
   - Assignment & quiz progress
   - Submission tracking

---

## 💡 BEST PRACTICES

### Frontend Implementation
1. **Cache dashboard data** untuk 5-10 menit
2. **Refresh on user action** (complete lesson, earn badge)
3. **Show loading states** untuk better UX
4. **Handle empty states** gracefully
5. **Use skeleton loaders** saat loading

### Performance Tips
1. Dashboard overview sangat ringan (no pagination)
2. Use limit parameter untuk control data size
3. Recent learning includes eager loading (course, units, lessons)
4. Recommended courses uses efficient query with counts

### UX Recommendations
1. **Streak**: Show fire icon, celebrate milestones
2. **Level**: Show progress bar, next level preview
3. **Recent Learning**: "Continue Learning" button
4. **Achievements**: Show badge animation when earned
5. **Recommendations**: "Explore" or "Enroll Now" CTA

---

## 📱 MOBILE APP CONSIDERATIONS

### Dashboard Layout
```
┌─────────────────────────────┐
│  Streak & Level Card        │
│  - Current streak: 7 days   │
│  - Level 5 (45% progress)   │
└─────────────────────────────┘

┌─────────────────────────────┐
│  Continue Learning          │
│  - Course thumbnail         │
│  - Progress bar (33%)       │
│  - Last lesson title        │
└─────────────────────────────┘

┌─────────────────────────────┐
│  Recent Achievements        │
│  [Badge] [Badge] [Badge]    │
└─────────────────────────────┘

┌─────────────────────────────┐
│  Recommended for You        │
│  - Course 1                 │
│  - Course 2                 │
└─────────────────────────────┘
```

### API Call Sequence
```javascript
// On dashboard mount
Promise.all([
  fetch('/dashboard'),                    // Overview
  fetch('/dashboard/recent-learning'),    // Recent learning
  fetch('/dashboard/recent-achievements'), // Badges
  fetch('/dashboard/recommended-courses')  // Recommendations
]).then(([overview, learning, achievements, recommendations]) => {
  // Render dashboard
});
```

---

## 🧪 TESTING SCENARIOS

### Test Case 1: New Student (No Activity)
```
GET /dashboard
Expected: streak = 0, level = 1, progress = 0%, total_xp = 0, this_month_xp = 0

GET /dashboard/recent-learning
Expected: [] (empty array)

GET /dashboard/recent-achievements
Expected: [] (empty array)

GET /dashboard/recommended-courses
Expected: Popular courses (no personalization)
```

### Test Case 2: Active Student
```
GET /dashboard
Expected: streak > 0, level > 1, progress > 0%, total_xp > 0, this_month_xp > 0

GET /dashboard/recent-learning?limit=3
Expected: 3 courses with progress data

GET /dashboard/recent-achievements?limit=4
Expected: 4 badges with earned_at

GET /dashboard/recommended-courses?limit=2
Expected: 2 courses based on enrolled courses
```

### Test Case 3: XP This Month Validation
```
GET /dashboard
Expected: this_month_xp <= total_xp (XP bulan ini tidak boleh lebih dari total)

// Test di awal bulan (tanggal 1-5)
Expected: this_month_xp relatif kecil

// Test di akhir bulan (tanggal 25-31)
Expected: this_month_xp bisa lebih besar (akumulasi dari tanggal 1)
```

### Test Case 3: Limit Parameters
```
GET /dashboard/recent-learning?limit=15
Expected: Max 10 courses (limit capped)

GET /dashboard/recent-achievements?limit=25
Expected: Max 20 badges (limit capped)

GET /dashboard/recommended-courses?limit=15
Expected: Max 10 courses (limit capped)
```

---

**Dokumentasi ini mencakup complete dashboard system untuk student overview.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
