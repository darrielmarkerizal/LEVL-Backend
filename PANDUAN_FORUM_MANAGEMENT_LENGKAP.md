# Panduan Lengkap Forum Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap untuk semua operasi forum management dari sisi Management (Superadmin, Admin, Instructor).

---

## Daftar Isi

1. [Forum Dashboard & Overview](#1-forum-dashboard--overview)
2. [Thread Moderation (Pin/Unpin)](#2-thread-moderation-pinunpin)
3. [Thread Status (Close/Open)](#3-thread-status-closeopen)
4. [Thread Resolution (Resolve/Unresolve)](#4-thread-resolution-resolveunresolve)
5. [Delete Thread](#5-delete-thread)
6. [Reply Moderation (Accept/Unaccept)](#6-reply-moderation-acceptunaccept)
7. [Delete Reply](#7-delete-reply)
8. [Reaction Management](#8-reaction-management)
9. [Forum Statistics](#9-forum-statistics)

---

## 1. FORUM DASHBOARD & OVERVIEW

### 1.1 List All Threads (Cross-Course)

Menampilkan semua thread dari semua course yang dikelola oleh user (Admin/Instructor/Superadmin). Berbeda dengan endpoint per-course, endpoint ini menampilkan thread dari multiple courses sekaligus untuk dashboard management.

#### Endpoint
```
GET /api/v1/forums/threads
```

#### Authorization
- Role: Admin, Instructor, Superadmin
- Instructor: Hanya melihat thread dari course yang mereka ajar
- Admin/Superadmin: Melihat semua thread dari semua course

#### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `per_page` | integer | ❌ Tidak | 20 | Jumlah data per halaman (max: 100) |
| `sort` | string | ❌ Tidak | -last_activity_at | Field untuk sorting |
| `search` | string | ❌ Tidak | - | Pencarian full-text (via Meilisearch) |
| `filter[author_id]` | integer | ❌ Tidak | - | Filter by user ID pembuat thread |
| `filter[pinned]` | boolean | ❌ Tidak | - | Filter pinned threads |
| `filter[resolved]` | boolean | ❌ Tidak | - | Filter resolved threads |
| `filter[closed]` | boolean | ❌ Tidak | - | Filter closed threads |
| `filter[is_mentioned]` | boolean | ❌ Tidak | - | Filter thread dimana user di-mention |

#### Allowed Sorts

| Sort | Deskripsi |
|------|-----------|
| `id` | Sort by ID thread |
| `created_at` | Sort by tanggal dibuat |
| `last_activity_at` | Sort by aktivitas terakhir (default) |
| `views_count` | Sort by jumlah views |
| `replies_count` | Sort by jumlah replies |

**Catatan**: Tambahkan `-` di depan untuk descending (contoh: `-created_at`)

#### Contoh Request

##### 1. Get All Threads (Default)
```
GET /api/v1/forums/threads
```

##### 2. Filter Pinned Threads
```
GET /api/v1/forums/threads?filter[pinned]=1
```

##### 3. Search Threads
```
GET /api/v1/forums/threads?search=laravel
```

##### 4. Kombinasi Filter + Search + Sort
```
GET /api/v1/forums/threads?search=javascript&filter[resolved]=0&sort=-replies_count&per_page=50
```

#### Response Format

```json
{
  "success": true,
  "message": "Threads retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Bagaimana cara install Laravel?",
      "author": {
        "id": 10,
        "username": "jane.smith",
        "name": "Jane Smith",
        "email": "jane@example.com",
        "avatar": "https://example.com/avatar.jpg"
      },
      "forumable_type": "Modules\\Schemes\\Models\\Course",
      "forumable_slug": "web-development-course",
      "course": {
        "slug": "web-development-course",
        "title": "Web Development Basics"
      },
      "views_count": 150,
      "replies_count": 25,
      "is_pinned": true,
      "is_closed": false,
      "is_resolved": false,
      "is_mentioned": false,
      "last_activity_at": "2026-03-08T10:30:00Z",
      "created_at": "2026-02-01T08:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 450,
    "last_page": 23
  }
}
```

#### Catatan Penting
- Endpoint ini untuk **dashboard management** (cross-course view)
- Instructor hanya melihat thread dari course yang mereka manage
- Data di-paginate secara default
- Search menggunakan Meilisearch (fast full-text search)

---

### 1.2 My Threads

Menampilkan semua thread yang dibuat oleh user yang sedang login (cross-course).

#### Endpoint
```
GET /api/v1/forums/my-threads
```

#### Authorization
- Role: Semua user (Student, Instructor, Admin, Superadmin)

#### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `per_page` | integer | ❌ Tidak | 20 | Jumlah data per halaman (max: 100) |
| `sort` | string | ❌ Tidak | -last_activity_at | Field untuk sorting |
| `search` | string | ❌ Tidak | - | Pencarian full-text |
| `filter[pinned]` | boolean | ❌ Tidak | - | Filter pinned threads |
| `filter[resolved]` | boolean | ❌ Tidak | - | Filter resolved threads |
| `filter[closed]` | boolean | ❌ Tidak | - | Filter closed threads |
| `filter[is_mentioned]` | boolean | ❌ Tidak | - | Filter thread with mentions |

#### Contoh Request

##### 1. Get My Threads (Default)
```
GET /api/v1/forums/my-threads
```

##### 2. Filter Unresolved Threads
```
GET /api/v1/forums/my-threads?filter[resolved]=0
```

##### 3. Search My Threads
```
GET /api/v1/forums/my-threads?search=docker&sort=-created_at
```

#### Response Format

```json
{
  "success": true,
  "message": "Your threads retrieved successfully.",
  "data": [
    {
      "id": 5,
      "title": "Setup Project dengan Docker",
      "author": {
        "id": 17,
        "username": "john.doe",
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "https://example.com/avatar.jpg"
      },
      "forumable_slug": "web-development-course",
      "course": {
        "slug": "web-development-course",
        "title": "Web Development Basics"
      },
      "replies_count": 3,
      "views_count": 20,
      "is_pinned": false,
      "is_closed": false,
      "is_resolved": false,
      "last_activity_at": "2026-03-05T14:20:00Z",
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

### 1.3 Trending Threads

Menampilkan thread dengan aktivitas tertinggi (paling banyak replies/views) dalam periode tertentu.

#### Endpoint
```
GET /api/v1/forums/threads/trending
```

#### Authorization
- Role: Admin, Instructor, Superadmin
- Instructor: Hanya melihat trending threads dari course yang mereka manage

#### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `search` | string | ❌ Tidak | - | Pencarian full-text |
| `filter[limit]` | integer | ❌ Tidak | 10 | Jumlah data per halaman (max: 50) |
| `filter[period]` | string | ❌ Tidak | 7days | Periode aktivitas |

#### Filter Period Options

| Value | Deskripsi |
|-------|-----------|
| `24hours` | 24 jam terakhir |
| `7days` | 7 hari terakhir (default) |
| `30days` | 30 hari terakhir |
| `90days` | 90 hari terakhir |

#### Contoh Request

##### 1. Get Trending Threads (Last 7 Days)
```
GET /api/v1/forums/threads/trending
```

##### 2. Get Top 20 Trending (Last 24 Hours)
```
GET /api/v1/forums/threads/trending?filter[period]=24hours&filter[limit]=20
```

##### 3. Search Trending Threads
```
GET /api/v1/forums/threads/trending?filter[period]=30days&search=laravel
```

#### Response Format

```json
{
  "success": true,
  "message": "Trending threads retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Bagaimana cara install Laravel?",
      "author": {
        "id": 10,
        "username": "jane.smith",
        "name": "Jane Smith",
        "email": "jane@example.com",
        "avatar": "https://example.com/avatar.jpg"
      },
      "forumable_slug": "web-development-course",
      "course": {
        "slug": "web-development-course",
        "title": "Web Development Basics"
      },
      "replies_count": 25,
      "views_count": 150,
      "is_pinned": true,
      "is_closed": false,
      "is_resolved": false,
      "created_at": "2026-02-01T08:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "last_page": 5
  }
}
```

#### Catatan Penting
- Trending ditentukan berdasarkan kombinasi views dan replies
- Period default adalah 7 days
- Limit default adalah 10, maksimal 50

---

## 2. THREAD MODERATION (PIN/UNPIN)

### 2.1 Pin Thread

Menyematkan thread agar selalu muncul di atas daftar thread. Berguna untuk pengumuman penting atau FAQ.

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/pin
```

#### Authorization
- Role: Instructor (untuk course tersebut), Admin, Superadmin
- Permission: User harus punya akses `update` untuk course

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread yang akan di-pin |

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/pin
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Thread pinned successfully.",
  "data": {
    "id": 5,
    "title": "Pengumuman: Perubahan Jadwal Kelas",
    "content": "Mohon perhatian semua peserta...",
    "is_pinned": true,
    "is_closed": false,
    "is_resolved": false,
    "author": {
      "id": 10,
      "username": "jane.smith",
      "name": "Jane Smith",
      "email": "jane@example.com",
      "avatar": "https://example.com/avatar.jpg"
    },
    "created_at": "2026-02-01T08:00:00Z",
    "updated_at": "2026-03-08T10:00:00Z"
  }
}
```

#### Error Responses

##### Unauthorized (403)
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

##### Thread Not Found (404)
```json
{
  "success": false,
  "message": "Thread not found.",
  "errors": null
}
```

#### Catatan Penting
- Pinned thread selalu muncul di atas (sorting otomatis)
- Hanya moderator (Instructor/Admin/Superadmin) yang bisa pin
- Tidak ada batasan jumlah pinned threads per course
- Pinned thread tetap bisa di-edit/delete

---

### 2.2 Unpin Thread

Melepas sematan thread sehingga kembali ke urutan normal (sorted by last activity).

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/unpin
```

#### Authorization
- Role: Instructor (untuk course tersebut), Admin, Superadmin
- Permission: User harus punya akses `update` untuk course

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread yang akan di-unpin |

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/unpin
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Thread unpinned successfully.",
  "data": {
    "id": 5,
    "title": "Pengumuman: Perubahan Jadwal Kelas",
    "is_pinned": false,
    "updated_at": "2026-03-08T10:15:00Z"
  }
}
```

---

## 3. THREAD STATUS (CLOSE/OPEN)

### 3.1 Close Thread

Menutup thread sehingga tidak bisa menerima reply baru. Berguna untuk thread yang sudah selesai dibahas atau tidak relevan lagi.

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/close
```

#### Authorization
- Role: Instructor (untuk course tersebut), Admin, Superadmin
- Permission: User harus punya akses `update` untuk course

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread yang akan ditutup |

#### Business Rules
- Thread yang closed tidak bisa menerima reply baru
- Thread yang closed masih bisa dilihat (read-only)
- Thread yang closed masih bisa di-pin/unpin
- Thread yang closed masih bisa di-resolve/unresolve
- Hanya moderator yang bisa close thread

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/close
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Thread closed successfully.",
  "data": {
    "id": 5,
    "title": "Diskusi: Setup Environment",
    "is_closed": true,
    "is_pinned": false,
    "is_resolved": false,
    "updated_at": "2026-03-08T10:30:00Z"
  }
}
```

#### Error Responses

##### Cannot Reply (When Student Tries to Reply to Closed Thread)
```json
{
  "success": false,
  "message": "This thread is closed and cannot accept new replies.",
  "errors": null
}
```

#### Catatan Penting
- Closed thread ditampilkan dengan badge "Closed"
- Student tidak bisa membuat reply baru di closed thread
- Moderator tetap bisa edit/delete thread yang closed
- Thread bisa di-open kembali kapan saja

---

### 3.2 Open Thread

Membuka kembali thread yang sudah closed sehingga bisa menerima reply baru lagi.

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/open
```

#### Authorization
- Role: Instructor (untuk course tersebut), Admin, Superadmin
- Permission: User harus punya akses `update` untuk course

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread yang akan dibuka |

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/open
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Thread opened successfully.",
  "data": {
    "id": 5,
    "title": "Diskusi: Setup Environment",
    "is_closed": false,
    "is_pinned": false,
    "is_resolved": false,
    "updated_at": "2026-03-08T10:45:00Z"
  }
}
```

---

## 4. THREAD RESOLUTION (RESOLVE/UNRESOLVE)

### 4.1 Resolve Thread

Menandai thread sebagai "Resolved" (masalah sudah terpecahkan). Berguna untuk Q&A forum.

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/resolve
```

#### Authorization
- Role: Thread Author, Instructor (untuk course tersebut), Admin, Superadmin
- Permission: Author bisa resolve thread sendiri, atau moderator

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread yang akan di-resolve |

#### Business Rules
- Author thread bisa resolve thread sendiri
- Moderator (Instructor/Admin/Superadmin) bisa resolve thread apapun
- Resolved thread tetap bisa menerima reply baru
- Resolved thread ditampilkan dengan badge "Resolved"
- Biasanya digunakan bersamaan dengan "Accept Reply" untuk menandai solusi

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/resolve
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Thread resolved successfully.",
  "data": {
    "id": 5,
    "title": "Error saat composer install",
    "is_resolved": true,
    "is_closed": false,
    "is_pinned": false,
    "resolved_at": "2026-03-08T11:00:00Z",
    "updated_at": "2026-03-08T11:00:00Z"
  }
}
```

#### Catatan Penting
- Resolve berbeda dengan Close (thread masih bisa menerima reply)
- Biasanya author resolve thread setelah mendapat jawaban yang memuaskan
- Moderator bisa resolve thread untuk menandai sudah selesai dibahas
- Filter `filter[resolved]=0` di list threads untuk melihat unresolved threads

---

### 4.2 Unresolve Thread

Membatalkan status "Resolved" dari thread. Berguna jika ternyata masalah belum sepenuhnya selesai.

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/unresolve
```

#### Authorization
- Role: Thread Author, Instructor (untuk course tersebut), Admin, Superadmin
- Permission: Author bisa unresolve thread sendiri, atau moderator

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread yang akan di-unresolve |

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/unresolve
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Thread unresolved successfully.",
  "data": {
    "id": 5,
    "title": "Error saat composer install",
    "is_resolved": false,
    "resolved_at": null,
    "updated_at": "2026-03-08T11:15:00Z"
  }
}
```

---

## 5. DELETE THREAD

### Endpoint
```
DELETE /api/v1/courses/{course_slug}/forum/threads/{thread_id}
```

### Authorization
- Role: Thread Author, Instructor (untuk course tersebut), Admin, Superadmin
- Permission:
  - Author: Bisa delete thread sendiri
  - Moderator: Bisa delete thread apapun di course yang mereka manage

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread yang akan dihapus |

### Business Rules
- Thread di-soft delete (tidak benar-benar dihapus dari database)
- Semua replies di thread juga ikut ter-delete (soft delete)
- Data masih bisa di-restore jika diperlukan
- Author bisa delete thread sendiri (dalam waktu tertentu setelah dibuat)
- Moderator bisa delete thread kapan saja

### Contoh Request

```
DELETE /api/v1/courses/web-development-basics/forum/threads/5
```

### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Thread deleted successfully.",
  "data": null
}
```

### Error Responses

#### Unauthorized (403)
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

#### Thread Not Found (404)
```json
{
  "success": false,
  "message": "Thread not found.",
  "errors": null
}
```

### Catatan Penting
- Soft delete memungkinkan data recovery jika dibutuhkan
- Semua media attachments tetap tersimpan
- Thread yang dihapus tidak muncul di list threads
- Moderator bisa melihat deleted threads melalui admin panel (jika diimplementasikan)

---

## 6. REPLY MODERATION (ACCEPT/UNACCEPT)

### 6.1 Accept Reply (Mark as Solution)

Menandai reply sebagai jawaban yang diterima/solusi untuk thread. Berguna untuk Q&A forum agar student lain bisa langsung melihat solusi yang benar.

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/replies/{reply_id}/accept
```

#### Authorization
- Role: Thread Author, Instructor (untuk course tersebut), Admin, Superadmin
- Permission:
  - Thread author: Bisa accept reply di thread sendiri
  - Moderator: Bisa accept reply di thread apapun

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread |
| `reply_id` | integer | ✅ Ya | ID reply yang akan di-accept |

#### Business Rules
- Hanya 1 reply yang bisa di-accept per thread
- Jika ada reply lain yang sudah di-accept, akan otomatis di-unaccept
- Accepted reply ditampilkan dengan badge "Accepted Answer"
- Accepted reply biasanya ditampilkan di atas (UI decision)
- Thread author atau moderator yang bisa accept reply

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/replies/12/accept
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Reply marked as accepted answer.",
  "data": {
    "id": 12,
    "thread_id": 5,
    "content": "Coba jalankan perintah composer update --lock",
    "is_accepted": true,
    "author": {
      "id": 15,
      "username": "alice.brown",
      "name": "Alice Brown",
      "email": "alice@example.com",
      "avatar": "https://example.com/avatar.jpg"
    },
    "accepted_at": "2026-03-08T11:30:00Z",
    "created_at": "2026-02-01T09:30:00Z",
    "updated_at": "2026-03-08T11:30:00Z"
  }
}
```

#### Error Responses

##### Unauthorized (403)
```json
{
  "success": false,
  "message": "Only thread author or moderators can accept replies.",
  "errors": null
}
```

##### Reply Not Found (404)
```json
{
  "success": false,
  "message": "Reply not found.",
  "errors": null
}
```

#### Catatan Penting
- Accept reply biasanya dilakukan bersamaan dengan resolve thread
- Hanya 1 accepted answer per thread (auto-unaccept yang lama)
- Accepted reply ditampilkan dengan highlight/badge khusus
- Filter berdasarkan accepted reply untuk melihat best answers

---

### 6.2 Unaccept Reply

Membatalkan status "Accepted Answer" dari reply.

#### Endpoint
```
PATCH /api/v1/courses/{course_slug}/forum/threads/{thread_id}/replies/{reply_id}/unaccept
```

#### Authorization
- Role: Thread Author, Instructor (untuk course tersebut), Admin, Superadmin
- Permission: Thread author atau moderator

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread |
| `reply_id` | integer | ✅ Ya | ID reply yang akan di-unaccept |

#### Contoh Request

```
PATCH /api/v1/courses/web-development-basics/forum/threads/5/replies/12/unaccept
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Reply unmarked as accepted answer.",
  "data": {
    "id": 12,
    "thread_id": 5,
    "is_accepted": false,
    "accepted_at": null,
    "updated_at": "2026-03-08T11:45:00Z"
  }
}
```

---

## 7. DELETE REPLY

### Endpoint
```
DELETE /api/v1/courses/{course_slug}/forum/threads/{thread_id}/replies/{reply_id}
```

### Authorization
- Role: Reply Author, Instructor (untuk course tersebut), Admin, Superadmin
- Permission:
  - Author: Bisa delete reply sendiri
  - Moderator: Bisa delete reply apapun di course yang mereka manage

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread |
| `reply_id` | integer | ✅ Ya | ID reply yang akan dihapus |

### Business Rules
- Reply di-soft delete (tidak benar-benar dihapus)
- Jika reply punya child replies (nested), child replies tetap tersimpan
- Author bisa delete reply sendiri (dalam waktu tertentu)
- Moderator bisa delete reply kapan saja
- Media attachments tetap tersimpan

### Contoh Request

```
DELETE /api/v1/courses/web-development-basics/forum/threads/5/replies/12
```

### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Reply deleted successfully.",
  "data": null
}
```

### Error Responses

#### Unauthorized (403)
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

#### Reply Not Found (404)
```json
{
  "success": false,
  "message": "Reply not found.",
  "errors": null
}
```

### Catatan Penting
- Soft delete memungkinkan data recovery
- Child replies (nested replies) TIDAK ikut terhapus
- Reply yang dihapus tidak muncul di list replies
- Counter `replies_count` di thread tetap akurat

---

## 8. REACTION MANAGEMENT

### 8.1 Delete Reaction from Thread

Menghapus reaction dari thread. Moderator bisa menghapus reaction yang tidak pantas atau spam.

#### Endpoint
```
DELETE /api/v1/courses/{course_slug}/forum/threads/{thread_id}/reactions/{reaction_id}
```

#### Authorization
- Role: Reaction Owner, Instructor (untuk course tersebut), Admin, Superadmin
- Permission:
  - Owner: Bisa delete reaction sendiri
  - Moderator: Bisa delete reaction apapun

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread |
| `reaction_id` | integer | ✅ Ya | ID reaction yang akan dihapus |

#### Contoh Request

```
DELETE /api/v1/courses/web-development-basics/forum/threads/5/reactions/15
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Reaction removed successfully.",
  "data": null
}
```

---

### 8.2 Delete Reaction from Reply

Menghapus reaction dari reply.

#### Endpoint
```
DELETE /api/v1/courses/{course_slug}/forum/threads/{thread_id}/replies/{reply_id}/reactions/{reaction_id}
```

#### Authorization
- Role: Reaction Owner, Instructor (untuk course tersebut), Admin, Superadmin
- Permission:
  - Owner: Bisa delete reaction sendiri
  - Moderator: Bisa delete reaction apapun

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |
| `thread_id` | integer | ✅ Ya | ID thread |
| `reply_id` | integer | ✅ Ya | ID reply |
| `reaction_id` | integer | ✅ Ya | ID reaction yang akan dihapus |

#### Contoh Request

```
DELETE /api/v1/courses/web-development-basics/forum/threads/5/replies/12/reactions/20
```

#### Response Format (200 OK)

```json
{
  "success": true,
  "message": "Reaction removed successfully.",
  "data": null
}
```

#### Catatan Penting
- Moderator bisa menghapus reaction yang tidak pantas
- Delete reaction tidak memberikan notifikasi ke reaction owner
- Reaction types: `like`, `helpful`, `solved`

---

## 9. FORUM STATISTICS

### 9.1 Course Forum Statistics

Melihat statistik umum forum untuk satu course. Hanya moderator yang bisa akses.

#### Endpoint
```
GET /api/v1/courses/{course_slug}/forum/statistics
```

#### Authorization
- Role: Instructor (untuk course tersebut), Admin, Superadmin
- Permission: User harus punya akses `view` untuk course

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |

#### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `filter[period_start]` | date | ❌ Tidak | Start of month | Tanggal mulai (Y-m-d) |
| `filter[period_end]` | date | ❌ Tidak | End of month | Tanggal akhir (Y-m-d) |
| `filter[user_id]` | integer | ❌ Tidak | - | Filter statistik user tertentu |

#### Business Rules
- Default period: Current month (dari tanggal 1 sampai akhir bulan)
- `period_end` harus >= `period_start`
- Jika `user_id` diisi, tampilkan statistik user tersebut di course

#### Contoh Request

##### 1. Get Course Statistics (Current Month)
```
GET /api/v1/courses/web-development-basics/forum/statistics
```

##### 2. Get Course Statistics (Specific Period)
```
GET /api/v1/courses/web-development-basics/forum/statistics?filter[period_start]=2026-02-01&filter[period_end]=2026-02-28
```

##### 3. Get User Statistics in Course
```
GET /api/v1/courses/web-development-basics/forum/statistics?filter[user_id]=10&filter[period_start]=2026-02-01&filter[period_end]=2026-02-28
```

#### Response Format (Course Statistics)

```json
{
  "success": true,
  "message": "Forum statistics retrieved successfully.",
  "data": {
    "course_id": 1,
    "course_slug": "web-development-basics",
    "period_start": "2026-02-01",
    "period_end": "2026-02-28",
    "total_threads": 45,
    "total_replies": 320,
    "total_reactions": 850,
    "active_users": 28,
    "average_reply_time": "2.5 hours",
    "most_active_user": {
      "id": 17,
      "username": "john.doe",
      "name": "John Doe",
      "contributions": 45
    },
    "top_contributors": [
      {
        "id": 17,
        "username": "john.doe",
        "name": "John Doe",
        "threads_count": 8,
        "replies_count": 37,
        "total_contributions": 45
      },
      {
        "id": 10,
        "username": "jane.smith",
        "name": "Jane Smith",
        "threads_count": 5,
        "replies_count": 32,
        "total_contributions": 37
      }
    ]
  }
}
```

#### Response Format (User Statistics in Course)

```json
{
  "success": true,
  "message": "User statistics retrieved successfully.",
  "data": {
    "user_id": 10,
    "user": {
      "id": 10,
      "username": "jane.smith",
      "name": "Jane Smith",
      "email": "jane@example.com",
      "avatar": "https://example.com/avatar.jpg"
    },
    "course_id": 1,
    "course_slug": "web-development-basics",
    "period_start": "2026-02-01",
    "period_end": "2026-02-28",
    "threads_created": 5,
    "replies_created": 32,
    "reactions_given": 45,
    "reactions_received": 78,
    "accepted_answers": 3,
    "last_activity_at": "2026-02-28T15:30:00Z"
  }
}
```

#### Catatan Penting
- Statistics memberikan insight untuk moderator
- Average reply time menunjukkan seberapa aktif diskusi
- Top contributors bisa diberikan reward/badge
- User statistics berguna untuk evaluasi partisipasi student

---

### 9.2 My Forum Statistics

Melihat statistik aktivitas forum user yang sedang login di course tertentu.

#### Endpoint
```
GET /api/v1/courses/{course_slug}/forum/my-statistics
```

#### Authorization
- Role: Semua user yang enrolled di course

#### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |

#### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `filter[period_start]` | date | ❌ Tidak | Start of month | Tanggal mulai (Y-m-d) |
| `filter[period_end]` | date | ❌ Tidak | End of month | Tanggal akhir (Y-m-d) |

#### Contoh Request

##### 1. Get My Statistics (Current Month)
```
GET /api/v1/courses/web-development-basics/forum/my-statistics
```

##### 2. Get My Statistics (Specific Period)
```
GET /api/v1/courses/web-development-basics/forum/my-statistics?filter[period_start]=2026-02-01&filter[period_end]=2026-02-28
```

#### Response Format

```json
{
  "success": true,
  "message": "User statistics retrieved successfully.",
  "data": {
    "user_id": 17,
    "course_id": 1,
    "course_slug": "web-development-basics",
    "period_start": "2026-02-01",
    "period_end": "2026-02-28",
    "threads_created": 5,
    "replies_created": 23,
    "reactions_given": 45,
    "reactions_received": 78,
    "accepted_answers": 2,
    "mentions_count": 8,
    "last_activity_at": "2026-02-28T15:30:00Z",
    "rank": {
      "position": 3,
      "total_users": 28,
      "percentile": 89.3
    }
  }
}
```

#### Catatan Penting
- Student bisa melihat statistik sendiri
- Rank menunjukkan posisi user dibanding user lain di course
- Berguna untuk gamification (leaderboard, badges)
- Percentile menunjukkan persentase user yang lebih aktif

---

## CATATAN UMUM

### Authorization Matrix

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| List All Threads | ❌ | ✅ (Own courses) | ✅ | ✅ |
| My Threads | ✅ | ✅ | ✅ | ✅ |
| Trending Threads | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Pin/Unpin Thread | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Close/Open Thread | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Resolve/Unresolve (Own) | ✅ | ✅ | ✅ | ✅ |
| Resolve/Unresolve (Others) | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Delete Thread (Own) | ✅ | ✅ | ✅ | ✅ |
| Delete Thread (Others) | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Accept/Unaccept Reply (Own Thread) | ✅ | ✅ | ✅ | ✅ |
| Accept/Unaccept Reply (Others) | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Delete Reply (Own) | ✅ | ✅ | ✅ | ✅ |
| Delete Reply (Others) | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Delete Reaction (Own) | ✅ | ✅ | ✅ | ✅ |
| Delete Reaction (Others) | ❌ | ✅ (Own courses) | ✅ | ✅ |
| Course Statistics | ❌ | ✅ (Own courses) | ✅ | ✅ |
| My Statistics | ✅ | ✅ | ✅ | ✅ |

---

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
  "message": "Data retrieved successfully",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 450,
    "last_page": 23
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

---

### HTTP Status Codes
- `200` - Success (GET, PATCH, DELETE)
- `201` - Created (POST)
- `400` - Bad Request
- `401` - Unauthorized (tidak login)
- `403` - Forbidden (tidak punya akses)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

### Tips untuk UI/UX

#### 1. Forum Dashboard
- Tampilkan trending threads di sidebar
- Gunakan tabs untuk filter: All, Pinned, Unresolved, Closed
- Search dengan debounce (300ms)
- Badge untuk pinned/resolved/closed threads
- Highlight threads dengan unread replies

#### 2. Thread Moderation UI
- Action buttons untuk moderator: Pin, Close, Resolve, Delete
- Confirmation modal untuk destructive actions
- Quick actions dropdown di setiap thread card
- Batch operations untuk multiple threads
- Audit log untuk moderation actions

#### 3. Thread Detail Page
- Accepted answer highlighted di atas
- Moderator badge di nama instructor
- Inline edit untuk thread/reply
- Quick reaction buttons (like, helpful, solved)
- Report button untuk inappropriate content

#### 4. Statistics Dashboard
- Charts untuk trend aktivitas (line chart)
- Top contributors leaderboard
- Period selector (This Week, This Month, Custom)
- Export statistics ke CSV/Excel
- Real-time update untuk active users

#### 5. Accessibility
- Keyboard shortcuts untuk common actions
- Screen reader friendly
- High contrast mode untuk badges
- ARIA labels untuk moderation actions

---

### Workflow Rekomendasi

#### Moderator Daily Workflow
1. Check trending threads (last 24 hours)
2. Review reported threads/replies
3. Pin important announcements
4. Resolve answered questions
5. Close outdated threads
6. Monitor statistics for engagement

#### Thread Lifecycle
```
Created → Active Discussion → (Optional: Pinned) → Resolved → (Optional: Closed)
                ↓
            Reported
                ↓
         Moderated/Deleted
