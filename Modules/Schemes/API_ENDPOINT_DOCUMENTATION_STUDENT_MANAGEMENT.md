# Schemes API Documentation (Student vs Manajemen)

Dokumen ini merangkum endpoint API yang terkait module `Schemes` dan controller Schemes yang diekspos dari route module lain.

Ruang lingkup endpoint:
- `Modules/Schemes/routes/api.php`
- `Modules/Common/routes/api.php` untuk endpoint `tags` (controller dari `Modules/Schemes`)

## 1) Konvensi Umum Response

Semua endpoint yang menggunakan trait `ApiResponse` punya wrapper:
- `success` (boolean)
- `message` (string)
- `data` (mixed)
- `meta` (object|null)
- `errors` (object|array|null)

Untuk endpoint paginasi, `meta.pagination` berisi:
- `current_page`, `per_page`, `total`, `last_page`, `from`, `to`, `has_next`, `has_prev`

Sumber:
- `app/Support/ApiResponse.php`

## 2) Pembagian Akses

- `Public`: tanpa login
- `Student`: user role Student (auth)
- `Manajemen`: `Instructor`, `Admin`, `Superadmin`

Catatan penting:
- Beberapa route dibatasi role via middleware, lalu diperketat lagi oleh policy/authorize di controller.
- Endpoint `tags` write (`POST/PUT/DELETE`) route mengizinkan `Superadmin|Admin|Instructor`, tetapi policy `TagPolicy` efektif membatasi ke `Superadmin`.
- Management dapat mengirim `tags` custom (array string) langsung pada create/update course. Tag baru akan otomatis dibuat/sync dan langsung bisa dipakai untuk search course via query `tag`.

Sumber:
- `Modules/Schemes/routes/api.php`
- `Modules/Common/routes/api.php`
- `Modules/Schemes/app/Policies/*.php`

## 3) Endpoint Student (termasuk Public + Auth Student)

## 3.1 Courses

### GET `/v1/courses` (Public)
- Tujuan: list course
- Query params:
  - `per_page` integer, default `15`
  - `search` string (custom full-text via scope search)
  - `tag` string/array (custom, cocok ke slug/nama tag)
  - `filter[status]` exact (`CourseStatus`)
  - `filter[level_tag]` exact (`LevelTag`)
  - `filter[type]` exact (`CourseType`)
  - `filter[category_id]` exact integer
  - `include` comma-separated (PUBLIC include only): `tags,category,instructor,units`
  - `sort` allowed: `id,code,title,created_at,updated_at,published_at` (Spatie sort, boleh prefiks `-`)

Nilai cepat endpoint ini:
- `filter[status]`: `draft|published|archived`
- `filter[level_tag]`: `dasar|menengah|mahir`
- `filter[type]`: `okupasi|kluster`
- `include`: `tags,category,instructor,units`
- `sort`: `id,code,title,created_at,updated_at,published_at` (prefix `-` untuk desc)

Sumber nilai:
- `Modules/Schemes/app/Enums/CourseStatus.php`
- `Modules/Schemes/app/Enums/LevelTag.php`
- `Modules/Schemes/app/Enums/CourseType.php`
- `Modules/Schemes/app/Services/Support/CourseIncludeAuthorizer.php`
- `Modules/Schemes/app/Services/Support/CourseFinder.php` (`applyTagFilter`)

Sumber:
- `CourseController::index`
- `CourseFinder::buildQueryForIndex`
- `CourseIncludeAuthorizer::getPublicIncludes`

### GET `/v1/courses/{course:slug}` (Public, policy `can:view,course`)
- Tujuan: detail course
- Query params:
  - `include` role-based:
    - Public: `tags,category,instructor,units`
    - Student enrolled aktif: Public + `lessons,quizzes,assignments,units.lessons`
    - Manajemen: Student + `enrollments,enrollments.user,admins,units.lessons.blocks`
- Tanpa include: return course dasar

Sumber:
- `routes/api.php` + `CourseController::show`
- `CourseFinder::findBySlugWithIncludes`
- `Modules/Schemes/app/Services/Support/CourseIncludeAuthorizer.php`

