# DOKUMENTASI API MANAJEMEN SKEMA ADMIN - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Schemes - Admin Course Management  
**Platform**: Web Admin Dashboard

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [Manajemen Skema (Course)](#manajemen-skema-course)
4. [Manajemen Unit Kompetensi](#manajemen-unit-kompetensi)
5. [Manajemen Elemen Kompetensi (Lesson)](#manajemen-elemen-kompetensi-lesson)
6. [Response Format](#response-format)
7. [Error Codes](#error-codes)
8. [Complete Use Case](#complete-use-case)

---

## 🎯 RINGKASAN

Dokumentasi ini menjelaskan complete admin workflow untuk mengelola skema pembelajaran:
1. **Manajemen Skema** - CRUD skema/course dengan pengaturan lengkap
2. **Manajemen Unit** - CRUD unit kompetensi dalam skema
3. **Manajemen Elemen** - CRUD elemen kompetensi/lesson dalam unit
4. **Pengaturan Konten** - Upload media, atur urutan, publikasi

### Fitur Utama
- ✅ CRUD lengkap untuk skema, unit, dan elemen
- ✅ Bulk operations (delete, reorder, duplicate)
- ✅ Media management (upload gambar, video, dokumen)
- ✅ Status management (draft, published)
- ✅ Prerequisite management
- ✅ Instructor assignment
- ✅ Statistics & analytics
- ✅ Soft delete & restore

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

## 📚 MANAJEMEN SKEMA (COURSE)

### 1.1. GET [Admin] Skema - Daftar Semua Skema

Melihat daftar semua skema dengan filtering, sorting, dan search.

#### Endpoint
```
GET /admin/courses
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `search` | string | ❌ No | - | Search di title, code, description |
| `filter[status]` | string | ❌ No | - | Filter status: `published`, `draft` |
| `filter[level_tag]` | string | ❌ No | - | Filter level: `beginner`, `intermediate`, `advanced` |
| `filter[type]` | string | ❌ No | - | Filter tipe: `self_paced`, `scheduled` |
| `filter[category_id]` | integer | ❌ No | - | Filter berdasarkan kategori ID |
| `filter[instructor_id]` | integer | ❌ No | - | Filter berdasarkan instructor ID |
| `sort` | string | ❌ No | -created_at | Sorting: `title`, `code`, `created_at`, `updated_at`, `published_at` |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[status]**:
- `published` - Skema yang sudah dipublikasikan
- `draft` - Skema yang masih draft

**filter[level_tag]**:
- `beginner` - Level pemula
- `intermediate` - Level menengah
- `advanced` - Level lanjut

**filter[type]**:
- `self_paced` - Kursus belajar mandiri
- `scheduled` - Kursus terjadwal

**sort**:
- `title` / `-title` - Berdasarkan judul
- `code` / `-code` - Berdasarkan kode
- `created_at` / `-created_at` - Berdasarkan tanggal dibuat (default: newest first)
- `updated_at` / `-updated_at` - Berdasarkan tanggal update
- `published_at` / `-published_at` - Berdasarkan tanggal publikasi

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar skema berhasil diambil",
  "data": [
    {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming",
      "code": "PROG-101",
      "short_desc": "Learn programming basics",
      "thumbnail_url": "https://api.levl.id/storage/courses/intro.jpg",
      "level_tag": "beginner",
      "type": "self_paced",
      "status": "published",
      "duration_estimate": 40,
      "instructor": {
        "id": 2,
        "name": "John Doe"
      },
      "category": {
        "id": 1,
        "name": "Programming"
      },
      "stats": {
        "total_units": 5,
        "total_lessons": 33,
        "total_students": 150
      },
      "created_at": "2026-01-15T10:00:00.000000Z",
      "published_at": "2026-01-20T10:00:00.000000Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 50,
      "last_page": 4
    }
  }
}
```

#### Postman Example
```javascript
// Query Params - All Courses
per_page: 15
sort: -created_at
page: 1

// Query Params - Search & Filter
search: programming
filter[status]: published
filter[level_tag]: beginner
sort: title

// Query Params - Filter by Instructor
filter[instructor_id]: 2
filter[status]: published
sort: -published_at

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has courses", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Has pagination", () => {
    const meta = pm.response.json().meta;
    pm.expect(meta).to.have.property('pagination');
});

// Save first course
if (pm.response.json().data.length > 0) {
    pm.environment.set("course_id", pm.response.json().data[0].id);
    pm.environment.set("course_slug", pm.response.json().data[0].slug);
}
```

---

### 1.2. GET [Admin] Skema - Detail Skema

Melihat detail lengkap skema termasuk units, lessons, assignments, quizzes.

#### Endpoint
```
GET /admin/courses/{course_id}
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_id` | integer | ✅ Yes | ID skema |

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `include` | string | ❌ No | Includes: `units`, `instructor`, `category`, `outcomes`, `prerequisites` |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Detail skema berhasil diambil",
  "data": {
    "id": 1,
    "title": "Introduction to Programming",
    "slug": "introduction-to-programming",
    "code": "PROG-101",
    "short_desc": "Learn programming basics",
    "description": "Comprehensive introduction to programming...",
    "thumbnail_url": "https://api.levl.id/storage/courses/intro.jpg",
    "banner_url": "https://api.levl.id/storage/courses/intro-banner.jpg",
    "level_tag": "beginner",
    "type": "self_paced",
    "duration_estimate": 40,
    "enrollment_type": "open",
    "status": "published",
    "instructor": {
      "id": 2,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "category": {
      "id": 1,
      "name": "Programming",
      "slug": "programming"
    },
    "outcomes": [
      {
        "id": 1,
        "description": "Understand basic programming concepts",
        "order": 1
      }
    ],
    "stats": {
      "total_units": 5,
      "total_lessons": 33,
      "total_assignments": 8,
      "total_quizzes": 5,
      "total_students": 150,
      "active_students": 120,
      "completed_students": 30
    },
    "created_at": "2026-01-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z",
    "published_at": "2026-01-20T10:00:00.000000Z"
  }
}
```

---

### 1.3. POST [Admin] Skema - Tambah Skema Baru

Membuat skema/course baru.

#### Endpoint
```
POST /admin/courses
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Request Body (JSON)
```json
{
  "title": "Introduction to Programming",
  "code": "PROG-101",
  "short_desc": "Learn programming basics",
  "description": "Comprehensive introduction to programming concepts...",
  "level_tag": "beginner",
  "type": "self_paced",
  "duration_estimate": 40,
  "enrollment_type": "open",
  "status": "draft",
  "category_id": 1,
  "instructor_id": 2,
  "thumbnail": "file_upload",
  "banner": "file_upload"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `title` | string | ✅ Yes | max:255, unique | Judul skema |
| `code` | string | ✅ Yes | max:50, unique | Kode skema |
| `short_desc` | string | ✅ Yes | max:500 | Deskripsi singkat |
| `description` | text | ❌ No | - | Deskripsi lengkap |
| `level_tag` | string | ✅ Yes | in:beginner,intermediate,advanced | Level skema |
| `type` | string | ✅ Yes | in:self_paced,scheduled | Tipe skema |
| `duration_estimate` | integer | ❌ No | min:1 | Estimasi durasi (menit) |
| `enrollment_type` | string | ✅ Yes | in:open,key,approval | Tipe pendaftaran |
| `status` | string | ❌ No | in:draft,published | Status (default: draft) |
| `category_id` | integer | ✅ Yes | exists:categories,id | ID kategori |
| `instructor_id` | integer | ✅ Yes | exists:users,id | ID instructor |
| `thumbnail` | file | ❌ No | image, max:2MB | Thumbnail skema |
| `banner` | file | ❌ No | image, max:5MB | Banner skema |

#### Response Success (201 Created)
```json
{
  "success": true,
  "message": "Skema berhasil dibuat",
  "data": {
    "id": 51,
    "title": "Introduction to Programming",
    "slug": "introduction-to-programming",
    "code": "PROG-101",
    "status": "draft",
    "created_at": "2026-03-15T10:00:00.000000Z"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "title": ["The title has already been taken."],
    "code": ["The code has already been taken."],
    "category_id": ["The selected category is invalid."]
  }
}
```

---

### 1.4. PUT [Admin] Skema - Update Skema

Mengupdate data skema yang sudah ada.

#### Endpoint
```
PUT /admin/courses/{course_id}
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_id` | integer | ✅ Yes | ID skema |

#### Request Body (JSON)
```json
{
  "title": "Introduction to Programming - Updated",
  "short_desc": "Learn programming basics - Updated",
  "description": "Updated description...",
  "level_tag": "intermediate",
  "duration_estimate": 45
}
```

**Note**: Hanya field yang ingin diupdate yang perlu dikirim (partial update).

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Skema berhasil diupdate",
  "data": {
    "id": 1,
    "title": "Introduction to Programming - Updated",
    "slug": "introduction-to-programming-updated",
    "updated_at": "2026-03-15T11:00:00.000000Z"
  }
}
```

---

### 1.5. DELETE [Admin] Skema - Hapus Skema

Menghapus skema (soft delete). Skema akan dipindahkan ke trash dan bisa di-restore.

#### Endpoint
```
DELETE /admin/courses/{course_id}
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Skema berhasil dihapus",
  "data": {
    "id": 1,
    "title": "Introduction to Programming",
    "deleted_at": "2026-03-15T11:00:00.000000Z"
  }
}
```

**Note**: 
- Skema yang dihapus akan masuk ke trash
- Bisa di-restore melalui endpoint `/admin/trash/restore`
- Permanent delete melalui endpoint `/admin/trash/force-delete`

---

### 1.6. POST [Admin] Skema - Publikasikan Skema

Mengubah status skema dari draft menjadi published.

#### Endpoint
```
POST /admin/courses/{course_id}/publish
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Validation Rules
- ✅ Skema harus memiliki minimal 1 unit
- ✅ Setiap unit harus memiliki minimal 1 lesson
- ✅ Instructor harus sudah di-assign
- ✅ Thumbnail dan banner harus sudah di-upload

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Skema berhasil dipublikasikan",
  "data": {
    "id": 1,
    "title": "Introduction to Programming",
    "status": "published",
    "published_at": "2026-03-15T11:00:00.000000Z"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Skema tidak dapat dipublikasikan",
  "errors": {
    "units": ["Skema harus memiliki minimal 1 unit"],
    "instructor": ["Instructor belum di-assign"]
  }
}
```

---

### 1.7. POST [Admin] Skema - Batalkan Publikasi

Mengubah status skema dari published kembali ke draft.

#### Endpoint
```
POST /admin/courses/{course_id}/unpublish
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Publikasi skema berhasil dibatalkan",
  "data": {
    "id": 1,
    "title": "Introduction to Programming",
    "status": "draft",
    "published_at": null
  }
}
```

**Warning**: Skema yang sudah memiliki student enrollment tidak bisa di-unpublish.

---

### 1.8. POST [Admin] Skema - Duplikasi Skema

Menduplikasi skema beserta semua unit dan lesson-nya.

#### Endpoint
```
POST /admin/courses/{course_id}/duplicate
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Request Body (JSON)
```json
{
  "title": "Introduction to Programming - Copy",
  "code": "PROG-101-COPY"
}
```

#### Response Success (201 Created)
```json
{
  "success": true,
  "message": "Skema berhasil diduplikasi",
  "data": {
    "id": 52,
    "title": "Introduction to Programming - Copy",
    "slug": "introduction-to-programming-copy",
    "code": "PROG-101-COPY",
    "status": "draft",
    "original_id": 1,
    "created_at": "2026-03-15T11:00:00.000000Z"
  }
}
```

**Note**: 
- Duplikasi akan menyalin semua units, lessons, assignments, quizzes
- Status selalu draft
- Enrollment data tidak disalin
- Media files akan di-copy

---

### 1.9. GET [Admin] Skema - Statistik Skema

Melihat statistik lengkap skema.

#### Endpoint
```
GET /admin/courses/{course_id}/statistics
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Statistik skema berhasil diambil",
  "data": {
    "course": {
      "id": 1,
      "title": "Introduction to Programming"
    },
    "content": {
      "total_units": 5,
      "total_lessons": 33,
      "total_assignments": 8,
      "total_quizzes": 5,
      "total_duration_minutes": 2400
    },
    "enrollment": {
      "total_students": 150,
      "active_students": 120,
      "completed_students": 30,
      "completion_rate": 20
    },
    "engagement": {
      "average_progress": 45.5,
      "average_time_spent": 1250,
      "forum_posts": 245,
      "forum_replies": 680
    },
    "assessment": {
      "average_assignment_score": 82.5,
      "average_quiz_score": 78.3,
      "submission_rate": 85
    }
  }
}
```
