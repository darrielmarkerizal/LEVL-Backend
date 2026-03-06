# Schemes API Documentation for Postman

Dokumentasi ini mencakup seluruh API yang berada di module `Schemes` (`Modules/Schemes`), dibagi dalam dua kategori besar: **Student (Public/Enrolled)** dan **Manajemen (Admin/Instructor)**.

---

## 👨‍🎓 BAGIAN 1: STUDENT & PUBLIC APIS
Endpoint di bagian ini dapat diakses secara publik atau oleh user rolenya (disesuaikan dengan auth).

### 1.1 Course API
#### 1.1.1 Get List Courses (Public)
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses`
- **Desc:** Menampilkan list course public (yang berstatus published).
- **Params:**
  - `page`: (int) Halaman (misal: 1)
  - `per_page`: (int) Jumlah data per halaman (default: 15)
  - `search`: (string) Kata kunci pencarian global
  - `filter[status]`: (string) Filter status (contoh: `published`, `draft`)
  - `filter[level_tag]`: (string) Filter level (`beginner`, `intermediate`, `advanced`)
  - `filter[type]`: (string) Filter tipe course (`self_paced`, `instructor_led`, `hybrid`)
  - `filter[category_id]`: (int) Filter spesifik ke Master Category
  - `tag`: (string) Filter list of tags comma-separated (contoh: `php,laravel`)
  - `sort`: (string) Pengurutan (allowed: `id`, `code`, `title`, `created_at`, `updated_at`, `published_at`). Tambahkan `-` di awal untuk descending (contoh: `-created_at`).
  - `include`: (string) Relasi yang di load (allowed: `tags`, `category`, `instructor`, `units`).

#### 1.1.2 Get Detail Course
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug`
- **Desc:** Detail dari suatu course. Student harus bisa akses based on Policy. Untuk Student yang enrolled lebih banyak include yang diperbolehkan.
- **Path Variable:**
  - `course_slug`: slug dari course (contoh: `belajar-laravel-11`)
- **Params:**
  - `include`: (string) Relasi. 
    - *Public:* `tags`, `category`, `instructor`, `units`
    - *Student (Enrolled):* Tambahan bisa `lessons`, `quizzes`, `assignments`, `units.lessons`

#### 1.1.3 Get My Enrolled Courses
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/my-courses`
- **Auth:** Bearer Token (Student)
- **Desc:** Menampilkan list course di mana student tersebut terdaftar (enrolled).
- **Params:**
  - `page`: (int) default 1
  - `per_page`: (int) default 15
  - `filter[status]`: (string) Filter status enroll: `active`, `completed`
  - `filter[level_tag]`: (string) `beginner`, dsb.
  - `filter[type]`: (string) `self_paced`, dsb.
  - `filter[category_id]`: (int) ID Category
  - `sort`: (string) Allowed: `title`, `created_at`, `updated_at` (default `-updated_at`)
  - `include`: (string) Allowed: `tags`, `category`, `instructor`, `units`

#### 1.1.4 Get Course Progress
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/progress`
- **Auth:** Bearer Token
- **Desc:** Melihat data progress dari sebuah course (berapa persen selesai, lesson apa saja yang kelar).
- **Path Variable:** `course_slug`

### 1.2 Unit API
#### 1.2.1 Get All Units (Global)
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/units`
- **Auth:** Bearer Token
- **Params:**
  - `per_page`: (int) default 15
  - `search`: (string) Cari unit name/title
- **Desc:** List seluruh unit tanpa context spesifik course (auth-based view).

#### 1.2.2 Get Unit Detail (Global)
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/units/:unit_slug`
- **Auth:** Bearer Token
- **Desc:** Melihat detail unit berdasarkan slug.

#### 1.2.3 Get Units in a Course
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units`
- **Auth:** Bearer Token
- **Desc:** List unit yang spesifik berada dalam suatu course.
- **Params:** `per_page` (int)

#### 1.2.4 Get Unit Detail in a Course
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug`
- **Auth:** Bearer Token