### GET `/v1/my-courses` (Student auth)
- Tujuan: list course yang di-enroll user
- Query params:
  - `per_page` integer, default `15`
  - `filter[status]` khusus enrollment: `active` atau `completed` (jika tidak diisi, keduanya)
  - `filter[level_tag]` exact (`LevelTag`)
  - `filter[type]` exact (`CourseType`)
  - `filter[category_id]` exact integer
  - `include` (public include only): `tags,category,instructor,units`
  - `sort` allowed: `title,created_at,updated_at` (boleh prefiks `-`)

Nilai cepat endpoint ini:
- `filter[status]`: `active|completed`
- `filter[level_tag]`: `dasar|menengah|mahir`
- `filter[type]`: `okupasi|kluster`
- `include`: `tags,category,instructor,units`
- `sort`: `title,created_at,updated_at` (prefix `-` untuk desc)
- enum status enrollment lengkap (referensi domain): `pending|active|completed|cancelled`

Sumber nilai:
- `Modules/Enrollments/app/Enums/EnrollmentStatus.php`
- `Modules/Schemes/app/Enums/LevelTag.php`
- `Modules/Schemes/app/Enums/CourseType.php`
- `Modules/Schemes/app/Services/Support/CourseIncludeAuthorizer.php`

Sumber:
- `CourseController::myEnrolledCourses`
- `CourseFinder::listEnrolledCourses`
- `Modules/Enrollments/app/Enums/EnrollmentStatus.php`

## 3.2 Units

### GET `/v1/courses/{course:slug}/units` (Auth)
- Query params:
  - `per_page` integer default `15`
  - `filter[status]` exact (umumnya `draft|published|archived`, mengikuti data)
  - `include`: `course,lessons`
  - `sort`: `order,title,created_at`

Nilai cepat endpoint ini:
- `filter[status]`: `draft|published|archived`
- `include`: `course,lessons`
- `sort`: `order,title,created_at`

Sumber nilai:
- `Modules/Schemes/app/Enums/CourseStatus.php`

Sumber:
- `UnitController::index`
- `UnitService::paginate`

### GET `/v1/courses/{course:slug}/units/{unit:slug}` (Auth + `can:view,unit`)
- Query params:
  - `include` role-based:
    - Public set: `course`
    - Student enrolled aktif: `course,lessons`
    - Manajemen: `course,lessons,lessons.blocks`

Sumber:
- `UnitController::show`
- `UnitService::findWithIncludes`
- `Modules/Schemes/app/Services/Support/UnitIncludeAuthorizer.php`

### GET `/v1/units` (Auth)
- Tujuan: global unit list (sudah difilter role)
- Query params:
  - `per_page` integer default `15`
  - `search` (top-level) string
  - `filter[status]`
  - `filter[course_slug]`
  - `filter[include]` comma-separated: `course,lessons`
  - `filter[sort]`: `order,title,created_at`
  - `filter[order]`: `asc|desc`

Nilai cepat endpoint ini:
- `filter[status]`: `draft|published|archived`
- `filter[include]`: `course,lessons`
- `filter[sort]`: `order,title,created_at`
- `filter[order]`: `asc|desc`

Sumber nilai:
- `Modules/Schemes/app/Enums/CourseStatus.php`

Sumber:
- `UnitController::indexAll`
- `UnitService::paginateAll`

### GET `/v1/units/{unit:slug}` (Auth + `can:view,unit`)
- Query params:
  - `include` role-based:
    - Public set: `course`
    - Student enrolled aktif: `course,lessons`
    - Manajemen: `course,lessons,lessons.blocks`

Sumber:
- `UnitController::showGlobal`
- `UnitService::findWithIncludes`
- `Modules/Schemes/app/Services/Support/UnitIncludeAuthorizer.php`

### GET `/v1/courses/{course:slug}/units/{unit:slug}/contents` (Auth)
- Tujuan: gabungan content unit (lesson/quiz/assignment) untuk user
- Tidak ada query filter/sort/include resmi
- Data sudah diproses lock/prerequisite per urutan content

Sumber:
- `UnitController::contents`
- `UnitService::getContents`

## 3.3 Lessons

