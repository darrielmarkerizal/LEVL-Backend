# Trash API Documentation

Dokumentasi ini menjelaskan seluruh API pada modul Trash, termasuk endpoint, parameter, request body, format response, permission, pagination, search, sorting, filtering, serta dukungan include.

## 1. Informasi Umum

- Base URL prefix: `/api/v1`
- Middleware: `auth:api`
- Role middleware route: `role:Admin|Superadmin|Instructor`
- Content-Type request: `application/json`
- Format response standar:

```json
{
  "success": true,
  "message": "...",
  "data": {},
  "meta": null,
  "errors": null
}
```

Catatan akses service layer:
- Superadmin: akses seluruh data trash dan aksi global (`restoreAll`, `forceDeleteAll`).
- Admin/Instructor: akses item milik sendiri (`deleted_by`) atau item terkait course yang dikelola/diampu.

## 2. Daftar Endpoint

1. `GET /api/v1/trash-bins`
2. `GET /api/v1/trash-bins/source-types`
3. `GET /api/v1/master-data/trash-bin-source-types`
4. `PATCH /api/v1/trash-bins/{trashBinId}`
5. `DELETE /api/v1/trash-bins/{trashBinId}`
6. `PATCH /api/v1/trash-bins`
7. `DELETE /api/v1/trash-bins`
8. `PATCH /api/v1/trash-bins/bulk/restore`
9. `POST /api/v1/trash-bins/bulk`

---

## 3. Endpoint Detail

### 3.1 GET /api/v1/trash-bins
Mengambil daftar item trash dengan pagination, search, sorting, dan filter.

### Field Tambahan di Response Item
- `resource_label` (string): label manusiawi dari `resource_type` (localized).

### Query Params
- `page` (optional, integer, min 1): halaman data.
- `per_page` (optional, integer, min 1, max 100, default 15): jumlah data per halaman.
- `search` (optional, string): pencarian full-text + `metadata.title`.
- `sort` (optional, string): field sort. Prefix `-` untuk descending.
- `filter[resource_type]` (optional, string)
- `filter[trashable_type]` (optional, string)
- `filter[group_uuid]` (optional, string)
- `filter[deleted_by]` (optional, integer)
- `filter[root_resource_type]` (optional, string)
- `filter[root_resource_id]` (optional, integer)

### Allowed Sort
- `id`
- `resource_type`
- `trashable_type`
- `deleted_at`
- `expires_at`
- `created_at`
- `updated_at`

Default sort: `-deleted_at`

### Allowed Filter + Nilai

1. `filter[resource_type]`
- Nilai enum yang didukung:
  - `assignment`
  - `badge`
  - `course`
  - `lesson`
  - `news`
  - `quiz`
  - `unit`
  - `user`

2. `filter[trashable_type]`
- Nilai class name (FQCN) yang didukung:
  - `Modules\\Learning\\Models\\Assignment`
  - `Modules\\Gamification\\Models\\Badge`
  - `Modules\\Schemes\\Models\\Course`
  - `Modules\\Schemes\\Models\\Lesson`
  - `Modules\\Content\\Models\\News`
  - `Modules\\Learning\\Models\\Quiz`
  - `Modules\\Schemes\\Models\\Unit`
  - `Modules\\Auth\\Models\\User`

3. `filter[group_uuid]`
- Nilai: UUID group cascade delete (contoh: `3a0a14ed-3f64-4d2a-8a1a-c1f0d33d1b39`).

4. `filter[deleted_by]`
- Nilai: ID user integer.

5. `filter[root_resource_type]`
- Nilai class name (FQCN) root resource (set yang sama dengan `trashable_type`).

6. `filter[root_resource_id]`
- Nilai: ID integer root resource.

### Include
- Parameter `include` tidak didukung pada endpoint ini.
- `allowedIncludes()` tidak didefinisikan.

### Contoh Request
```http
GET /api/v1/trash-bins?per_page=20&search=algoritma&sort=-deleted_at&filter[resource_type]=quiz
Authorization: Bearer <token>
```

### Contoh Response
```json
{
  "success": true,
  "message": "Item trash berhasil diambil.",
  "data": [
    {
      "id": 12,
      "resource_type": "quiz",
      "resource_label": "Quiz",
      "trashable_type": "Modules\\Learning\\Models\\Quiz",
      "trashable_id": 42,
      "group_uuid": "3a0a14ed-3f64-4d2a-8a1a-c1f0d33d1b39",
      "root_resource_type": "Modules\\Schemes\\Models\\Course",
      "root_resource_id": 10,
      "original_status": "published",
      "trashed_status": "archived",
      "deleted_by": 7,
      "deleted_at": "2026-03-10T08:10:00.000000Z",
      "expires_at": "2026-04-09T08:10:00.000000Z",
      "metadata": {
        "title": "Quiz Final",
        "course_id": 10
      },
      "created_at": "2026-03-10T08:10:00.000000Z",
      "updated_at": "2026-03-10T08:10:00.000000Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 1,
      "last_page": 1,
      "from": 1,
      "to": 1,
      "has_next": false,
      "has_prev": false
    },
    "sorting": {
      "sort_by": "-deleted_at",
      "sort_order": "asc"
    },
    "search": {
      "query": "algoritma"
    },
    "filtering": {
      "resource_type": "quiz"
    }
  },
  "errors": null
}
```

---

### 3.2 GET /api/v1/trash-bins/source-types
Mengambil tipe source dinamis berdasarkan data trash yang memang tersedia dan bisa diakses user.

### Query Params
- Tidak ada.

### Include
- Tidak didukung.

