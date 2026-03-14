# Panduan Lengkap Badge Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap untuk semua form dan operasi badge management dari sisi Management (Superadmin, Admin).

---

## Daftar Isi

1. [Badge Overview](#1-badge-overview)
2. [List Badges (Daftar Badge)](#2-list-badges-daftar-badge)
3. [Show Badge Detail](#3-show-badge-detail)
4. [Create Badge (Buat Badge Baru)](#4-create-badge-buat-badge-baru)
5. [Update Badge](#5-update-badge)
6. [Delete Badge](#6-delete-badge)
7. [User Badges (Badge Pengguna)](#7-user-badges-badge-pengguna)
8. [Badge Rules System](#8-badge-rules-system)

---

## 1. BADGE OVERVIEW

### Base Endpoint
- Badge Management API: `/api/v1/badges`
- User Badge API: `/api/v1/user/badges`

### Authentication
Semua endpoint membutuhkan token Bearer:
```
Authorization: Bearer {token}
```

### Badge Types (Tipe Badge)

| Type | Value | Deskripsi | Warna Badge |
|------|-------|-----------|-------------|
| Completion | `completion` | Badge untuk menyelesaikan sesuatu | 🟢 Hijau |
| Quality | `quality` | Badge untuk performa berkualitas tinggi | 🔵 Biru |
| Speed | `speed` | Badge untuk menyelesaikan dengan cepat | 🟡 Kuning |
| Habit | `habit` | Badge untuk konsistensi dan kebiasaan | 🟣 Ungu |
| Social | `social` | Badge untuk interaksi sosial | 🟠 Oranye |
| Hidden | `hidden` | Badge rahasia (tidak ditampilkan sampai didapat) | ⚫ Abu-abu |

### Badge Fields (Core)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID badge |
| `code` | string | Unique identifier (e.g., `first_step`) |
| `name` | string | Nama badge (e.g., "Langkah Pertama") |
| `description` | text | Deskripsi badge |
| `type` | enum | Tipe badge (completion, quality, speed, habit, social, hidden) |
| `threshold` | integer | Jumlah pencapaian yang dibutuhkan (null = 1 kali) |
| `icon_url` | string | URL icon badge (full size) |
| `icon_thumb_url` | string | URL icon badge (thumbnail) |
| `created_at` | datetime | Waktu dibuat |
| `updated_at` | datetime | Waktu diupdate |

---

## 2. LIST BADGES (Daftar Badge)

### Endpoint
```
GET /api/v1/badges
```

### Authorization
- Role: Semua user yang authenticated
- Public endpoint (semua user bisa melihat daftar badge)

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `per_page` | integer | ❌ Tidak | 15 | Jumlah data per halaman (min: 1, max: 100) |
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `search` | string | ❌ Tidak | - | Pencarian full-text (name, description, code) |
| `filter[type]` | string | ❌ Tidak | - | Filter by type (exact match) |
| `filter[code]` | string | ❌ Tidak | - | Filter by code (partial match) |
| `filter[name]` | string | ❌ Tidak | - | Filter by name (partial match) |
| `sort` | string | ❌ Tidak | -created_at | Field untuk sorting |
| `include` | string | ❌ Tidak | - | Include relations: `rules` |

### Allowed Sorts

| Sort | Deskripsi |
|------|-----------|
| `id` | Sort by ID |
| `code` | Sort by code |
| `name` | Sort by name |
| `type` | Sort by type |
| `threshold` | Sort by threshold |
| `created_at` | Sort by tanggal dibuat (default) |
| `updated_at` | Sort by tanggal diupdate |

**Catatan**: Tambahkan `-` di depan untuk descending (contoh: `-created_at`)


### Contoh Request

#### 1. Get All Badges (Default)
```
GET /api/v1/badges
```

#### 2. Search Badges
```
GET /api/v1/badges?search=master
```

#### 3. Filter by Type
```
GET /api/v1/badges?filter[type]=completion
```

#### 4. Filter by Code (Partial)
```
GET /api/v1/badges?filter[code]=level_
```

#### 5. Kombinasi Filter + Search + Sort
```
GET /api/v1/badges?search=master&filter[type]=quality&sort=name&per_page=20
```

#### 6. Include Badge Rules
```
GET /api/v1/badges?include=rules
```

### Response Format

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
      "created_at": "2026-03-14T10:00:00Z",
      "updated_at": "2026-03-14T10:00:00Z"
    },
    {
      "id": 2,
      "code": "perfect_score",
      "name": "Nilai Sempurna",
      "description": "Mendapat nilai 100 pada assignment atau quiz.",
      "type": "quality",
      "threshold": 1,
      "icon_url": "https://cdn.example.com/badges/perfect_score.svg",
      "icon_thumb_url": "https://cdn.example.com/badges/perfect_score_thumb.svg",
      "created_at": "2026-03-14T10:00:00Z",
      "updated_at": "2026-03-14T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

### Response dengan Include Rules

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
          "badge_id": 1,
          "event_trigger": "lesson_completed",
          "conditions": null,
          "created_at": "2026-03-14T10:00:00Z",
          "updated_at": "2026-03-14T10:00:00Z"
        }
      ],
      "created_at": "2026-03-14T10:00:00Z",
      "updated_at": "2026-03-14T10:00:00Z"
    }
  ]
}
```

### Catatan Penting
- Data di-paginate secara default
- Search menggunakan Meilisearch (fast full-text search)
- Cache: 5 menit (auto-refresh)
- Include `rules` untuk melihat kondisi badge

---

## 3. SHOW BADGE DETAIL

### Endpoint
```
GET /api/v1/badges/{badge_id}
```

### Authorization
- Role: Semua user yang authenticated
- Public endpoint

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `badge_id` | integer | ✅ Ya | ID badge |

### Contoh Request

```
GET /api/v1/badges/1
```

### Response Format

```json
{
  "success": true,
  "data": {
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
        "badge_id": 1,
        "event_trigger": "lesson_completed",
        "conditions": null,
        "created_at": "2026-03-14T10:00:00Z",
        "updated_at": "2026-03-14T10:00:00Z"
      }
    ],
    "created_at": "2026-03-14T10:00:00Z",
    "updated_at": "2026-03-14T10:00:00Z"
  }
}
```

### Error Response (404)

```json
{
  "success": false,
  "message": "Badge not found",
  "errors": null
}
```

### Catatan Penting
- Badge detail selalu include `rules` relationship
- Response sama dengan list, tapi single object

---

## 4. CREATE BADGE (Buat Badge Baru)

### Endpoint
```
POST /api/v1/badges
```

### Authorization
- Role: **Superadmin only**
- Admin tidak bisa create badge

### Content-Type
`multipart/form-data` (karena ada upload icon)

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `code` | string | ✅ Ya | max:50, unique | - | Unique identifier (e.g., `first_step`) |
| `name` | string | ✅ Ya | max:255 | - | Nama badge |
| `description` | text | ❌ Tidak | max:1000 | null | Deskripsi badge |
| `type` | enum | ✅ Ya | completion, quality, speed, habit, social, hidden | - | Tipe badge |
| `threshold` | integer | ❌ Tidak | min:1 | null | Jumlah pencapaian yang dibutuhkan |
| `icon` | file | ✅ Ya | mimes:jpeg,png,svg,webp, max:2048KB | - | Icon badge |
| `rules` | array | ❌ Tidak | array | [] | Badge rules (kondisi untuk mendapat badge) |
| `rules.*.criterion` | string | ✅ Ya (jika rules ada) | max:50 | - | Kriteria rule (e.g., `score`, `attempts`) |
| `rules.*.operator` | string | ✅ Ya (jika rules ada) | =, >=, > | - | Operator perbandingan |
| `rules.*.value` | integer | ✅ Ya (jika rules ada) | min:1 | - | Nilai threshold |

### Nilai Enum

#### type
- `completion` - Badge untuk menyelesaikan sesuatu
- `quality` - Badge untuk performa berkualitas tinggi
- `speed` - Badge untuk menyelesaikan dengan cepat
- `habit` - Badge untuk konsistensi dan kebiasaan
- `social` - Badge untuk interaksi sosial
- `hidden` - Badge rahasia (tidak ditampilkan sampai didapat)

#### rules.*.operator
- `=` - Sama dengan
- `>=` - Lebih dari atau sama dengan
- `>` - Lebih dari

### Validasi Icon
- Format: JPEG, PNG, SVG, WebP
- Max size: 2MB (2048KB)
- Recommended: SVG untuk scalability
- Recommended size: 512x512px


### Contoh Request

#### 1. Create Badge (Basic - Tanpa Rules)
```
POST /api/v1/badges
Content-Type: multipart/form-data

code: first_step
name: Langkah Pertama
description: Bagian dari permulaan perjalanan LMS Anda.
type: completion
threshold: 1
icon: [FILE]
```

#### 2. Create Badge (Dengan Rules)
```
POST /api/v1/badges
Content-Type: multipart/form-data

code: perfect_score
name: Nilai Sempurna
description: Mendapat nilai 100 pada assignment atau quiz.
type: quality
threshold: 1
icon: [FILE]
rules[0][criterion]: score
rules[0][operator]: =
rules[0][value]: 100
```

#### 3. Create Badge (Multiple Rules)
```
POST /api/v1/badges
Content-Type: multipart/form-data

code: quality_assured_5x
name: Quality Assured 5x
description: Konsisten mendapat nilai 85+ sebanyak 5 kali.
type: quality
threshold: 5
icon: [FILE]
rules[0][criterion]: score
rules[0][operator]: >=
rules[0][value]: 85
rules[1][criterion]: attempts
rules[1][operator]: >=
rules[1][value]: 5
```

#### 4. Create Hidden Badge
```
POST /api/v1/badges
Content-Type: multipart/form-data

code: night_owl
name: Kelelawar Malam
description: Mengumpulkan tugas di atas jam 12 malam.
type: hidden
threshold: 5
icon: [FILE]
rules[0][criterion]: time_after
rules[0][operator]: >=
rules[0][value]: 0
```

### Response Format (201 Created)

```json
{
  "success": true,
  "message": "Badge created successfully",
  "data": {
    "id": 101,
    "code": "perfect_score",
    "name": "Nilai Sempurna",
    "description": "Mendapat nilai 100 pada assignment atau quiz.",
    "type": "quality",
    "threshold": 1,
    "icon_url": "https://cdn.example.com/badges/perfect_score.svg",
    "icon_thumb_url": "https://cdn.example.com/badges/perfect_score_thumb.svg",
    "rules": [
      {
        "id": 201,
        "badge_id": 101,
        "criterion": "score",
        "operator": "=",
        "value": 100,
        "created_at": "2026-03-14T10:00:00Z",
        "updated_at": "2026-03-14T10:00:00Z"
      }
    ],
    "created_at": "2026-03-14T10:00:00Z",
    "updated_at": "2026-03-14T10:00:00Z"
  }
}
```

### Error Responses

#### Code Already Exists (422)
```json
{
  "success": false,
  "message": "Data yang Anda kirim tidak valid.",
  "errors": {
    "code": ["The code has already been taken."]
  }
}
```

#### Invalid Type (422)
```json
{
  "success": false,
  "message": "Data yang Anda kirim tidak valid.",
  "errors": {
    "type": ["The selected type is invalid."]
  }
}
```

#### Icon Too Large (422)
```json
{
  "success": false,
  "message": "Data yang Anda kirim tidak valid.",
  "errors": {
    "icon": ["The icon must not be greater than 2048 kilobytes."]
  }
}
```

#### Unauthorized (403)
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

### Catatan Penting
- Code harus unique di seluruh sistem
- Icon akan di-upload ke DigitalOcean Spaces
- Sistem akan generate thumbnail otomatis
- Rules bersifat optional (bisa dibuat badge tanpa rules)
- Cache badge list akan di-clear otomatis setelah create

---

## 5. UPDATE BADGE

### Endpoint
```
PUT /api/v1/badges/{badge_id}
```

### Authorization
- Role: **Superadmin only**

### Content-Type
`multipart/form-data` (jika ada upload icon baru)

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `badge_id` | integer | ✅ Ya | ID badge yang akan diupdate |

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `code` | string | ❌ Tidak | max:50, unique | Unique identifier |
| `name` | string | ❌ Tidak | max:255 | Nama badge |
| `description` | text | ❌ Tidak | max:1000 | Deskripsi badge |
| `type` | enum | ❌ Tidak | completion, quality, speed, habit, social, hidden | Tipe badge |
| `threshold` | integer | ❌ Tidak | min:1 | Jumlah pencapaian |
| `icon` | file | ❌ Tidak | mimes:jpeg,png,svg,webp, max:2048KB | Icon badge baru |
| `rules` | array | ❌ Tidak | array | Badge rules (akan replace semua rules lama) |
| `rules.*.criterion` | string | ✅ Ya (jika rules ada) | max:50 | Kriteria rule |
| `rules.*.operator` | string | ✅ Ya (jika rules ada) | =, >=, > | Operator |
| `rules.*.value` | integer | ✅ Ya (jika rules ada) | min:1 | Nilai threshold |

### Contoh Request

#### 1. Update Name Only
```
PUT /api/v1/badges/101
Content-Type: application/json

{
  "name": "Nilai Sempurna (Updated)"
}
```

#### 2. Update Description
```
PUT /api/v1/badges/101
Content-Type: application/json

{
  "description": "Mendapat nilai 100 pada assignment atau quiz. Badge ini menunjukkan dedikasi dan pemahaman yang sempurna."
}
```

#### 3. Update Icon
```
PUT /api/v1/badges/101
Content-Type: multipart/form-data

icon: [NEW_FILE]
```

#### 4. Update Rules (Replace All)
```
PUT /api/v1/badges/101
Content-Type: multipart/form-data

rules[0][criterion]: score
rules[0][operator]: >=
rules[0][value]: 95
```

#### 5. Update Multiple Fields
```
PUT /api/v1/badges/101
Content-Type: multipart/form-data

name: Nilai Hampir Sempurna
description: Mendapat nilai 95+ pada assignment atau quiz.
threshold: 3
icon: [NEW_FILE]
rules[0][criterion]: score
rules[0][operator]: >=
rules[0][value]: 95
```

### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Badge updated successfully",
  "data": {
    "id": 101,
    "code": "perfect_score",
    "name": "Nilai Sempurna (Updated)",
    "description": "Mendapat nilai 100 pada assignment atau quiz. Badge ini menunjukkan dedikasi dan pemahaman yang sempurna.",
    "type": "quality",
    "threshold": 1,
    "icon_url": "https://cdn.example.com/badges/perfect_score_new.svg",
    "icon_thumb_url": "https://cdn.example.com/badges/perfect_score_new_thumb.svg",
    "rules": [
      {
        "id": 202,
        "badge_id": 101,
        "criterion": "score",
        "operator": ">=",
        "value": 95,
        "created_at": "2026-03-14T11:00:00Z",
        "updated_at": "2026-03-14T11:00:00Z"
      }
    ],
    "created_at": "2026-03-14T10:00:00Z",
    "updated_at": "2026-03-14T11:00:00Z"
  }
}
```

### Error Response (404)

```json
{
  "success": false,
  "message": "Badge not found",
  "errors": null
}
```

### Catatan Penting
- Update bersifat partial (hanya field yang dikirim yang di-update)
- Jika update `rules`, semua rules lama akan di-replace
- Jika update `icon`, icon lama akan di-delete dari storage
- Cache badge list akan di-clear otomatis setelah update
- Code tetap harus unique jika diubah

---

## 6. DELETE BADGE

### Endpoint
```
DELETE /api/v1/badges/{badge_id}
```

### Authorization
- Role: **Superadmin only**

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `badge_id` | integer | ✅ Ya | ID badge yang akan dihapus |

### Contoh Request

```
DELETE /api/v1/badges/101
```

### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Badge deleted successfully",
  "data": []
}
```

### Error Response (404)

```json
{
  "success": false,
  "message": "Badge not found",
  "errors": null
}
```

### Business Rules
- Badge di-soft delete (tidak benar-benar dihapus dari database)
- User yang sudah punya badge tetap memilikinya
- Badge rules juga ikut ter-delete
- Icon badge akan di-delete dari storage
- Cache badge list akan di-clear otomatis

### Catatan Penting
- Soft delete memungkinkan data recovery jika dibutuhkan
- User badges (user_badges table) tidak ikut terhapus
- Badge yang sudah dihapus tidak muncul di list badges
- Superadmin bisa restore badge melalui database jika diperlukan

---

## 7. USER BADGES (Badge Pengguna)

### 7.1 Get My Badges

Melihat semua badge yang dimiliki user yang sedang login.

#### Endpoint
```
GET /api/v1/user/badges
```

#### Authorization
- Role: Semua user yang authenticated
- User hanya bisa melihat badge sendiri

#### Query Parameters
Tidak ada.

#### Contoh Request

```
GET /api/v1/user/badges
```

#### Response Format

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "badge_id": 1,
      "earned_at": "2026-03-14T10:00:00Z",
      "badge": {
        "id": 1,
        "code": "first_step",
        "name": "Langkah Pertama",
        "description": "Bagian dari permulaan perjalanan LMS Anda.",
        "type": "completion",
        "threshold": 1,
        "icon_url": "https://cdn.example.com/badges/first_step.svg",
        "icon_thumb_url": "https://cdn.example.com/badges/first_step_thumb.svg"
      }
    },
    {
      "id": 2,
      "user_id": 123,
      "badge_id": 5,
      "earned_at": "2026-03-15T14:30:00Z",
      "badge": {
        "id": 5,
        "code": "perfect_score",
        "name": "Nilai Sempurna",
        "description": "Mendapat nilai 100 pada assignment atau quiz.",
        "type": "quality",
        "threshold": 1,
        "icon_url": "https://cdn.example.com/badges/perfect_score.svg",
        "icon_thumb_url": "https://cdn.example.com/badges/perfect_score_thumb.svg"
      }
    }
  ]
}
```

