# Panduan Lengkap Form Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap untuk semua form pembuatan konten pembelajaran dari sisi Management (Superadmin, Admin, Instructor).

---

## Daftar Isi

1. [Course (Kursus)](#1-course-kursus)
2. [Unit](#2-unit)
3. [Lesson (Pelajaran)](#3-lesson-pelajaran)
4. [Lesson Block (Konten/Element)](#4-lesson-block-kontenelement)
5. [Assignment (Tugas)](#5-assignment-tugas)
6. [Quiz (Kuis)](#6-quiz-kuis)
7. [Quiz Question (Pertanyaan Kuis)](#7-quiz-question-pertanyaan-kuis)

---

## 1. COURSE (Kursus)

### Endpoint
```
POST /api/v1/courses
PUT /api/v1/courses/{slug}
```

### Content-Type
`multipart/form-data` (jika ada upload file) atau `application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `code` | string | ✅ Ya | max:50, unique | - | Kode unik kursus (contoh: "IT-001") |
| `slug` | string | ❌ Tidak | max:100, unique | Auto-generate dari title | URL-friendly identifier |
| `title` | string | ✅ Ya | max:255 | - | Judul kursus |
| `short_desc` | text | ❌ Tidak | - | null | Deskripsi singkat kursus |
| `level_tag` | enum | ✅ Ya | dasar, menengah, mahir | - | Tingkat kesulitan |
| `type` | enum | ✅ Ya | okupasi, kluster | - | Jenis kursus |
| `enrollment_type` | enum | ✅ Ya | auto_accept, key_based, approval | - | Cara pendaftaran |
| `enrollment_key` | string | Conditional | max:100 | null | **Required jika** `enrollment_type` = `key_based` |
| `category_id` | integer | ✅ Ya (create) | exists:categories,id | - | ID kategori kursus |
| `tags` | array | ❌ Tidak | array of strings | [] | Tag kursus (contoh: ["programming", "web"]) |
| `outcomes` | array | ❌ Tidak | array of strings | [] | Learning outcomes (contoh: ["Mampu membuat website"]) |
| `prereq` | text | ❌ Tidak | - | null | Prasyarat kursus |
| `thumbnail` | file | ❌ Tidak | image (jpg,jpeg,png,webp), max:4MB | null | Gambar thumbnail |
| `banner` | file | ❌ Tidak | image (jpg,jpeg,png,webp), max:6MB | null | Gambar banner |
| `status` | enum | ❌ Tidak | draft, published, archived | draft | Status publikasi |
| `instructor_id` | integer | ❌ Tidak | exists:users,id | null | ID instructor utama |
| `course_admins` | array | ❌ Tidak | array of integers, exists:users,id | [] | Array ID admin kursus |

### Nilai Enum

#### level_tag
- `dasar` - Tingkat Dasar
- `menengah` - Tingkat Menengah
- `mahir` - Tingkat Mahir

#### type
- `okupasi` - Kursus Okupasi
- `kluster` - Kursus Kluster

#### enrollment_type
- `auto_accept` - Otomatis diterima
- `key_based` - Butuh kunci pendaftaran
- `approval` - Butuh persetujuan admin

#### status
- `draft` - Draft (tidak terlihat student)
- `published` - Dipublikasikan (terlihat student)
- `archived` - Diarsipkan

### Contoh Request (JSON)

```json
{
  "code": "IT-WEB-001",
  "title": "Pemrograman Web Dasar",
  "short_desc": "Belajar membuat website dari nol",
  "level_tag": "dasar",
  "type": "okupasi",
  "enrollment_type": "auto_accept",
  "category_id": 1,
  "tags": ["programming", "web", "html", "css"],
  "outcomes": [
    "Mampu membuat struktur HTML",
    "Mampu styling dengan CSS",
    "Memahami responsive design"
  ],
  "prereq": "Tidak ada prasyarat khusus",
  "status": "draft",
  "instructor_id": 5
}
```

### Contoh Request (Form Data dengan File)

```
code: IT-WEB-001
title: Pemrograman Web Dasar
short_desc: Belajar membuat website dari nol
level_tag: dasar
type: okupasi
enrollment_type: auto_accept
category_id: 1
tags: ["programming", "web", "html", "css"]
outcomes: ["Mampu membuat struktur HTML", "Mampu styling dengan CSS"]
prereq: Tidak ada prasyarat khusus
status: draft
instructor_id: 5
thumbnail: [FILE]
banner: [FILE]
```

### Catatan Penting
- Field `tags`, `outcomes`, dan `course_admins` bisa dikirim sebagai JSON string atau array
- `enrollment_key` hanya required jika `enrollment_type` = `key_based`
- Saat update, `enrollment_key` tidak required jika sudah ada sebelumnya
- `category_id` hanya required saat create, tidak required saat update

---

## 2. UNIT

### Endpoint
```
POST /api/v1/courses/{course_slug}/units
PUT /api/v1/courses/{course_slug}/units/{unit_slug}
```

### Content-Type
`application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `code` | string | ✅ Ya | max:50, unique | - | Kode unik unit |
| `slug` | string | ❌ Tidak | max:100, unique per course | Auto-generate | URL-friendly identifier |
| `title` | string | ✅ Ya | max:255 | - | Judul unit |
| `description` | text | ❌ Tidak | - | null | Deskripsi unit |
| `order` | integer | ❌ Tidak | min:1, unique per course | Auto-generate | Urutan tampilan |
| `status` | enum | ❌ Tidak | draft, published | draft | Status publikasi |

### Nilai Enum

#### status
- `draft` - Draft (tidak terlihat student)
- `published` - Dipublikasikan (terlihat student)

### Contoh Request

```json
{
  "code": "UNIT-01",
  "title": "Pengenalan HTML",
  "description": "Unit ini membahas dasar-dasar HTML",
  "order": 1,
  "status": "draft"
}
```

### Catatan Penting
- `code` harus unique di seluruh sistem
- `slug` harus unique per course
- `order` harus unique per course
- Jika `order` tidak diisi, akan auto-generate ke urutan terakhir

---

## 3. LESSON (Pelajaran)

### Endpoint
```
POST /api/v1/units/{unit_slug}/lessons
PUT /api/v1/units/{unit_slug}/lessons/{lesson_slug}
```

### Content-Type
`application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `slug` | string | ❌ Tidak | max:100, unique per unit | Auto-generate | URL-friendly identifier |
| `title` | string | ✅ Ya | max:255 | - | Judul pelajaran |
| `description` | text | ❌ Tidak | - | null | Deskripsi pelajaran |
| `markdown_content` | text | ❌ Tidak | - | null | Konten dalam format Markdown |
| `order` | integer | ❌ Tidak | min:1 | Auto-generate | Urutan tampilan |
| `duration_minutes` | integer | ❌ Tidak | min:0 | 0 | Estimasi durasi (menit) |
| `status` | enum | ❌ Tidak | draft, published | draft | Status publikasi |

### Nilai Enum

#### status
- `draft` - Draft (tidak terlihat student)
- `published` - Dipublikasikan (terlihat student)

### Contoh Request

```json
{
  "title": "Struktur Dasar HTML",
  "description": "Mempelajari struktur dasar dokumen HTML",
  "markdown_content": "# Struktur HTML\n\nHTML memiliki struktur dasar:\n\n```html\n<!DOCTYPE html>\n<html>\n<head>\n  <title>Judul</title>\n</head>\n<body>\n  <h1>Hello World</h1>\n</body>\n</html>\n```",
  "order": 1,
  "duration_minutes": 30,
  "status": "draft"
}
```

### Catatan Penting
- `markdown_content` mendukung full Markdown syntax termasuk code blocks
- Konten Markdown TIDAK di-sanitize saat input, sanitasi dilakukan saat render
- `slug` harus unique per unit
- Jika `order` tidak diisi, akan auto-generate ke urutan terakhir

---

## 4. LESSON BLOCK (Konten/Element)

### Endpoint
```
POST /api/v1/lessons/{lesson_slug}/blocks
PUT /api/v1/lessons/{lesson_slug}/blocks/{block_id}
```

### Content-Type
`multipart/form-data` (karena bisa upload file)

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `type` | enum | ✅ Ya | text, video, image, file | - | Jenis konten block |
| `content` | text | Conditional | - | null | **Required untuk type=text** |
| `order` | integer | ❌ Tidak | min:1 | Auto-generate | Urutan tampilan |
| `media` | file | Conditional | max:50MB | null | **Required untuk type=video/image/file** |

### Nilai Enum

#### type
- `text` - Konten teks/HTML
- `video` - Video file
- `image` - Gambar
- `file` - File dokumen

### Validasi Khusus

#### Untuk type = "text"
- `content` **REQUIRED**
- `media` tidak boleh ada

#### Untuk type = "video"
- `media` **REQUIRED**
- `media` harus file video (mime type: video/*)
- Max size: 50MB (configurable)

#### Untuk type = "image"
- `media` **REQUIRED**
- `media` harus file image (mime type: image/*)
- Max size: 50MB (configurable)

#### Untuk type = "file"
- `media` **REQUIRED**
- `media` bisa file apapun
- Max size: 50MB (configurable)

### Contoh Request (Text Block)

```
type: text
content: <p>Ini adalah paragraf penjelasan tentang HTML</p>
order: 1
```

### Contoh Request (Video Block)

```
type: video
order: 2
media: [VIDEO_FILE]
```

### Contoh Request (Image Block)

```
type: image
order: 3
media: [IMAGE_FILE]
```

### Contoh Request (File Block)

```
type: file
content: Silakan download materi PDF berikut
order: 4
media: [PDF_FILE]
```

### Catatan Penting
- Max upload size default: 50MB (bisa dikonfigurasi di `config/app.lesson_block_max_upload_mb`)
- Untuk type video/image/file, mime type akan divalidasi sesuai type
- `content` bisa diisi untuk semua type sebagai caption/deskripsi
- Jika `order` tidak diisi, akan auto-generate ke urutan terakhir

---

## 5. ASSIGNMENT (Tugas)

### Endpoint
```
POST /api/v1/assignments
PUT /api/v1/assignments/{assignment_id}
```

### Content-Type
`multipart/form-data` (jika ada attachment) atau `application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `title` | string | ✅ Ya | max:255 | - | Judul assignment |
| `description` | text | ❌ Tidak | - | null | Instruksi assignment |
| `unit_slug` | string | ✅ Ya | exists:units,slug | - | Slug unit |
| `order` | integer | ❌ Tidak | min:1 | Auto-generate | Urutan tampilan |
| `submission_type` | enum | ✅ Ya | file, mixed | - | Tipe submission |
| `max_score` | integer | ❌ Tidak | min:1, max:1000 | 100 | Nilai maksimal |
| `passing_grade` | decimal | ❌ Tidak | min:0, max:100 | 60 | Nilai minimum lulus (%) |
| `status` | enum | ❌ Tidak | draft, published, archived | draft | Status publikasi |
| `time_limit_minutes` | integer | ❌ Tidak | min:1 | null | Batas waktu pengerjaan (menit) |
| `attachments` | array | ❌ Tidak | max:5 files, each max:10MB | [] | File lampiran untuk student |

### Nilai Enum

#### submission_type
- `file` - Upload file saja
- `mixed` - Kombinasi file + text + link

#### status
- `draft` - Draft (tidak terlihat student)
- `published` - Dipublikasikan (terlihat student)
- `archived` - Diarsipkan

### Format File Attachment
- Allowed: pdf, doc, docx, xls, xlsx, ppt, pptx, zip, jpg, jpeg, png, webp
- Max per file: 10MB
- Max total files: 5

### Contoh Request (JSON)

```json
{
  "title": "Tugas Membuat Website Portfolio",
  "description": "Buatlah website portfolio pribadi menggunakan HTML dan CSS. Upload file ZIP yang berisi semua file HTML, CSS, dan asset.",
  "unit_slug": "pengenalan-html",
  "submission_type": "file",
  "max_score": 100,
  "passing_grade": 70,
  "status": "draft",
  "time_limit_minutes": 120,
  "order": 1
}
```

### Contoh Request (Form Data dengan Attachment)

```
title: Tugas Membuat Website Portfolio
description: Buatlah website portfolio pribadi...
unit_slug: pengenalan-html
submission_type: file
max_score: 100
passing_grade: 70
status: draft
time_limit_minutes: 120
order: 1
attachments[]: [FILE_1.pdf]
attachments[]: [FILE_2.docx]
```

### Update Assignment - Hapus Attachment

Untuk update, ada field tambahan:

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `delete_attachments` | array | ❌ Tidak | array of integers, exists:media,id | Array ID media yang akan dihapus |

```json
{
  "title": "Tugas Membuat Website Portfolio (Updated)",
  "delete_attachments": [123, 456]
}
```

### Catatan Penting
- Assignment adalah tugas berbasis file upload dengan grading manual
- Field `submission_type` hanya boleh `"file"` atau `"mixed"`
- Attachments adalah file yang diberikan instructor ke student sebagai referensi
- Student akan upload file submission mereka di endpoint terpisah
- Grading dilakukan manual oleh instructor

---

## 6. QUIZ (Kuis)

### Endpoint
```
POST /api/v1/quizzes
PUT /api/v1/quizzes/{quiz_id}
```

### Content-Type
`multipart/form-data` (jika ada attachment) atau `application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `unit_slug` | string | ✅ Ya | exists:units,slug | - | Slug unit |
| `order` | integer | ❌ Tidak | min:1 | Auto-generate | Urutan tampilan |
| `title` | string | ✅ Ya | max:255 | - | Judul quiz |
| `description` | text | ❌ Tidak | - | null | Instruksi quiz |
| `passing_grade` | decimal | ❌ Tidak | min:0, max:100 | 60 | Nilai minimum lulus (%) |
| `auto_grading` | boolean | ❌ Tidak | true/false | true | Grading otomatis |
| `max_score` | decimal | ❌ Tidak | min:1 | null | Nilai maksimal (auto-calculate dari questions) |
| `time_limit_minutes` | integer | ❌ Tidak | min:1 | null | Batas waktu pengerjaan (menit) |
| `randomization_type` | enum | ❌ Tidak | static, random_order, bank | static | Tipe pengacakan soal |
| `question_bank_count` | integer | ❌ Tidak | min:1 | null | Jumlah soal yang ditampilkan (untuk type=bank) |
| `review_mode` | enum | ❌ Tidak | immediate, after_deadline, never | immediate | Kapan jawaban ditampilkan |
| `attachments` | array | ❌ Tidak | array of files | [] | File lampiran untuk student |

### Nilai Enum

#### randomization_type
- `static` - Soal selalu urutan sama
- `random_order` - Soal diacak urutannya
- `bank` - Tampilkan subset random dari bank soal

#### review_mode
- `immediate` - Tampilkan jawaban benar setelah submit
- `after_deadline` - Tampilkan setelah deadline
- `never` - Tidak pernah tampilkan jawaban benar

### Contoh Request

```json
{
  "unit_slug": "pengenalan-html",
  "title": "Quiz HTML Dasar",
  "description": "Kuis untuk menguji pemahaman HTML dasar",
  "passing_grade": 80,
  "auto_grading": true,
  "time_limit_minutes": 30,
  "randomization_type": "random_order",
  "review_mode": "immediate",
  "order": 2
}
```

### Catatan Penting
- `max_score` akan auto-calculate dari total score semua questions
- `auto_grading` = true jika semua soal objektif (multiple choice, checkbox, true/false)
- `auto_grading` = false jika ada soal essay
- `question_bank_count` hanya digunakan jika `randomization_type` = `bank`
- Setelah membuat quiz, tambahkan questions menggunakan endpoint terpisah

---

## 7. QUIZ QUESTION (Pertanyaan Kuis)

### Endpoint
```
POST /api/v1/quizzes/{quiz_id}/questions
PUT /api/v1/quizzes/{quiz_id}/questions/{question_id}
```

### Content-Type
`multipart/form-data` (jika ada option image) atau `application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `type` | enum | ✅ Ya | multiple_choice, checkbox, true_false, essay | - | Jenis pertanyaan |
| `content` | text | ✅ Ya | - | - | Teks pertanyaan |
| `options` | array | Conditional | - | null | **Required untuk multiple_choice, checkbox, true_false** |
| `options.*.text` | string | Conditional | - | - | Teks pilihan jawaban |
| `options.*.image` | file | ❌ Tidak | image | null | Gambar pilihan (optional) |
| `answer_key` | array | Conditional | - | null | **Required untuk soal objektif** |
| `weight` | decimal | ❌ Tidak | min:0.01 | 1.0 | Bobot soal untuk scoring |
| `order` | integer | ❌ Tidak | min:0 | Auto-generate | Urutan soal |
| `max_score` | decimal | ❌ Tidak | min:0 | null | Nilai maksimal soal ini |

### Nilai Enum

#### type
- `multiple_choice` - Pilihan ganda (1 jawaban benar)
- `checkbox` - Pilihan ganda (bisa >1 jawaban benar)
- `true_false` - Benar/Salah
- `essay` - Essay (jawaban panjang)

### Struktur Data per Type

#### 1. Multiple Choice (Pilihan Ganda)

```json
{
  "type": "multiple_choice",
  "content": "Apa kepanjangan dari HTML?",
  "weight": 1.0,
  "max_score": 10,
  "order": 1,
  "options": [
    {"text": "Hyper Text Markup Language"},
    {"text": "High Tech Modern Language"},
    {"text": "Home Tool Markup Language"}
  ],
  "answer_key": [0]
}
```

**Penjelasan**:
- `options`: Array pilihan jawaban (minimal 2)
- `answer_key`: Array berisi index jawaban benar (dimulai dari 0)
- Untuk multiple choice, `answer_key` berisi 1 index saja

#### 2. Checkbox (Pilihan Ganda Multiple)

```json
{
  "type": "checkbox",
  "content": "Pilih tag HTML yang valid (bisa lebih dari 1):",
  "weight": 1.5,
  "max_score": 15,
  "order": 2,
  "options": [
    {"text": "<div>"},
    {"text": "<span>"},
    {"text": "<section>"},
    {"text": "<paragraph>"}
  ],
  "answer_key": [0, 1, 2]
}
```

**Penjelasan**:
- `answer_key`: Array berisi multiple index jawaban benar
- Student bisa pilih lebih dari 1 jawaban

#### 3. True/False (Benar/Salah)

```json
{
  "type": "true_false",
  "content": "HTML adalah bahasa pemrograman",
  "weight": 0.5,
  "max_score": 5,
  "order": 3,
  "options": [
    {"text": "Benar"},
    {"text": "Salah"}
  ],
  "answer_key": [1]
}
```

**Penjelasan**:
- `options`: Selalu 2 pilihan (Benar/Salah atau True/False)
- `answer_key`: [0] untuk Benar, [1] untuk Salah

#### 4. Essay (Jawaban Panjang)

```json
{
  "type": "essay",
  "content": "Jelaskan perbedaan antara tag <div> dan <span> dalam HTML!",
  "weight": 2.0,
  "max_score": 20,
  "order": 4
}
```

**Penjelasan**:
- `options` TIDAK diperlukan
- `answer_key` TIDAK diperlukan
- Grading manual oleh instructor

### Contoh Request dengan Option Image (Form Data)

```
type: multiple_choice
content: Pilih logo HTML yang benar:
weight: 1.0
max_score: 10
order: 1
options[0][text]: Logo A
options[0][image]: [IMAGE_FILE_1]
options[1][text]: Logo B
options[1][image]: [IMAGE_FILE_2]
options[2][text]: Logo C
options[2][image]: [IMAGE_FILE_3]
answer_key: [0]
```

### Validasi Khusus

#### Untuk type = "multiple_choice" atau "checkbox"
- `options` **REQUIRED** (minimal 2 pilihan)
- `answer_key` **REQUIRED** (minimal 1 index)
- `options.*.text` atau `options.*.image` harus ada salah satu

#### Untuk type = "true_false"
- `options` **REQUIRED** (harus 2 pilihan)
- `answer_key` **REQUIRED** (1 index: 0 atau 1)

#### Untuk type = "essay"
- `options` TIDAK boleh ada
- `answer_key` TIDAK boleh ada

### Reorder Questions

Untuk mengubah urutan soal:

```
POST /api/v1/quizzes/{quiz_id}/questions/reorder
```

```json
{
  "ids": [3, 1, 2, 5, 4]
}
```

**Penjelasan**: Array berisi ID questions dalam urutan yang diinginkan

### Catatan Penting
- `weight` digunakan untuk menghitung proporsi nilai soal
- `max_score` akan override perhitungan otomatis jika diisi
- Soal objektif (multiple_choice, checkbox, true_false) akan auto-graded
- Soal essay memerlukan grading manual oleh instructor
- `answer_key` berisi array of index (dimulai dari 0)
- Untuk true_false, gunakan index 0 untuk True/Benar, index 1 untuk False/Salah

---

## Catatan Umum

### Authorization
Semua endpoint di atas memerlukan:
- Authentication: `Bearer {token}` di header
- Role: Superadmin, Admin, atau Instructor
- Permission: Sesuai dengan resource yang diakses

### Response Format
Semua endpoint menggunakan format response standar:

```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": { ... },
  "meta": { ... },
  "errors": null
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation error",
  "data": null,
  "meta": null,
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Status Codes
- `200` - Success (GET, PUT)
- `201` - Created (POST)
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

### Tips untuk UI/UX

1. **Dropdown Fields**: Gunakan nilai enum yang sudah ditentukan
2. **File Upload**: Tampilkan progress bar dan validasi size/type di frontend
3. **Required Fields**: Tandai dengan asterisk (*) merah
4. **Conditional Fields**: 
   - `enrollment_key` muncul jika `enrollment_type` = `key_based`
   - `question_bank_count` muncul jika `randomization_type` = `bank`
   - `options` muncul untuk soal objektif, hidden untuk essay
5. **Auto-generate Fields**: Bisa dikosongkan, sistem akan generate otomatis
6. **Array Fields**: Gunakan dynamic form (add/remove items)
7. **Rich Text Editor**: Untuk field `description`, `content`, `markdown_content`
8. **Order Field**: Bisa gunakan drag-and-drop untuk reorder

### Workflow Umum

1. **Buat Course** → Set status `draft`
2. **Tambah Units** ke Course → Set status `draft`
3. **Tambah Lessons** ke Unit → Set status `draft`
4. **Tambah Lesson Blocks** ke Lesson (opsional)
5. **Tambah Assignments/Quizzes** ke Unit → Set status `draft`
6. **Untuk Quiz**: Tambah Questions
7. **Review semua konten**
8. **Publish**: Ubah status menjadi `published` (dari bawah ke atas: Lesson → Unit → Course)

---

**Versi**: 1.0  
**Terakhir Update**: 6 Maret 2026  
**Kontak**: Backend Team
