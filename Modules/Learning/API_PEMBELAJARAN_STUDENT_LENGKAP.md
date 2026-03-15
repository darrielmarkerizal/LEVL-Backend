# DOKUMENTASI API PEMBELAJARAN STUDENT - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Learning - Student Journey  
**Platform**: Mobile & Web Student

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [Student Learning Journey](#student-learning-journey)
4. [1. Mencari & Browse Kursus](#1-mencari--browse-kursus)
5. [2. Enrollment (Pendaftaran)](#2-enrollment-pendaftaran)
6. [3. Akses Kursus & Progress](#3-akses-kursus--progress)
7. [4. Pembelajaran Materi](#4-pembelajaran-materi)
8. [5. Mengerjakan Tugas](#5-mengerjakan-tugas)
9. [6. Mengerjakan Kuis](#6-mengerjakan-kuis)
10. [Response Format](#response-format)
11. [Error Codes](#error-codes)
12. [Complete Use Case](#complete-use-case)

---

## 🎯 RINGKASAN

Dokumentasi ini menjelaskan complete student learning journey dari awal sampai akhir:
1. **Mencari Kursus** - Browse dan search kursus yang tersedia
2. **Enroll** - Mendaftar ke kursus yang dipilih
3. **Akses Konten** - Melihat struktur kursus (units & lessons)
4. **Belajar Materi** - Membaca materi dan menandai selesai
5. **Mengerjakan Tugas** - Submit assignment dan mendapat feedback
6. **Mengerjakan Kuis** - Attempt quiz dan mendapat nilai

### Fitur Utama
- ✅ Search & filter kursus
- ✅ Self-enrollment dengan/tanpa enrollment key
- ✅ Track progress real-time
- ✅ Complete lessons dan dapatkan XP
- ✅ Submit assignments dengan file upload
- ✅ Take quizzes dengan timer
- ✅ Automatic grading untuk quiz
- ✅ Gamification (XP, badges, levels)

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

## 🚀 STUDENT LEARNING JOURNEY

```
┌─────────────────────────────────────────────────────────────┐
│                   STUDENT LEARNING FLOW                      │
└─────────────────────────────────────────────────────────────┘

1. DISCOVERY PHASE
   ├── Browse & search courses (GET /courses?search=keyword)
   ├── Filter courses (GET /courses?filter[level_tag]=beginner)
   └── View course detail (GET /courses/{slug})

2. ENROLLMENT PHASE
   ├── Check enrollment status (GET /courses/{slug}/enrollment-status)
   ├── Enroll to course (POST /courses/{slug}/enroll)
   └── View my courses (GET /my-courses)

3. LEARNING PHASE
   ├── View course structure (GET /courses/{slug}/units)
   ├── View unit contents (GET /courses/{slug}/units/{slug}/contents)
   ├── Read lesson (GET /courses/{slug}/units/{slug}/lessons/{slug})
   ├── Mark lesson complete (POST /lessons/{slug}/complete) → +XP
   └── Track progress (GET /courses/{slug}/progress)

4. ASSESSMENT PHASE - ASSIGNMENTS
   ├── View assignments (GET /courses/{slug}/assignments)
   ├── View assignment detail (GET /assignments/{id})
   ├── Submit assignment (POST /assignments/{id}/submissions) → +XP
   ├── View submission (GET /assignments/{id}/submissions/{id})
   └── Check grade (GET /assignments/{id}/submissions/highest)

5. ASSESSMENT PHASE - QUIZZES
   ├── View quizzes (GET /courses/{slug}/quizzes)
   ├── View quiz detail (GET /quizzes/{id})
   ├── Start quiz attempt (POST /quizzes/{id}/submissions/start) → +XP
   ├── Answer questions (POST /quiz-submissions/{id}/answers)
   ├── Submit quiz (POST /quiz-submissions/{id}/submit) → +XP
   └── View results (GET /quiz-submissions/{id})

6. COMPLETION
   └── Course completed → Certificate + Badge + XP
```

---

## 1. MENCARI & BROWSE KURSUS

### 1.1. GET [Mobile] Kursus - Browse & Cari Kursus

Melihat daftar kursus dengan fitur search, filter, dan sorting.

#### Endpoint
```
GET /courses
```

#### Authorization
```
🌐 PUBLIC - No authentication required (Bearer token optional)
```

**Note**: Endpoint ini bisa diakses tanpa login untuk memungkinkan user browse kursus sebelum mendaftar. Jika authenticated, response akan include informasi enrollment status user untuk setiap kursus.

**Default Behavior**: Endpoint ini secara default hanya menampilkan course dengan `status: published`. Course dengan status `draft` tidak akan muncul di list public.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `search` | string | ❌ No | - | Keyword pencarian (search di title, description, code) |
| `filter[status]` | string | ❌ No | published | Filter status: `published`, `draft` |
| `filter[level_tag]` | string | ❌ No | - | Filter level: `beginner`, `intermediate`, `advanced` |
| `filter[type]` | string | ❌ No | - | Filter tipe: `self_paced`, `scheduled` |
| `filter[category_id]` | integer | ❌ No | - | Filter berdasarkan kategori ID |
| `sort` | string | ❌ No | title | Sorting: `title`, `code`, `created_at`, `updated_at`, `published_at` (prefix `-` untuk desc) |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**search**:
- Minimal: 1 karakter
- Maksimal: 255 karakter
- Mencari di: title, description, code
- Contoh: `"programming"`, `"web development"`, `"PROG-101"`

**filter[status]**:
- `published` - Hanya kursus yang dipublikasikan (default untuk public)
- `draft` - Hanya draft (admin/instructor only)

**filter[level_tag]**:
- `beginner` - Level pemula
- `intermediate` - Level menengah
- `advanced` - Level lanjut

**filter[type]**:
- `self_paced` - Kursus belajar mandiri
- `scheduled` - Kursus terjadwal

**sort**:
- `title` - Berdasarkan judul (A-Z)
- `-title` - Berdasarkan judul (Z-A)
- `code` - Berdasarkan kode kursus
- `created_at` - Berdasarkan tanggal dibuat (oldest first)
- `-created_at` - Berdasarkan tanggal dibuat (newest first)
- `updated_at` - Berdasarkan tanggal update
- `-updated_at` - Berdasarkan tanggal update (newest first)
- `published_at` - Berdasarkan tanggal publikasi
- `-published_at` - Berdasarkan tanggal publikasi (newest first)

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar kursus berhasil diambil",
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
        "name": "John Doe",
        "avatar_url": "https://api.levl.id/storage/avatars/john.jpg"
      },
      "category": {
        "id": 1,
        "name": "Programming"
      },
      "enrollments_count": 150
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
// Headers (OPTIONAL - dapat diakses tanpa token)
// Authorization: Bearer {{auth_token}}

// Query Params - Browse All
per_page: 15
page: 1
sort: -published_at

// Query Params - Search with Filters
search: programming
filter[level_tag]: beginner
filter[category_id]: 1
sort: title
per_page: 15

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has courses", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Pagination exists", () => {
    const meta = pm.response.json().meta;
    pm.expect(meta).to.have.property('pagination');
});
pm.test("Public access works", () => {
    // Endpoint ini bisa diakses tanpa authentication
    pm.expect(pm.response.code).to.equal(200);
});

// Save first course
if (pm.response.json().data.length > 0) {
    pm.environment.set("course_slug", pm.response.json().data[0].slug);
}
```

---

### 1.2. GET [Mobile] Kursus - Detail Kursus

Melihat detail lengkap kursus sebelum enroll.

#### Endpoint
```
GET /courses/{course_slug}
```

#### Authorization
```
🌐 PUBLIC - No authentication required (Bearer token optional)
```

**Note**: Endpoint ini bisa diakses tanpa login untuk memungkinkan user melihat detail kursus sebelum mendaftar. Jika authenticated, response akan include informasi enrollment status user.

**Authorization Rules**:
- ✅ **Published course** (`status: published`): Dapat diakses siapa saja tanpa authentication
- 🔒 **Draft course** (`status: draft`): Hanya dapat diakses oleh:
  - Superadmin
  - Admin
  - Instructor (pemilik course)

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_slug` | string | ✅ Yes | Slug kursus |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Detail kursus berhasil diambil",
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
      "avatar_url": "https://api.levl.id/storage/avatars/john.jpg",
      "bio": "Experienced programming instructor"
    },
    "category": {
      "id": 1,
      "name": "Programming",
      "slug": "programming"
    },
    "tags": ["programming", "beginner", "fundamentals"],
    "outcomes": [
      {
        "id": 1,
        "description": "Understand basic programming concepts",
        "order": 1
      },
      {
        "id": 2,
        "description": "Write simple programs",
        "order": 2
      }
    ],
    "stats": {
      "total_units": 5,
      "total_lessons": 33,
      "total_assignments": 8,
      "total_quizzes": 5,
      "total_students": 150,
      "average_rating": 4.5
    },
    "created_at": "2026-01-15T10:00:00.000000Z",
    "published_at": "2026-01-20T10:00:00.000000Z"
  }
}
```

#### Response Error (403 Forbidden - Course Not Published)
```json
{
  "success": false,
  "message": "Akses ke resource ini tidak diizinkan.",
  "errors": null
}
```

**Penyebab**: Course status bukan `published`. Hanya published courses yang bisa diakses public.

**Solusi**: 
- Gunakan course yang sudah published
- Atau login sebagai Admin/Instructor untuk akses draft courses

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "Course not found",
  "errors": null
}
```

#### Postman Example
```javascript
// Headers (OPTIONAL - dapat diakses tanpa token)
// Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/courses/{{course_slug}}

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has course detail", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('id');
    pm.expect(data).to.have.property('enrollment_type');
    pm.expect(data).to.have.property('instructor');
});
pm.test("Public access works", () => {
    // Endpoint ini bisa diakses tanpa authentication
    pm.expect(pm.response.code).to.equal(200);
});
```

---

## 2. ENROLLMENT (PENDAFTARAN)

### 2.1. GET [Mobile] Enrollment - Cek Status Enrollment

Mengecek apakah student sudah enroll ke kursus atau belum.

#### Endpoint
```
GET /courses/{course_slug}/enrollment-status
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_slug` | string | ✅ Yes | Slug kursus |

#### Response Success (200 OK) - Not Enrolled
```json
{
  "success": true,
  "message": "Status enrollment berhasil diambil",
  "data": {
    "is_enrolled": false,
    "can_enroll": true,
    "enrollment_type": "open",
    "requires_key": false,
    "message": "Anda belum terdaftar di kursus ini"
  }
}
```

#### Response Success (200 OK) - Already Enrolled
```json
{
  "success": true,
  "message": "Status enrollment berhasil diambil",
  "data": {
    "is_enrolled": true,
    "can_enroll": false,
    "enrollment": {
      "id": 10,
      "status": "active",
      "enrolled_at": "2026-03-01T10:00:00.000000Z",
      "progress_percentage": 0
    },
    "message": "Anda sudah terdaftar di kursus ini"
  }
}
```

---

### 2.2. POST [Mobile] Enrollment - Daftar ke Kursus

Mendaftar (enroll) ke kursus yang dipilih.

#### Endpoint
```
POST /courses/{course_slug}/enroll
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Rate Limit
```
5 requests per minute (enrollment throttle)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_slug` | string | ✅ Yes | Slug kursus |

#### Request Body (JSON)
```json
{
  "enrollment_key": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `enrollment_key` | string | ⚠️ Conditional | required_if:enrollment_type,key | Enrollment key (jika required) |

#### Valid Values

**enrollment_key**:
- Required jika course `enrollment_type` = `key`
- Optional jika course `enrollment_type` = `open`
- Contoh: `"PROG101-2026"`

#### Response Success (201 Created)
```json
{
  "success": true,
  "message": "Berhasil mendaftar ke kursus",
  "data": {
    "enrollment": {
      "id": 10,
      "user_id": 5,
      "course_id": 1,
      "status": "active",
      "enrolled_at": "2026-03-15T10:00:00.000000Z",
      "progress_percentage": 0
    },
    "course": {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming",
      "thumbnail_url": "https://api.levl.id/storage/courses/intro.jpg"
    }
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "enrollment_key": [
      "The enrollment key is required for this course.",
      "The enrollment key is invalid."
    ]
  }
}
```

#### Response Error (409 Conflict)
```json
{
  "success": false,
  "message": "You are already enrolled in this course",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/courses/{{course_slug}}/enroll

// Body (if enrollment key required)
{
  "enrollment_key": "PROG101-2026"
}

// Body (if open enrollment)
{}

// Tests
pm.test("Status 201", () => pm.response.to.have.status(201));
pm.test("Enrollment created", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('enrollment');
    pm.expect(data.enrollment.status).to.equal('active');
});

// Save enrollment ID
if (pm.response.code === 201) {
    pm.environment.set("enrollment_id", pm.response.json().data.enrollment.id);
}
```

---

### 2.3. GET [Mobile] Kursus - Kursus Saya

Melihat semua kursus yang sudah di-enroll dengan filter dan sorting.

Melihat semua kursus yang sudah di-enroll.

#### Endpoint
```
GET /my-courses
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `filter[status]` | string | ❌ No | - | Filter enrollment status: `active`, `completed` |
| `filter[level_tag]` | string | ❌ No | - | Filter level: `beginner`, `intermediate`, `advanced` |
| `filter[type]` | string | ❌ No | - | Filter tipe: `self_paced`, `scheduled` |
| `filter[category_id]` | integer | ❌ No | - | Filter berdasarkan kategori ID |
| `sort` | string | ❌ No | -updated_at | Sorting: `title`, `created_at`, `updated_at` (prefix `-` untuk desc) |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[status]** (enrollment status):
- `active` - Hanya kursus yang sedang aktif
- `completed` - Hanya kursus yang sudah selesai
- Jika tidak diisi: menampilkan semua (active + completed)

**sort**:
- `title` - Berdasarkan judul (A-Z)
- `-title` - Berdasarkan judul (Z-A)
- `created_at` - Berdasarkan tanggal enroll (oldest first)
- `-created_at` - Berdasarkan tanggal enroll (newest first)
- `updated_at` - Berdasarkan last accessed
- `-updated_at` - Berdasarkan last accessed (newest first) - DEFAULT

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Kursus berhasil diambil",
  "data": [
    {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming",
      "short_desc": "Learn programming basics",
      "thumbnail_url": "https://api.levl.id/storage/courses/intro.jpg",
      "level_tag": "beginner",
      "instructor": {
        "id": 2,
        "name": "John Doe"
      },
      "enrollment": {
        "id": 10,
        "status": "active",
        "enrolled_at": "2026-03-01T10:00:00.000000Z",
        "progress_percentage": 45.5,
        "completed_lessons": 15,
        "total_lessons": 33,
        "last_accessed_at": "2026-03-15T09:30:00.000000Z"
      },
      "stats": {
        "total_units": 5,
        "total_lessons": 33,
        "total_assignments": 8,
        "total_quizzes": 5
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 5,
      "last_page": 1
    }
  }
}
```

#### Postman Example
```javascript
// Query Params - All My Courses
per_page: 15
page: 1

// Query Params - Active Courses Only
filter[status]: active
sort: -updated_at
per_page: 15

// Query Params - Completed Courses
filter[status]: completed
sort: -created_at

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has enrolled courses", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Each course has enrollment info", () => {
    const data = pm.response.json().data;
    if (data.length > 0) {
        pm.expect(data[0]).to.have.property('enrollment');
        pm.expect(data[0].enrollment).to.have.property('progress_percentage');
    }
});
```

---

## 3. AKSES KURSUS & PROGRESS

### 3.1. GET [Mobile] Kursus - Struktur Kursus (Units)

Melihat struktur kursus berupa daftar unit kompetensi.

#### Endpoint
```
GET /courses/{course_slug}/units
```

#### Authorization
```
Bearer Token Required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar unit berhasil diambil",
  "data": [
    {
      "id": 1,
      "title": "Getting Started",
      "slug": "getting-started",
      "description": "Introduction to the course",
      "order": 1,
      "status": "published",
      "stats": {
        "total_lessons": 5,
        "total_assignments": 1,
        "total_quizzes": 1,
        "duration_estimate": 120
      },
      "progress": {
        "completed_lessons": 5,
        "total_lessons": 5,
        "percentage": 100,
        "is_completed": true
      }
    },
    {
      "id": 2,
      "title": "Basic Concepts",
      "slug": "basic-concepts",
      "description": "Learn fundamental concepts",
      "order": 2,
      "status": "published",
      "stats": {
        "total_lessons": 10,
        "total_assignments": 2,
        "total_quizzes": 1,
        "duration_estimate": 300
      },
      "progress": {
        "completed_lessons": 3,
        "total_lessons": 10,
        "percentage": 30,
        "is_completed": false
      }
    }
  ]
}
```

#### Postman Example
```javascript
// Save first unit slug
if (pm.response.json().data.length > 0) {
    pm.environment.set("unit_slug", pm.response.json().data[0].slug);
}
```

---

### 3.2. GET [Mobile] Unit - Konten Unit

Melihat semua konten dalam unit (lessons, assignments, quizzes) dalam urutan yang benar.

#### Endpoint
```
GET /courses/{course_slug}/units/{unit_slug}/contents
```

#### Authorization
```
Bearer Token Required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Konten unit berhasil diambil",
  "data": {
    "unit": {
      "id": 1,
      "title": "Getting Started",
      "slug": "getting-started",
      "description": "Introduction to the course"
    },
    "contents": [
      {
        "id": 1,
        "type": "lesson",
        "title": "Introduction",
        "slug": "introduction",
        "order": 1,
        "duration_minutes": 15,
        "is_completed": true,
        "completed_at": "2026-03-02T10:30:00.000000Z"
      },
      {
        "id": 2,
        "type": "lesson",
        "title": "Setup Environment",
        "slug": "setup-environment",
        "order": 2,
        "duration_minutes": 30,
        "is_completed": true,
        "completed_at": "2026-03-02T11:00:00.000000Z"
      },
      {
        "id": 1,
        "type": "assignment",
        "title": "Practice Exercise 1",
        "order": 3,
        "due_date": "2026-03-20T23:59:59.000000Z",
        "is_submitted": false,
        "submission_status": null
      },
      {
        "id": 1,
        "type": "quiz",
        "title": "Quiz: Getting Started",
        "order": 4,
        "time_limit_minutes": 30,
        "attempts_allowed": 3,
        "attempts_used": 0,
        "highest_score": null
      }
    ]
  }
}
```

---

### 3.3. GET [Mobile] Kursus - Progress Detail

Melihat progress pembelajaran secara detail.

#### Endpoint
```
GET /courses/{course_slug}/progress
```

#### Authorization
```
Bearer Token Required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Progress berhasil diambil",
  "data": {
    "course": {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming"
    },
    "enrollment": {
      "id": 10,
      "status": "active",
      "enrolled_at": "2026-03-01T10:00:00.000000Z"
    },
    "progress": {
      "overall_percentage": 45.5,
      "completed_lessons": 15,
      "total_lessons": 33,
      "completed_assignments": 3,
      "total_assignments": 8,
      "completed_quizzes": 2,
      "total_quizzes": 5,
      "time_spent_minutes": 1250,
      "xp_earned": 850
    },
    "units": [
      {
        "id": 1,
        "title": "Getting Started",
        "order": 1,
        "progress_percentage": 100,
        "completed_lessons": 5,
        "total_lessons": 5
      },
      {
        "id": 2,
        "title": "Basic Concepts",
        "order": 2,
        "progress_percentage": 30,
        "completed_lessons": 3,
        "total_lessons": 10
      }
    ],
    "next_lesson": {
      "id": 16,
      "title": "Control Flow",
      "slug": "control-flow",
      "unit_title": "Basic Concepts"
    }
  }
}
```

---

## 4. PEMBELAJARAN MATERI

### 4.1. GET [Mobile] Materi - Daftar Materi dalam Unit

Melihat daftar materi (lessons) dalam unit.

#### Endpoint
```
GET /courses/{course_slug}/units/{unit_slug}/lessons
```

#### Authorization
```
Bearer Token Required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar materi berhasil diambil",
  "data": [
    {
      "id": 1,
      "title": "Introduction",
      "slug": "introduction",
      "description": "Course introduction",
      "order": 1,
      "content_type": "markdown",
      "duration_minutes": 15,
      "status": "published",
      "is_completed": true,
      "completed_at": "2026-03-02T10:30:00.000000Z"
    },
    {
      "id": 2,
      "title": "Setup Environment",
      "slug": "setup-environment",
      "description": "How to setup your environment",
      "order": 2,
      "content_type": "video",
      "duration_minutes": 30,
      "status": "published",
      "is_completed": false,
      "completed_at": null
    }
  ]
}
```

---

### 4.2. GET [Mobile] Materi - Detail Materi

Membaca konten materi lengkap.

#### Endpoint
```
GET /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}
```

#### Authorization
```
Bearer Token Required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Detail materi berhasil diambil",
  "data": {
    "id": 2,
    "title": "Setup Environment",
    "slug": "setup-environment",
    "description": "How to setup your development environment",
    "markdown_content": "# Setup Environment\n\n## Prerequisites\n...",
    "content_type": "markdown",
    "content_url": null,
    "order": 2,
    "duration_minutes": 30,
    "status": "published",
    "unit": {
      "id": 1,
      "title": "Getting Started",
      "slug": "getting-started"
    },
    "course": {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming"
    },
    "is_completed": false,
    "completed_at": null,
    "navigation": {
      "previous": {
        "id": 1,
        "title": "Introduction",
        "slug": "introduction"
      },
      "next": {
        "id": 3,
        "title": "Your First Program",
        "slug": "your-first-program"
      }
    }
  }
}
```

#### Postman Example
```javascript
// Save lesson slug
pm.environment.set("lesson_slug", pm.response.json().data.slug);
```

---

### 4.3. POST [Mobile] Materi - Tandai Selesai

Menandai materi sebagai selesai dan mendapatkan XP.

#### Endpoint
```
POST /lessons/{lesson_slug}/complete
```

#### Authorization
```
Bearer Token Required
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `lesson_slug` | string | ✅ Yes | Slug lesson |

#### Request Body
```json
{}
```

#### Response Success (200 OK) - With XP Info
```json
{
  "success": true,
  "message": "Materi berhasil ditandai selesai",
  "data": {
    "lesson": {
      "id": 2,
      "title": "Setup Environment",
      "slug": "setup-environment"
    },
    "completion": {
      "id": 15,
      "user_id": 5,
      "lesson_id": 2,
      "completed_at": "2026-03-15T10:30:00.000000Z"
    },
    "progress": {
      "unit_progress": 40,
      "course_progress": 48.5
    }
  },
  "xp_info": {
    "awarded": true,
    "amount": 10,
    "source": "lesson_completed",
    "description": "Menyelesaikan materi: Setup Environment",
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

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/lessons/{{lesson_slug}}/complete

// Body
{}

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("XP awarded", () => {
    const xp = pm.response.json().xp_info;
    pm.expect(xp.awarded).to.be.true;
    pm.expect(xp.amount).to.be.above(0);
});
pm.test("Progress updated", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('progress');
});
```

---

### 4.4. DELETE [Mobile] Materi - Batalkan Selesai

Membatalkan status selesai materi (untuk koreksi).

#### Endpoint
```
DELETE /lessons/{lesson_slug}/complete
```

#### Authorization
```
Bearer Token Required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Status selesai berhasil dibatalkan",
  "data": {
    "lesson": {
      "id": 2,
      "title": "Setup Environment",
      "slug": "setup-environment"
    },
    "is_completed": false
  }
}
```

---

## 5. MENGERJAKAN TUGAS

### 5.1. GET [Mobile] Tugas - Daftar Tugas

```
GET /courses/{course_slug}/assignments
```

Response: Daftar assignments dalam course dengan status submission.

### 5.2. GET [Mobile] Tugas - Detail Tugas

```
GET /assignments/{assignment_id}
```

Response: Detail assignment termasuk instruksi, due date, max score.

### 5.3. POST [Mobile] Tugas - Submit Tugas

```
POST /assignments/{assignment_id}/submissions
```

Request Body:
```json
{
  "answers": {
    "1": "Answer for question 1",
    "2": "Answer for question 2"
  },
  "files": ["file_id_1", "file_id_2"]
}
```

Response: Submission created + XP awarded

### 5.4. GET [Mobile] Tugas - Lihat Submission

```
GET /assignments/{assignment_id}/submissions/{submission_id}
```

Response: Detail submission dengan grade (jika sudah dinilai).

---

## 6. MENGERJAKAN KUIS

### 6.1. GET [Mobile] Kuis - Daftar Kuis dalam Kursus

Melihat semua kuis dalam kursus dengan informasi attempts dan status.

#### Endpoint
```
GET /courses/{course_slug}/quizzes
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|


| `course_slug` | string | ✅ Yes | Slug kursus |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar kuis berhasil diambil",
  "data": [
    {
      "id": 1,
      "title": "Quiz: Getting Started",
      "description": "Test your understanding of the basics",
      "passing_grade": "70.00",
      "max_score": "100.00",
      "time_limit_minutes": 30,
      "attempts_allowed": 3,
      "unit": {
        "id": 1,
        "title": "Getting Started",
        "slug": "getting-started"
      },
      "student_info": {
        "attempts_used": 1,
        "highest_score": "85.00",
        "is_passed": true,
        "last_attempt_at": "2026-03-10T14:30:00.000000Z",
        "has_draft": false
      },
      "xp_reward": 30,
      "xp_perfect_bonus": 10
    }
  ]
}
```

---

### 6.2. GET [Mobile] Kuis - Detail Kuis

Melihat detail kuis sebelum memulai attempt.

#### Endpoint
```
GET /quizzes/{quiz_id}
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `quiz_id` | integer | ✅ Yes | ID kuis |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Detail kuis berhasil diambil",
  "data": {
    "id": 1,
    "title": "Quiz: Getting Started",
    "description": "Test your understanding of the basics",
    "passing_grade": "70.00",
    "max_score": "100.00",
    "time_limit_minutes": 30,
    "auto_grading": true,
    "review_mode": "immediate",
    "unit_slug": "getting-started",
    "is_locked": false,
    "submission_status": "completed",
    "submission_status_label": "Selesai",
    "score": "85.00",
    "submitted_at": "2026-03-10T14:30:00.000000Z",
    "is_completed": true,
    "attempts_used": 1,
    "xp_reward": 30,
    "xp_perfect_bonus": 10,
    "questions_count": 10,
    "created_at": "2026-01-15T10:00:00.000000Z"
  }
}
```

#### Response Error (403 Forbidden - Quiz Locked)
```json
{
  "success": false,
  "message": "Kuis ini terkunci. Selesaikan prasyarat untuk membukanya.",
  "errors": null
}
```

---

### 6.3. POST [Mobile] Kuis - Mulai Attempt

Memulai attempt baru untuk mengerjakan kuis. Akan mendapatkan XP saat memulai.

#### Endpoint
```
POST /quizzes/{quiz_id}/submissions/start
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `quiz_id` | integer | ✅ Yes | ID kuis |

#### Request Body
```json
{}
```

#### Response Success (201 Created) - With XP Info
```json
{
  "success": true,
  "message": "Kuis dimulai. Semangat!",
  "data": {
    "id": 15,
    "quiz_id": 1,
    "user_id": 5,
    "status": "draft",
    "grading_status": "pending",
    "attempt_number": 2,
    "started_at": "2026-03-15T10:00:00.000000Z",
    "submitted_at": null,
    "score": null,
    "final_score": null,
    "quiz": {
      "id": 1,
      "title": "Quiz: Getting Started",
      "time_limit_minutes": 30,
      "max_score": "100.00"
    }
  },
  "xp_info": {
    "awarded": true,
    "amount": 5,
    "source": "quiz_started",
    "description": "Memulai kuis: Quiz: Getting Started",
    "total_xp": 95,
    "level_info": {
      "current_level": 2,
      "level_name": "Novice",
      "xp_for_current_level": 50,
      "xp_for_next_level": 150,
      "xp_progress": 45,
      "xp_needed": 55,
      "progress_percentage": 45
    }
  }
}
```

#### Response Error (422 Validation Error - Draft Exists)
```json
{
  "success": false,
  "message": "Anda memiliki percobaan kuis yang belum selesai. Harap selesaikan atau batalkan terlebih dahulu sebelum memulai yang baru.",
  "errors": {
    "quiz": ["Anda memiliki percobaan kuis yang belum selesai. Harap selesaikan atau batalkan terlebih dahulu sebelum memulai yang baru."]
  }
}
```

#### Response Error (422 Validation Error - Quiz Locked)
```json
{
  "success": false,
  "message": "Kuis ini terkunci. Selesaikan 3 prasyarat terlebih dahulu.",
  "errors": {
    "quiz": ["Kuis ini terkunci. Selesaikan 3 prasyarat terlebih dahulu."]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/quizzes/{{quiz_id}}/submissions/start

// Body
{}

// Tests
pm.test("Status 201", () => pm.response.to.have.status(201));
pm.test("Submission created", () => {
    const data = pm.response.json().data;
    pm.expect(data.status).to.equal('draft');
    pm.expect(data).to.have.property('started_at');
});
pm.test("XP awarded", () => {
    const xp = pm.response.json().xp_info;
    pm.expect(xp.awarded).to.be.true;
});

// Save submission ID
if (pm.response.code === 201) {
    pm.environment.set("submission_id", pm.response.json().data.id);
}
```

---

### 6.4. GET [Mobile] Kuis - Ambil Pertanyaan (1 per 1)

Mengambil pertanyaan satu per satu dengan pagination. Student hanya bisa melihat 1 pertanyaan pada satu waktu.

#### Endpoint
```
GET /quiz-submissions/{submission_id}/questions?page={page}
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `submission_id` | integer | ✅ Yes | ID quiz submission |

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | ✅ Yes | 1 | Nomor pertanyaan (1-based) |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "id": 1,
    "type": "multiple_choice",
    "content": "What is a variable in programming?",
    "options": [
      "A container for storing data",
      "A type of loop",
      "A function",
      "A class"
    ],
    "weight": "10.00",
    "order": 1,
    "answer": {
      "id": 25,
      "selected_options": ["A container for storing data"],
      "content": null
    }
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "total": 10,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

**Note**: 
- `answer` akan null jika belum dijawab
- `answer` akan berisi jawaban terakhir jika sudah pernah dijawab (draft)
- `answer_key` TIDAK ditampilkan untuk student (hanya untuk instructor)

#### Response Error (422 Validation Error - Invalid Page)
```json
{
  "success": false,
  "message": "Halaman tidak valid.",
  "errors": null
}
```

#### Postman Example
```javascript
// URL - Get question 1
{{base_url}}/quiz-submissions/{{submission_id}}/questions?page=1

// URL - Get question 2
{{base_url}}/quiz-submissions/{{submission_id}}/questions?page=2

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has question", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('id');
    pm.expect(data).to.have.property('content');
    pm.expect(data).to.have.property('type');
});
pm.test("Has pagination", () => {
    const meta = pm.response.json().meta;
    pm.expect(meta.pagination).to.have.property('current_page');
    pm.expect(meta.pagination).to.have.property('total');
});
pm.test("No answer key for student", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.not.have.property('answer_key');
});

// Save question ID
if (pm.response.code === 200) {
    pm.environment.set("question_id", pm.response.json().data.id);
}
```

---

### 6.5. POST [Mobile] Kuis - Simpan Jawaban (Draft)

Menyimpan jawaban untuk pertanyaan tertentu. Jawaban disimpan sebagai draft dan bisa diubah sebelum submit.

#### Endpoint
```
POST /quiz-submissions/{submission_id}/answers
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `submission_id` | integer | ✅ Yes | ID quiz submission |

#### Request Body (JSON)
```json
{
  "quiz_question_id": 1,
  "selected_options": ["A container for storing data"],
  "content": null
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `quiz_question_id` | integer | ✅ Yes | exists:quiz_questions,id | ID pertanyaan |
| `selected_options` | array | ⚠️ Conditional | required_if:type,multiple_choice,checkbox,true_false | Pilihan jawaban (untuk objective) |
| `content` | string | ⚠️ Conditional | required_if:type,essay,file_upload | Konten jawaban (untuk essay) |

#### Valid Values

**selected_options** (untuk multiple_choice, checkbox, true_false):
- Array of strings
- Contoh multiple_choice: `["A container for storing data"]`
- Contoh checkbox: `["Option A", "Option C"]`
- Contoh true_false: `["true"]` atau `["false"]`

**content** (untuk essay, file_upload):
- String text untuk essay
- Minimal: 1 karakter
- Maksimal: 10000 karakter

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Jawaban berhasil disimpan.",
  "data": {
    "id": 25,
    "quiz_submission_id": 15,
    "quiz_question_id": 1,
    "selected_options": ["A container for storing data"],
    "content": null,
    "is_auto_graded": false,
    "score": null
  }
}
```

#### Response Error (422 Validation Error - Not Draft)
```json
{
  "success": false,
  "message": "Pengumpulan kuis ini tidak dalam status draft.",
  "errors": {
    "submission": ["Pengumpulan kuis ini tidak dalam status draft."]
  }
}
```

#### Response Error (422 Validation Error - Quiz Locked)
```json
{
  "success": false,
  "message": "Kuis ini terkunci. Anda tidak dapat menjawab pertanyaan.",
  "errors": {
    "quiz": ["Kuis ini terkunci. Anda tidak dapat menjawab pertanyaan."]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/quiz-submissions/{{submission_id}}/answers

// Body - Multiple Choice
{
  "quiz_question_id": 1,
  "selected_options": ["A container for storing data"]
}

// Body - Checkbox (multiple answers)
{
  "quiz_question_id": 2,
  "selected_options": ["Option A", "Option C", "Option D"]
}

// Body - True/False
{
  "quiz_question_id": 3,
  "selected_options": ["true"]
}

// Body - Essay
{
  "quiz_question_id": 4,
  "content": "A variable is a named storage location in memory that holds a value..."
}

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Answer saved", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('id');
    pm.expect(data.quiz_submission_id).to.equal(parseInt(pm.environment.get("submission_id")));
});
```

---

### 6.6. POST [Mobile] Kuis - Submit Kuis

Mengumpulkan kuis setelah semua pertanyaan dijawab. Kuis akan di-grade otomatis dan student mendapat XP.

#### Endpoint
```
POST /quiz-submissions/{submission_id}/submit
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `submission_id` | integer | ✅ Yes | ID quiz submission |

#### Request Body
```json
{}
```

#### Validation Rules
- ✅ Submission harus dalam status `draft`
- ✅ Quiz tidak boleh locked
- ✅ **SEMUA pertanyaan harus sudah dijawab**

#### Response Success (200 OK) - With XP Info
```json
{
  "success": true,
  "message": "Kuis berhasil dikumpulkan.",
  "data": {
    "id": 15,
    "quiz_id": 1,
    "user_id": 5,
    "status": "graded",
    "grading_status": "graded",
    "attempt_number": 2,
    "started_at": "2026-03-15T10:00:00.000000Z",
    "submitted_at": "2026-03-15T10:25:00.000000Z",
    "score": "90.00",
    "final_score": "90.00",
    "quiz": {
      "id": 1,
      "title": "Quiz: Getting Started",
      "passing_grade": "70.00",
      "max_score": "100.00"
    },
    "is_passed": true
  },
  "xp_info": {
    "awarded": true,
    "amount": 40,
    "source": "quiz_completed",
    "description": "Menyelesaikan kuis: Quiz: Getting Started (Score: 90/100)",
    "total_xp": 135,
    "level_info": {
      "current_level": 2,
      "level_name": "Novice",
      "xp_for_current_level": 50,
      "xp_for_next_level": 150,
      "xp_progress": 85,
      "xp_needed": 15,
      "progress_percentage": 85
    }
  }
}
```

**XP Breakdown**:
- Base XP for completion: +30 XP
- High score bonus (≥80): +10 XP
- Total: 40 XP

#### Response Error (422 Validation Error - Unanswered Questions)
```json
{
  "success": false,
  "message": "Anda harus menjawab semua 10 pertanyaan sebelum mengumpulkan.",
  "errors": {
    "answers": ["Anda harus menjawab semua 10 pertanyaan sebelum mengumpulkan."]
  }
}
```

#### Response Error (422 Validation Error - Not Draft)
```json
{
  "success": false,
  "message": "Pengumpulan kuis ini tidak dalam status draft.",
  "errors": {
    "submission": ["Pengumpulan kuis ini tidak dalam status draft."]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/quiz-submissions/{{submission_id}}/submit

// Body
{}

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Quiz graded", () => {
    const data = pm.response.json().data;
    pm.expect(data.status).to.equal('graded');
    pm.expect(data).to.have.property('final_score');
    pm.expect(data).to.have.property('is_passed');
});
pm.test("XP awarded", () => {
    const xp = pm.response.json().xp_info;
    pm.expect(xp.awarded).to.be.true;
    pm.expect(xp.amount).to.be.above(0);
});
```

---

### 6.7. GET [Mobile] Kuis - Lihat Hasil

Melihat hasil kuis setelah submit, termasuk score, jawaban benar/salah, dan feedback.

#### Endpoint
```
GET /quiz-submissions/{submission_id}?include=answers,quiz.questions
```

#### Authorization
```
Bearer Token Required (Student only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `submission_id` | integer | ✅ Yes | ID quiz submission |

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `include` | string | ❌ No | Includes: `answers`, `quiz`, `quiz.questions` |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "id": 15,
    "quiz_id": 1,
    "user_id": 5,
    "status": "graded",
    "grading_status": "graded",
    "attempt_number": 2,
    "started_at": "2026-03-15T10:00:00.000000Z",
    "submitted_at": "2026-03-15T10:25:00.000000Z",
    "score": "90.00",
    "final_score": "90.00",
    "is_passed": true,
    "quiz": {
      "id": 1,
      "title": "Quiz: Getting Started",
      "passing_grade": "70.00",
      "max_score": "100.00",
      "questions": [
        {
          "id": 1,
          "type": "multiple_choice",
          "content": "What is a variable?",
          "options": ["A", "B", "C", "D"],
          "weight": "10.00",
          "order": 1
        }
      ]
    },
    "answers": [
      {
        "id": 25,
        "quiz_question_id": 1,
        "selected_options": ["A"],
        "content": null,
        "is_auto_graded": true,
        "score": "10.00",
        "is_correct": true
      }
    ]
  }
}
```

**Note**: 
- `answer_key` hanya ditampilkan jika `review_mode` = `immediate`
- Jika `review_mode` = `after_due_date`, answer key baru muncul setelah due date
- Student bisa melihat jawaban mereka dan score per question

#### Postman Example
```javascript
// URL
{{base_url}}/quiz-submissions/{{submission_id}}?include=answers,quiz.questions

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has results", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('final_score');
    pm.expect(data).to.have.property('is_passed');
});
pm.test("Has answers", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('answers');
    pm.expect(data.answers).to.be.an('array');
});
```

---

## 📖 COMPLETE USE CASE: STUDENT LEARNING JOURNEY

### Scenario: Student belajar dari awal sampai selesai

```javascript
// ============================================
// STEP 1: DISCOVERY - Cari Kursus
// ============================================