#### Catatan Penting
- Response berisi semua badge yang dimiliki user
- Badge di-sort by `earned_at` descending (terbaru dulu)
- Badge detail include full badge information


---

## 8. BADGE RULES SYSTEM

### Overview

Badge Rules adalah sistem kondisi yang menentukan kapan badge diberikan ke user. Rules bersifat optional - badge bisa dibuat tanpa rules untuk manual awarding.

### Event Triggers yang Tersedia

| Event Trigger | Deskripsi | Status |
|---------------|-----------|--------|
| `lesson_completed` | Saat user menyelesaikan lesson | ✅ Aktif |
| `unit_completed` | Saat user menyelesaikan unit | ⚠️ Partial |
| `course_completed` | Saat user menyelesaikan course | ✅ Aktif |
| `assignment_graded` | Saat assignment di-grade | ⚠️ Bug |
| `assignment_submitted` | Saat user submit assignment | ❌ Belum ada |
| `quiz_graded` | Saat quiz di-grade | ❌ Belum ada |
| `login` | Saat user login | ✅ Aktif |
| `forum_post_created` | Saat user buat post forum | ❌ Belum ada |
| `forum_reply_created` | Saat user buat reply forum | ❌ Belum ada |
| `forum_liked` | Saat user dapat like di forum | ❌ Belum ada |
| `account_created` | Saat user register | ❌ Belum ada |
| `profile_updated` | Saat user update profile | ❌ Belum ada |

