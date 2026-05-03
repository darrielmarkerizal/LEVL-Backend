# 🗑️ Trash Bin – Panduan Endpoint

> **Base URL:** `{{url}}/api/v1`

---

## Tentang Token per Role

| Folder | Token yang Digunakan |
|--------|----------------------|
| Admin / Superadmin (akses penuh semua item) | `{{access_token_admin}}` |
| Instructor (akses item milik sendiri & kursus yang dikelola) | `{{access_token_instructor}}` |

> **Catatan:** Student tidak memiliki akses ke endpoint Trash Bin.

---

## Konsep Penting

### Resource Type yang Didukung
| Value | Keterangan |
|-------|------------|
| `course` | Kursus |
| `unit` | Unit dalam kursus |
| `lesson` | Pelajaran dalam unit |
| `quiz` | Kuis dalam unit |
| `assignment` | Tugas dalam unit |
| `user` | Akun pengguna |
| `badge` | Badge gamifikasi |
| `news` | Berita/konten |

### Hierarki Penghapusan (Cascade)
Ketika sebuah **Course** dihapus, semua Unit, Lesson, Quiz, dan Assignment di dalamnya ikut masuk ke trash secara otomatis dalam satu **group** (ditandai dengan `group_uuid` yang sama). Restore atau force delete pada item root akan memproses seluruh group sekaligus.

### Retensi 30 Hari
Item di trash akan otomatis dihapus permanen setelah **30 hari** via scheduled command `trash:purge-expired`.

### Operasi Async
Force delete pada item root yang memiliki banyak child akan diproses secara **async** (queued job). Response akan mengembalikan `"queued": true` dengan HTTP 202.

---

## Alur Trash Bin

```
=== ADMIN / SUPERADMIN ===
1. Lihat daftar item di trash                   → GET    /trash-bins
2. Lihat tipe sumber yang tersedia              → GET    /trash-bins/source-types
3. Lihat semua tipe sumber (master data)        → GET    /master-data/trash-bin-source-types
4. Restore satu item                            → PATCH  /trash-bins/:trashBinId
5. Hapus permanen satu item                     → DELETE /trash-bins/:trashBinId
6. Restore semua item (opsional filter tipe)    → PATCH  /trash-bins
7. Hapus permanen semua item (opsional filter)  → DELETE /trash-bins
8. Bulk restore beberapa item sekaligus         → PATCH  /trash-bins/bulk/restore
9. Bulk hapus permanen beberapa item            → POST   /trash-bins/bulk
```

---

## ── TRASH BIN ──

---

## 1. [GET] Daftar Item di Trash

**Endpoint:**
```
GET {{url}}/api/v1/trash-bins
```

**Authorization:** Bearer `{{access_token_admin}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman (maks 100) |
| `search` | `pengantar` | Cari berdasarkan judul item atau nama penghapus |
| `filter[resource_type]` | `lesson` | Filter berdasarkan tipe resource |
| `filter[deleted_by]` | `4` | Filter berdasarkan ID user yang menghapus |
| `filter[group_uuid]` | `uuid-string` | Filter berdasarkan grup penghapusan |
| `sort` | `-deleted_at` | Urutan: `deleted_at`, `-deleted_at`, `expires_at`, `created_at` |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar trash bin berhasil diambil.",
  "data": [
    {
      "id": 12,
      "resource_type": "lesson",
      "resource_label": "Lesson",
      "trashable_type": "Modules\\Schemes\\Models\\Lesson",
      "trashable_id": 340,
      "group_uuid": "550e8400-e29b-41d4-a716-446655440000",
      "root_resource_type": "Modules\\Schemes\\Models\\Unit",
      "root_resource_id": 76,
      "original_status": "published",
      "trashed_status": "draft",
      "deleted_by": 4,
      "deleted_by_user": {
        "id": 4,
        "name": "Budi Santoso",
        "username": "budi.santoso"
      },
      "deleted_at": "2026-05-01T10:00:00.000000Z",
      "expires_at": "2026-05-31T10:00:00.000000Z",
      "metadata": {
        "title": "Memahami Prosedur Kerja",
        "course_id": 26,
        "original_order": 3
      },
      "restored_at": null,
      "force_deleted_at": null,
      "created_at": "2026-05-01T10:00:00.000000Z",
      "updated_at": "2026-05-01T10:00:00.000000Z"
    },
    {
      "id": 11,
      "resource_type": "course",
      "resource_label": "Course",
      "trashable_type": "Modules\\Schemes\\Models\\Course",
      "trashable_id": 26,
      "group_uuid": "660e8400-e29b-41d4-a716-446655440001",
      "root_resource_type": "Modules\\Schemes\\Models\\Course",
      "root_resource_id": 26,
      "original_status": "published",
      "trashed_status": "archived",
      "deleted_by": 2,
      "deleted_by_user": {
        "id": 2,
        "name": "Admin LMS",
        "username": "admin.lms"
      },
      "deleted_at": "2026-04-30T08:00:00.000000Z",
      "expires_at": "2026-05-30T08:00:00.000000Z",
      "metadata": {
        "title": "Manajemen Proyek Sesuai Standar Industri",
        "course_id": 26,
        "original_order": null
      },
      "restored_at": null,
      "force_deleted_at": null,
      "created_at": "2026-04-30T08:00:00.000000Z",
      "updated_at": "2026-04-30T08:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 38
  },
  "errors": null
}
```