#### 1.2.5 Get Unit Contents (Lessons, Assignment, Quizzes)
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/contents`
- **Auth:** Bearer Token
- **Desc:** Mengambil isi struktur dari Unit (menggabungkan Lesson, Assignment, dsb yang ada di dalamnya).

### 1.3 Lesson API
#### 1.3.1 Get All Lessons (Global)
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/lessons`
- **Auth:** Bearer Token
- **Params:** `per_page`, `search`

#### 1.3.2 Get Lesson Detail (Global)
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/lessons/:lesson_slug`
- **Auth:** Bearer Token

#### 1.3.3 Get Lessons in a Unit
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons`
- **Auth:** Bearer Token
- **Params:** `per_page`

#### 1.3.4 Get Lesson Detail in a Unit
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug`
- **Auth:** Bearer Token

### 1.4 Lesson Completion & Progression
#### 1.4.1 Complete Lesson (In Course Context)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/complete`
- **Auth:** Bearer Token
- **Desc:** Menandai lesson selesai dan memperbarui progress course.

#### 1.4.2 Uncomplete Lesson (In Course Context)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/uncomplete`
- **Auth:** Bearer Token
- **Desc:** Membatalkan status selesai dari lesson dan merevert progress.

#### 1.4.3 Mark Lesson Complete (Global without Course Context)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/lessons/:lesson_slug/complete`
- **Auth:** Bearer Token

#### 1.4.4 Delete Mark Lesson Complete (Global without Course Context)
- **Method:** `DELETE`
- **URL:** `{{base_url}}/api/v1/lessons/:lesson_slug/complete`
- **Auth:** Bearer Token

### 1.5 Lesson Block (Content Parts)
#### 1.5.1 Get Blocks of a Lesson
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/blocks`
- **Auth:** Bearer Token
- **Desc:** Menampilkan isi blok per lesson (misal: blok text, blok video). 
- **Catatan:** Prerequisite akan dicek di endpoint ini. Jika user belum bisa akses lesson, endpoint me-return 403.

#### 1.5.2 Get Block Detail
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/blocks/:block_id`
- **Auth:** Bearer Token

---
## 👨‍💼 BAGIAN 2: MANAJEMEN APIS (Superadmin, Admin, Instructor)
Semua endpoint di kategori ini umumnya membutuhkan Form Data jika mengandung gambar, atau Raw JSON untuk field biasa. Perhatikan body payloads untuk Create, Update, dan Delete. Role yang dibutuhkan: `Superadmin`, `Admin`, atau `Instructor`.

### 2.1 Course Management
#### 2.1.1 Create Course
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses`
- **Content-Type:** `multipart/form-data` (disarankan karena ada image banner/thumbnail)
- **Body Form-Data:**
  - `code` (string, required): Kode unik (max 50)
  - `slug` (string, optional): Slug unik (akan di generate jika tidak ada)
  - `title` (string, required): Judul course
  - `short_desc` (string, optional): Deskripsi singkat
  - `level_tag` (string, required): `beginner`, `intermediate`, `advanced`
  - `type` (string, required): `self_paced`, `instructor_led`, `hybrid`
  - `enrollment_type` (string, required): `auto_accept`, `key_based`, `approval`
  - `enrollment_key` (string, required jika enrollment_type="key_based"): Max 100 character.
  - `category_id` (int, required): ID Master Category
  - `tags[]` (string, optional): Array untuk tags, cth: `tags[0]=php`, `tags[1]=laravel` (Jika raw json bisa kirim dalam format JSON Stringified)
  - `outcomes[]` (string, optional): Array of outcomes (cth: `outcomes[0]=Bisa membuat API`)
  - `prereq` (string, optional): Text prasyarat.
  - `thumbnail` (file, optional): Mimes (jpg, jpeg, png, webp), Maksimal 4MB.
  - `banner` (file, optional): Mimes (jpg, jpeg, png, webp), Maksimal 6MB.
  - `status` (string, optional): Secara enum `draft` atau `published`.
  - `instructor_id` (int, optional): ID user dari Instructor (harus exist).
  - `course_admins[]` (int, optional): Array User IDs tambahan admin (cth: `course_admins[0]=2`).

- **Contoh RAW JSON Payload (Jika tidak kirim file image):**
```json
{
    "code": "CRS-001",
    "title": "Belajar Laravel 11",
    "level_tag": "beginner",
    "type": "self_paced",
    "enrollment_type": "auto_accept",
    "category_id": 1,
    "tags": ["PHP", "Backend"],
    "outcomes": ["Menguasai routing", "Query Builder"],
    "status": "draft"
}
```

#### 2.1.2 Update Course
- **Method:** `PUT` (Jika via Postman dengan File, Laravel sering reject. **Gunakan metode POST dengan penambahan key `_method`="PUT" di Form-Data**)
- **URL:** `{{base_url}}/api/v1/courses/:course_slug`
- **Content-Type:** `multipart/form-data`
- **Body Form-Data:** Sama seperti Create Course, tapi `category_id` tidak dapat diganti jika tidak mengirim form dari awal/bisa opsional jika rules mengizinkan. Kirim value override. 
- Contoh Body Form-Data sama, hanya tinggal masukan parameter yang mau diedit.

#### 2.1.3 Delete Course
- **Method:** `DELETE`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug`