```

#### Q&A Thread Flow
1. Student posts question (thread created)
2. Students/Instructors reply with answers
3. Thread author or instructor marks best reply (accept)
4. Thread author or instructor resolves thread
5. (Optional) Instructor closes thread if no longer relevant

---

### Security Considerations

#### 1. Moderation Actions Logging
- Semua moderation actions (pin, close, delete) harus di-log
- Log mencatat: actor, action, timestamp, reason (optional)
- Audit trail untuk accountability

#### 2. Auto-Moderation (Optional Implementation)
- Auto-close threads setelah X days inactive
- Auto-flag threads dengan banyak reports
- Spam detection untuk content

#### 3. Permission Validation
- Server-side permission check untuk setiap action
- TIDAK hanya mengandalkan client-side hiding buttons
- Rate limiting untuk bulk operations

#### 4. Data Privacy
- Deleted content tidak boleh accessible via API
- Soft delete untuk data recovery (admin only)
- GDPR compliance untuk user data export/delete

---

## Error Handling

### Common Errors

#### 1. Unauthorized Access
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

#### 2. Thread Not Found
```json
{
  "success": false,
  "message": "Thread not found.",
  "errors": null
}
```

#### 3. Reply Not Found
```json
{
  "success": false,
  "message": "Reply not found.",
  "errors": null
}
```

#### 4. Thread Closed (Cannot Reply)
```json
{
  "success": false,
  "message": "This thread is closed and cannot accept new replies.",
  "errors": null
}
```

#### 5. Invalid Period Range
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "filter.period_end": ["The filter.period_end must be a date after or equal to filter.period_start."]
  }
}
```

---

## Changelog

### Version 1.0 (8 Maret 2026)
- Initial release
- Forum dashboard dengan cross-course view
- Thread moderation: Pin/Unpin, Close/Open, Resolve/Unresolve
- Reply moderation: Accept/Unaccept, Delete
- Reaction management: Delete reactions
- Forum statistics untuk course dan user
- Complete authorization matrix
- UI/UX workflow recommendations

---

**Versi**: 1.0  
**Terakhir Update**: 8 Maret 2026  
**Kontak**: Backend Team