### Rule Criteria (Kriteria)

#### 1. Score-Based (Berbasis Nilai)

| Criterion | Operator | Value | Deskripsi |
|-----------|----------|-------|-----------|
| `score` | `=` | 100 | Nilai sama dengan 100 |
| `score` | `>=` | 85 | Nilai minimal 85 |
| `score` | `>` | 90 | Nilai lebih dari 90 |

**Contoh Badge:**
- "Nilai Sempurna" - score = 100
- "Quality Assured" - score >= 85

#### 2. Attempts-Based (Berbasis Percobaan)

| Criterion | Operator | Value | Deskripsi |
|-----------|----------|-------|-----------|
| `attempts` | `=` | 1 | Percobaan pertama |
| `attempts` | `>=` | 5 | Minimal 5 percobaan |

**Contoh Badge:**
- "First Try Success" - attempts = 1
- "Persistent Learner" - attempts >= 5

#### 3. Time-Based (Berbasis Waktu)

| Criterion | Operator | Value | Deskripsi |
|-----------|----------|-------|-----------|
| `time_before` | `>=` | 6 | Sebelum jam 6 pagi |
| `time_after` | `>=` | 0 | Setelah jam 12 malam |
| `duration_days` | `>=` | 3 | Durasi minimal 3 hari |

