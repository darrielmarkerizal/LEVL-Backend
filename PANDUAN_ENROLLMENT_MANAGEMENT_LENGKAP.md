# Panduan Lengkap Enrollment Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap untuk semua operasi enrollment (pendaftaran kursus) dari sisi Management (Superadmin, Admin, Instructor) dan Student.

---

## Daftar Isi

1. [Enrollment Status & Flow](#1-enrollment-status--flow)
2. [Enroll in Course (Student)](#2-enroll-in-course-student)
3. [Check Enrollment Status](#3-check-enrollment-status)
4. [Cancel Enrollment Request (Student)](#4-cancel-enrollment-request-student)
5. [Withdraw from Course (Student)](#5-withdraw-from-course-student)
6. [List Enrollments by Course (Manager)](#6-list-enrollments-by-course-manager)
7. [List Enrollments (Context-Aware)](#7-list-enrollments-context-aware)
8. [Approve Enrollment (Manager)](#8-approve-enrollment-manager)
9. [Decline Enrollment (Manager)](#9-decline-enrollment-manager)
10. [Remove User from Course (Manager)](#10-remove-user-from-course-manager)
11. [Bulk Operations (Manager)](#11-bulk-operations-manager)

---

## 1. ENROLLMENT STATUS & FLOW

### Status Enrollment

| Status | Deskripsi | Warna Badge |
|--------|-----------|-------------|
| `pending` | Menunggu persetujuan admin/instructor | 🟡 Kuning |
| `active` | User terdaftar aktif di course | 🟢 Hijau |
| `completed` | User telah menyelesaikan course | 🔵 Biru |
| `cancelled` | Enrollment dibatalkan/ditolak | 🔴 Merah |

### Enrollment Type (Tipe Pendaftaran Course)

| Type | Deskripsi | Flow |
|------|-----------|------|
| `auto_accept` | Otomatis diterima | Student enroll → Langsung `active` |
| `key_based` | Butuh kunci pendaftaran | Student enroll + key → Langsung `active` |
| `approval` | Butuh persetujuan | Student enroll → `pending` → Admin approve → `active` |

### Status Transition Flow

```
┌─────────────┐
│   Student   │
│   Action    │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────────────┐
│  Enroll (POST /courses/{slug}/enroll)          │
└──────┬──────────────────────────────────────────┘
       │
       ├─── auto_accept ──────► active
       │
       ├─── key_based ────────► active (jika key valid)
       │
       └─── approval ─────────► pending
                                   │
                                   ├─── Approve ──► active
                                   │
                                   └─── Decline ──► cancelled

┌─────────────────────────────────────────────────┐
│  Student Actions (Setelah Enroll)              │
└─────────────────────────────────────────────────┘
   pending ──── Cancel ────► cancelled
   active ───── Withdraw ──► cancelled

┌─────────────────────────────────────────────────┐
│  Manager Actions                                │
└─────────────────────────────────────────────────┘
   pending ──── Approve ──► active
   pending ──── Decline ──► cancelled
   active ───── Remove ───► cancelled
   pending ──── Remove ───► cancelled
```

### Catatan Penting
- Status `completed` di-set otomatis oleh sistem saat user menyelesaikan semua requirements
- Status `cancelled` bersifat final (tidak bisa diubah lagi)
- Student hanya bisa cancel enrollment dengan status `pending`
- Student hanya bisa withdraw dari enrollment dengan status `active`
- Manager bisa remove enrollment dengan status `pending` atau `active`

---

## 2. ENROLL IN COURSE (Student)

### Endpoint
```
POST /api/v1/courses/{course_slug}/enroll
```

### Authorization
- Role: **Student only**
- Rate Limit: 5 requests per minute

### Content-Type
`application/json`

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course yang akan di-enroll |

### Request Body

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `enrollment_key` | string | Conditional | max:100 | **Required jika** course `enrollment_type` = `key_based` |

### Contoh Request

#### 1. Auto Accept Course (Tanpa Key)
```json
POST /api/v1/courses/web-development-basics/enroll
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Enrol berhasil. Anda sekarang terdaftar pada course ini.",
  "data": {
    "id": 123,
    "user_id": 5,
    "course_id": 2,
    "status": "active",
    "enrolled_at": "2026-03-06T10:00:00Z",
    "completed_at": null,
    "created_at": "2026-03-06T10:00:00Z",
    "updated_at": "2026-03-06T10:00:00Z"
  }
}
```

#### 2. Key-Based Course (Dengan Key)
```json
POST /api/v1/courses/premium-web-course/enroll
{
  "enrollment_key": "ABC123XYZ"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Enrol berhasil. Anda sekarang terdaftar pada course ini.",
  "data": {
    "id": 124,
    "user_id": 5,
    "course_id": 3,
    "status": "active",
    "enrolled_at": "2026-03-06T10:05:00Z",
    "completed_at": null,
    "created_at": "2026-03-06T10:05:00Z",
    "updated_at": "2026-03-06T10:05:00Z"
  }
}
```

#### 3. Approval Course (Menunggu Persetujuan)
```json
POST /api/v1/courses/advanced-programming/enroll
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Permintaan enrollment berhasil dikirim. Menunggu persetujuan.",
  "data": {
    "id": 125,
    "user_id": 5,
    "course_id": 4,
    "status": "pending",
    "enrolled_at": "2026-03-06T10:10:00Z",
    "completed_at": null,
    "created_at": "2026-03-06T10:10:00Z",
    "updated_at": "2026-03-06T10:10:00Z"
  }
}
```

### Error Responses

#### Already Enrolled
```json
{
  "success": false,
  "message": "Anda sudah terdaftar di course ini.",
  "errors": {
    "enrollment": ["Anda sudah terdaftar di course ini."]
  }
}
```

#### Invalid Enrollment Key
```json
{
  "success": false,
  "message": "Kode enrollment tidak valid.",
  "errors": {
    "enrollment_key": ["Kode enrollment tidak valid."]
  }
}
```

#### Missing Enrollment Key
```json
{
  "success": false,
  "message": "Data yang Anda kirim tidak valid.",
  "errors": {
    "enrollment_key": ["Kode enrollment wajib diisi."]
  }
}
```

### Catatan Penting
- Student hanya bisa enroll 1 kali per course
- Jika sudah pernah enroll (status apapun), tidak bisa enroll lagi
- Enrollment key case-sensitive
- Email notification dikirim ke student dan course managers

---

## 3. CHECK ENROLLMENT STATUS

### Endpoint
```
GET /api/v1/courses/{course_slug}/enrollment-status
```

### Authorization
- Role: Student (untuk cek status sendiri)
- Role: Superadmin (bisa cek status user lain)

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |

### Query Parameters (Superadmin Only)

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `user_id` | integer | ❌ Tidak | ID user yang akan dicek statusnya |

### Contoh Request

#### 1. Student Check Own Status
```
GET /api/v1/courses/web-development-basics/enrollment-status
```

**Response - Enrolled** (200 OK):
```json
{
  "success": true,
  "message": "Status enrollment berhasil diambil.",
  "data": {
    "status": "active",
    "enrollment": {
      "id": 123,
      "user_id": 5,
      "course_id": 2,
      "status": "active",
      "enrolled_at": "2026-03-06T10:00:00Z",
      "completed_at": null,
      "created_at": "2026-03-06T10:00:00Z",
      "updated_at": "2026-03-06T10:00:00Z",
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "course": {
        "id": 2,
        "title": "Web Development Basics",
        "slug": "web-development-basics",
        "code": "WEB-001"
      }
    }
  }
}
```

**Response - Not Enrolled** (200 OK):
```json
{
  "success": true,
  "message": "Belum terdaftar di course ini.",
  "data": {
    "status": "not_enrolled",
    "enrollment": null
  }
}
```

#### 2. Superadmin Check Other User Status
```
GET /api/v1/courses/web-development-basics/enrollment-status?user_id=10
```

### Catatan Penting
- Endpoint ini untuk cek status enrollment saja (tidak mengubah data)
- Student hanya bisa cek status sendiri
- Superadmin bisa cek status user manapun
- Response `status` bisa: `active`, `pending`, `completed`, `cancelled`, `not_enrolled`

---

## 4. CANCEL ENROLLMENT REQUEST (Student)

### Endpoint
```
POST /api/v1/courses/{course_slug}/cancel
```

### Authorization
- Role: Student (untuk cancel enrollment sendiri)
- Role: Superadmin (bisa cancel enrollment user lain)
- Rate Limit: 5 requests per minute

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |

### Request Body (Superadmin Only)

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `user_id` | integer | ❌ Tidak | ID user yang enrollment-nya akan di-cancel |

### Business Rules
- Hanya enrollment dengan status `pending` yang bisa di-cancel
- Student hanya bisa cancel enrollment sendiri
- Superadmin bisa cancel enrollment user manapun

### Contoh Request

#### 1. Student Cancel Own Enrollment
```json
POST /api/v1/courses/advanced-programming/cancel
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Permintaan enrollment berhasil dibatalkan.",
  "data": {
    "id": 125,
    "user_id": 5,
    "course_id": 4,
    "status": "cancelled",
    "enrolled_at": "2026-03-06T10:10:00Z",
    "completed_at": null,
    "created_at": "2026-03-06T10:10:00Z",
    "updated_at": "2026-03-06T10:30:00Z"
  }
}
```

#### 2. Superadmin Cancel User Enrollment
```json
POST /api/v1/courses/advanced-programming/cancel
{
  "user_id": 10
}
```

### Error Responses

#### Enrollment Not Found
```json
{
  "success": false,
  "message": "Permintaan enrollment tidak ditemukan untuk course ini.",
  "errors": null
}
```

#### Cannot Cancel (Wrong Status)
```json
{
  "success": false,
  "message": "Hanya enrollment dengan status pending yang dapat dibatalkan.",
  "errors": {
    "enrollment": ["Hanya enrollment dengan status pending yang dapat dibatalkan."]
  }
}
```

### Catatan Penting
- Cancel hanya untuk enrollment `pending`
- Setelah di-cancel, status menjadi `cancelled` (final)
- Tidak bisa enroll lagi setelah di-cancel (harus contact admin)

---

## 5. WITHDRAW FROM COURSE (Student)

### Endpoint
```
POST /api/v1/courses/{course_slug}/withdraw
```

### Authorization
- Role: Student (untuk withdraw sendiri)
- Role: Superadmin (bisa withdraw user lain)
- Rate Limit: 5 requests per minute

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |

### Request Body (Superadmin Only)

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `user_id` | integer | ❌ Tidak | ID user yang akan di-withdraw |

### Business Rules
- Hanya enrollment dengan status `active` yang bisa di-withdraw
- Student hanya bisa withdraw enrollment sendiri
- Superadmin bisa withdraw enrollment user manapun
- Progress dan submissions tetap tersimpan

### Contoh Request

#### 1. Student Withdraw from Course
```json
POST /api/v1/courses/web-development-basics/withdraw
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Anda berhasil mengundurkan diri dari course.",
  "data": {
    "id": 123,
    "user_id": 5,
    "course_id": 2,
    "status": "cancelled",
    "enrolled_at": "2026-03-06T10:00:00Z",
    "completed_at": null,
    "created_at": "2026-03-06T10:00:00Z",
    "updated_at": "2026-03-06T11:00:00Z"
  }
}
```

### Error Responses

#### Cannot Withdraw (Wrong Status)
```json
{
  "success": false,
  "message": "Hanya enrollment dengan status active yang dapat di-withdraw.",
  "errors": {
    "enrollment": ["Hanya enrollment dengan status active yang dapat di-withdraw."]
  }
}
```

### Catatan Penting
- Withdraw hanya untuk enrollment `active`
- Setelah withdraw, status menjadi `cancelled` (final)
- Progress dan submissions tidak dihapus (tetap tersimpan)
- Tidak bisa re-enroll setelah withdraw (harus contact admin)

---

## 6. LIST ENROLLMENTS BY COURSE (Manager)

### Endpoint
```
GET /api/v1/courses/{course_slug}/enrollments
```

### Authorization
- Role: Admin, Instructor, Superadmin
- Permission: Harus punya `update` permission pada course

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `course_slug` | string | ✅ Ya | Slug course |

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `per_page` | integer | ❌ Tidak | 15 | Jumlah data per halaman |
| `sort` | string | ❌ Tidak | priority | Field untuk sorting |
| `search` | string | ❌ Tidak | - | Pencarian (name, email) via Meilisearch |
| `filter[status]` | string | ❌ Tidak | - | Filter by status (exact match) |
| `filter[user_id]` | integer | ❌ Tidak | - | Filter by student ID |
| `filter[enrolled_from]` | date | ❌ Tidak | - | Tanggal mulai (YYYY-MM-DD) |
| `filter[enrolled_to]` | date | ❌ Tidak | - | Tanggal akhir (YYYY-MM-DD) |
| `include` | string | ❌ Tidak | - | Relationships: `user`, `course` |

### Allowed Sorts

| Sort | Deskripsi |
|------|-----------|
| `priority` | Default - Pending first, then by creation date |
| `enrolled_at` | Tanggal enrollment |
| `completed_at` | Tanggal completion |
| `created_at` | Tanggal dibuat |

**Catatan**: Tambahkan `-` di depan untuk descending (contoh: `-enrolled_at`)

### Contoh Request

#### 1. Get All Enrollments (Default)
```
GET /api/v1/courses/web-development-basics/enrollments
```

#### 2. Filter Pending Enrollments
```
GET /api/v1/courses/web-development-basics/enrollments?filter[status]=pending
```

#### 3. Search by Name/Email
```
GET /api/v1/courses/web-development-basics/enrollments?search=john
```

#### 4. Filter by Date Range
```
GET /api/v1/courses/web-development-basics/enrollments?filter[enrolled_from]=2026-01-01&filter[enrolled_to]=2026-03-31
```

#### 5. Kombinasi Filter + Search + Sort
```
GET /api/v1/courses/web-development-basics/enrollments?search=john&filter[status]=active&sort=-enrolled_at&per_page=20
```

#### 6. Include Relationships
```
GET /api/v1/courses/web-development-basics/enrollments?include=user,course
```

### Response Format

```json
{
  "success": true,
  "message": "Daftar enrollment course berhasil diambil.",
  "data": [
    {
      "id": 123,
      "user_id": 5,
      "course_id": 2,
      "status": "pending",
      "enrolled_at": "2026-03-06T10:00:00Z",
      "completed_at": null,
      "created_at": "2026-03-06T09:30:00Z",
      "updated_at": "2026-03-06T09:30:00Z",
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "https://example.com/avatars/john.jpg"
      }
    },
    {
      "id": 124,
      "user_id": 8,
      "course_id": 2,
      "status": "active",
      "enrolled_at": "2026-03-05T14:00:00Z",
      "completed_at": null,
      "created_at": "2026-03-05T14:00:00Z",
      "updated_at": "2026-03-05T14:00:00Z",
      "user": {
        "id": 8,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "avatar": null
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 45,
      "last_page": 3,
      "from": 1,
      "to": 15,
      "has_next": true,
      "has_prev": false
    },
    "filtering": {
      "status": null,
      "user_id": null,
      "enrolled_from": null,
      "enrolled_to": null
    }
  }
}
```

### Catatan Penting
- Sort `priority` menampilkan pending enrollments di atas (untuk approval workflow)
- Search menggunakan Meilisearch (fast full-text search)
- Filter `status` exact match: `pending`, `active`, `completed`, `cancelled`
- Include `user` dan `course` untuk mengurangi API calls

---

## 7. LIST ENROLLMENTS (Context-Aware)

### Endpoint
```
GET /api/v1/enrollments
```

### Authorization
- Role: Student, Instructor, Admin, Superadmin
- Behavior berbeda per role

### Role-Based Behavior

| Role | Returns |
|------|---------|
| **Superadmin** | Semua enrollments di sistem dengan full filtering |
| **Admin/Instructor** | Enrollments dari courses yang mereka manage |
| **Student** | Enrollments milik mereka sendiri |

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `per_page` | integer | ❌ Tidak | 15 | Jumlah data per halaman |
| `sort` | string | ❌ Tidak | priority | Field untuk sorting |
| `search` | string | ❌ Tidak | - | Full-text search via Meilisearch |

### Filters (Superadmin)

| Filter | Tipe | Keterangan |
|--------|------|------------|
| `filter[status]` | string | Exact match |
| `filter[user_id]` | integer | Filter by student ID |
| `filter[course_slug]` | string | Filter by course slug |
| `filter[enrolled_from]` | date | Tanggal mulai (YYYY-MM-DD) |
| `filter[enrolled_to]` | date | Tanggal akhir (YYYY-MM-DD) |

### Filters (Admin/Instructor)

| Filter | Tipe | Keterangan |
|--------|------|------------|
| `filter[course_slug]` | string | Filter by specific managed course |
| `filter[status]` | string | Exact match |
| `filter[user_id]` | integer | Filter by student ID |
| `filter[enrolled_from]` | date | Tanggal mulai |
| `filter[enrolled_to]` | date | Tanggal akhir |

### Filters (Student)

| Filter | Tipe | Keterangan |
|--------|------|------------|
| `filter[status]` | string | Exact match |
| `filter[course_slug]` | string | Filter by course slug |
| `filter[enrolled_from]` | date | Tanggal mulai |
| `filter[enrolled_to]` | date | Tanggal akhir |

### Contoh Request

#### 1. Student - Get My Enrollments
```
GET /api/v1/enrollments
```

#### 2. Student - Filter Active Enrollments
```
GET /api/v1/enrollments?filter[status]=active
```

#### 3. Admin - Get Enrollments from Managed Courses
```
GET /api/v1/enrollments?filter[status]=pending&sort=priority
```

#### 4. Superadmin - Get All Enrollments with Filters
```
GET /api/v1/enrollments?filter[course_slug]=web-development-basics&filter[status]=active&search=john
```

### Response Format

Same as endpoint #6 (List Enrollments by Course)

### Error Responses

#### No Managed Courses (Admin/Instructor)
```json
{
  "success": false,
  "message": "Anda tidak mengelola course apapun.",
  "errors": null
}
```

### Catatan Penting
- Endpoint ini context-aware (behavior berbeda per role)
- Student hanya melihat enrollments sendiri
- Admin/Instructor hanya melihat enrollments dari courses yang mereka manage
- Superadmin melihat semua enrollments

---

## 8. APPROVE ENROLLMENT (Manager)

### Endpoint
```
POST /api/v1/enrollments/{enrollment_id}/approve
```

### Authorization
- Role: Admin, Instructor, Superadmin
- Permission: Harus punya `update` permission pada course
- Rate Limit: 5 requests per minute

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `enrollment_id` | integer | ✅ Ya | ID enrollment |

### Business Rules
- Hanya enrollment dengan status `pending` yang bisa di-approve
- User harus punya permission untuk manage course
- Email notification dikirim ke student

### Contoh Request

```
POST /api/v1/enrollments/123/approve
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Permintaan enrollment disetujui.",
  "data": {
    "id": 123,
    "user_id": 5,
    "course_id": 2,
    "status": "active",
    "enrolled_at": "2026-03-06T11:00:00Z",
    "completed_at": null,
    "created_at": "2026-03-06T10:00:00Z",
    "updated_at": "2026-03-06T11:00:00Z"
  }
}
```

### Error Responses

#### Not Authorized
```json
{
  "success": false,
  "message": "Anda tidak memiliki akses untuk melakukan aksi ini.",
  "errors": null
}
```

#### Cannot Approve (Wrong Status)
```json
{
  "success": false,
  "message": "Hanya enrollment dengan status pending yang dapat disetujui.",
  "errors": {
    "enrollment": ["Hanya enrollment dengan status pending yang dapat disetujui."]
  }
}
```

### Catatan Penting
- Approve mengubah status dari `pending` ke `active`
- Student akan menerima email notification
- Student langsung bisa akses course content

---

## 9. DECLINE ENROLLMENT (Manager)

### Endpoint
```
POST /api/v1/enrollments/{enrollment_id}/decline
```

### Authorization
- Role: Admin, Instructor, Superadmin
- Permission: Harus punya `update` permission pada course
- Rate Limit: 5 requests per minute

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `enrollment_id` | integer | ✅ Ya | ID enrollment |

### Business Rules
- Hanya enrollment dengan status `pending` yang bisa di-decline
- User harus punya permission untuk manage course
- Email notification dikirim ke student

### Contoh Request

```
POST /api/v1/enrollments/125/decline
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Permintaan enrollment ditolak.",
  "data": {
    "id": 125,
    "user_id": 7,
    "course_id": 2,
    "status": "cancelled",
    "enrolled_at": "2026-03-06T10:30:00Z",
    "completed_at": null,
    "created_at": "2026-03-06T10:30:00Z",
    "updated_at": "2026-03-06T11:15:00Z"
  }
}
```

### Error Responses

Same as Approve Enrollment

### Catatan Penting
- Decline mengubah status dari `pending` ke `cancelled`
- Student akan menerima email notification
- Status `cancelled` bersifat final

---

## 10. REMOVE USER FROM COURSE (Manager)

### Endpoint
```
POST /api/v1/enrollments/{enrollment_id}/remove
```

### Authorization
- Role: Admin, Instructor, Superadmin
- Permission: Harus punya `update` permission pada course
- Rate Limit: 5 requests per minute

### Path Parameters

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `enrollment_id` | integer | ✅ Ya | ID enrollment |

### Business Rules
- Bisa remove enrollment dengan status `active` atau `pending`
- User harus punya permission untuk manage course
- Status berubah menjadi `cancelled`

### Contoh Request

```
POST /api/v1/enrollments/124/remove
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Peserta berhasil dikeluarkan dari course.",
  "data": {
    "id": 124,
    "user_id": 8,
    "course_id": 2,
    "status": "cancelled",
    "enrolled_at": "2026-03-05T14:00:00Z",
    "completed_at": null,
    "created_at": "2026-03-05T14:00:00Z",
    "updated_at": "2026-03-06T11:30:00Z"
  }
}
```

### Error Responses

#### Cannot Remove (Wrong Status)
```json
{
  "success": false,
  "message": "Hanya enrollment dengan status active atau pending yang dapat dihapus.",
  "errors": {
    "enrollment": ["Hanya enrollment dengan status active atau pending yang dapat dihapus."]
  }
}
```

### Catatan Penting
- Remove bisa untuk status `active` atau `pending`
- Status berubah menjadi `cancelled` (final)
- Progress dan submissions tetap tersimpan
- User tidak bisa re-enroll (harus contact admin)

---

## 11. BULK OPERATIONS (Manager)

### 11.1 Bulk Approve Enrollments

#### Endpoint
```
POST /api/v1/enrollments/approve/bulk
```

#### Authorization
- Role: Admin, Instructor, Superadmin
- Permission: Hanya enrollments yang user punya permission yang akan diproses
- Rate Limit: 5 requests per minute

#### Request Body

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `enrollment_ids` | array | ✅ Ya | array, min:1, max:100 | Array of enrollment IDs |

#### Contoh Request

```json
POST /api/v1/enrollments/approve/bulk
{
  "enrollment_ids": [123, 125, 127]
}
```

#### Response Format

```json
{
  "success": true,
  "message": "Bulk action completed.",
  "data": {
    "processed": [
      {
        "id": 123,
        "user_id": 5,
        "course_id": 2,
        "status": "active",
        "enrolled_at": "2026-03-06T11:00:00Z",
        "completed_at": null,
        "created_at": "2026-03-06T10:00:00Z",
        "updated_at": "2026-03-06T11:00:00Z"
      },
      {
        "id": 125,
        "user_id": 7,
        "course_id": 2,
        "status": "active",
        "enrolled_at": "2026-03-06T11:00:00Z",
        "completed_at": null,
        "created_at": "2026-03-06T10:30:00Z",
        "updated_at": "2026-03-06T11:00:00Z"
      }
    ],
    "failed": [
      {
        "id": 127,
        "reason": "Only pending enrollment requests can be approved."
      }
    ]
  }
}
```

#### Business Rules
- Hanya enrollment `pending` yang bisa di-approve
- Enrollment yang tidak authorized akan di-skip
- Enrollment dengan status salah akan masuk ke `failed`
- Email notification dikirim ke setiap student yang di-approve

---

### 11.2 Bulk Decline Enrollments

#### Endpoint
```
POST /api/v1/enrollments/decline/bulk
```

#### Authorization
Same as Bulk Approve

#### Request Body

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `enrollment_ids` | array | ✅ Ya | array, min:1, max:100 | Array of enrollment IDs |

#### Contoh Request

```json
POST /api/v1/enrollments/decline/bulk
{
  "enrollment_ids": [130, 131, 132]
}
```

#### Response Format

Same structure as Bulk Approve (`data.processed`, `data.failed`)

#### Business Rules
- Hanya enrollment `pending` yang bisa di-decline
- Enrollment yang tidak authorized akan di-skip
- Email notification dikirim ke setiap student yang di-decline

---

### 11.3 Bulk Remove Enrollments

#### Endpoint
```
POST /api/v1/enrollments/remove/bulk
```

#### Authorization
Same as Bulk Approve

#### Request Body

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `enrollment_ids` | array | ✅ Ya | array, min:1, max:100 | Array of enrollment IDs |

#### Contoh Request

```json
POST /api/v1/enrollments/remove/bulk
{
  "enrollment_ids": [140, 141, 142]
}
```

#### Response Format

Same structure as Bulk Approve (`data.processed`, `data.failed`)

#### Business Rules
- Bisa remove enrollment `active` atau `pending`
- Enrollment yang tidak authorized akan di-skip
- Status berubah menjadi `cancelled`

---

## Catatan Umum

### Authorization Matrix

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| Enroll in Course | ✅ | ❌ | ❌ | ❌ |
| Check Enrollment Status | ✅ (own) | ❌ | ❌ | ✅ (all) |
| Cancel Enrollment | ✅ (own, pending) | ❌ | ❌ | ✅ (all) |
| Withdraw from Course | ✅ (own, active) | ❌ | ❌ | ✅ (all) |
| List Enrollments | ✅ (own) | ✅ (managed courses) | ✅ (managed courses) | ✅ (all) |
| Approve Enrollment | ❌ | ✅ (managed courses) | ✅ (managed courses) | ✅ (all) |
| Decline Enrollment | ❌ | ✅ (managed courses) | ✅ (managed courses) | ✅ (all) |
| Remove from Course | ❌ | ✅ (managed courses) | ✅ (managed courses) | ✅ (all) |
| Bulk Operations | ❌ | ✅ (managed courses) | ✅ (managed courses) | ✅ (all) |

### Response Format Standar

#### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "meta": { ... },
  "errors": null
}
```

#### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "data": null,
  "errors": {
    "field_name": ["Error detail"]
  }
}
```

### HTTP Status Codes
- `200` - Success (GET, POST untuk state changes)
- `201` - Created (POST untuk enroll)
- `400` - Bad Request (business rule violation)
- `401` - Unauthorized (tidak login)
- `403` - Forbidden (tidak punya permission)
- `404` - Not Found (enrollment/course tidak ditemukan)
- `422` - Validation Error (input tidak valid)
- `429` - Too Many Requests (rate limit exceeded)
- `500` - Server Error

### Rate Limiting

| Endpoint Group | Limit |
|----------------|-------|
| State-changing (enroll, cancel, withdraw, approve, decline, remove, bulk) | 5 requests/minute |
| Read-only (list, status) | 60 requests/minute |

**Rate Limit Headers**:
```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 4
X-RateLimit-Reset: 1737364800
```

---

## Tips untuk UI/UX

### 1. Student Enrollment Flow

#### Enrollment Button States
```javascript
// Check enrollment status first
GET /api/v1/courses/{slug}/enrollment-status

// Button states based on status:
if (status === 'not_enrolled') {
  showButton('Enroll Now', 'primary');
} else if (status === 'pending') {
  showButton('Pending Approval', 'warning', disabled=true);
  showAction('Cancel Request', 'secondary');
} else if (status === 'active') {
  showButton('Continue Learning', 'success');
  showAction('Withdraw', 'danger');
} else if (status === 'completed') {
  showButton('Review Course', 'info');
} else if (status === 'cancelled') {
  showMessage('Contact admin to re-enroll');
}
```

#### Enrollment Modal
```javascript
// For key-based courses
if (course.enrollment_type === 'key_based') {
  showModal({
    title: 'Enter Enrollment Key',
    fields: ['enrollment_key'],
    submitText: 'Enroll'
  });
}

// For approval courses
if (course.enrollment_type === 'approval') {
  showModal({
    title: 'Request Enrollment',
    message: 'Your request will be reviewed by course admin',
    submitText: 'Send Request'
  });
}

// For auto-accept courses
if (course.enrollment_type === 'auto_accept') {
  showConfirmation({
    title: 'Enroll in Course',
    message: 'You will be enrolled immediately',
    submitText: 'Enroll Now'
  });
}
```

### 2. Manager Enrollment List

#### Table Columns
- Avatar + Name (clickable → user profile)
- Email
- Status (badge dengan warna)
- Enrolled Date
- Actions (Approve/Decline/Remove)

#### Status Badge Colors
```css
.badge-pending { background: #FFC107; color: #000; }
.badge-active { background: #4CAF50; color: #FFF; }
.badge-completed { background: #2196F3; color: #FFF; }
.badge-cancelled { background: #F44336; color: #FFF; }
```

#### Filter Panel
```javascript
// Filters
- Status dropdown (All, Pending, Active, Completed, Cancelled)
- Date range picker (Enrolled From - To)
- Search box (Name/Email) with debounce

// Sort options
- Priority (Pending first) - Default
- Newest First
- Oldest First
- Name A-Z
- Name Z-A
```

#### Bulk Actions
```javascript
// Checkbox selection
- Select All checkbox
- Individual checkboxes per row
- Selected count indicator

// Bulk action dropdown
- Approve Selected (only for pending)
- Decline Selected (only for pending)
- Remove Selected (for active/pending)

// Confirmation modal
showModal({
  title: 'Confirm Bulk Action',
  message: `Are you sure you want to approve ${selectedCount} enrollments?`,
  actions: ['Cancel', 'Confirm']
});
```

### 3. Enrollment Detail View

#### Student View (My Enrollments)
```javascript
// Card layout
- Course thumbnail
- Course title + code
- Status badge
- Enrolled date
- Progress bar (if active)
- Actions:
  * Continue Learning (if active)
  * Cancel Request (if pending)
  * Withdraw (if active)
```

#### Manager View (Course Enrollments)
```javascript
// Detailed view
- Student info (avatar, name, email)
- Enrollment info (date, status)
- Progress info (completed units, assignments, quizzes)
- Activity timeline
- Actions:
  * Approve (if pending)
  * Decline (if pending)
  * Remove (if active/pending)
  * View Student Profile
  * Send Message
```

### 4. Notifications

#### Email Notifications
- **Enrollment Created (Approval)**: "Your enrollment request has been submitted"
- **Enrollment Approved**: "Your enrollment has been approved"
- **Enrollment Declined**: "Your enrollment request has been declined"
- **Enrollment Removed**: "You have been removed from the course"

#### In-App Notifications
```javascript
// Toast notifications
toast.success('Enrolled successfully!');
toast.warning('Enrollment pending approval');
toast.error('Enrollment key is invalid');
toast.info('Enrollment request cancelled');
```

### 5. Search & Filter Implementation

#### Search with Debounce
```javascript
const searchEnrollments = debounce((query) => {
  fetch(`/api/v1/enrollments?search=${query}`)
    .then(response => response.json())
    .then(data => updateTable(data));
}, 300);
```

#### Filter State Management
```javascript
const filters = {
  status: null,
  course_slug: null,
  enrolled_from: null,
  enrolled_to: null,
  search: ''
};

// Build query string
const queryString = Object.entries(filters)
  .filter(([key, value]) => value !== null && value !== '')
  .map(([key, value]) => {
    if (key === 'search') return `search=${value}`;
    return `filter[${key}]=${value}`;
  })
  .join('&');
```

### 6. Pagination

#### Pagination Component
```javascript
// Show pagination info
"Showing 1-15 of 45 enrollments"

// Pagination controls
- Previous button (disabled if first page)
- Page numbers (show 5 pages max)
- Next button (disabled if last page)
- Per page dropdown (15, 30, 50, 100)
```

### 7. Empty States

#### No Enrollments
```
┌─────────────────────────────────────┐
│         📚                          │
│   No Enrollments Yet                │
│   Start by enrolling in a course    │
│   [Browse Courses]                  │
└─────────────────────────────────────┘
```

#### No Pending Approvals
```
┌─────────────────────────────────────┐
│         ✅                          │
│   All Caught Up!                    │
│   No pending enrollment requests    │
└─────────────────────────────────────┘
```

#### No Search Results
```
┌─────────────────────────────────────┐
│         🔍                          │
│   No Results Found                  │
│   Try adjusting your search         │
│   [Clear Filters]                   │
└─────────────────────────────────────┘
```

---

## Workflow Rekomendasi

### Student Enrollment Workflow

```
1. Browse Courses
   ↓
2. Click "Enroll" on Course Card
   ↓
3. Check Enrollment Type:
   - Auto Accept → Confirm → Enrolled ✅
   - Key Based → Enter Key → Enrolled ✅
   - Approval → Send Request → Pending ⏳
   ↓
4. If Pending:
   - Wait for approval
   - Can cancel request
   - Receive email when approved/declined
   ↓
5. If Approved:
   - Start learning
   - Can withdraw anytime
```

### Manager Approval Workflow

```
1. Navigate to Course Management
   ↓
2. Click "Enrollments" tab
   ↓
3. Filter by "Pending" status
   ↓
4. Review enrollment requests:
   - View student profile
   - Check prerequisites
   - Decide: Approve or Decline
   ↓
5. Single Action:
   - Click "Approve" or "Decline" button
   - Confirm action
   - Student receives email
   ↓
6. Bulk Action (for multiple):
   - Select multiple enrollments
   - Choose "Approve Selected" or "Decline Selected"
   - Confirm bulk action
   - All students receive email
```

### Manager Remove Student Workflow

```
1. Navigate to Course Enrollments
   ↓
2. Find student to remove
   ↓
3. Click "Remove" button
   ↓
4. Confirmation modal:
   - "Are you sure you want to remove [Student Name]?"
   - "This action cannot be undone"
   - [Cancel] [Remove]
   ↓
5. Student removed (status → cancelled)
   ↓
6. Student loses access to course
```

---

## Error Handling

### Common Errors

#### 1. Already Enrolled
```json
{
  "success": false,
  "message": "Anda sudah terdaftar di course ini.",
  "errors": {
    "enrollment": ["Anda sudah terdaftar di course ini."]
  }
}
```

**UI Action**: Show message, redirect to course page

#### 2. Invalid Enrollment Key
```json
{
  "success": false,
  "message": "Kode enrollment tidak valid.",
  "errors": {
    "enrollment_key": ["Kode enrollment tidak valid."]
  }
}
```

**UI Action**: Show error on input field, allow retry

#### 3. Cannot Cancel (Wrong Status)
```json
{
  "success": false,
  "message": "Hanya enrollment dengan status pending yang dapat dibatalkan.",
  "errors": {
    "enrollment": ["Hanya enrollment dengan status pending yang dapat dibatalkan."]
  }
}
```

**UI Action**: Show error message, refresh enrollment status

#### 4. Not Authorized
```json
{
  "success": false,
  "message": "Anda tidak memiliki akses untuk melakukan aksi ini.",
  "errors": null
}
```

**UI Action**: Show error message, hide action buttons

#### 5. Rate Limit Exceeded
```json
{
  "success": false,
  "message": "Terlalu banyak percobaan. Silakan coba lagi dalam beberapa saat.",
  "errors": null
}
```

**UI Action**: Show countdown timer, disable buttons temporarily

#### 6. Enrollment Not Found
```json
{
  "success": false,
  "message": "Permintaan enrollment tidak ditemukan untuk course ini.",
  "errors": null
}
```

**UI Action**: Show error message, redirect to course list

---

## Security Considerations

### 1. Rate Limiting
- State-changing operations: 5 requests/minute
- Prevent spam enrollments
- Prevent bulk action abuse

### 2. Authorization
- Student can only manage own enrollments
- Manager can only manage enrollments from courses they manage
- Superadmin has full access

### 3. Validation
- Enrollment key validation (case-sensitive)
- Status transition validation (prevent invalid state changes)
- Permission validation (check course management permission)

### 4. Audit Log
- Log all enrollment state changes
- Track who approved/declined/removed enrollments
- Track bulk operations

### 5. Email Verification
- Send confirmation emails for all state changes
- Include enrollment details in email
- Provide links to relevant pages

---

## Performance Optimization

### 1. Search Performance
- Use Meilisearch for fast full-text search
- Index: user name, email, course title
- Update index on enrollment changes

### 2. Pagination
- Default: 15 items per page
- Max: 100 items per page
- Use cursor-based pagination for large datasets

### 3. Caching
- Cache enrollment counts per course
- Cache user enrollment status per course
- Invalidate cache on enrollment changes

### 4. Database Optimization
- Index on: user_id, course_id, status, enrolled_at
- Composite index: (course_id, status) for filtering
- Use eager loading for relationships (user, course)

---

## Testing Checklist

### Student Flow
- ✅ Enroll in auto-accept course
- ✅ Enroll in key-based course (valid key)
- ✅ Enroll in key-based course (invalid key)
- ✅ Enroll in approval course
- ✅ Cancel pending enrollment
- ✅ Withdraw from active enrollment
- ✅ Check enrollment status
- ✅ View my enrollments

### Manager Flow
- ✅ List enrollments by course
- ✅ Filter enrollments by status
- ✅ Search enrollments by name/email
- ✅ Approve pending enrollment
- ✅ Decline pending enrollment
- ✅ Remove active enrollment
- ✅ Bulk approve enrollments
- ✅ Bulk decline enrollments
- ✅ Bulk remove enrollments

### Edge Cases
- ✅ Enroll when already enrolled (should fail)
- ✅ Cancel non-pending enrollment (should fail)
- ✅ Withdraw non-active enrollment (should fail)
- ✅ Approve non-pending enrollment (should fail)
- ✅ Manager approve enrollment from non-managed course (should fail)
- ✅ Rate limit exceeded (should return 429)

---

## Changelog

### Version 1.0 (6 Maret 2026)
- Initial release
- Student enrollment operations (enroll, cancel, withdraw)
- Manager enrollment operations (approve, decline, remove)
- Bulk operations (approve, decline, remove)
- Context-aware list enrollments
- Full filtering and search support
- Rate limiting implementation

---

**Versi**: 1.0  
**Terakhir Update**: 6 Maret 2026  
**Kontak**: Backend Team