### GET `/v1/courses/{course:slug}/units/{unit:slug}/lessons` (Auth)
- Query params:
  - `per_page` integer default `15`
  - `filter[content_type]` exact (`ContentType`)
  - `filter[status]` exact
  - `include`: `unit,blocks`
  - `sort`: `order,title,created_at`

Nilai cepat endpoint ini:
- `filter[content_type]`: `markdown|video|link`
- `filter[status]`: `draft|published|archived`
- `include`: `unit,blocks`
- `sort`: `order,title,created_at`

Sumber nilai:
- `Modules/Schemes/app/Enums/ContentType.php`
- `Modules/Schemes/app/Enums/CourseStatus.php`

Sumber:
- `LessonController::index`
- `LessonFinder::paginate`

### GET `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}` (Auth + `can:view,lesson`)
- Tidak ada include/sort/filter
- Student akan dicek akses prerequisite via `LessonService::getLessonForUser`

### GET `/v1/lessons` (Auth)
- Tujuan: global lesson list (sudah difilter role)
- Query params:
  - `per_page` integer default `15`
  - `search` (top-level) string
  - `filter[content_type]` exact (`ContentType`)
  - `filter[status]`
  - `filter[unit_slug]`
  - `filter[course_slug]`
  - `filter[include]`: `unit,unit.course,blocks`
  - `filter[sort]`: `order,title,created_at` (boleh prefiks `-`)

Nilai cepat endpoint ini:
- `filter[content_type]`: `markdown|video|link`
- `filter[status]`: `draft|published|archived`
- `filter[include]`: `unit,unit.course,blocks`
- `filter[sort]`: `order,title,created_at` (prefix `-` untuk desc)

Sumber nilai:
- `Modules/Schemes/app/Enums/ContentType.php`
- `Modules/Schemes/app/Enums/CourseStatus.php`

Sumber:
- `LessonController::indexAll`
- `LessonFinder::paginateAll`

### GET `/v1/lessons/{lesson:slug}` (Auth + `can:view,lesson`)
- Tidak ada include/sort/filter

## 3.4 Lesson Blocks

### GET `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/blocks` (Auth)
- Query params:
  - `filter[block_type]` exact (`text|video|image|file`)
  - `sort`: `order,created_at`
- Student akan ditahan jika lesson locked prerequisite

Nilai cepat endpoint ini:
- `filter[block_type]`: `text|video|image|file`
- `sort`: `order,created_at`

Sumber nilai:
- `Modules/Schemes/app/Http/Requests/LessonBlockRequest.php`

Sumber:
- `LessonBlockController::index`
- `LessonBlockService::list`

### GET `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/blocks/{block:slug}` (Auth)
- Tidak ada query filter/sort/include
- Student juga kena cek prerequisite

## 3.5 Progress and Completion

### GET `/v1/courses/{course:slug}/progress` (Auth)
- Query params:
  - `user_id` optional (hanya jika pemanggil berhak)
- Data progress dihitung dari progress model + unit/lesson published

Sumber:
- `ProgressController::show`
- `ProgressionService` + `ProgressionStateProcessor`

### POST `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/complete` (Auth)
- Body: none

### POST `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/uncomplete` (Auth)
- Body: none

### POST `/v1/lessons/{lesson:slug}/complete` (Auth)
- Body: none

### DELETE `/v1/lessons/{lesson:slug}/complete` (Auth)
- Body: none

## 3.6 Tags (controller Schemes, route Common)

### GET `/v1/tags` (Public)
- Query params:
  - `per_page` integer default `15`
  - `search` string (custom)
  - `name` partial filter
  - `slug` partial filter
  - `description` partial filter
  - `sort`: `name,slug,created_at,updated_at` (Spatie sort, boleh `-`)

Nilai cepat endpoint ini:
- `sort`: `name,slug,created_at,updated_at` (prefix `-` untuk desc)

Sumber:
- `Modules/Common/routes/api.php`
- `TagController::index`
- `TagService::buildQuery`

### GET `/v1/tags/{tag:slug}` (Public)
- Tidak ada query filter

## 4) Endpoint Manajemen (Instructor/Admin/Superadmin)