#### 2.1.4 Publish Course
- **Method:** `PUT`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/publish`
- **Desc:** Mengubah status dari draft menjadi published.
- **Body:** Kosong

#### 2.1.5 Unpublish Course
- **Method:** `PUT`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/unpublish`
- **Desc:** Mengubah status dari published jadi draft.
- **Body:** Kosong

#### 2.1.6 Generate Random Enrollment Key
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/enrollment-key/generate`
- **Desc:** Men-generate kode acak baru yang di hash dan kembalikan "plain key". Akan merubah `enrollment_type` ke `key_based`.

#### 2.1.7 Update Enrollment Key & Type
- **Method:** `PUT`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/enrollment-key`
- **Content-Type:** `application/json`
- **Body JSON:**
```json
{
    "enrollment_type": "key_based", 
    "enrollment_key": "KODERAHASIA2026"
}
```
*(type allowed: `auto_accept`, `key_based`, `approval`)*

#### 2.1.8 Remove Enrollment Key
- **Method:** `DELETE`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/enrollment-key`
- **Desc:** Menghapus key/hash dan menjadikan type `auto_accept`.

### 2.2 Unit Management
#### 2.2.1 Create Unit in a Course
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units`
- **Content-Type:** `application/json`
- **Body JSON:**
```json
{
    "code": "UNT-01",
    "title": "Pengenalan Ekosistem",
    "description": "Deskripsi tentang unit",
    "order": 1,
    "status": "draft"
}
```
*(Code max 50 char, order min:1, status allowed: `draft`, `published`)*

#### 2.2.2 Update Unit
- **Method:** `PUT`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug`
- **Content-Type:** `application/json`
- Parameter body sama seperti di atas (Create). Bisa digunakan hanya properti yang ingin diganti (opsional).

#### 2.2.3 Delete Unit
- **Method:** `DELETE`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug`

#### 2.2.4 Publish / Unpublish Unit
- **Method:** `PUT`
- **URL:** 
  - Publish: `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/publish`
  - Unpublish: `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/unpublish`

#### 2.2.5 Reorder Units in a Course
- **Method:** `PUT`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/reorder`
- **Content-Type:** `application/json`
- **Body JSON:**
```json
{
    "units": [
        2,
        3,
        1
    ]
}
```
*Array `units` berisi integer/ID dari unit secara urut.*

#### 2.2.6 Get Unit Content Order
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/content-order`
- **Desc:** Melihat struktur urutan (Lesson, Quiz, dll) yang terdapat dalam unit.