---

## 2. [GET] Tipe Sumber yang Tersedia

**Endpoint:**
```
GET {{url}}/api/v1/trash-bins/source-types
```

**Authorization:** Bearer `{{access_token_admin}}`

> Mengembalikan daftar `resource_type` yang **saat ini ada** di trash bin milik user (sesuai akses role). Berguna untuk mengisi dropdown filter di frontend.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Tipe sumber berhasil diambil.",
  "data": [
    { "value": "assignment", "label": "Assignment" },
    { "value": "course", "label": "Course" },
    { "value": "lesson", "label": "Lesson" },
    { "value": "quiz", "label": "Quiz" },
    { "value": "unit", "label": "Unit" }
  ],
  "meta": null,
  "errors": null
}
```

---

## 3. [GET] Semua Tipe Sumber (Master Data)

**Endpoint:**
```
GET {{url}}/api/v1/master-data/trash-bin-source-types
```

**Authorization:** Bearer `{{access_token_admin}}`

> Mengembalikan **semua** tipe resource yang didukung sistem, terlepas dari apakah ada item di trash atau tidak. Berguna untuk filter statis di frontend.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Tipe sumber berhasil diambil.",
  "data": [
    { "value": "assignment", "label": "Assignment" },
    { "value": "badge", "label": "Badge" },
    { "value": "course", "label": "Course" },
    { "value": "lesson", "label": "Lesson" },
    { "value": "news", "label": "News" },
    { "value": "quiz", "label": "Quiz" },
    { "value": "unit", "label": "Unit" },
    { "value": "user", "label": "User" }
  ],
  "meta": null,
  "errors": null
}
```

---

## 4. [PATCH] Restore Satu Item

**Endpoint:**
```
PATCH {{url}}/api/v1/trash-bins/:trashBinId
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `trashBinId` | `12` | ID trash bin yang ingin di-restore |

> Jika item yang di-restore adalah **root** dari sebuah group (misal: Course), maka seluruh child dalam group yang sama (Unit, Lesson, Quiz, Assignment) akan ikut di-restore. Status original item akan dikembalikan.

**Contoh Respons (200 OK) — Langsung selesai:**
```json
{
  "success": true,
  "message": "Item berhasil di-restore.",
  "data": null,
  "meta": null,
  "errors": null
}
```

**Contoh Respons (202 Accepted) — Diproses async (item root dengan banyak child):**
```json
{
  "success": true,
  "message": "Restore sedang diproses.",
  "data": {
    "queued": true,
    "trash_bin_id": 11,
    "group_uuid": "660e8400-e29b-41d4-a716-446655440001",
    "group_items": 15,
    "resource_type": "course"
  },
  "meta": null,
  "errors": null
}
```

---

## 5. [DELETE] Hapus Permanen Satu Item

**Endpoint:**
```
DELETE {{url}}/api/v1/trash-bins/:trashBinId
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `trashBinId` | `12` | ID trash bin yang ingin dihapus permanen |

> Menghapus item secara **permanen** dari database beserta semua media terkait. Jika item adalah root group, seluruh child ikut dihapus. Operasi ini **tidak dapat dibatalkan**.

**Contoh Respons (200 OK) — Langsung selesai:**
```json
{
  "success": true,
  "message": "Item berhasil dihapus permanen.",
  "data": null,
  "meta": null,
  "errors": null
}
```

**Contoh Respons (202 Accepted) — Diproses async:**
```json
{
  "success": true,
  "message": "Penghapusan permanen sedang diproses.",
  "data": {
    "queued": true,
    "trash_bin_id": 11,
    "group_uuid": "660e8400-e29b-41d4-a716-446655440001",
    "group_items": 15,
    "resource_type": "course"
  },
  "meta": null,
  "errors": null
}
```

---

## 6. [PATCH] Restore Semua Item

**Endpoint:**
```
PATCH {{url}}/api/v1/trash-bins
```

**Authorization:** Bearer `{{access_token_admin}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `resource_type` | `lesson` | Hanya restore item dengan tipe ini. Jika kosong, semua item di-restore. |

> Hanya bisa diakses oleh **Superadmin** dan **Admin**. Instructor tidak diizinkan.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Semua item berhasil di-restore.",
  "data": {
    "queued": false,
    "resource_type": "lesson",
    "count": 8
  },
  "meta": null,
  "errors": null
}
```

