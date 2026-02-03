# Dokumentasi API Forum

Dokumentasi ini menjelaskan endpoint API untuk modul Forum.

## Base URLs
- **Course Forum:** `/api/v1/courses/{course}/forum`
- **Forum Dashboard:** `/api/v1/forums`

## Daftar Isi
- [Overview](#overview)
- [Forum Dashboard (Admin/Instructor)](#forum-dashboard-admininstructor)
- [Thread (Diskusi)](#thread-diskusi)
- [Reply (Balasan)](#reply-balasan)
- [Reaction (Reaksi)](#reaction-reaksi)
- [Statistik](#statistik)
- [Contoh Penggunaan](#contoh-penggunaan)

---

## Overview

### Forum Types (Polymorphic)

Forum dapat dibuat di berbagai level:

| Type | `forumable_type` | Deskripsi |
| :--- | :--- | :--- |
| **Course** | `Modules\\Schemes\\Models\\Course` | Forum diskusi level course |
| **Unit** | `Modules\\Learning\\Models\\Unit` | Forum diskusi level unit dalam course |
| **Lesson** | `Modules\\Learning\\Models\\Lesson` | Forum diskusi level lesson dalam unit |
| **Assignment** | `Modules\\Learning\\Models\\Assignment` | Forum diskusi untuk assignment diskusi |

### Authentication
Semua endpoint membutuhkan:
- Header: `Authorization: Bearer {token}`
- Minimal role: `student`, `instructor`, atau `admin`
- User harus terdaftar/enroll dalam course

---

## Forum Dashboard (Admin/Instructor)

### 1. List All Threads (Admin Only)
Menampilkan semua thread dari semua course (hanya admin/superadmin).

**Endpoint:** `GET /forums/threads`

**Authorization:** Admin atau Superadmin saja

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi | Default |
| :--- | :--- | :--- | :--- | :--- |
| `page` | integer | Tidak | Halaman pagination | `1` |
| `per_page` | integer | Tidak | Jumlah item per halaman (max 100) | `20` |
| `search` | string | Tidak | Kata kunci pencarian | - |
| `filter[author_id]`| integer | Tidak | Filter berdasarkan author | - |
| `filter[pinned]` | boolean | Tidak | Filter pinned threads | - |
| `filter[resolved]` | boolean | Tidak | Filter resolved threads | - |
| `filter[closed]` | boolean | Tidak | Filter closed threads | - |

**Contoh Request:**
```http
GET /api/v1/forums/threads?page=1&per_page=50&search=laravel
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Threads retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Bagaimana cara install Laravel?",
      "author": {
        "id": 10,
        "name": "John Doe"
      },
      "forumable_type": "Modules\\Schemes\\Models\\Course",
      "forumable_id": 1,
      "forumable": {
        "id": 1,
        "title": "Web Development Course"
      },
      "views_count": 150,
      "replies_count": 25,
      "created_at": "2026-02-01T08:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 450,
    "last_page": 9
  }
}
```

---

### 2. My Threads
Menampilkan semua thread yang dibuat oleh user yang login.

**Endpoint:** `GET /forums/my-threads`

**Authorization:** Semua user (student, instructor, admin)

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi | Default |
| :--- | :--- | :--- | :--- | :--- |
| `page` | integer | Tidak | Halaman pagination | `1` |
| `per_page` | integer | Tidak | Jumlah item per halaman | `20` |
| `search` | string | Tidak | Kata kunci pencarian | - |

**Response:**
```json
{
  "success": true,
  "message": "My threads retrieved successfully",
  "data": [
    {
      "id": 5,
      "title": "Diskusi: Setup Project Baru",
      "forumable": {
        "id": 1,
        "title": "Web Development Course"
      },
      "replies_count": 3,
      "views_count": 20,
      "created_at": "2026-02-03T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 8,
    "last_page": 1
  }
}
```

---

### 3. Recent Threads (Admin/Instructor)
Menampilkan thread terbaru dari semua course yang user punya akses.

**Endpoint:** `GET /forums/threads/recent`

**Authorization:** Admin atau Instructor

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi | Default |
| :--- | :--- | :--- | :--- | :--- |
| `limit` | integer | Tidak | Jumlah item (max 50) | `10` |

**Contoh Request (Admin):**
```http
GET /api/v1/forums/threads/recent?limit=15
Authorization: Bearer {admin_token}
```

**Contoh Request (Instructor):**
```http
GET /api/v1/forums/threads/recent?limit=15
Authorization: Bearer {instructor_token}
```

Untuk Admin: semua recent threads
Untuk Instructor: recent threads dari course mereka saja

**Response:**
```json
{
  "success": true,
  "message": "Recent threads retrieved successfully",
  "data": [
    {
      "id": 10,
      "title": "Bug Report: Login Error",
      "author": { "id": 12, "name": "Jane Smith" },
      "forumable": { "id": 2, "title": "Advanced JavaScript" },
      "replies_count": 2,
      "created_at": "2026-02-03T14:30:00Z"
    }
  ]
}
```

---

### 4. Trending Threads (Admin/Instructor)
Menampilkan thread dengan aktivitas tertinggi (paling banyak replies/views).

**Endpoint:** `GET /forums/threads/trending`

**Authorization:** Admin atau Instructor

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi | Default |
| :--- | :--- | :--- | :--- | :--- |
| `limit` | integer | Tidak | Jumlah item (max 50) | `10` |
| `period` | string | Tidak | `24hours`, `7days`, `30days`, `90days` | `7days` |

**Contoh Request:**
```http
GET /api/v1/forums/threads/trending?limit=10&period=7days
Authorization: Bearer {instructor_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Trending threads retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Bagaimana cara install Laravel?",
      "author": { "id": 10, "name": "John Doe" },
      "forumable": { "id": 1, "title": "Web Development Course" },
      "replies_count": 25,
      "views_count": 150,
      "created_at": "2026-02-01T08:00:00Z"
    }
  ]
}
```

---

## Thread (Diskusi)

### 1. Menampilkan Daftar Thread
Menampilkan daftar diskusi dengan fitur pencarian, filter, dan pagination.

**Endpoint:** `GET /courses/{course}/forum/threads`

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi | Default |
| :--- | :--- | :--- | :--- | :--- |
| `forumable_type` | string | Tidak | Tipe forum (lihat Forum Types) | `Modules\\Schemes\\Models\\Course` |
| `forumable_id` | integer | Tidak | ID resource (course/unit/lesson/assignment) | Course ID dari URL |
| `page` | integer | Tidak | Halaman pagination (contoh: `1`) | `1` |
| `per_page` | integer | Tidak | Jumlah item per halaman (max 100) | `20` |
| `search` | string | Tidak | Kata kunci pencarian judul/konten | - |
| `filter[pinned]` | boolean | Tidak | Filter thread yang disematkan (`1` atau `0`) | - |
| `filter[resolved]` | boolean | Tidak | Filter thread yang sudah selesai (`1` atau `0`) | - |
| `filter[closed]` | boolean | Tidak | Filter thread yang ditutup (`1` atau `0`) | - |
| `filter[author_id]`| integer | Tidak | Filter berdasarkan ID pembuat thread | - |

**Contoh Request (Course Forum):**
```http
GET /api/v1/courses/1/forum/threads?page=1&per_page=20
```

**Contoh Request (Unit Forum):**
```http
GET /api/v1/courses/1/forum/threads?forumable_type=Modules\\\\Learning\\\\Models\\\\Unit&forumable_id=5
```

---

### 2. Membuat Thread Baru
Membuat topik diskusi baru. User harus terdaftar/enroll dalam course.

**Endpoint:** `POST /courses/{course}/forum/threads`

**Body (JSON):**
| Key | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `forumable_type` | string | Ya | Tipe forum: `Modules\\Schemes\\Models\\Course`, `Modules\\Learning\\Models\\Unit`, `Modules\\Learning\\Models\\Lesson`, `Modules\\Learning\\Models\\Assignment` |
| `forumable_id` | integer | Ya | ID resource (course_id, unit_id, lesson_id, atau assignment_id) |
| `title` | string | Ya | Judul thread (min 3, max 255 karakter) |
| `content` | string | Ya | Isi konten thread (min 1, max 5000 karakter) |

**Validasi:**
- ✅ Title: `min:3|max:255`
- ✅ Content: `min:1|max:5000`
- ✅ No XSS patterns allowed (script, javascript:, onerror, onclick)
- ✅ User must be enrolled in the course

**Contoh JSON (Course Forum):**
```json
{
    "forumable_type": "Modules\\\\Schemes\\\\Models\\\\Course",
    "forumable_id": 1,
    "title": "Bagaimana cara install Laravel?",
    "content": "Saya mengalami kendala saat install composer..."
}
```

**Contoh JSON (Unit Forum):**
```json
{
    "forumable_type": "Modules\\\\Learning\\\\Models\\\\Unit",
    "forumable_id": 5,
    "title": "Diskusi Unit: Basic PHP Concepts",
    "content": "Mari kita diskusikan tentang variable scope di PHP"
}
```

**Contoh JSON (Assignment Forum):**
```json
{
    "forumable_type": "Modules\\\\Learning\\\\Models\\\\Assignment",
    "forumable_id": 12,
    "title": "Bantuan: Assignment Build Todo App",
    "content": "Saya kesulitan implement delete functionality"
}
```

---

### 3. Detail Thread
Melihat detail satu thread beserta relasinya (replies, reactions, author info).

**Endpoint:** `GET /courses/{course}/forum/threads/{thread_id}`

---

### 4. Update Thread
Mengubah judul atau konten thread. Hanya author atau moderator yang bisa update.

**Endpoint:** `PUT /courses/{course}/forum/threads/{thread_id}`

**Body (JSON):**
| Key | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `title` | string | Tidak | Judul baru (min 3, max 255 karakter) |
| `content` | string | Tidak | Konten baru (min 1, max 5000 karakter) |

**Validasi:**
- Minimal salah satu field harus ada (title atau content)
- Content tidak boleh kosong
- Authorized: author atau moderator saja

**Contoh JSON:**
```json
{
    "title": "Judul diperbarui",
    "content": "Konten diperbarui dengan informasi yang lebih lengkap"
}
```

---

### 5. Hapus Thread
Menghapus thread (Soft Delete). Hanya author atau moderator yang bisa hapus.

**Endpoint:** `DELETE /courses/{course}/forum/threads/{thread_id}`

**Authorization:**
- Author (pembuat thread)
- Moderator (instructor untuk course tersebut)
- Admin

---

### 6. Pin Thread
Menyematkan thread agar muncul di atas (hanya moderator).

**Endpoint:** `PATCH /courses/{course}/forum/threads/{thread_id}/pin`

**Authorization:** Moderator (instructor) atau Admin saja

---

### 7. Close Thread
Menutup thread sehingga tidak bisa menerima reply baru (hanya moderator).

**Endpoint:** `PATCH /courses/{course}/forum/threads/{thread_id}/close`

**Authorization:** Moderator (instructor) atau Admin saja

---

## Reply (Balasan)

### 1. Membuat Balasan
Membalas sebuah thread atau membalas balasan lain (nested reply, max 5 level).

**Endpoint:** `POST /courses/{course}/forum/threads/{thread_id}/replies`

**Body (JSON):**
| Key | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `content` | string | Ya | Isi balasan (min 1, max 5000 karakter) |
| `parent_id` | integer | Tidak | ID reply lain jika ingin membalas komentar (nested). Kosongkan jika membalas thread utama |

**Validasi:**
- Content: `min:1|max:5000`
- Parent reply must exist in same thread
- Max nesting depth: 5 levels
- Thread tidak boleh closed
- No XSS patterns allowed

**Contoh JSON (Reply to Thread):**
```json
{
    "content": "Coba jalankan perintah composer install",
    "parent_id": null
}
```

**Contoh JSON (Nested Reply):**
```json
{
    "content": "Benar, atau bisa juga composer update jika sudah ada composer.lock",
    "parent_id": 1
}
```

---

### 2. Update Balasan
Mengubah konten balasan. Hanya author atau moderator yang bisa update.

**Endpoint:** `PUT /courses/{course}/forum/replies/{reply_id}`

**Body (JSON):**
| Key | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `content` | string | Ya | Konten baru (min 1, max 5000 karakter) |

**Contoh JSON:**
```json
{
    "content": "Koreksi: coba composer update"
}
```

---

### 3. Hapus Balasan
Menghapus balasan (Soft Delete). Hanya author atau moderator yang bisa hapus.

**Endpoint:** `DELETE /courses/{course}/forum/replies/{reply_id}`

---

### 4. Mark as Accepted Answer
Menandai balasan sebagai jawaban terbaik/jawaban yang benar (hanya thread author atau moderator).

**Endpoint:** `PATCH /courses/{course}/forum/replies/{reply_id}/accept`

**Authorization:**
- Thread author (pembuat thread)
- Moderator (instructor)
- Admin

---

## Reaction (Reaksi)

### Tipe Reaksi
| Type | Deskripsi |
| :--- | :--- |
| `like` | Like/Suka konten |
| `helpful` | Konten sangat membantu |
| `solved` | Menandai reply sebagai solusi (berbeda dengan accepted answer) |

### 1. Upsert Reaksi Thread
Menambahkan atau menghapus (toggle) reaksi pada thread.

**Endpoint:** `POST /courses/{course}/forum/threads/{thread_id}/reaction`

**Body (JSON):**
| Key | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `type` | string | Ya | `like`, `helpful`, atau `solved` |

**Contoh JSON:**
```json
{
    "type": "helpful"
}
```

---

### 2. Hapus Reaksi Thread
Menghapus reaksi dari thread.

**Endpoint:** `DELETE /courses/{course}/forum/threads/{thread_id}/reaction`

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `type` | string | Ya | `like`, `helpful`, atau `solved` |

**Contoh:**
```http
DELETE /api/v1/courses/1/forum/threads/1/reaction?type=helpful
```

---

### 3. Upsert Reaksi Balasan
Menambahkan atau menghapus (toggle) reaksi pada balasan.

**Endpoint:** `POST /courses/{course}/forum/replies/{reply_id}/reaction`

**Body (JSON):**
| Key | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `type` | string | Ya | `like`, `helpful`, atau `solved` |

**Contoh JSON:**
```json
{
    "type": "like"
}
```

---

### 4. Hapus Reaksi Balasan
Menghapus reaksi dari balasan.

**Endpoint:** `DELETE /courses/{course}/forum/replies/{reply_id}/reaction`

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `type` | string | Ya | `like`, `helpful`, atau `solved` |

---

## Statistik

### 1. Statistik Forum (Moderator Only)
Melihat statistik umum forum course (Admin/Instructor saja).

**Endpoint:** `GET /courses/{course}/forum/statistics`

**Authorization:** Instructor atau Admin saja

**Query Parameters:**
| Parameter | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `filter[period_start]` | date | Awal periode (Y-m-d) |
| `filter[period_end]` | date | Akhir periode (Y-m-d) |
| `filter[user_id]` | integer | (Opsional) Filter statistik user tertentu |

---

### 2. Statistik Saya
Melihat statistik aktivitas forum user yang sedang login.

**Endpoint:** `GET /courses/{course}/forum/my-statistics`

**Query Parameters:**
| Parameter | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `filter[period_start]` | date | Awal periode (Y-m-d) |
| `filter[period_end]` | date | Akhir periode (Y-m-d) |

---

## Contoh Penggunaan

### Skenario 1: Membuat Thread Course dan Reply

```bash
# 1. Create thread di course forum
curl -X POST http://localhost:8000/api/v1/courses/1/forum/threads \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "forumable_type": "Modules\\\\Schemes\\\\Models\\\\Course",
    "forumable_id": 1,
    "title": "Bagaimana cara setup project?",
    "content": "Saya ingin tahu langkah-langkah setup project"
  }'

# 2. Create reply
curl -X POST http://localhost:8000/api/v1/courses/1/forum/threads/5/replies \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Coba ikuti dokumentasi di README.md",
    "parent_id": null
  }'

# 3. Add reaction to reply
curl -X POST http://localhost:8000/api/v1/courses/1/forum/replies/10/reaction \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "helpful"
  }'
```

### Skenario 2: Forum per Unit

```bash
# Get threads for specific unit
curl -X GET "http://localhost:8000/api/v1/courses/1/forum/threads?forumable_type=Modules%5C%5CLearning%5C%5CModels%5C%5CUnit&forumable_id=5" \
  -H "Authorization: Bearer {token}"

# Create thread for unit
curl -X POST http://localhost:8000/api/v1/courses/1/forum/threads \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "forumable_type": "Modules\\\\Learning\\\\Models\\\\Unit",
    "forumable_id": 5,
    "title": "Diskusi Unit: PHP Basics",
    "content": "Mari kita diskusikan konsep-konsep dasar PHP"
  }'
```

### Skenario 3: Moderator Actions

```bash
# Pin a thread (instructor only)
curl -X PATCH http://localhost:8000/api/v1/courses/1/forum/threads/5/pin \
  -H "Authorization: Bearer {instructor_token}"

# Close a thread
curl -X PATCH http://localhost:8000/api/v1/courses/1/forum/threads/5/close \
  -H "Authorization: Bearer {instructor_token}"
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "content": ["The content must be at least 1 characters."]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Thread not found."
}
```