### Response Data
Array object:
- `value` (string)
- `label` (string)

### Contoh Response
```json
{
  "success": true,
  "message": "Tipe sumber trash bin berhasil diambil.",
  "data": [
    { "value": "quiz", "label": "Quiz" },
    { "value": "assignment", "label": "Assignment" }
  ],
  "meta": null,
  "errors": null
}
```

---

### 3.3 GET /api/v1/master-data/trash-bin-source-types
Mengambil master source types statis modul trash (tidak bergantung isi tabel trash).

### Query Params
- Tidak ada.

### Include
- Tidak didukung.

### Response Data
Array object:
- `value` (string)
- `label` (string)

### Nilai Master
- `assignment`
- `badge`
- `course`
- `lesson`
- `news`
- `quiz`
- `unit`
- `user`

### Contoh Response
```json
{
  "success": true,
  "message": "Tipe sumber trash bin berhasil diambil.",
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

### 3.4 PATCH /api/v1/trash-bins/{trashBinId}
Restore satu item trash (dengan cascade group jika item adalah root group).

### Path Param
- `trashBinId` (required, integer, exists di `trash_bins.id`).

### Query Params
- Tidak ada.

### Include
- Tidak didukung.

### Contoh Response
```json
{
  "success": true,
  "message": "Item trash berhasil dipulihkan.",
  "data": null,
  "meta": null,
  "errors": null
}
```

---

### 3.5 DELETE /api/v1/trash-bins/{trashBinId}
Hapus permanen satu item trash (dengan cascade group jika item adalah root group).

### Path Param
- `trashBinId` (required, integer, exists di `trash_bins.id`).

### Query Params
- Tidak ada.

### Include
- Tidak didukung.

### Contoh Response
```json
{
  "success": true,
  "message": "Item trash berhasil dihapus permanen.",
  "data": null,
  "meta": null,
  "errors": null
}
```

---

### 3.6 PATCH /api/v1/trash-bins
Restore semua item trash. Hanya Superadmin.

### Query Params
- `resource_type` (optional, string): filter restore global per tipe.

### Nilai resource_type
- `assignment`, `badge`, `course`, `lesson`, `news`, `quiz`, `unit`, `user`

### Include
- Tidak didukung.

### Contoh Response
```json
{
  "success": true,
  "message": "Berhasil memulihkan 12 item trash.",
  "data": {
    "restored": 12
  },
  "meta": null,
  "errors": null
}
```

---

### 3.7 DELETE /api/v1/trash-bins
Hapus permanen semua item trash. Hanya Superadmin.

### Query Params
- `resource_type` (optional, string): filter delete global per tipe.

### Nilai resource_type
- `assignment`, `badge`, `course`, `lesson`, `news`, `quiz`, `unit`, `user`

### Include
- Tidak didukung.

### Contoh Response
```json
{
  "success": true,
  "message": "Berhasil menghapus permanen 8 item trash.",
  "data": {
    "deleted": 8
  },
  "meta": null,
  "errors": null
}
```

---

### 3.8 PATCH /api/v1/trash-bins/bulk/restore
Restore banyak item trash berdasarkan IDs.

### Body Params
- `ids` (required, array, min 1)
- `ids.*` (required, integer, distinct, exists: `trash_bins.id`)

### Include
- Tidak didukung.

### Contoh Request
```json
{
  "ids": [1, 2, 3]
}
```

### Contoh Response
```json
{
  "success": true,
  "message": "Berhasil memulihkan 3 item trash terpilih.",
  "data": {
    "restored": 3
  },
  "meta": null,
  "errors": null
}
```

---

### 3.9 POST /api/v1/trash-bins/bulk
Hapus permanen banyak item trash berdasarkan IDs.

### Body Params
- `ids` (required, array, min 1)
- `ids.*` (required, integer, distinct, exists: `trash_bins.id`)

### Include
- Tidak didukung.

### Contoh Request
```json
{
  "ids": [4, 5, 6]
}
```

### Contoh Response
```json
{
  "success": true,
  "message": "Berhasil menghapus permanen 3 item trash terpilih.",
  "data": {
    "deleted": 3
  },
  "meta": null,
  "errors": null
}
```

---

## 4. Error Response Umum

### 401 Unauthenticated
```json
{
  "success": false,
  "message": "Sesi anda telah berakhir. Silakan login kembali.",
  "errors": null
}
```

### 403 Forbidden
Terjadi jika user tidak memenuhi aturan akses di service layer.
```json
{
  "success": false,
  "message": "Anda tidak memiliki izin untuk melakukan aksi ini.",
  "errors": null
}
```

### 422 Validation Error (bulk endpoints)
```json
{
  "success": false,
  "message": "Data yang Anda kirim tidak valid. Periksa kembali isian Anda.",
  "errors": {
    "ids": ["The ids field is required."]
  }
}
```

### 404 Not Found
Terjadi jika `trashBinId` tidak ditemukan.
```json
{
  "success": false,
  "message": "Resource yang Anda cari tidak ditemukan.",
  "errors": null
}
```

---

## 5. Catatan Teknis Penting

- Retensi trash: 30 hari, lalu dapat dipurge permanen oleh command scheduler.
- Restore/delete per item dapat melakukan cascade berdasarkan `group_uuid`.
- Field `metadata.course_id` dipakai untuk akses berbasis relasi course (Admin/Instructor).
- Endpoint list menggunakan Spatie Query Builder + PgSearchable (`search`) + ILIKE pada `metadata.title`.
- Parameter `include` tidak tersedia pada seluruh endpoint Trash saat ini.