// 1.1. Browse all courses
GET /courses?per_page=15&sort=-published_at
// Response: List of all published courses (newest first)

// 1.2. Search courses with filters
GET /courses?search=programming&filter[level_tag]=beginner&filter[category_id]=1&sort=title
// Response: List of beginner programming courses sorted by title
// Save: course_slug = "introduction-to-programming"

// 1.3. View course detail
GET /courses/introduction-to-programming
// Response: Full course details, instructor, outcomes, stats
// Check: enrollment_type = "open" (no key needed)

// ============================================
// STEP 2: ENROLLMENT - Daftar Kursus
// ============================================

// 2.1. Check enrollment status
GET /courses/introduction-to-programming/enrollment-status
// Response: is_enrolled = false, can_enroll = true

// 2.2. Enroll to course
POST /courses/introduction-to-programming/enroll
Body: {}
// Response: enrollment created, status = "active"
// Save: enrollment_id = 10

// 2.3. Verify enrollment
GET /my-courses
// Response: List includes "Introduction to Programming"

// ============================================
// STEP 3: LEARNING - Akses Konten
// ============================================

// 3.1. View course structure
GET /courses/introduction-to-programming/units
// Response: 5 units, unit 1 = "Getting Started"
// Save: unit_slug = "getting-started"

// 3.2. View unit contents
GET /courses/introduction-to-programming/units/getting-started/contents
// Response: 5 lessons, 1 assignment, 1 quiz
// Save: lesson_slug = "introduction"