#### 2.2.7 Reorder Content Inside Unit
- **Method:** `PUT`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/content-order`
- **Content-Type:** `application/json`
- **Body JSON:**
```json
{
    "content": [
        {
            "type": "lesson",
            "id": 14,
            "order": 1
        },
        {
            "type": "quiz",
            "id": 5,
            "order": 2
        }
    ]
}
```
*Tipe yang diperbolehkan di content.*type: `lesson`, `assignment`, `quiz`*

#### 2.2.8 Create Content Element Placeholder (Shortcut)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/contents`
- **Desc:** Membuat content gundul langsung tanpa mengisi deskripsi lengkap dll (misal membuat lesson/quiz cepat).
- **Body JSON:**
```json
{
    "type": "lesson",
    "title": "Belajar Eloquent"
}
```

### 2.3 Lesson Management
#### 2.3.1 Create Lesson
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons`
- **Content-Type:** `application/json`
- **Body JSON:**
```json
{
    "title": "Syntax Dasar PHP",
    "description": "Pengenalan awalan PHP 8",
    "markdown_content": "# Intro \n\n PHP 8 brings...",
    "order": 1,
    "duration_minutes": 15,
    "status": "draft"
}
```

#### 2.3.2 Update Lesson
- **Method:** `PUT`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug`
- **Content-Type:** `application/json`
- *Parameter Body sama dengan Create di atas.*

#### 2.3.3 Delete, Publish, Unpublish Lesson
- **Methods:** 
  - Delete: `DELETE`
  - Publish: `PUT`
  - Unpublish: `PUT`
- **URLs:** 
  - Delete: `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug`
  - Publish: `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/publish`
  - Unpublish: `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/unpublish`

### 2.4 Lesson Block Management
Blok digunakan mendefinisikan part-part khusus di dalam satu lesson (video diselip gambar, dll). Karena mungkin mengandung file media (video/image/document), wajib perhatikan Form-Data vs RAW JSON.

#### 2.4.1 Create Lesson Block (Tipe: TEXT)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/blocks`
- **Content-Type:** `application/json`
- **Body JSON:**
```json
{
    "type": "text",
    "content": "Baca panduan berikut ini sebelum memulai modding.",
    "order": 1
}
```

#### 2.4.2 Create Lesson Block (Tipe: VIDEO / IMAGE / FILE)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/blocks`
- **Content-Type:** `multipart/form-data`
- **Body Form-Data:**
  - `type` (string, required): `video`, `image`, atau `file`
  - `content` (string, optional): caption/teks opsional.
  - `order` (int, optional): urutan blok
  - `media` (file, required): FIle media aslinya.
    - Image limit: 50MB (by default env), mimes image/*
    - Video limit: 50MB (by default env), mimes video/*
    - File limit: 50MB (by default env)

#### 2.4.3 Update Lesson Block (ALL TYPE)
- **Method:** `POST` (Sematkan parameter `_method="PUT"` untuk Laravel form-data update, atau pure `PUT` jika raw JSON/Text type).
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/blocks/:block_id_atauslug`
- **Content-Type:** Tergantung type.
- Jika tipe `image`/`video`/`file` dan ingin mengganti file, pastikan set `media` pada `multipart/form-data` dan tambahkan `_method=PUT` di field.

#### 2.4.4 Delete Lesson Block
- **Method:** `DELETE`
- **URL:** `{{base_url}}/api/v1/courses/:course_slug/units/:unit_slug/lessons/:lesson_slug/blocks/:block_id_atauslug`

---

### Tips Postman:
1. **Sanctum/JWT Auth:** Sebagian besar endpoint mewajibkan token login di dalam header `Authorization: Bearer <token_kamu>`.
2. **Accept Header:** Selalu gunakan `Accept: application/json` pada Request Headers.
3. **Paginasi & Pencarian:** Berlaku param `?page=...&per_page=...&search=...` secara native berkat query builder Spatie.
4. **Validasi Error Code:** Akan di handle standar dengan kode Status `422 Unprocessable Entity` yang menampilkan dictionary validation error spesifik.
5. **Exception:** Data duplikat di Database (DuplicateResourceException) dihandle khusus untuk Slug dan Code sehingga muncul pada array validation error json `["code": ["sudah terpakai"]]` dan bukan server crash 500.

---
**End of File**