Semua endpoint Student yang butuh auth tetap berlaku untuk manajemen.
Tambahan endpoint write/manajemen:

## 4.1 Courses (Manajemen)

### POST `/v1/courses`
Body (raw JSON minimal valid):
```json
{
  "code": "CRS-001",
  "title": "Dasar Pemrograman",
  "short_desc": "Pengantar",
  "level_tag": "dasar",
  "type": "okupasi",
  "enrollment_type": "auto_accept",
  "category_id": 1
}
```

Alternatif body (raw JSON lengkap):
```json
{
  "code": "CRS-001",
  "slug": "dasar-pemrograman",
  "title": "Dasar Pemrograman",
  "short_desc": "Pengantar lengkap",
  "level_tag": "dasar",
  "type": "okupasi",
  "enrollment_type": "approval",
  "enrollment_key": null,
  "category_id": 1,
  "tags": ["php", "backend", "tag-baru-bebas"],
  "outcomes": ["Memahami variabel", "Memahami control flow"],
  "prereq": "Laptop",
  "status": "draft",
  "instructor_id": 10,
  "course_admins": [10, 11]
}
```

Alternatif body (raw JSON key-based enrollment):
```json
{
  "code": "CRS-002",
  "title": "Secure Coding",
  "short_desc": "Kelas kunci",
  "level_tag": "menengah",
  "type": "kluster",
  "enrollment_type": "key_based",
  "enrollment_key": "MY-SECRET-KEY",
  "category_id": 2
}
```

Alternatif body (multipart/form-data):
```text
code=CRS-003
title=UI Dasar
short_desc=Kelas desain
level_tag=dasar
type=okupasi
enrollment_type=auto_accept
category_id=3
tags=["design","figma","custom-tag-baru"]
outcomes=["Memahami layout"]
course_admins=[12,13]
thumbnail=<file jpg/png/webp max 4MB>
banner=<file jpg/png/webp max 6MB>
```

### PUT `/v1/courses/{course:slug}`
Body (raw JSON minimal valid):
```json
{
  "code": "CRS-001",
  "title": "Dasar Pemrograman",
  "short_desc": "Pengantar",
  "level_tag": "dasar",
  "type": "okupasi",
  "enrollment_type": "auto_accept",
  "category_id": 1
}
```

Alternatif body (raw JSON lengkap):
```json
{
  "code": "CRS-001",
  "slug": "dasar-pemrograman",
  "title": "Dasar Pemrograman",
  "short_desc": "Pengantar lengkap",
  "level_tag": "dasar",
  "type": "okupasi",
  "enrollment_type": "approval",
  "enrollment_key": null,
  "category_id": 1,
  "tags": ["php", "backend", "tag-baru-bebas"],
  "outcomes": ["Memahami variabel", "Memahami control flow"],
  "prereq": "Laptop",
  "status": "draft",
  "instructor_id": 10,
  "course_admins": [10, 11]
}
```

### DELETE `/v1/courses/{course:slug}`
Body: none

### PUT `/v1/courses/{course:slug}/publish`
Body: none

### PUT `/v1/courses/{course:slug}/unpublish`
Body: none

### POST `/v1/courses/{course:slug}/enrollment-key/generate`
Body: none

### PUT `/v1/courses/{course:slug}/enrollment-key`
Body (raw JSON):
```json
{
  "enrollment_type": "key_based",
  "enrollment_key": "NEW-KEY-123"
}
```

### DELETE `/v1/courses/{course:slug}/enrollment-key`
Body: none

## 4.2 Units (Manajemen)

### POST `/v1/courses/{course:slug}/units`
Body (raw JSON minimal valid):
```json
{
  "code": "UNIT-001",
  "title": "Unit 1",
  "description": "Pengenalan"
}
```

Alternatif body (raw JSON lengkap):
```json
{
  "code": "UNIT-001",
  "title": "Unit 1",
  "description": "Pengenalan lengkap",
  "order": 1,
  "status": "draft"
}
```

### PUT `/v1/courses/{course:slug}/units/{unit:slug}`
Body (raw JSON minimal valid):
```json
{
  "code": "UNIT-001",
  "title": "Unit 1",
  "description": "Pengenalan"
}
```