// 3.3. Read first lesson
GET /courses/introduction-to-programming/units/getting-started/lessons/introduction
// Response: Lesson content (markdown/video)
// Student reads/watches the content

// 3.4. Mark lesson complete
POST /lessons/introduction/complete
Body: {}
// Response: ✅ Lesson completed
// XP Info: +10 XP, total XP = 10, level = 1
// Progress: unit 20%, course 3%

// 3.5. Continue to next lesson
GET /courses/introduction-to-programming/units/getting-started/lessons/setup-environment
// Student reads content

POST /lessons/setup-environment/complete
// Response: ✅ +10 XP, total = 20 XP

// ... Student completes all 5 lessons in unit 1 ...
// Total XP from lessons: 50 XP

// ============================================
// STEP 4: ASSESSMENT - Tugas
// ============================================

// 4.1. View assignments
GET /courses/introduction-to-programming/assignments
// Response: 8 assignments, first = "Practice Exercise 1"
// Save: assignment_id = 1

// 4.2. View assignment detail
GET /assignments/1
// Response: Instructions, questions, due date, max score = 100

// 4.3. Submit assignment
POST /assignments/1/submissions
Body: {
  "answers": {
    "1": "Variables are containers for storing data",
    "2": "int, float, string, boolean"
  }
}
// Response: ✅ Submission created
// XP Info: +20 XP for submission, total = 70 XP
// Status: "pending_review" (waiting for instructor grading)

