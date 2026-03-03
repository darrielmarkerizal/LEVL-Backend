# DOKUMENTASI API LENGKAP - LEARNING MANAGEMENT SYSTEM

## Daftar Isi
1. [Pengenalan](#pengenalan)
2. [Autentikasi](#autentikasi)
3. [Role & Permission](#role--permission)
4. [Sistem Prerequisite](#sistem-prerequisite)
5. [API Courses (Kursus)](#api-courses)
6. [API Units (Unit Pembelajaran)](#api-units)
7. [API Lessons (Pelajaran)](#api-lessons)
8. [API Assignments (Tugas)](#api-assignments)
9. [API Quizzes (Kuis)](#api-quizzes)
10. [API Submissions (Pengumpulan Tugas)](#api-submissions)
11. [API Quiz Submissions (Pengumpulan Kuis)](#api-quiz-submissions)
12. [API Progress (Progres Belajar)](#api-progress)

---

## Pengenalan

Dokumentasi ini menjelaskan seluruh API endpoint yang tersedia dalam sistem Learning Management System (LMS). API dibagi menjadi dua kategori akses utama:

### 1. Student (Siswa)
- Dapat melihat kursus yang dipublikasikan
- Dapat mengakses konten kursus yang sudah di-enroll
- Dapat mengerjakan assignment dan quiz
- Dapat melihat progres belajar sendiri
- Hanya dapat melihat submission milik sendiri

### 2. Manajemen (Admin/Instructor/Superadmin)
- **Superadmin**: Akses penuh ke semua data dan fitur
- **Admin**: Dapat mengelola kursus yang ditugaskan
- **Instructor**: Dapat mengelola kursus yang dibuat sendiri
- Dapat melihat semua submission siswa
- Dapat memberikan override prerequisite
- Dapat mengelola konten pembelajaran

---

## Autentikasi

Semua endpoint memerlukan autentikasi menggunakan Bearer Token di header:

```
Authorization: Bearer {your_token}
```

**Middleware yang digunakan:**
- `auth:api` - Autentikasi API menggunakan Sanctum/Passport
- Role-based authorization menggunakan Spatie Permission

---

## Role & Permission

### Hierarki Role
1. **Superadmin** - Akses penuh tanpa batasan
2. **Admin** - Akses ke kursus yang ditugaskan
3. **Instructor** - Akses ke kursus yang dibuat sendiri
4. **Student** - Akses ke kursus yang di-enroll

### Permission Check
- Policy classes mengatur authorization per resource
- `Gate::before()` memberikan bypass untuk Superadmin
- Course-specific admin check menggunakan pivot table `course_admins`

---

## Sistem Prerequisite

### Aturan Prerequisite

#### 1. Unit Level
- **Unit 1**: Selalu dapat diakses (tidak ada prerequisite)
- **Unit 2+**: Memerlukan penyelesaian 100% unit sebelumnya
  - Semua lesson harus completed
  - Semua assignment harus passing (score >= 60% dari max_score)
  - Semua quiz harus passing (final_score >= passing_grade)

#### 2. Lesson Level
- Lesson harus diakses secara berurutan dalam satu unit
- Lesson sebelumnya harus completed

#### 3. Assignment/Quiz Level
- Semua konten sebelumnya dalam unit harus completed/passed
- Konten diurutkan berdasarkan field `order`

### Field `is_locked`
- `true`: Konten terkunci karena prerequisite belum terpenuhi
- `false`: Konten dapat diakses
- Validasi dilakukan saat:
  - Complete lesson
  - Create/start assignment submission
  - Start quiz submission

### Passing Grade
- **Assignment**: 60% dari max_score (hardcoded)
- **Quiz**: Sesuai field `passing_grade` di tabel quizzes

---

## API Courses

### Base URL
```
/api/courses
```

### 1. List Courses (Index)
**Endpoint:** `GET /api/courses`

**Akses:**
- ✅ Public (tanpa auth untuk published courses)
- ✅ Student (melihat published courses)
- ✅ Manajemen (melihat semua courses)

**Query Parameters:**

| Parameter | Tipe | Deskripsi | Contoh |
|-----------|------|-----------|--------|
| `per_page` | integer | Jumlah data per halaman (default: 15, max: 100) | `?per_page=20` |
| `page` | integer | Nomor halaman | `?page=2` |
| `search` | string | Pencarian full-text (title, description, code) | `?search=Laravel` |
| `filter[status]` | string | Filter berdasarkan status | `?filter[status]=published` |
| `filter[level_tag]` | string | Filter berdasarkan level | `?filter[level_tag]=beginner` |
| `filter[type]` | string | Filter berdasarkan tipe | `?filter[type]=online` |
| `filter[category_id]` | integer | Filter berdasarkan kategori | `?filter[category_id]=5` |
| `filter[tag]` | string/array | Filter berdasarkan tag | `?filter[tag]=php` atau `?filter[tag][]=php&filter[tag][]=laravel` |
| `sort` | string | Sorting field | `?sort=title` atau `?sort=-created_at` (descending) |
| `include` | string | Include relasi (comma-separated) | `?include=tags,category,instructor` |

**Filter Values:**

**Status:** (Enum: `CourseStatus`)
- `draft` - Kursus masih draft
- `published` - Kursus sudah dipublikasikan
- `archived` - Kursus diarsipkan

**Level Tag:** (Enum: `LevelTag`)
- `dasar` - Dasar
- `menengah` - Menengah
- `mahir` - Mahir

**Type:** (Enum: `CourseType`)
- `okupasi` - Okupasi
- `kluster` - Kluster

**Enrollment Type:** (Enum: `EnrollmentType`)
- `auto_accept` - Auto Accept (langsung diterima)
- `key_based` - Key Based (butuh enrollment key)
- `approval` - Approval (butuh persetujuan admin)

**Sort Fields:**
- `id` - ID kursus
- `code` - Kode kursus
- `title` - Judul kursus
- `created_at` - Tanggal dibuat
- `updated_at` - Tanggal diupdate
- `published_at` - Tanggal dipublikasikan
- Prefix dengan `-` untuk descending (contoh: `-created_at`)

**Include Options:**

| Include | Akses | Deskripsi |
|---------|-------|-----------|
| `tags` | Public | Tag kursus |
| `category` | Public | Kategori kursus |
| `instructor` | Public | Data instructor |
| `units` | Public | Unit pembelajaran |
| `lessons` | Enrolled Student/Manager | Lesson dalam kursus |
| `quizzes` | Enrolled Student/Manager | Quiz dalam kursus |
| `assignments` | Enrolled Student/Manager | Assignment dalam kursus |
| `units.lessons` | Enrolled Student/Manager | Lesson dalam setiap unit |
| `units.lessons.blocks` | Enrolled Student/Manager | Block dalam setiap lesson |
| `enrollments` | Manager Only | Data enrollment |
| `enrollments.user` | Manager Only | User yang enroll |
| `admins` | Manager Only | Admin kursus |

**Response Student:**
```json
{
  "success": true,
  "message": "Daftar kursus berhasil diambil",
  "data": [
    {
      "id": 1,
      "code": "C001",
      "slug": "laravel-fundamentals",
      "title": "Laravel Fundamentals",
      "short_desc": "Belajar dasar Laravel",
      "type": "online",
      "level_tag": "beginner",
      "enrollment_type": "open",
      "status": "published",
      "enrollment_status": "active",
      "published_at": "2026-01-01T00:00:00+00:00",
      "created_at": "2026-01-01T00:00:00+00:00",
      "updated_at": "2026-01-01T00:00:00+00:00",
      "thumbnail": "https://example.com/thumbnail.jpg",
      "banner": "https://example.com/banner.jpg",
      "category": {...},
      "instructor": {...},
      "creator": {...},
      "admins_count": 2,
      "enrollments_count": 150
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

**Response Manajemen (dengan include enrollments):**
```json
{
  "data": [
    {
      "id": 1,
      "...": "...",
      "enrollments": [
        {
          "id": 1,
          "user_id": 10,
          "status": "active",
          "enrolled_at": "2026-01-01T00:00:00+00:00"
        }
      ],
      "admins": [
        {
          "id": 5,
          "name": "Admin Name",
          "email": "admin@example.com"
        }
      ]
    }
  ]
}
```

---

### 2. Show Course Detail
**Endpoint:** `GET /api/courses/{slug}`

**Akses:**
- ✅ Public (untuk published courses)
- ✅ Student (untuk enrolled courses)
- ✅ Manajemen (untuk managed courses)

**Path Parameters:**
- `slug` - Slug kursus

**Query Parameters:**
- `include` - Sama seperti list courses

**Response:** Sama seperti list, tapi single object

---

### 3. Create Course
**Endpoint:** `POST /api/courses`

**Akses:**
- ❌ Student
- ✅ Instructor
- ✅ Admin
- ✅ Superadmin

**Request Body:**
```json
{
  "title": "Laravel Advanced",
  "short_desc": "Belajar Laravel tingkat lanjut",
  "type": "online",
  "level_tag": "advanced",
  "enrollment_type": "open",
  "category_id": 5,
  "instructor_id": 10,
  "tags": ["php", "laravel", "backend"]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Kursus berhasil dibuat",
  "data": {...}
}
```

---

### 4. Update Course
**Endpoint:** `PUT /api/courses/{slug}`

**Akses:**
- ❌ Student
- ✅ Instructor (own courses)
- ✅ Admin (assigned courses)
- ✅ Superadmin

**Request Body:** Sama seperti create (semua field optional)

---

### 5. Delete Course
**Endpoint:** `DELETE /api/courses/{slug}`

**Akses:** Sama seperti update

---

### 6. Publish Course
**Endpoint:** `POST /api/courses/{slug}/publish`

**Akses:** Sama seperti update

---

### 7. Unpublish Course
**Endpoint:** `POST /api/courses/{slug}/unpublish`

**Akses:** Sama seperti update

---

### 8. My Enrolled Courses
**Endpoint:** `GET /api/my-courses`

**Akses:**
- ✅ Student Only

**Query Parameters:** Sama seperti list courses

**Response:** List kursus yang sudah di-enroll oleh student (auto-filter berdasarkan user yang login)

---

## API Units

### Base URL
```
/api/courses/{course_slug}/units
```

### 1. List Units
**Endpoint:** `GET /api/courses/{course_slug}/units`

**Akses:**
- ✅ Public (untuk published courses)
- ✅ Student (untuk enrolled courses)
- ✅ Manajemen

**Query Parameters:**

| Parameter | Tipe | Deskripsi | Contoh |
|-----------|------|-----------|--------|
| `per_page` | integer | Jumlah data per halaman (default: 15) | `?per_page=20` |
| `page` | integer | Nomor halaman | `?page=2` |
| `filter[status]` | string | Filter berdasarkan status | `?filter[status]=published` |
| `sort` | string | Sorting field | `?sort=order` atau `?sort=-created_at` |
| `include` | string | Include relasi | `?include=course,lessons` |

**Filter Values:**

**Status:** (Enum: `CourseStatus` - sama dengan Course)
- `draft` - Unit masih draft
- `published` - Unit sudah dipublikasikan
- `archived` - Unit diarsipkan

**Sort Fields:**
- `id` - ID unit
- `code` - Kode unit
- `title` - Judul unit
- `order` - Urutan unit
- `status` - Status unit
- `created_at` - Tanggal dibuat
- `updated_at` - Tanggal diupdate

**Include Options:**

| Include | Akses | Deskripsi |
|---------|-------|-----------|
| `course` | Public | Data kursus |
| `lessons` | Enrolled Student/Manager | Lesson dalam unit |
| `lessons.blocks` | Enrolled Student/Manager | Block dalam setiap lesson |

**Response Student:**
```json
{
  "success": true,
  "message": "Daftar unit berhasil diambil",
  "data": [
    {
      "id": 87,
      "course_id": 29,
      "code": "U29_1_98361e",
      "slug": "getting-started-29",
      "title": "Getting Started",
      "description": "Learn industry best practices",
      "order": 1,
      "status": "published",
      "is_locked": false,
      "created_at": "2026-03-03T05:03:36+00:00",
      "updated_at": "2026-03-03T05:03:36+00:00"
    },
    {
      "id": 88,
      "course_id": 29,
      "code": "U29_2_143247",
      "slug": "fundamentals-and-core-concepts-29",
      "title": "Fundamentals and Core Concepts",
      "description": "Dive deeper into advanced topics",
      "order": 2,
      "status": "published",
      "is_locked": true,
      "prerequisite_message": "Complete all lessons and pass all assignments/quizzes in previous unit",
      "created_at": "2026-03-03T05:03:36+00:00",
      "updated_at": "2026-03-03T05:03:36+00:00"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 3,
      "last_page": 1,
      "from": 1,
      "to": 3,
      "has_next": false,
      "has_prev": false
    }
  }
}
```

**Catatan Penting:**
- Field `is_locked` menunjukkan apakah unit dapat diakses
- Unit dengan `order=1` selalu `is_locked=false`
- Unit dengan `order>1` akan `is_locked=true` jika unit sebelumnya belum 100% selesai
- Untuk membuka unit berikutnya, student harus:
  - ✅ Complete semua lesson di unit sebelumnya
  - ✅ Pass semua assignment di unit sebelumnya (score >= 60%)
  - ✅ Pass semua quiz di unit sebelumnya (score >= passing_grade)

---

### 2. Show Unit Detail
**Endpoint:** `GET /api/courses/{course_slug}/units/{unit_slug}`

**Akses:** Sama seperti list units

**Query Parameters:**
- `include` - Sama seperti list units

**Response:** Single object unit dengan detail lengkap

---

### 3. Create Unit
**Endpoint:** `POST /api/courses/{course_slug}/units`

**Akses:**
- ❌ Student
- ✅ Instructor (own courses)
- ✅ Admin (assigned courses)
- ✅ Superadmin

**Request Body:**
```json
{
  "title": "Advanced Concepts",
  "description": "Learn advanced programming concepts",
  "order": 3,
  "status": "draft"
}
```

---

### 4. Update Unit
**Endpoint:** `PUT /api/courses/{course_slug}/units/{unit_slug}`

**Akses:** Sama seperti create

---

### 5. Delete Unit
**Endpoint:** `DELETE /api/courses/{course_slug}/units/{unit_slug}`

**Akses:** Sama seperti create

---

## API Lessons

### Base URL
```
/api/courses/{course_slug}/units/{unit_slug}/lessons
```

### 1. List Lessons
**Endpoint:** `GET /api/courses/{course_slug}/units/{unit_slug}/lessons`

**Akses:**
- ✅ Student (enrolled)
- ✅ Manajemen

**Query Parameters:**

| Parameter | Tipe | Deskripsi | Contoh |
|-----------|------|-----------|--------|
| `per_page` | integer | Jumlah data per halaman | `?per_page=20` |
| `filter[status]` | string | Filter berdasarkan status | `?filter[status]=published` |
| `sort` | string | Sorting field | `?sort=order` |
| `include` | string | Include relasi | `?include=blocks,unit` |

**Sort Fields:**
- `order` - Urutan lesson
- `title` - Judul lesson
- `created_at` - Tanggal dibuat

**Include Options:**

| Include | Akses | Deskripsi |
|---------|-------|-----------|
| `unit` | All | Data unit |
| `blocks` | All | Block konten lesson |

**Response Student:**
```json
{
  "success": true,
  "message": "Daftar lesson berhasil diambil",
  "data": [
    {
      "id": 1,
      "unit_id": 87,
      "slug": "introduction-to-laravel",
      "title": "Introduction to Laravel",
      "description": "Getting started with Laravel",
      "order": 1,
      "status": "published",
      "is_locked": false,
      "is_completed": true,
      "created_at": "2026-03-03T05:03:36+00:00",
      "updated_at": "2026-03-03T05:03:36+00:00"
    },
    {
      "id": 2,
      "unit_id": 87,
      "slug": "laravel-routing",
      "title": "Laravel Routing",
      "description": "Understanding Laravel routing",
      "order": 2,
      "status": "published",
      "is_locked": false,
      "is_completed": false,
      "created_at": "2026-03-03T05:03:36+00:00",
      "updated_at": "2026-03-03T05:03:36+00:00"
    }
  ]
}
```

**Catatan:**
- Field `is_locked` menunjukkan apakah lesson dapat diakses
- Field `is_completed` menunjukkan apakah lesson sudah diselesaikan oleh student
- Lesson harus diakses secara berurutan (lesson sebelumnya harus completed)

---

### 2. Show Lesson Detail
**Endpoint:** `GET /api/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Akses:** Sama seperti list lessons

**Query Parameters:**
- `include=blocks` - Include konten blocks

**Response:**
```json
{
  "success": true,
  "message": "Detail lesson berhasil diambil",
  "data": {
    "id": 1,
    "unit_id": 87,
    "slug": "introduction-to-laravel",
    "title": "Introduction to Laravel",
    "description": "Getting started with Laravel",
    "order": 1,
    "status": "published",
    "is_locked": false,
    "is_completed": true,
    "blocks": [
      {
        "id": 1,
        "lesson_id": 1,
        "type": "text",
        "order": 1,
        "content": {
          "text": "Welcome to Laravel course..."
        }
      },
      {
        "id": 2,
        "lesson_id": 1,
        "type": "video",
        "order": 2,
        "content": {
          "url": "https://youtube.com/watch?v=...",
          "duration": 600
        }
      }
    ]
  }
}
```

**Block Types:**
- `text` - Konten teks/HTML
- `video` - Video embed
- `image` - Gambar
- `file` - File download
- `code` - Code snippet
- `quiz` - Inline quiz

---

### 3. Complete Lesson
**Endpoint:** `POST /api/lessons/{lesson_id}/complete`

**Akses:**
- ✅ Student Only

**Validasi:**
- Lesson harus `is_locked=false`
- Jika lesson terkunci, akan return error 422

**Response:**
```json
{
  "success": true,
  "message": "Lesson berhasil diselesaikan",
  "data": {
    "lesson_id": 1,
    "completed_at": "2026-03-03T10:30:00+00:00"
  }
}
```

---

### 4. Uncomplete Lesson
**Endpoint:** `POST /api/lessons/{lesson_id}/uncomplete`

**Akses:**
- ✅ Instructor (own courses)
- ✅ Admin (assigned courses)
- ✅ Superadmin

**Response:** Sama seperti complete

---

## API Assignments

### Base URL
```
/api/courses/{course_slug}/assignments
```

### 1. List Assignments
**Endpoint:** `GET /api/courses/{course_slug}/assignments`

**Akses:**
- ✅ Student (enrolled)
- ✅ Manajemen

**Query Parameters:**

| Parameter | Tipe | Deskripsi | Contoh |
|-----------|------|-----------|--------|
| `per_page` | integer | Jumlah data per halaman | `?per_page=20` |
| `page` | integer | Nomor halaman | `?page=2` |
| `filter[status]` | string | Filter berdasarkan status | `?filter[status]=published` |
| `filter[unit_id]` | integer | Filter berdasarkan unit | `?filter[unit_id]=87` |
| `sort` | string | Sorting field | `?sort=order` atau `?sort=-created_at` |
| `include` | string | Include relasi | `?include=unit,questions` |

**Filter Values:**

**Status:** (Enum: `AssignmentStatus`)
- `draft` - Assignment masih draft
- `published` - Assignment sudah dipublikasikan
- `archived` - Assignment diarsipkan

**Submission Type:** (Enum: `SubmissionType`)
- `text` - Text (jawaban teks)
- `file` - File (upload file)
- `mixed` - Mixed (teks + file)

**Review Mode:** (Enum: `ReviewMode`)
- `immediate` - Immediate (langsung lihat hasil)
- `manual` - Manual (setelah manual grading)
- `deferred` - Deferred (ditunda sampai waktu tertentu)
- `hidden` - Hidden (tidak bisa lihat hasil)

**Sort Fields:**
- `order` - Urutan assignment dalam unit
- `title` - Judul assignment
- `created_at` - Tanggal dibuat
- `updated_at` - Tanggal diupdate

**Include Options (Manajemen Only):**
- `unit` - Data unit
- `questions` - Soal assignment
- `creator` - Pembuat assignment
- `prerequisites` - Prerequisite assignment

**Response Student:**
```json
{
  "success": true,
  "message": "Daftar assignment berhasil diambil",
  "data": [
    {
      "id": 1,
      "title": "Laravel Basics Assignment",
      "description": "Complete the following tasks",
      "submission_type": "file",
      "max_score": 100,
      "max_attempts": 3,
      "retake_enabled": true,
      "review_mode": "after_graded",
      "unit_slug": "getting-started-29",
      "course_slug": "laravel-fundamentals",
      "is_locked": false,
      "user_submission": {
        "id": 10,
        "status": "graded",
        "score": 85,
        "submitted_at": "2026-03-03T10:00:00+00:00"
      },
      "attachments": [
        {
          "id": 1,
          "file_name": "instructions.pdf",
          "url": "https://example.com/files/instructions.pdf",
          "mime_type": "application/pdf",
          "size": 102400
        }
      ],
      "created_at": "2026-03-03T05:03:36+00:00"
    }
  ]
}
```

**Response Manajemen:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Laravel Basics Assignment",
      "description": "Complete the following tasks",
      "submission_type": "file",
      "max_score": 100,
      "max_attempts": 3,
      "cooldown_minutes": 60,
      "retake_enabled": true,
      "review_mode": "after_graded",
      "status": "published",
      "allow_resubmit": true,
      "unit_slug": "getting-started-29",
      "course_slug": "laravel-fundamentals",
      "is_available": true,
      "questions_count": 5,
      "creator": {
        "id": 5,
        "name": "Instructor Name",
        "email": "instructor@example.com"
      },
      "attachments": [...],
      "created_at": "2026-03-03T05:03:36+00:00",
      "updated_at": "2026-03-03T05:03:36+00:00"
    }
  ]
}
```

**Catatan Penting:**
- Student hanya melihat field yang relevan (tidak ada `status`, `allow_resubmit`, dll)
- Field `is_locked` menunjukkan apakah assignment dapat dikerjakan
- Field `user_submission` hanya muncul jika student sudah pernah submit
- Manajemen melihat semua field termasuk metadata

---

### 2. Show Assignment Detail
**Endpoint:** `GET /api/assignments/{assignment_id}`

**Akses:**
- ✅ Student (enrolled)
- ✅ Manajemen

**Query Parameters:**
- `include` - Sama seperti list (Manajemen only)

**Response:** Single object assignment

---

### 3. List Assignment Questions
**Endpoint:** `GET /api/assignments/{assignment_id}/questions`

**Akses:**
- ❌ Student (tidak bisa akses langsung)
- ✅ Manajemen Only

**Response:**
```json
{
  "success": true,
  "message": "Daftar soal berhasil diambil",
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "type": "essay",
      "content": "Explain the MVC pattern in Laravel",
      "weight": 25,
      "order": 1,
      "max_score": 25,
      "created_at": "2026-03-03T05:03:36+00:00"
    },
    {
      "id": 2,
      "assignment_id": 1,
      "type": "file_upload",
      "content": "Upload your Laravel project",
      "weight": 75,
      "order": 2,
      "max_score": 75,
      "created_at": "2026-03-03T05:03:36+00:00"
    }
  ]
}
```

**Question Types:** (Enum: `QuestionType`)
- `essay` - Jawaban esai (manual grading)
- `file_upload` - Upload file (manual grading)
- `multiple_choice` - Pilihan ganda (auto-grading)
- `checkbox` - Multiple select (auto-grading)

---

### 4. Create Assignment
**Endpoint:** `POST /api/assignments`

**Akses:**
- ❌ Student
- ✅ Instructor (own courses)
- ✅ Admin (assigned courses)
- ✅ Superadmin

**Request Body:**
```json
{
  "unit_id": 87,
  "title": "Laravel Basics Assignment",
  "description": "Complete the following tasks",
  "submission_type": "file",
  "max_score": 100,
  "max_attempts": 3,
  "cooldown_minutes": 60,
  "retake_enabled": true,
  "review_mode": "after_graded",
  "status": "draft",
  "order": 1
}
```

---

### 5. Update Assignment
**Endpoint:** `PUT /api/assignments/{assignment_id}`

**Akses:** Sama seperti create

---

### 6. Delete Assignment
**Endpoint:** `DELETE /api/assignments/{assignment_id}`

**Akses:** Sama seperti create

---

### 7. Publish Assignment
**Endpoint:** `POST /api/assignments/{assignment_id}/publish`

**Akses:** Sama seperti create

---

### 8. Check Prerequisites
**Endpoint:** `GET /api/assignments/{assignment_id}/check-prerequisites`

**Akses:**
- ✅ Student Only

**Response (Accessible):**
```json
{
  "success": true,
  "message": "Assignment dapat diakses",
  "data": {
    "accessible": true,
    "missing": []
  }
}
```

**Response (Locked):**
```json
{
  "success": true,
  "message": "Assignment terkunci",
  "data": {
    "accessible": false,
    "missing": [
      {
        "type": "lesson",
        "id": 5,
        "title": "Laravel Routing",
        "slug": "laravel-routing"
      },
      {
        "type": "quiz",
        "id": 2,
        "title": "Laravel Basics Quiz",
        "slug": "laravel-basics-quiz"
      }
    ]
  }
}
```

---

### 9. Duplicate Assignment
**Endpoint:** `POST /api/assignments/{assignment_id}/duplicate`

**Akses:**
- ❌ Student
- ✅ Instructor (own courses)
- ✅ Admin (assigned courses)
- ✅ Superadmin

**Request Body:**
```json
{
  "unit_id": 88,
  "title": "Laravel Basics Assignment (Copy)"
}
```

---

## API Submissions (Pengumpulan Assignment)

### Base URL
```
/api/submissions
```

### 1. List Submissions for Assignment
**Endpoint:** `GET /api/assignments/{assignment_id}/submissions`

**Akses:**
- ✅ Student (auto-filter: hanya melihat submission sendiri)
- ✅ Manajemen (melihat semua submissions dengan filter)

**Query Parameters:**

| Parameter | Tipe | Deskripsi | Contoh |
|-----------|------|-----------|--------|
| `per_page` | integer | Jumlah data per halaman | `?per_page=20` |
| `filter[status]` | string | Filter berdasarkan status | `?filter[status]=graded` |
| `filter[user_id]` | integer | Filter berdasarkan user (Manajemen only, Student auto-filter) | `?filter[user_id]=10` |
| `filter[is_late]` | boolean | Filter submission terlambat | `?filter[is_late]=true` |
| `filter[date_from]` | date | Filter dari tanggal | `?filter[date_from]=2026-03-01` |
| `filter[date_to]` | date | Filter sampai tanggal | `?filter[date_to]=2026-03-31` |
| `sort` | string | Sorting field | `?sort=-submitted_at` |

**Filter Values:**

**Status:** (Enum: `SubmissionStatus`)
- `draft` - Submission masih draft (belum disubmit)
- `submitted` - Sudah disubmit, menunggu grading
- `graded` - Sudah dinilai
- `late` - Terlambat
- `missing` - Tidak dikumpulkan

**Sort Fields:**
- `submitted_at` - Tanggal submit
- `created_at` - Tanggal dibuat
- `score` - Nilai
- `status` - Status

**Response Student (auto-filter own submissions):**
```json
{
  "success": true,
  "message": "Daftar submission berhasil diambil",
  "data": [
    {
      "id": 10,
      "assignment_id": 1,
      "status": "graded",
      "score": 85,
      "max_score": 100,
      "is_late": false,
      "submitted_at": "2026-03-03T10:00:00+00:00",
      "graded_at": "2026-03-03T15:00:00+00:00",
      "feedback": "Good work! Keep it up.",
      "files": [
        {
          "id": 1,
          "file_name": "assignment.pdf",
          "url": "https://example.com/files/assignment.pdf",
          "mime_type": "application/pdf",
          "size": 204800
        }
      ]
    }
  ]
}
```

**Response Manajemen (with filters):**
```json
{
  "data": [
    {
      "id": 10,
      "assignment_id": 1,
      "user": {
        "id": 15,
        "name": "Student Name",
        "email": "student@example.com"
      },
      "status": "graded",
      "score": 85,
      "max_score": 100,
      "is_late": false,
      "submitted_at": "2026-03-03T10:00:00+00:00",
      "graded_at": "2026-03-03T15:00:00+00:00",
      "grader": {
        "id": 5,
        "name": "Instructor Name",
        "email": "instructor@example.com"
      },
      "feedback": "Good work! Keep it up.",
      "files": [...],
      "answers": [
        {
          "id": 1,
          "question_id": 1,
          "answer_text": "MVC stands for Model-View-Controller...",
          "score": 20,
          "max_score": 25
        }
      ]
    }
  ]
}
```

---

### 2. Create Submission (Start Assignment)
**Endpoint:** `POST /api/assignments/{assignment_id}/submissions`

**Akses:**
- ✅ Student Only

**Validasi:**
- Assignment harus `is_locked=false`
- Belum mencapai `max_attempts`
- Cooldown period sudah lewat (jika retake)

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": 1,
      "answer_text": "MVC stands for Model-View-Controller..."
    },
    {
      "question_id": 2,
      "file": "base64_encoded_file_or_upload"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Submission berhasil dibuat",
  "data": {
    "id": 10,
    "assignment_id": 1,
    "status": "submitted",
    "submitted_at": "2026-03-03T10:00:00+00:00"
  }
}
```

---

### 3. Show Submission Detail
**Endpoint:** `GET /api/submissions/{submission_id}`

**Akses:**
- ✅ Student (own submission)
- ✅ Manajemen

**Response:** Single object submission dengan detail lengkap

---

### 4. List Submission Questions (Paginated)
**Endpoint:** `GET /api/submissions/{submission_id}/questions`

**Akses:**
- ✅ Student (own submission, 1 per page)
- ✅ Manajemen (all questions)

**Query Parameters:**
- `page` - Nomor halaman (Student: 1 question per page)

**Response Student (1 per page):**
```json
{
  "success": true,
  "message": "Soal berhasil diambil",
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "type": "essay",
      "content": "Explain the MVC pattern in Laravel",
      "weight": 25,
      "order": 1,
      "max_score": 25,
      "user_answer": {
        "id": 1,
        "answer_text": "MVC stands for...",
        "score": 20,
        "feedback": "Good explanation"
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 1,
      "total": 5,
      "last_page": 5,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

---

### 5. Grade Submission
**Endpoint:** `POST /api/submissions/{submission_id}/grade`

**Akses:**
- ❌ Student
- ✅ Instructor (own courses)
- ✅ Admin (assigned courses)
- ✅ Superadmin

**Request Body:**
```json
{
  "answers": [
    {
      "answer_id": 1,
      "score": 20,
      "feedback": "Good explanation, but could be more detailed"
    },
    {
      "answer_id": 2,
      "score": 70,
      "feedback": "Excellent work!"
    }
  ],
  "overall_feedback": "Great job overall!"
}
```

---

## API Quizzes

### Base URL
```
/api/courses/{course_slug}/quizzes
```

### 1. List Quizzes
**Endpoint:** `GET /api/courses/{course_slug}/quizzes`

**Akses:**
- ✅ Student (enrolled)
- ✅ Manajemen

**Query Parameters:**

| Parameter | Tipe | Deskripsi | Contoh |
|-----------|------|-----------|--------|
| `per_page` | integer | Jumlah data per halaman | `?per_page=20` |
| `page` | integer | Nomor halaman | `?page=2` |
| `filter[status]` | string | Filter berdasarkan status | `?filter[status]=published` |
| `filter[unit_id]` | integer | Filter berdasarkan unit | `?filter[unit_id]=87` |
| `sort` | string | Sorting field | `?sort=order` |
| `include` | string | Include relasi (Manajemen only) | `?include=questions,creator` |

**Filter Values:**

**Status:** (Enum: `QuizStatus`)
- `draft` - Quiz masih draft
- `published` - Quiz sudah dipublikasikan
- `archived` - Quiz diarsipkan

**Review Mode:** (Enum: `ReviewMode`)
- `immediate` - Immediate (langsung lihat hasil)
- `manual` - Manual (setelah manual grading)
- `deferred` - Deferred (ditunda sampai waktu tertentu)
- `hidden` - Hidden (tidak bisa lihat hasil)

**Sort Fields:**
- `order` - Urutan quiz dalam unit
- `title` - Judul quiz
- `created_at` - Tanggal dibuat

**Response Student:**
```json
{
  "success": true,
  "message": "Daftar quiz berhasil diambil",
  "data": [
    {
      "id": 162,
      "title": "Laravel Basics Quiz",
      "description": "Test your Laravel knowledge",
      "passing_grade": 70,
      "max_score": 100,
      "max_attempts": 3,
      "time_limit_minutes": 30,
      "retake_enabled": true,
      "auto_grading": true,
      "review_mode": "after_graded",
      "is_locked": false,
      "unit_slug": "getting-started-29",
      "questions_count": 4,
      "user_submission": {
        "id": 50,
        "status": "graded",
        "final_score": 85,
        "submitted_at": "2026-03-03T10:00:00+00:00"
      },
      "attachments": [
        {
          "id": 1,
          "name": "quiz-instructions.pdf",
          "url": "https://example.com/files/quiz-instructions.pdf",
          "mime_type": "application/pdf",
          "size": 51200
        }
      ],
      "created_at": "2026-03-03T05:06:23+00:00"
    }
  ]
}
```

**Response Manajemen:**
```json
{
  "data": [
    {
      "id": 162,
      "title": "Laravel Basics Quiz",
      "description": "Test your Laravel knowledge",
      "passing_grade": 70,
      "auto_grading": true,
      "max_score": 100,
      "max_attempts": 3,
      "cooldown_minutes": 60,
      "time_limit_minutes": 30,
      "retake_enabled": true,
      "randomization_type": "none",
      "question_bank_count": null,
      "review_mode": "after_graded",
      "status": "published",
      "available_from": "2026-03-01T00:00:00+00:00",
      "deadline_at": "2026-03-31T23:59:59+00:00",
      "tolerance_minutes": 15,
      "late_penalty_percent": 10,
      "scope_type": "unit",
      "assignable_type": null,
      "assignable_id": null,
      "lesson_id": null,
      "created_by": 5,
      "creator": {
        "id": 5,
        "name": "Instructor Name"
      },
      "questions_count": 4,
      "attachments": [...],
      "created_at": "2026-03-03T05:06:23+00:00",
      "updated_at": "2026-03-03T05:06:23+00:00"
    }
  ]
}
```

**Catatan Penting:**
- Student TIDAK melihat `answer_key` pada soal
- Field `is_locked` menunjukkan apakah quiz dapat dikerjakan
- Field `user_submission` hanya muncul jika student sudah pernah mengerjakan
- Auto-grading otomatis menilai soal pilihan ganda, true/false, checkbox

---

### 2. Show Quiz Detail
**Endpoint:** `GET /api/quizzes/{quiz_id}`

**Akses:**
- ✅ Student (enrolled)
- ✅ Manajemen

**Query Parameters:**
- `include` - Sama seperti list (Manajemen only)

**Response:** Single object quiz

---

### 3. List Quiz Questions (Direct Access)
**Endpoint:** `GET /api/quizzes/{quiz_id}/questions`

**Akses:**
- ❌ Student (FORBIDDEN - harus start quiz dulu)
- ✅ Manajemen Only (untuk preview)

**Response Manajemen:**
```json
{
  "success": true,
  "message": "Daftar soal berhasil diambil",
  "data": [
    {
      "id": 645,
      "quiz_id": 162,
      "type": "multiple_choice",
      "content": "Which of the following best describes dependency injection?",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "answer_key": [0],
      "weight": 25,
      "order": 1,
      "max_score": 25,
      "created_at": "2026-03-03T05:06:23+00:00"
    },
    {
      "id": 646,
      "quiz_id": 162,
      "type": "true_false",
      "content": "Laravel uses the MVC architectural pattern.",
      "options": ["True", "False"],
      "answer_key": [0],
      "weight": 25,
      "order": 2,
      "max_score": 25,
      "created_at": "2026-03-03T05:06:23+00:00"
    },
    {
      "id": 647,
      "quiz_id": 162,
      "type": "checkbox",
      "content": "Select all valid HTTP methods:",
      "options": ["Option 1", "Option 2", "Option 3", "Option 4"],
      "answer_key": [0, 2],
      "weight": 25,
      "order": 3,
      "max_score": 25,
      "created_at": "2026-03-03T05:06:23+00:00"
    },
    {
      "id": 648,
      "quiz_id": 162,
      "type": "essay",
      "content": "Explain the concept of middleware in web applications.",
      "options": null,
      "answer_key": null,
      "weight": 25,
      "order": 4,
      "max_score": 25,
      "created_at": "2026-03-03T05:06:23+00:00"
    }
  ]
}
```

**Response Student:**
```json
{
  "success": false,
  "message": "Anda harus memulai quiz terlebih dahulu",
  "errors": {
    "quiz": ["You must start the quiz first to access questions"]
  }
}
```

**Question Types:** (Enum: `QuizQuestionType`)
- `multiple_choice` - Pilihan ganda (1 jawaban benar, auto-grading)
- `checkbox` - Multiple select (bisa lebih dari 1 jawaban benar, auto-grading)
- `true_false` - Benar/Salah (auto-grading)
- `essay` - Jawaban esai (manual grading)

---

### 4. Create Quiz
**Endpoint:** `POST /api/quizzes`

**Akses:**
- ❌ Student
- ✅ Instructor (own courses)
- ✅ Admin (assigned courses)
- ✅ Superadmin

**Request Body:**
```json
{
  "unit_id": 87,
  "title": "Laravel Basics Quiz",
  "description": "Test your Laravel knowledge",
  "passing_grade": 70,
  "max_score": 100,
  "max_attempts": 3,
  "time_limit_minutes": 30,
  "retake_enabled": true,
  "auto_grading": true,
  "review_mode": "after_graded",
  "status": "draft",
  "order": 1
}
```

---

### 5. Update Quiz
**Endpoint:** `PUT /api/quizzes/{quiz_id}`

**Akses:** Sama seperti create

---

### 6. Delete Quiz
**Endpoint:** `DELETE /api/quizzes/{quiz_id}`

**Akses:** Sama seperti create

---

### 7. Publish Quiz
**Endpoint:** `POST /api/quizzes/{quiz_id}/publish`

**Akses:** Sama seperti create

---

## API Quiz Submissions

### Base URL
```
/api/quizzes/{quiz_id}/submissions
```

### 1. Start Quiz
**Endpoint:** `POST /api/quizzes/{quiz_id}/submissions/start`

**Akses:**
- ✅ Student Only

**Validasi:**
- Quiz harus `is_locked=false`
- Belum ada draft submission (jika sudah ada, return error dengan submission_id)
- Belum mencapai `max_attempts`
- Cooldown period sudah lewat (jika retake)

**Response (Success):**
```json
{
  "success": true,
  "message": "Quiz berhasil dimulai",
  "data": {
    "submission_id": 50,
    "quiz_id": 162,
    "status": "draft",
    "started_at": "2026-03-03T10:00:00+00:00",
    "time_limit_minutes": 30,
    "must_submit_before": "2026-03-03T10:30:00+00:00"
  }
}
```

**Response (Already Started):**
```json
{
  "success": false,
  "message": "Anda sudah memulai quiz ini",
  "errors": {
    "quiz": ["You already have a draft submission"]
  },
  "data": {
    "submission_id": 50
  }
}
```

**Response (Locked):**
```json
{
  "success": false,
  "message": "Quiz terkunci",
  "errors": {
    "quiz": ["Complete all prerequisites first"]
  }
}
```

---

### 2. List Quiz Submission Questions (Paginated)
**Endpoint:** `GET /api/quiz-submissions/{submission_id}/questions`

**Akses:**
- ✅ Student (own submission, 1 per page)
- ✅ Manajemen (all questions)

**Query Parameters:**
- `page` - Nomor halaman (Student: 1 question per page)

**Response Student (1 per page, NO answer_key):**
```json
{
  "success": true,
  "message": "Soal berhasil diambil",
  "data": [
    {
      "id": 645,
      "quiz_id": 162,
      "type": "multiple_choice",
      "content": "Which of the following best describes dependency injection?",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "weight": 25,
      "order": 1,
      "max_score": 25,
      "user_answer": {
        "id": 100,
        "selected_options": [1],
        "answer_text": null
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 1,
      "total": 4,
      "last_page": 4,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

**Response Manajemen (with answer_key):**
```json
{
  "data": [
    {
      "id": 645,
      "quiz_id": 162,
      "type": "multiple_choice",
      "content": "Which of the following best describes dependency injection?",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "answer_key": [0],
      "weight": 25,
      "order": 1,
      "max_score": 25,
      "user_answer": {
        "id": 100,
        "selected_options": [1],
        "answer_text": null,
        "is_correct": false,
        "score": 0
      }
    }
  ]
}
```

**Catatan Penting:**
- Student TIDAK pernah melihat field `answer_key`
- Pagination 1 per 1 untuk student (navigasi soal satu per satu)
- Manajemen dapat melihat semua soal sekaligus

---

### 3. Submit Answer
**Endpoint:** `POST /api/quiz-submissions/{submission_id}/answers`

**Akses:**
- ✅ Student Only

**Request Body (Multiple Choice/True False):**
```json
{
  "question_id": 645,
  "selected_options": [0]
}
```

**Request Body (Checkbox):**
```json
{
  "question_id": 647,
  "selected_options": [0, 2]
}
```

**Request Body (Essay):**
```json
{
  "question_id": 648,
  "answer_text": "Middleware is a mechanism that filters HTTP requests..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Jawaban berhasil disimpan",
  "data": {
    "answer_id": 100,
    "question_id": 645,
    "saved_at": "2026-03-03T10:05:00+00:00"
  }
}
```

---

### 4. Submit Quiz (Finish)
**Endpoint:** `POST /api/quiz-submissions/{submission_id}/submit`

**Akses:**
- ✅ Student Only

**Validasi:**
- Semua soal harus sudah dijawab (kecuali essay optional)
- Time limit belum habis (jika ada)

**Response:**
```json
{
  "success": true,
  "message": "Quiz berhasil disubmit",
  "data": {
    "submission_id": 50,
    "status": "graded",
    "final_score": 85,
    "max_score": 100,
    "passed": true,
    "submitted_at": "2026-03-03T10:25:00+00:00",
    "graded_at": "2026-03-03T10:25:00+00:00"
  }
}
```

**Catatan:**
- Auto-grading langsung menilai soal multiple_choice, checkbox, true_false
- Essay perlu manual grading oleh instructor
- Status `graded` jika semua soal auto-graded, `submitted` jika ada essay

---

### 5. List Quiz Submissions
**Endpoint:** `GET /api/quizzes/{quiz_id}/submissions`

**Akses:**
- ✅ Student (auto-filter: hanya melihat submission sendiri)
- ✅ Manajemen (melihat semua submissions dengan filter)

**Query Parameters:**

| Parameter | Tipe | Deskripsi | Contoh |
|-----------|------|-----------|--------|
| `per_page` | integer | Jumlah data per halaman | `?per_page=20` |
| `filter[status]` | string | Filter berdasarkan status | `?filter[status]=graded` |
| `filter[user_id]` | integer | Filter berdasarkan user | `?filter[user_id]=15` |
| `filter[passed]` | boolean | Filter yang lulus | `?filter[passed]=true` |
| `sort` | string | Sorting field | `?sort=-submitted_at` |

**Response:**
```json
{
  "data": [
    {
      "id": 50,
      "quiz_id": 162,
      "user": {
        "id": 15,
        "name": "Student Name",
        "email": "student@example.com"
      },
      "status": "graded",
      "final_score": 85,
      "max_score": 100,
      "passed": true,
      "attempt_number": 1,
      "started_at": "2026-03-03T10:00:00+00:00",
      "submitted_at": "2026-03-03T10:25:00+00:00",
      "graded_at": "2026-03-03T10:25:00+00:00",
      "answers": [...]
    }
  ]
}
```

---

## API Progress (Progres Belajar)

### Base URL
```
/api/progress
```

### 1. Get Course Progress
**Endpoint:** `GET /api/courses/{course_slug}/progress`

**Akses:**
- ✅ Student (own progress)
- ✅ Manajemen (any student progress with user_id param)

**Query Parameters (Manajemen Only):**
- `user_id` - ID user yang ingin dilihat progressnya

**Response Student:**
```json
{
  "success": true,
  "message": "Progress berhasil diambil",
  "data": {
    "course": {
      "id": 29,
      "title": "Laravel Fundamentals",
      "slug": "laravel-fundamentals"
    },
    "overall_progress": {
      "percentage": 45.5,
      "completed_items": 10,
      "total_items": 22
    },
    "units": [
      {
        "id": 87,
        "title": "Getting Started",
        "order": 1,
        "is_locked": false,
        "progress": {
          "percentage": 100,
          "completed_items": 8,
          "total_items": 8
        },
        "lessons": [
          {
            "id": 1,
            "title": "Introduction to Laravel",
            "order": 1,
            "is_completed": true,
            "completed_at": "2026-03-03T10:00:00+00:00"
          },
          {
            "id": 2,
            "title": "Laravel Routing",
            "order": 2,
            "is_completed": true,
            "completed_at": "2026-03-03T11:00:00+00:00"
          }
        ],
        "assignments": [
          {
            "id": 1,
            "title": "Laravel Basics Assignment",
            "order": 3,
            "is_passed": true,
            "score": 85,
            "max_score": 100,
            "submitted_at": "2026-03-03T12:00:00+00:00"
          }
        ],
        "quizzes": [
          {
            "id": 162,
            "title": "Laravel Basics Quiz",
            "order": 4,
            "is_passed": true,
            "final_score": 85,
            "passing_grade": 70,
            "submitted_at": "2026-03-03T13:00:00+00:00"
          }
        ]
      },
      {
        "id": 88,
        "title": "Fundamentals and Core Concepts",
        "order": 2,
        "is_locked": false,
        "progress": {
          "percentage": 25,
          "completed_items": 2,
          "total_items": 8
        },
        "lessons": [...],
        "assignments": [...],
        "quizzes": [...]
      },
      {
        "id": 89,
        "title": "Intermediate Techniques",
        "order": 3,
        "is_locked": true,
        "prerequisite_message": "Complete all lessons and pass all assignments/quizzes in previous unit",
        "missing_prerequisites": [
          {
            "type": "lesson",
            "id": 10,
            "title": "Advanced Routing",
            "unit_title": "Fundamentals and Core Concepts"
          },
          {
            "type": "assignment",
            "id": 5,
            "title": "Routing Assignment",
            "unit_title": "Fundamentals and Core Concepts",
            "passing_required": true
          }
        ],
        "progress": {
          "percentage": 0,
          "completed_items": 0,
          "total_items": 6
        }
      }
    ]
  }
}
```

**Catatan Penting:**
- Progress dihitung berdasarkan completion status
- Lesson: completed = true/false
- Assignment: passed = score >= 60% dari max_score
- Quiz: passed = final_score >= passing_grade
- Unit terkunci jika unit sebelumnya belum 100% selesai
- Field `missing_prerequisites` menunjukkan item yang harus diselesaikan

---

### 2. Get Unit Progress Detail
**Endpoint:** `GET /api/units/{unit_id}/progress`

**Akses:**
- ✅ Student (own progress)
- ✅ Manajemen (any student with user_id param)

**Response:**
```json
{
  "success": true,
  "message": "Progress unit berhasil diambil",
  "data": {
    "unit": {
      "id": 87,
      "title": "Getting Started",
      "order": 1
    },
    "progress": {
      "percentage": 100,
      "completed_items": 8,
      "total_items": 8
    },
    "content": [
      {
        "type": "lesson",
        "id": 1,
        "title": "Introduction to Laravel",
        "order": 1,
        "is_completed": true,
        "completed_at": "2026-03-03T10:00:00+00:00"
      },
      {
        "type": "lesson",
        "id": 2,
        "title": "Laravel Routing",
        "order": 2,
        "is_completed": true,
        "completed_at": "2026-03-03T11:00:00+00:00"
      },
      {
        "type": "assignment",
        "id": 1,
        "title": "Laravel Basics Assignment",
        "order": 3,
        "is_passed": true,
        "score": 85,
        "max_score": 100,
        "passing_required": true,
        "submitted_at": "2026-03-03T12:00:00+00:00"
      },
      {
        "type": "quiz",
        "id": 162,
        "title": "Laravel Basics Quiz",
        "order": 4,
        "is_passed": true,
        "final_score": 85,
        "passing_grade": 70,
        "passing_required": true,
        "submitted_at": "2026-03-03T13:00:00+00:00"
      }
    ]
  }
}
```

---

## Ringkasan Filter, Sort, dan Include

### Courses API

**Filters:**
- `status`: draft, published, archived (Enum: `CourseStatus`)
- `level_tag`: dasar, menengah, mahir (Enum: `LevelTag`)
- `type`: okupasi, kluster (Enum: `CourseType`)
- `category_id`: integer (dari API `/api/categories`)
- `tag`: string atau array (dari courses dengan `include=tags`)

**Sorts:**
- `id`, `code`, `title`, `created_at`, `updated_at`, `published_at`

**Includes:**
- Public: `tags`, `category`, `instructor`, `units`
- Enrolled Student: `lessons`, `quizzes`, `assignments`, `units.lessons`, `units.lessons.blocks`
- Manager: `enrollments`, `enrollments.user`, `admins`

**Search:**
- Full-text search pada `title`, `description`, `code`

---

### Units API

**Filters:**
- `status`: draft, published, archived (Enum: `CourseStatus` - sama dengan Course)

**Sorts:**
- `id`, `code`, `title`, `order`, `status`, `created_at`, `updated_at`

**Includes:**
- Public: `course`
- Enrolled Student/Manager: `lessons`, `lessons.blocks`

---

### Lessons API

**Filters:**
- `status`: draft, published, archived (Enum: `CourseStatus` - sama dengan Course)

**Sorts:**
- `order`, `title`, `created_at`

**Includes:**
- `unit`, `blocks`

---

### Assignments API

**Filters:**
- `status`: draft, published, archived (Enum: `AssignmentStatus`)
- `unit_id`: integer (dari API `/api/courses/{course_slug}/units`)

**Sorts:**
- `order`, `title`, `created_at`, `updated_at`

**Includes (Manajemen Only):**
- `unit`, `questions`, `creator`, `prerequisites`

---

### Quizzes API

**Filters:**
- `status`: draft, published, archived (Enum: `QuizStatus`)
- `unit_id`: integer (dari API `/api/courses/{course_slug}/units`)

**Sorts:**
- `order`, `title`, `created_at`

**Includes (Manajemen Only):**
- `questions`, `creator`

---

### Submissions API

**Filters:**
- `status`: draft, submitted, graded, late, missing (Enum: `SubmissionStatus`)
- `user_id`: integer (Manajemen only, dari API enrollments)
- `is_late`: boolean (true/false)
- `date_from`: date (format: YYYY-MM-DD)
- `date_to`: date (format: YYYY-MM-DD)

**Sorts:**
- `submitted_at`, `created_at`, `score`, `status`

---

### Quiz Submissions API

**Filters:**
- `status`: draft, submitted, graded, late, missing (Enum: `QuizSubmissionStatus`)
- `user_id`: integer (Manajemen only, dari API enrollments)
- `passed`: boolean (true/false)

**Sorts:**
- `submitted_at`, `started_at`, `final_score`

---

## Cara Mendapatkan Filter Values

### 1. Status Values (Enum - Fixed)
Status values adalah enum yang sudah fixed di backend:

**Course Status:** (Enum: `CourseStatus`)
- `draft` - Draft
- `published` - Published
- `archived` - Archived

**Unit Status:** (Enum: `CourseStatus` - sama dengan Course)
- `draft` - Draft
- `published` - Published
- `archived` - Archived

**Lesson Status:** (Enum: `CourseStatus` - sama dengan Course)
- `draft` - Draft
- `published` - Published
- `archived` - Archived

**Assignment Status:** (Enum: `AssignmentStatus`)
- `draft` - Draft
- `published` - Published
- `archived` - Archived

**Quiz Status:** (Enum: `QuizStatus`)
- `draft` - Draft
- `published` - Published
- `archived` - Archived

**Submission Status:** (Enum: `SubmissionStatus`)
- `draft` - Draft (belum disubmit)
- `submitted` - Submitted (sudah disubmit, menunggu grading)
- `graded` - Graded (sudah dinilai)
- `late` - Late (terlambat)
- `missing` - Missing (tidak dikumpulkan)

**Quiz Submission Status:** (Enum: `QuizSubmissionStatus`)
- `draft` - Draft (sedang dikerjakan)
- `submitted` - Submitted (sudah disubmit, menunggu grading)
- `graded` - Graded (sudah dinilai)
- `late` - Late (terlambat)
- `missing` - Missing (tidak dikumpulkan)

**Submission Type:** (Enum: `SubmissionType`)
- `text` - Text (jawaban teks)
- `file` - File (upload file)
- `mixed` - Mixed (teks + file)

**Review Mode:** (Enum: `ReviewMode`)
- `immediate` - Immediate (langsung lihat hasil)
- `manual` - Manual (setelah manual grading)
- `deferred` - Deferred (ditunda sampai waktu tertentu)
- `hidden` - Hidden (tidak bisa lihat hasil)

**Question Type - Assignment:** (Enum: `QuestionType`)
- `multiple_choice` - Multiple Choice (pilihan ganda)
- `checkbox` - Checkbox (multiple select)
- `essay` - Essay (jawaban esai)
- `file_upload` - File Upload (upload file)

**Question Type - Quiz:** (Enum: `QuizQuestionType`)
- `multiple_choice` - Multiple Choice (pilihan ganda)
- `checkbox` - Checkbox (multiple select)
- `true_false` - True/False (benar/salah)
- `essay` - Essay (jawaban esai)

**Level Tag:** (Enum: `LevelTag`)
- `dasar` - Dasar
- `menengah` - Menengah
- `mahir` - Mahir

**Course Type:** (Enum: `CourseType`)
- `okupasi` - Okupasi
- `kluster` - Kluster

**Enrollment Type:** (Enum: `EnrollmentType`)
- `auto_accept` - Auto Accept
- `key_based` - Key Based
- `approval` - Approval

**Category Status:** (Enum: `CategoryStatus`)
- `active` - Active
- `inactive` - Inactive

---

### 2. Master Data API (Semua Enum Values)
**Endpoint:** `GET /api/master-data/types`

**Akses:** Public

**Response:**
```json
{
  "success": true,
  "data": {
    "course-status": [
      {"value": "draft", "label": "Draft"},
      {"value": "published", "label": "Published"},
      {"value": "archived", "label": "Archived"}
    ],
    "course-types": [
      {"value": "okupasi", "label": "Okupasi"},
      {"value": "kluster", "label": "Kluster"}
    ],
    "enrollment-types": [
      {"value": "auto_accept", "label": "Auto Accept"},
      {"value": "key_based", "label": "Key Based"},
      {"value": "approval", "label": "Approval"}
    ],
    "level-tags": [
      {"value": "dasar", "label": "Dasar"},
      {"value": "menengah", "label": "Menengah"},
      {"value": "mahir", "label": "Mahir"}
    ],
    "assignment-status": [
      {"value": "draft", "label": "Draft"},
      {"value": "published", "label": "Published"},
      {"value": "archived", "label": "Archived"}
    ],
    "submission-status": [
      {"value": "draft", "label": "Draft"},
      {"value": "submitted", "label": "Submitted"},
      {"value": "graded", "label": "Graded"},
      {"value": "late", "label": "Late"},
      {"value": "missing", "label": "Missing"}
    ],
    "submission-types": [
      {"value": "text", "label": "Text"},
      {"value": "file", "label": "File"},
      {"value": "mixed", "label": "Mixed"}
    ],
    "category-status": [
      {"value": "active", "label": "Active"},
      {"value": "inactive", "label": "Inactive"}
    ]
  }
}
```

**Catatan:** Endpoint ini mengembalikan semua enum values yang tersedia di sistem dalam format `{value, label}`.

---

### 3. Category Values (Dynamic dari Database)
**Endpoint:** `GET /api/categories`

**Akses:** Public

**Query Parameters:**
- `per_page` - Jumlah data per halaman
- `filter[status]` - Filter berdasarkan status (`active`, `inactive`)
- `sort` - Sorting (`name`, `value`, `created_at`, `updated_at`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Programming",
      "value": "programming",
      "description": "Programming courses",
      "status": "active",
      "created_at": "2026-01-01T00:00:00+00:00"
    },
    {
      "id": 2,
      "name": "Design",
      "value": "design",
      "description": "Design courses",
      "status": "active",
      "created_at": "2026-01-01T00:00:00+00:00"
    }
  ],
  "meta": {
    "pagination": {...},
    "filters": {
      "status": {
        "label": "Status",
        "type": "select",
        "options": [
          {"value": "active", "label": "Active"},
          {"value": "inactive", "label": "Inactive"}
        ]
      }
    },
    "allowed_sorts": ["name", "value", "created_at", "updated_at"]
  }
}
```

**Cara Menggunakan:**
- Gunakan field `id` untuk filter `filter[category_id]` di courses API
- Gunakan field `value` untuk display/search

---

### 4. Tag Values (Dynamic dari Database)
**Endpoint:** `GET /api/courses` dengan `include=tags`

**Akses:** Public

**Cara Mendapatkan:**
1. Ambil list courses dengan include tags
2. Extract unique tags dari response
3. Gunakan tag name/slug untuk filter

**Contoh:**
```bash
GET /api/courses?include=tags&per_page=100
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Laravel Fundamentals",
      "tags": [
        {"id": 1, "name": "PHP", "slug": "php"},
        {"id": 2, "name": "Laravel", "slug": "laravel"}
      ]
    }
  ]
}
```

**Filter Usage:**
```bash
GET /api/courses?filter[tag]=php
GET /api/courses?filter[tag][]=php&filter[tag][]=laravel
```

---

### 5. Unit Values (untuk filter assignment/quiz)
**Endpoint:** `GET /api/courses/{course_slug}/units`

**Akses:** Enrolled Student / Manajemen

**Response:**
```json
{
  "data": [
    {"id": 87, "title": "Getting Started", "slug": "getting-started-29"},
    {"id": 88, "title": "Fundamentals", "slug": "fundamentals-29"}
  ]
}
```

**Filter Usage:**
```bash
GET /api/courses/{course_slug}/assignments?filter[unit_id]=87
GET /api/courses/{course_slug}/quizzes?filter[unit_id]=87
```

---

### 6. User Values (untuk filter submission - Manajemen only)
**Endpoint:** `GET /api/courses/{course_slug}/enrollments` (Manajemen only)

**Akses:** Manajemen Only

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user": {
        "id": 15,
        "name": "Student Name",
        "email": "student@example.com"
      },
      "status": "active"
    }
  ]
}
```

**Filter Usage:**
```bash
GET /api/assignments/{assignment_id}/submissions?filter[user_id]=15
GET /api/quizzes/{quiz_id}/submissions?filter[user_id]=15
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": null
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "This action is unauthorized",
  "errors": {
    "authorization": ["You do not have permission to perform this action"]
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found",
  "errors": {
    "resource": ["The requested resource was not found"]
  }
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required"],
    "max_score": ["The max score must be at least 1"]
  }
}
```

### 422 Prerequisite Error
```json
{
  "success": false,
  "message": "Prerequisites not met",
  "errors": {
    "prerequisites": ["Complete all prerequisites first"]
  },
  "data": {
    "missing": [
      {
        "type": "lesson",
        "id": 5,
        "title": "Laravel Routing"
      }
    ]
  }
}
```

---

## Rate Limiting

Semua endpoint memiliki rate limiting:
- **Student**: 60 requests per minute
- **Manajemen**: 120 requests per minute

Response saat rate limit exceeded:
```json
{
  "success": false,
  "message": "Too many requests",
  "errors": {
    "rate_limit": ["Please wait before making another request"]
  }
}
```

---

## Pagination Format

Semua list endpoint menggunakan format pagination yang sama:

```json
{
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

---

## Best Practices

### 1. Untuk Student
- Selalu cek field `is_locked` sebelum mengakses konten
- Gunakan endpoint progress untuk tracking pembelajaran
- Submit assignment/quiz sebelum deadline
- Gunakan pagination untuk navigasi soal quiz/assignment

### 2. Untuk Manajemen
- Gunakan filter dan sort untuk manajemen data yang efisien
- Gunakan include untuk mengurangi jumlah request
- Monitor submission status untuk grading
- Gunakan search untuk mencari data spesifik

### 3. Performance
- Gunakan `per_page` yang sesuai (jangan terlalu besar)
- Gunakan `include` hanya untuk data yang dibutuhkan
- Cache response di client side jika memungkinkan
- Gunakan pagination untuk data besar

---

## Changelog

### Version 1.0 (2026-03-03)
- Initial documentation
- Prerequisite system implementation
- Quiz flow with pagination
- Assignment submission with pagination
- Role-based data visibility

---

## Support

Untuk pertanyaan atau issue, hubungi:
- Email: support@example.com
- Slack: #api-support

---

**Dokumentasi ini dibuat pada:** 2026-03-03  
**Terakhir diupdate:** 2026-03-03  
**Versi API:** 1.0