**Contoh Badge:**
- "Morning Bird" - time_before >= 6
- "Night Owl" - time_after >= 0
- "Speed Runner" - duration_days >= 3

#### 4. Streak-Based (Berbasis Konsistensi)

| Criterion | Operator | Value | Deskripsi |
|-----------|----------|-------|-----------|
| `streak_days` | `>=` | 7 | Streak minimal 7 hari |
| `streak_days` | `>=` | 30 | Streak minimal 30 hari |

**Contoh Badge:**
- "Konsisten 7 Hari" - streak_days >= 7
- "Dedikasi Bulanan" - streak_days >= 30

#### 5. Target-Based (Berbasis Target)

| Criterion | Operator | Value | Deskripsi |
|-----------|----------|-------|-----------|
| `course_slug` | `=` | laravel-101 | Course tertentu |
| `is_weekend` | `=` | 1 | Hanya weekend |
| `is_first` | `=` | 1 | Yang pertama |

**Contoh Badge:**
- "Laravel Master" - course_slug = laravel-101
- "Weekend Warrior" - is_weekend = 1
- "First Blood" - is_first = 1

### Contoh Badge dengan Rules

#### Badge: "Nilai Sempurna"
```json
{
  "code": "perfect_score",
  "name": "Nilai Sempurna",
  "type": "quality",
  "threshold": 1,
  "rules": [
    {
      "criterion": "score",
      "operator": "=",
      "value": 100
    }
  ]
}
```