// 4.4. Check submission later (after instructor grades)
GET /assignments/1/submissions/1
// Response: Grade = 85/100, feedback from instructor
// XP Info: +15 XP bonus for good score, total = 85 XP

// ============================================
// STEP 5: ASSESSMENT - Kuis
// ============================================

// 5.1. View quizzes
GET /courses/introduction-to-programming/quizzes
// Response: 5 quizzes, first = "Quiz: Getting Started"
// Save: quiz_id = 1

// 5.2. View quiz detail
GET /quizzes/1
// Response: 10 questions, 30 min time limit, 3 attempts, passing = 70%

// 5.3. Start quiz attempt
POST /quizzes/1/submissions/start
// Response: ✅ Quiz started
// XP Info: +5 XP for starting, total = 90 XP
// Data: submission_id = 1, questions list, started_at timestamp
// Save: submission_id = 1

// 5.4. Answer questions
POST /quiz-submissions/1/answers
Body: {"question_id": 1, "answer": "A"}
// Response: Answer saved

POST /quiz-submissions/1/answers
Body: {"question_id": 2, "answer": "C"}
// Response: Answer saved

// ... Answer all 10 questions ...

// 5.5. Submit quiz
POST /quiz-submissions/1/submit
// Response: ✅ Quiz submitted & auto-graded
// Score: 90/100 (9 correct out of 10)
// XP Info: +30 XP for completion, +10 XP bonus for high score
// Total XP: 130 XP
// Level up: Level 1 → Level 2 🎉