Alternatif body (raw JSON lengkap):
```json
{
  "code": "UNIT-001",
  "title": "Unit 1",
  "description": "Pengenalan lengkap",
  "order": 1,
  "status": "draft"
}
```

### DELETE `/v1/courses/{course:slug}/units/{unit:slug}`
Body: none

### PUT `/v1/courses/{course:slug}/units/{unit:slug}/publish`
Body: none

### PUT `/v1/courses/{course:slug}/units/{unit:slug}/unpublish`
Body: none

### PUT `/v1/courses/{course:slug}/units/reorder`
Body (raw JSON):
```json
{
  "units": [11, 15, 12, 18]
}
```

### GET `/v1/courses/{course:slug}/units/{unit:slug}/content-order`
Body: none

### PUT `/v1/courses/{course:slug}/units/{unit:slug}/content-order`
Body (raw JSON):
```json
{
  "content": [
    { "type": "lesson", "id": 101, "order": 1 },
    { "type": "assignment", "id": 201, "order": 2 },
    { "type": "quiz", "id": 301, "order": 3 }
  ]
}
```

### POST `/v1/courses/{course:slug}/units/{unit:slug}/contents` (create element minimal)
Body (raw JSON):
```json
{
  "type": "lesson",
  "title": "Intro Lesson"
}
```
Alternatif type:
```json
{ "type": "quiz", "title": "Quiz 1" }
```
```json
{ "type": "assignment", "title": "Tugas 1" }
```

## 4.3 Lessons (Manajemen)

### POST `/v1/courses/{course:slug}/units/{unit:slug}/lessons`
Body (raw JSON minimal valid):
```json
{
  "title": "Apa itu Variable"
}
```

Alternatif body (raw JSON lengkap):
```json
{
  "slug": "apa-itu-variable",
  "title": "Apa itu Variable",
  "description": "Deskripsi lesson",
  "markdown_content": "# Materi\nKonten markdown",
  "order": 1,
  "duration_minutes": 20,
  "status": "draft"
}
```

### PUT `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}`
Body (raw JSON minimal valid):
```json
{
  "title": "Apa itu Variable"
}
```

Alternatif body (raw JSON lengkap):
```json
{
  "slug": "apa-itu-variable",
  "title": "Apa itu Variable",
  "description": "Deskripsi lesson",
  "markdown_content": "# Materi\nKonten markdown",
  "order": 1,
  "duration_minutes": 20,
  "status": "draft"
}
```

### DELETE `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}`
Body: none

### PUT `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/publish`
Body: none

### PUT `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/unpublish`
Body: none

## 4.4 Lesson Blocks (Manajemen)

### POST `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/blocks`
Body (multipart/form-data text block):
```text
type=text
content=Konten teks panjang
order=1
```

Alternatif body (multipart/form-data image block):
```text
type=image
content=Caption gambar
order=2
media=<image/*>
```

Alternatif body (multipart/form-data video block):
```text
type=video
content=Deskripsi video
order=3
media=<video/*>
```

Alternatif body (multipart/form-data file block):
```text
type=file
content=Lampiran PDF
order=4
media=<any file>
```

### PUT `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/blocks/{block:slug}`
Body (multipart/form-data):
```text
type=text|video|image|file
content=<opsional>
order=<opsional, integer >=1>
media=<wajib untuk video|image|file>
```

### DELETE `/v1/courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/blocks/{block:slug}`
Body: none

## 4.5 Tags Write (effective: Superadmin only via policy)

### POST `/v1/tags`
Body create single (raw JSON):
```json
{
  "name": "php"
}
```

Alternatif body create bulk object (raw JSON):
```json
{
  "names": ["php", "laravel", "api"]
}
```

Alternatif body create bulk array (raw JSON):
```json
[
  { "name": "php" },
  { "name": "laravel" }
]
```

### PUT `/v1/tags/{tag:slug}`
Body (raw JSON):
```json
{
  "name": "laravel-advanced"
}
```

### DELETE `/v1/tags/{tag:slug}`
Body: none
## 5) Ringkasan Filter/Sort/Include per Endpoint