---

## 7. [DELETE] Hapus Permanen Semua Item

**Endpoint:**
```
DELETE {{url}}/api/v1/trash-bins
```

**Authorization:** Bearer `{{access_token_admin}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `resource_type` | `quiz` | Hanya hapus permanen item dengan tipe ini. Jika kosong, semua item dihapus. |

> Hanya bisa diakses oleh **Superadmin** dan **Admin**. Operasi ini **tidak dapat dibatalkan**.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Semua item berhasil dihapus permanen.",
  "data": {
    "queued": false,
    "resource_type": "quiz",
    "count": 5
  },
  "meta": null,
  "errors": null
}
```

---

## 8. [PATCH] Bulk Restore

**Endpoint:**
```
PATCH {{url}}/api/v1/trash-bins/bulk/restore
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
```json
{
  "ids": [10, 11, 12]
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `ids` | array of integer | ✅ | Daftar ID trash bin yang ingin di-restore (min 1, tidak boleh duplikat, harus ada di tabel) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Item berhasil di-restore secara massal.",
  "data": {
    "queued": false,
    "ids": [10, 11, 12],
    "count": 3
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons (422 Unprocessable) — Validasi gagal:**
```json
{
  "success": false,
  "message": "The ids field is required.",
  "data": null,
  "meta": null,
  "errors": {
    "ids": ["The ids field is required."]
  }
}
```

---

## 9. [POST] Bulk Hapus Permanen

**Endpoint:**
```
POST {{url}}/api/v1/trash-bins/bulk
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
```json
{
  "ids": [10, 11, 12]
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `ids` | array of integer | ✅ | Daftar ID trash bin yang ingin dihapus permanen (min 1, tidak boleh duplikat, harus ada di tabel) |

> Operasi ini **tidak dapat dibatalkan**. Semua media terkait juga akan dihapus.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Item berhasil dihapus permanen secara massal.",
  "data": {
    "queued": false,
    "ids": [10, 11, 12],
    "count": 3
  },
  "meta": null,
  "errors": null
}
```

---

## Struktur Objek TrashBin

| Field | Tipe | Keterangan |
|-------|------|------------|
| `id` | integer | ID trash bin |
| `resource_type` | string | Tipe resource: `course`, `unit`, `lesson`, `quiz`, `assignment`, `user`, `badge`, `news` |
| `resource_label` | string | Label terbaca untuk `resource_type` |
| `trashable_type` | string | Nama class model (fully qualified) |
| `trashable_id` | integer | ID record asli di tabel sumber |
| `group_uuid` | string | UUID grup penghapusan (cascade) |
| `root_resource_type` | string | Class model root dari grup ini |
| `root_resource_id` | integer | ID root dari grup ini |
| `original_status` | string\|null | Status sebelum dihapus (misal: `published`) |
| `trashed_status` | string\|null | Status saat dihapus (misal: `draft`, `archived`) |
| `deleted_by` | integer\|null | ID user yang menghapus |
| `deleted_by_user` | object\|null | Data user penghapus: `id`, `name`, `username` |
| `deleted_at` | datetime | Waktu dihapus |
| `expires_at` | datetime | Waktu kadaluarsa (30 hari setelah `deleted_at`) |
| `metadata` | object | Data tambahan: `title`, `course_id`, `original_order` |
| `restored_at` | datetime\|null | Waktu di-restore (jika sudah) |
| `force_deleted_at` | datetime\|null | Waktu dihapus permanen (jika sudah) |
| `created_at` | datetime | Waktu record dibuat |
| `updated_at` | datetime | Waktu record terakhir diperbarui |

---

## Catatan Penting

> **Akses berbasis role:**
> - **Superadmin & Admin** — melihat dan mengelola semua item di trash.
> - **Instructor** — hanya melihat dan mengelola item yang dihapus oleh dirinya sendiri, atau item yang terkait dengan kursus yang ia kelola.

> **Restore otomatis mengembalikan status original** — jika sebuah Lesson berstatus `published` sebelum dihapus, setelah di-restore statusnya akan kembali ke `published`. Urutan (`order`) juga dikembalikan ke posisi semula.

> **Group cascade** — menghapus atau me-restore sebuah Course akan memproses seluruh Unit, Lesson, Quiz, dan Assignment dalam group yang sama secara otomatis.

> **Async untuk operasi besar** — force delete pada item root dengan banyak child akan diproses di background queue (`trash` queue). Pantau status via response `"queued": true`.

> **Purge otomatis** — scheduled command `trash:purge-expired` berjalan secara berkala untuk menghapus permanen item yang sudah melewati masa retensi 30 hari.
