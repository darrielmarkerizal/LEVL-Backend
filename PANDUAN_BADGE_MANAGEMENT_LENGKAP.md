# Panduan Lengkap Badge Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap API Badge untuk kebutuhan management (Superadmin/Admin/Instructor) dan user profile (my badges).

---

## Daftar Isi

1. [Badge Overview](#1-badge-overview)
2. [List Badges](#2-list-badges)
3. [Show Badge Detail](#3-show-badge-detail)
4. [Create Badge](#4-create-badge)
5. [Update Badge](#5-update-badge)
6. [Delete Badge](#6-delete-badge)
7. [Get My Badges (User)](#7-get-my-badges-user)
8. [Authorization Matrix](#8-authorization-matrix)
9. [UI/UX Implementation Notes](#9-uiux-implementation-notes)

---

## 1. BADGE OVERVIEW

### Base Endpoint
- Management Badge API: `/api/v1/badges`
- User Badge API: `/api/v1/user/badges`

### Authentication
Semua endpoint membutuhkan token Bearer:
```
Authorization: Bearer {token}
```

### Badge Fields (Core)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID badge |
| `code` | string | Kode unik badge |
| `name` | string | Nama badge |
| `description` | string/null | Deskripsi badge |
| `type` | enum | Tipe badge |
| `threshold` | integer/null | Ambang batas perolehan badge |
| `icon_url` | string/null | URL icon badge |
| `icon_thumb_url` | string/null | URL thumbnail icon |
| `created_at` | datetime | Waktu dibuat |
| `updated_at` | datetime | Waktu diupdate |

### Badge Type (Enum)

| Value | Deskripsi |
|-------|-----------|
| `completion` | Badge penyelesaian konten/aktivitas |
| `quality` | Badge kualitas performa (nilai, skor, dsb.) |
| `speed` | Badge kecepatan penyelesaian |
| `habit` | Badge konsistensi/kebiasaan |
| `social` | Badge aktivitas sosial (interaksi komunitas) |
| `hidden` | Badge tersembunyi (secret badge) |

---

## 2. LIST BADGES

### Endpoint
```
GET /api/v1/badges
```

### Authorization
- Authenticated user
- Secara policy dapat diakses semua role yang login

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `per_page` | integer | ❌ Tidak | 15 | Jumlah item per halaman (min 1, max 100) |
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `search` | string | ❌ Tidak | - | Full-text search pada code/name/description |
| `filter[id]` | integer | ❌ Tidak | - | Filter exact by badge ID |
| `filter[code]` | string | ❌ Tidak | - | Filter partial by code |
| `filter[name]` | string | ❌ Tidak | - | Filter partial by name |
| `filter[type]` | string | ❌ Tidak | - | Filter exact by type |
| `filter[search]` | string | ❌ Tidak | - | Search via filter callback |
| `sort` | string | ❌ Tidak | -created_at | Sorting |
| `include` | string | ❌ Tidak | - | Include relations (`rules`) |

### Allowed Sorts

| Sort | Deskripsi |
|------|-----------|
| `id` | Sort by ID |
| `code` | Sort by code |
| `name` | Sort by name |
| `type` | Sort by type |
| `threshold` | Sort by threshold |
| `created_at` | Sort by created date |
| `updated_at` | Sort by updated date |

Catatan: Tambahkan `-` di depan untuk descending. Contoh: `-created_at`.

### Allowed Includes
- `rules`

### Contoh Request

#### 1. Default List
```
GET /api/v1/badges
```

#### 2. Filter by Type
```
GET /api/v1/badges?filter[type]=completion
```

#### 3. Search + Sort
```
GET /api/v1/badges?search=course&sort=name&per_page=20
```

#### 4. Include Rules
```
GET /api/v1/badges?include=rules
```

### Response Format

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "code": "course_completion_1",
      "name": "Course Finisher",
      "description": "Menyelesaikan 1 course",
      "type": "completion",
      "threshold": 1,
      "icon_url": "https://cdn.example.com/badges/course_completion_1.png",
      "icon_thumb_url": "https://cdn.example.com/badges/conversions/course_completion_1-thumb.png",
      "created_at": "2026-03-08T08:00:00.000000Z",
      "updated_at": "2026-03-08T08:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/v1/badges",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

## 3. SHOW BADGE DETAIL

### Endpoint
```
GET /api/v1/badges/{badge_id}
```

### Authorization
- Authenticated user
- Secara policy dapat diakses semua role yang login

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `badge_id` | integer | ✅ Ya | ID badge |

### Contoh Request
```
GET /api/v1/badges/1
```

### Response Format (200)

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "code": "course_completion_1",
    "name": "Course Finisher",
    "description": "Menyelesaikan 1 course",
    "type": "completion",
    "threshold": 1,
    "icon_url": "https://cdn.example.com/badges/course_completion_1.png",
    "icon_thumb_url": "https://cdn.example.com/badges/conversions/course_completion_1-thumb.png",
    "rules": [
      {
        "id": 10,
        "event_trigger": "course_completed",
        "conditions": {
          "min_courses": 1
        }
      }
    ],
    "created_at": "2026-03-08T08:00:00.000000Z",
    "updated_at": "2026-03-08T08:00:00.000000Z"
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

---

## 4. CREATE BADGE

### Endpoint
```
POST /api/v1/badges
```

### Authorization
- **Effective route access saat ini: Superadmin only**
- Endpoint berada di middleware `role:Superadmin`

### Content-Type
`multipart/form-data` (karena upload icon file)

### Body Fields

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `code` | string | ✅ Ya | max:50, unique | Kode unik badge |
| `name` | string | ✅ Ya | max:255 | Nama badge |
| `description` | string | ❌ Tidak | max:1000 | Deskripsi badge |
| `type` | enum | ✅ Ya | completion, quality, speed, habit, social, hidden | Tipe badge |
| `threshold` | integer | ❌ Tidak | min:1 | Ambang batas badge |
| `icon` | file | ✅ Ya | jpeg,png,svg,webp, max 2MB | File icon badge |
| `rules` | array | ❌ Tidak | array | Konfigurasi rule badge |

### Rule Fields (Request Validation)

| Field | Tipe | Required | Validasi |
|-------|------|----------|----------|
| `rules.*.criterion` | string | ✅ Ya (jika rules diisi) | max:50 |
| `rules.*.operator` | string | ✅ Ya (jika rules diisi) | `=`, `>=`, `>` |
| `rules.*.value` | integer | ✅ Ya (jika rules diisi) | min:1 |

### Contoh Request (Form Data)

```
code: course_completion_1
name: Course Finisher
description: Menyelesaikan 1 course
type: completion
threshold: 1
icon: [FILE PNG]
rules[0][criterion]: courses_completed
rules[0][operator]: >=
rules[0][value]: 1
```

### Response Format (201)

```json
{
  "success": true,
  "message": "Badge created successfully",
  "data": {
    "id": 21,
    "code": "course_completion_1",
    "name": "Course Finisher",
    "description": "Menyelesaikan 1 course",
    "type": "completion",
    "threshold": 1,
    "icon_url": "https://cdn.example.com/badges/course_completion_1.png",
    "icon_thumb_url": "https://cdn.example.com/badges/conversions/course_completion_1-thumb.png",
    "created_at": "2026-03-08T09:10:00.000000Z",
    "updated_at": "2026-03-08T09:10:00.000000Z"
  }
}
```

### Error Responses

#### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "code": ["The code has already been taken."],
    "icon": ["The icon field is required."],
    "type": ["The selected type is invalid."]
  }
}
```

#### Forbidden (403)
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

### Catatan Penting
- Gunakan `multipart/form-data` untuk create karena field `icon` wajib file.
- Icon disimpan sebagai single file collection (replace jika upload baru).
- SVG tidak butuh thumbnail conversion khusus (thumb bisa sama dengan original URL).

---

## 5. UPDATE BADGE

### Endpoint
```
PUT /api/v1/badges/{badge_id}
```

### Authorization
- **Effective route access saat ini: Superadmin only**

### Content-Type
`multipart/form-data` atau `application/json`

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `badge_id` | integer | ✅ Ya | ID badge |

### Body Fields
Semua field bersifat optional (`sometimes`), hanya kirim yang ingin diubah.

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `code` | string | ❌ Tidak | max:50, unique (exclude current id) | Kode badge |
| `name` | string | ❌ Tidak | max:255 | Nama badge |
| `description` | string | ❌ Tidak | max:1000 | Deskripsi badge |
| `type` | enum | ❌ Tidak | completion, quality, speed, habit, social, hidden | Tipe badge |
| `threshold` | integer | ❌ Tidak | min:1 | Ambang batas |
| `icon` | file | ❌ Tidak | jpeg,png,svg,webp, max 2MB | Icon baru |
| `rules` | array | ❌ Tidak | array | Replace seluruh rule badge |

### Catatan Rules Update
- Jika field `rules` dikirim: sistem akan hapus seluruh rule lama lalu insert rule baru.
- Jika field `rules` tidak dikirim: rule lama dipertahankan.

### Contoh Request (JSON)

```json
{
  "name": "Course Finisher Bronze",
  "description": "Menyelesaikan minimal 1 course",
  "threshold": 1
}
```

### Contoh Request (Form Data, update icon)

```
name: Course Finisher Bronze
icon: [FILE WEBP]
```

### Response Format (200)

```json
{
  "success": true,
  "message": "Badge updated successfully",
  "data": {
    "id": 21,
    "code": "course_completion_1",
    "name": "Course Finisher Bronze",
    "description": "Menyelesaikan minimal 1 course",
    "type": "completion",
    "threshold": 1,
    "icon_url": "https://cdn.example.com/badges/course_completion_1.webp",
    "icon_thumb_url": "https://cdn.example.com/badges/conversions/course_completion_1-thumb.webp",
    "created_at": "2026-03-08T09:10:00.000000Z",
    "updated_at": "2026-03-08T09:30:00.000000Z"
  }
}
```

### Error Responses

#### Badge Not Found (404)
```json
{
  "success": false,
  "message": "Badge not found",
  "errors": null
}
```

#### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "code": ["The code has already been taken."],
    "icon": ["The icon must be a file of type: jpeg, png, svg, webp."]
  }
}
```

---

## 6. DELETE BADGE

### Endpoint
```
DELETE /api/v1/badges/{badge_id}
```

### Authorization
- **Superadmin only**

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `badge_id` | integer | ✅ Ya | ID badge |

### Business Rules
- Menghapus media icon badge terlebih dahulu.
- Setelah itu badge dihapus dari database.

### Contoh Request
```
DELETE /api/v1/badges/21
```

### Response Format (200)

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

---

## 7. GET MY BADGES (USER)

Endpoint ini digunakan untuk menampilkan daftar badge yang sudah didapatkan user login. Biasanya dipakai di profile, progress, atau dashboard student.

### Endpoint
```
GET /api/v1/user/badges
```

### Authorization
- Authenticated user (Student, Instructor, Admin, Superadmin)

### Query Parameters
Tidak ada.

### Response Format (200)

```json
{
  "success": true,
  "message": "User badges retrieved successfully.",
  "data": [
    {
      "id": 1,
      "code": "course_completion_1",
      "name": "Course Finisher",
      "description": "Menyelesaikan 1 course",
      "icon_url": "https://cdn.example.com/badges/course_completion_1.png",
      "type": "completion",
      "awarded_at": "2026-03-08T10:00:00Z"
    },
    {
      "id": 7,
      "code": "streak_7_days",
      "name": "Week Warrior",
      "description": "Belajar 7 hari berturut-turut",
      "icon_url": "https://cdn.example.com/badges/streak_7_days.png",
      "type": "habit",
      "awarded_at": "2026-03-07T21:30:00Z"
    }
  ]
}
```

### Catatan Penting
- Data yang dikembalikan adalah badge milik user login, bukan katalog semua badge.
- Tidak ada pagination di endpoint ini.
- Gunakan endpoint ini untuk badge showcase di UI profile.

---

## 8. AUTHORIZATION MATRIX

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| List Badges | ✅ | ✅ | ✅ | ✅ |
| Show Badge Detail | ✅ | ✅ | ✅ | ✅ |
| Create Badge | ❌ | ❌ | ❌ (via route) | ✅ |
| Update Badge | ❌ | ❌ | ❌ (via route) | ✅ |
| Delete Badge | ❌ | ❌ | ❌ | ✅ |
| Get My Badges | ✅ | ✅ | ✅ | ✅ |

Catatan: Walau policy internal mengizinkan Admin untuk create/update, route saat ini membatasi endpoint write badge hanya untuk `Superadmin`.

---

## 9. UI/UX IMPLEMENTATION NOTES

### 9.1 Badge Management (Admin Panel)

1. Tampilkan table/grid dengan kolom: Icon, Code, Name, Type, Threshold, Updated At.
2. Sediakan filter cepat by type (`completion`, `quality`, dll).
3. Implementasi search dengan debounce 300ms.
4. Sediakan sort dropdown: Created Date, Name, Type, Threshold.
5. Gunakan modal konfirmasi sebelum delete.

### 9.2 Create/Update Form

1. Gunakan file uploader khusus icon (preview image langsung).
2. Validasi ukuran file di client: max 2MB.
3. Batasi extension icon: `.jpg`, `.jpeg`, `.png`, `.svg`, `.webp`.
4. Tampilkan helper text untuk `code`: harus unik dan konsisten snake_case.
5. Untuk hidden badge (`type=hidden`), rekomendasikan toggle "Tampilkan ke user" di frontend (opsional UI logic).

### 9.3 User Badge Showcase

1. Tampilkan badge sebagai card/icon grid di profile user.
2. Urutkan badge berdasarkan `awarded_at` terbaru terlebih dulu.
3. Tambahkan tooltip berisi `name` dan `description`.
4. Jika belum punya badge: tampilkan empty state + CTA belajar.
5. Bisa tambahkan grouped view by `type` untuk UX yang lebih rapi.

### 9.4 Suggested UI Flow

#### Create Badge Flow
1. Superadmin buka halaman Badge Management.
2. Klik tombol "Create Badge".
3. Isi form (code, name, type, threshold, icon, rules).
4. Submit.
5. Tampilkan toast sukses + redirect ke detail/list.

#### Edit Badge Flow
1. Superadmin klik action "Edit" pada badge.
2. Form pre-filled dengan data badge lama.
3. Ubah field yang dibutuhkan.
4. Submit.
5. Tampilkan perubahan di list/detail.

#### User Badge View Flow
1. User buka profile/dashboard.
2. Frontend hit endpoint `/api/v1/user/badges`.
3. Render badge grid.
4. User klik badge untuk detail modal/popover.

---

## HTTP Status Codes

- `200` - Success (GET, PUT, DELETE)
- `201` - Created (POST)
- `401` - Unauthorized (token invalid/tidak login)
- `403` - Forbidden (role tidak punya akses)
- `404` - Not Found (badge tidak ditemukan)
- `422` - Validation Error
- `500` - Server Error

---

## Changelog

### Version 1.0 (8 Maret 2026)
- Initial release
- Badge catalog endpoints (list/detail)
- Badge write endpoints (create/update/delete)
- User badges endpoint
- Full validation, auth matrix, dan UI/UX notes

---

**Versi**: 1.0  
**Terakhir Update**: 8 Maret 2026  
**Kontak**: Backend Team