#### Badge: "Quality Assured 5x"
```json
{
  "code": "quality_assured_5x",
  "name": "Quality Assured 5x",
  "type": "quality",
  "threshold": 5,
  "rules": [
    {
      "criterion": "score",
      "operator": ">=",
      "value": 85
    },
    {
      "criterion": "attempts",
      "operator": ">=",
      "value": 5
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
      "criterion": "time_before",
      "operator": ">=",
      "value": 6
    }
  ]
}
```

#### Badge: "Laravel Master"
```json
{
  "code": "laravel_master",
  "name": "Laravel Master",
  "type": "completion",
  "threshold": 1,
  "rules": [
    {
      "criterion": "course_slug",
      "operator": "=",
      "value": "laravel-101"
    }
  ]
}
```

### Catatan Penting
- Rules bersifat AND (semua kondisi harus terpenuhi)
- Threshold menentukan berapa kali kondisi harus terpenuhi
- Badge tanpa rules hanya bisa diberikan manual
- Rules di-evaluate otomatis saat event trigger

---

## CATATAN UMUM

### Authorization Matrix

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| List Badges | ✅ | ✅ | ✅ | ✅ |
| Show Badge Detail | ✅ | ✅ | ✅ | ✅ |
| Get My Badges | ✅ | ✅ | ✅ | ✅ |
| Create Badge | ❌ | ❌ | ❌ | ✅ |
| Update Badge | ❌ | ❌ | ❌ | ✅ |
| Delete Badge | ❌ | ❌ | ❌ | ✅ |