// 5.6. View quiz results
GET /quiz-submissions/1
// Response: Detailed results, correct/incorrect answers, explanations

// ============================================
// STEP 6: PROGRESS TRACKING
// ============================================

// 6.1. Check overall progress
GET /courses/introduction-to-programming/progress
// Response:
// - Overall: 25% complete
// - Unit 1: 100% complete ✅
// - Unit 2: 30% complete
// - Lessons: 10/33 completed
// - Assignments: 1/8 submitted
// - Quizzes: 1/5 completed
// - Total XP earned: 130 XP
// - Current level: 2

// ============================================
// STEP 7: CONTINUE LEARNING
// ============================================

// Student continues with Unit 2, 3, 4, 5...
// Completes all lessons, assignments, and quizzes
// Earns more XP, levels up, unlocks badges

// Final state:
// - Course: 100% complete ✅
// - Total XP: 850 XP
// - Level: 5 "Apprentice"
// - Badges earned: "First Steps", "Assignment Master", "Quiz Champion"
// - Certificate: Issued ✅

```

---

## 🎯 KEY POINTS

### XP System
- **Lesson Complete**: +10 XP per lesson
- **Assignment Submit**: +20 XP
- **Assignment High Score**: +15 XP bonus (score ≥ 80)
- **Quiz Start**: +5 XP
- **Quiz Complete**: +30 XP
- **Quiz High Score**: +10 XP bonus (score ≥ 80)
- **Perfect Score**: +20 XP bonus (score = 100)

### Progress Tracking
- Real-time progress updates
- Unit-level and course-level progress
- Completion percentage
- Time spent tracking
- XP and level progression

### Gamification
- XP earned from learning activities
- Level system (1-100)
- Badges for achievements
- Leaderboard rankings
- Certificates upon completion

---

## 📊 RESPONSE FORMAT

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { },
  "xp_info": {
    "awarded": true,
    "amount": 10,
    "source": "lesson_completed",
    "total_xp": 860,
    "level_info": { }
  },
  "errors": null
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
| 201 | Created | Resource berhasil dibuat |
| 401 | Unauthorized | Token invalid/expired |
| 403 | Forbidden | Tidak memiliki akses |
| 404 | Not Found | Resource tidak ditemukan |
| 409 | Conflict | Already enrolled/submitted |
| 422 | Validation Error | Input tidak valid |
| 429 | Too Many Requests | Rate limit exceeded |

---

**Dokumentasi ini mencakup complete student learning journey dari discovery sampai completion.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