### 5.1 Student/Public Read Endpoints

1. `GET /v1/courses`
- Filter: `filter[status]`, `filter[level_tag]`, `filter[type]`, `filter[category_id]`, `search`, `tag`
- Sort: `id,code,title,created_at,updated_at,published_at`
- Include: `tags,category,instructor,units`

2. `GET /v1/courses/{slug}`
- Filter: none
- Sort: none
- Include:
  - Public: `tags,category,instructor,units`
  - Student enrolled aktif: Public + `lessons,quizzes,assignments,units.lessons`
  - Manajemen: Student + `enrollments,enrollments.user,admins,units.lessons.blocks`

3. `GET /v1/my-courses`
- Filter: `filter[status]` (active/completed), `filter[level_tag]`, `filter[type]`, `filter[category_id]`
- Sort: `title,created_at,updated_at`
- Include: public include only

4. `GET /v1/courses/{course}/units`
- Filter: `filter[status]`
- Sort: `order,title,created_at`
- Include: `course,lessons`

5. `GET /v1/units`
- Filter: `filter[status]`, `filter[course_slug]`, `search`, `filter[include]`
- Sort: `filter[sort]` with `filter[order]`
- Include: via `filter[include]` => `course,lessons`

6. `GET /v1/courses/{course}/units/{unit}` and `GET /v1/units/{unit}`
- Include: role-based unit include

7. `GET /v1/courses/{course}/units/{unit}/contents`
- Tidak ada filter/sort/include resmi

8. `GET /v1/courses/{course}/units/{unit}/lessons`
- Filter: `filter[content_type]`, `filter[status]`
- Sort: `order,title,created_at`
- Include: `unit,blocks`

9. `GET /v1/lessons`
- Filter: `filter[content_type]`, `filter[status]`, `filter[unit_slug]`, `filter[course_slug]`, `search`, `filter[include]`
- Sort: `filter[sort]` (`order,title,created_at`, support `-`)
- Include: `unit,unit.course,blocks`

10. `GET /v1/courses/{course}/units/{unit}/lessons/{lesson}` and `GET /v1/lessons/{lesson}`
- Filter/sort/include: none

11. `GET /v1/.../blocks` (index)
- Filter: `filter[block_type]`
- Sort: `order,created_at`
- Include: none

12. `GET /v1/tags`
- Filter: `name`, `slug`, `description`, `search`
- Sort: `name,slug,created_at,updated_at`
- Include: none

### 5.2 Manajemen Read/Write Endpoints

1. Semua endpoint di 5.1 yang butuh auth
2. Tambahan write:
- Courses: create/update/delete/publish/unpublish/enrollment-key*
- Units: create/update/delete/reorder/publish/unpublish/content-order/content-create
- Lessons: create/update/delete/publish/unpublish
- Lesson blocks: create/update/delete
- Tags: create/update/delete (efektif Superadmin)

## 6) Catatan Implementasi Penting

1. `Course update` dan `Unit update` memakai request yang juga mewajibkan field inti seperti create (bukan patch ringan).
2. Endpoint progress menggunakan `ProgressResource`, namun file resource tersebut tidak ada di module Schemes pada struktur saat ini; shape data progress dapat diturunkan dari `ProgressionStateProcessor::getCourseProgressData`.
3. Untuk `tags` write endpoint khusus tags, route role lebih longgar daripada policy; policy tetap jadi penentu akhir.
4. Tag custom tetap bisa dibuat oleh management melalui payload `tags` di endpoint course create/update (tanpa harus memanggil endpoint `POST /v1/tags`).

## 7) Checklist Kelengkapan (No Missing Items)

- Daftar endpoint Schemes routes: lengkap
- Endpoint tags dari controller Schemes di Common routes: tercakup
- Params path/query/body tiap endpoint: tercakup
- Allowed include/sort/filter: tercakup
- Value filter + sumber nilai: tercakup
- Skenario Add/Edit/Delete:
  - JSON minimal
  - JSON lengkap
  - multipart/form-data (untuk media)
  - skenario khusus (key-based enrollment, bulk tag, create element minimal)