### Response Format Standar

#### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

#### Success Response (Paginated)
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

#### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error description"]
  }
}
```

### HTTP Status Codes
- `200` - Success (GET, PUT, DELETE)
- `201` - Created (POST)
- `400` - Bad Request
- `401` - Unauthorized (tidak login)
- `403` - Forbidden (tidak punya akses)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## TIPS UNTUK UI/UX

### 1. Badge List Page

#### Table Columns
- Icon (thumbnail)
- Code
- Name
- Type (badge dengan warna)
- Threshold
- Created At
- Actions (Edit, Delete)

#### Filter Panel
```typescript
// Filters
- Type dropdown (All, Completion, Quality, Speed, Habit, Social, Hidden)
- Search box (Name/Code/Description) dengan debounce

// Sort options
- Newest First (default)
- Oldest First
- Name A-Z
- Name Z-A
- Type
```

#### Badge Type Colors
```css
.badge-completion { background: #10B981; color: #FFF; }
.badge-quality { background: #3B82F6; color: #FFF; }
.badge-speed { background: #F59E0B; color: #FFF; }
.badge-habit { background: #8B5CF6; color: #FFF; }
.badge-social { background: #F97316; color: #FFF; }
.badge-hidden { background: #6B7280; color: #FFF; }
```

---

### 2. Create/Edit Badge Form

#### Form Layout
```typescript
interface BadgeForm {
  code: string;           // Text input (required)
  name: string;           // Text input (required)
  description: string;    // Textarea (optional)
  type: BadgeType;        // Dropdown (required)
  threshold: number;      // Number input (optional, default: 1)
  icon: File;             // File upload (required for create)
  rules: BadgeRule[];     // Dynamic array (optional)
}

interface BadgeRule {
  criterion: string;      // Text input (required)
  operator: '=' | '>=' | '>'; // Dropdown (required)
  value: number;          // Number input (required)
}
```

#### Form Sections

**Section 1: Basic Information**
- Code (text input, max 50 chars, unique)
- Name (text input, max 255 chars)
- Description (textarea, max 1000 chars)
- Type (dropdown dengan warna badge)
- Threshold (number input, min 1, default 1)

**Section 2: Icon Upload**
- File upload dengan preview
- Accepted formats: JPEG, PNG, SVG, WebP
- Max size: 2MB
- Recommended: SVG (512x512px)
- Show current icon saat edit

**Section 3: Badge Rules (Optional)**
- Dynamic form array (add/remove rules)
- Each rule:
  - Criterion (text input)
  - Operator (dropdown: =, >=, >)
  - Value (number input)
- "Add Rule" button
- "Remove Rule" button per item

#### Validation Messages
```typescript
const validationMessages = {
  code: {
    required: "Code wajib diisi",
    max: "Code maksimal 50 karakter",
    unique: "Code sudah digunakan"
  },
  name: {
    required: "Nama badge wajib diisi",
    max: "Nama maksimal 255 karakter"
  },
  description: {
    max: "Deskripsi maksimal 1000 karakter"
  },
  type: {
    required: "Tipe badge wajib dipilih",
    invalid: "Tipe badge tidak valid"
  },
  threshold: {
    min: "Threshold minimal 1"
  },
  icon: {
    required: "Icon wajib diupload",
    mimes: "Format icon harus JPEG, PNG, SVG, atau WebP",
    max: "Ukuran icon maksimal 2MB"
  },
  rules: {
    criterion: {
      required: "Criterion wajib diisi"
    },
    operator: {
      required: "Operator wajib dipilih"
    },
    value: {
      required: "Value wajib diisi",
      min: "Value minimal 1"
    }
  }
};
```

---

### 3. Badge Detail Page

#### Layout
```typescript
interface BadgeDetailView {
  // Header
  icon: string;           // Large icon display
  name: string;           // Badge name
  code: string;           // Badge code (small text)
  type: BadgeType;        // Badge type dengan warna
  
  // Stats
  threshold: number;      // Jumlah pencapaian
  totalEarned: number;    // Berapa user yang punya
  
  // Description
  description: string;    // Full description
  
  // Rules
  rules: BadgeRule[];     // List of rules
  
  // Actions (Superadmin only)
  editButton: boolean;
  deleteButton: boolean;
}
```

#### Rules Display
```typescript
// Display rules dalam format human-readable
function formatRule(rule: BadgeRule): string {
  const operators = {
    '=': 'sama dengan',
    '>=': 'minimal',
    '>': 'lebih dari'
  };
  
  return `${rule.criterion} ${operators[rule.operator]} ${rule.value}`;
}

// Example output:
// - score minimal 85
// - attempts sama dengan 1
// - streak_days minimal 7
```

---

### 4. User Badge Display

#### Badge Grid Layout
```typescript
interface UserBadgeCard {
  icon: string;           // Badge icon
  name: string;           // Badge name
  earnedAt: Date;         // Tanggal dapat badge
  type: BadgeType;        // Badge type
  description: string;    // Badge description (tooltip)
}
```

#### Badge Grid
- Grid layout (3-4 columns)
- Card dengan icon, name, earned date
- Hover: Show description
- Click: Show badge detail modal
- Sort by: Newest, Oldest, Type

#### Empty State
```
┌─────────────────────────────────────┐
│         🏆                          │
│   Belum Ada Badge                   │
│   Mulai belajar untuk mendapat      │
│   badge pertama Anda!               │
│   [Lihat Semua Badge]               │
└─────────────────────────────────────┘
```

---

### 5. Badge Notification

#### Real-time Badge Award
```typescript
interface BadgeAwardNotification {
  badge: {
    id: number;
    code: string;
    name: string;
    icon_url: string;
    type: BadgeType;
  };
  earnedAt: Date;
}

// Display
- Toast notification dengan icon badge
- Celebration animation (confetti)
- Sound effect (optional)
- CTA: "Lihat Badge" atau "Tutup"
```

#### Notification Component
```typescript
function BadgeAwardNotification({ badge }: Props) {
  return (
    <div className="badge-notification">
      <img src={badge.icon_url} alt={badge.name} />
      <div>
        <h3>Badge Baru!</h3>
        <p>{badge.name}</p>
      </div>
      <button>Lihat Badge</button>
    </div>
  );
}
```

---

### 6. Responsive Design

#### Mobile View
- Badge grid: 2 columns
- Compact badge card
- Bottom sheet untuk badge detail
- Simplified form layout

#### Desktop View
- Badge grid: 4 columns
- Full badge card dengan description
- Modal untuk badge detail
- Multi-column form layout

---

### 7. Accessibility

#### Screen Reader Support
- ARIA labels untuk badge icons
- ARIA live region untuk badge award
- Keyboard navigation untuk badge grid
- Focus management untuk modals

#### High Contrast Mode
- Clear badge type colors
- Sufficient color contrast
- Icon dengan border
- Readable text

---

### 8. Performance Optimization

#### Caching Strategy
```typescript
// Cache badge list for 5 minutes
const { data: badges } = useQuery({
  queryKey: ['badges'],
  queryFn: fetchBadges,
  staleTime: 5 * 60 * 1000,
});

// Cache user badges for 1 minute
const { data: userBadges } = useQuery({
  queryKey: ['user-badges'],
  queryFn: fetchUserBadges,
  staleTime: 60 * 1000,
});
```

#### Lazy Loading
- Load badge icons on demand
- Infinite scroll untuk badge list
- Lazy load badge detail

---

### 9. Error Handling

#### Common Errors

**1. Badge Not Found**
```typescript
if (!badge) {
  return <EmptyState message="Badge tidak ditemukan" />;
}
```

**2. Upload Failed**
```typescript
<ErrorAlert 
  message="Gagal upload icon badge" 
  onRetry={handleRetry}
/>
```

**3. Validation Error**
```typescript
<FormError 
  field="code" 
  message="Code sudah digunakan" 
/>
```

---

## WORKFLOW REKOMENDASI

### Superadmin Workflow

```
1. Navigate to Badge Management
   ↓
2. Click "Tambah Badge"
   ↓
3. Fill form:
   - Code (unique identifier)
   - Name (display name)
   - Description
   - Type (dropdown)
   - Threshold (optional)
   - Upload icon
   - Add rules (optional)
   ↓
4. Submit → Badge created
   ↓
5. Badge akan otomatis diberikan ke user
   saat kondisi rules terpenuhi
```

### User Workflow

```
1. User melakukan aktivitas (lesson, assignment, dll)
   ↓
2. Sistem check badge rules
   ↓
3. Jika kondisi terpenuhi:
   - Award badge ke user
   - Show notification
   - Update user profile
   ↓
4. User bisa lihat badge di profile
```

---

## CHANGELOG

### Version 1.0 (14 Maret 2026)
- Initial release
- CRUD badge management (Superadmin only)
- Badge rules system
- Event-driven badge awarding
- User badge display
- Complete UI/UX guidelines

---

**Versi**: 1.0  
**Terakhir Update**: 14 Maret 2026  
**Kontak**: Backend Team
