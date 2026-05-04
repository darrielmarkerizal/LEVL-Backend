# Dokumentasi Postman — Data Master & Sistem

Dokumentasi lengkap untuk seluruh endpoint **Data Master & Sistem**, mencakup upload media, log aktivitas, log audit, tag konten, master data dinamis, kategori, dan konfigurasi level.

> Base URL: `{{url}}/api/v1`
> Token Superadmin: `{{access_token_superadmin}}`
> Token Admin: `{{access_token_admin}}`
> Token Shared: `{{access_token_student}}`

---

## Struktur Folder Postman yang Direkomendasikan

```
📁 Data Master & Sistem
 ┣ 📁 Media
 ┃ ┗ 📄 [POST] Upload File
 ┣ 📁 Activity Log (Superadmin)
 ┃ ┣ 📄 [GET] Daftar Activity Log
 ┃ ┗ 📄 [GET] Detail Activity Log
 ┣ 📁 Audit Log (Admin)
 ┃ ┣ 📄 [GET] Daftar Audit Log
 ┃ ┣ 📄 [GET] Detail Audit Log
 ┃ ┗ 📄 [GET] Daftar Aksi Tersedia
 ┣ 📁 Tag Konten
 ┃ ┣ 📄 [GET] Daftar Tag (Public)
 ┃ ┣ 📄 [GET] Detail Tag (Public)
 ┃ ┣ 📄 [POST] Buat Tag (Admin)
 ┃ ┣ 📄 [PUT] Perbarui Tag (Admin)
 ┃ ┗ 📄 [DELETE] Hapus Tag (Admin)
 ┣ 📁 Master Data
 ┃ ┣ 📄 [GET] Tipe Master Data
 ┃ ┣ 📄 [GET] Semua Kursus (Master Data)
 ┃ ┣ 📄 [GET] Daftar Siswa (Admin)
 ┃ ┣ 📄 [GET] Data per Tipe (Paginated)
 ┃ ┣ 📄 [GET] Semua Data per Tipe (No Pagination)
 ┃ ┣ 📄 [GET] Detail Item
 ┃ ┣ 📄 [POST] Buat Item (Superadmin)
 ┃ ┣ 📄 [PUT] Perbarui Item (Superadmin)
 ┃ ┗ 📄 [DELETE] Hapus Item (Superadmin)
 ┣ 📁 Kategori
 ┃ ┣ 📄 [GET] Daftar Kategori (Public)
 ┃ ┣ 📄 [GET] Detail Kategori (Public)
 ┃ ┣ 📄 [POST] Buat Kategori (Superadmin)
 ┃ ┣ 📄 [PUT] Perbarui Kategori (Superadmin)
 ┃ ┗ 📄 [DELETE] Hapus Kategori (Superadmin)
 ┗ 📁 Konfigurasi Level
   ┣ 📄 [GET] Daftar Level Config (Public)
   ┣ 📄 [GET] Detail Level Config (Public)
   ┣ 📄 [POST] Buat Level Config (Superadmin)
   ┣ 📄 [PUT] Perbarui Level Config (Superadmin)
   ┗ 📄 [DELETE] Hapus Level Config (Superadmin)
```

---

## Daftar Endpoint

| No | Method | Endpoint | Role | Keterangan |
|----|--------|----------|------|------------|
| 1 | POST | `/media/upload` | Shared | Upload file ke storage |
| 2 | GET | `/activity-logs` | Superadmin | Daftar log aktivitas |
| 3 | GET | `/activity-logs/:id` | Superadmin | Detail log aktivitas |
| 4 | GET | `/audit-logs` | Admin | Daftar log audit |
| 5 | GET | `/audit-logs/:id` | Admin | Detail log audit |
| 6 | GET | `/audit-logs/meta/actions` | Admin | Daftar aksi audit tersedia |
| 7 | GET | `/tags` | Public | Daftar tag konten |
| 8 | GET | `/tags/:slug` | Public | Detail tag |
| 9 | POST | `/tags` | Admin | Buat tag baru |
| 10 | PUT | `/tags/:slug` | Admin | Perbarui tag |
| 11 | DELETE | `/tags/:slug` | Admin | Hapus tag |
| 12 | GET | `/master-data/types` | Public | Tipe master data yang tersedia |
| 13 | GET | `/master-data/courses` | Public | Semua kursus (untuk dropdown) |
| 14 | GET | `/master-data/students` | Admin | Semua siswa (untuk dropdown) |
| 15 | GET | `/master-data/:type` | Public | Data per tipe (paginated) |
| 16 | GET | `/master-data/:type/all` | Public | Semua data per tipe (tanpa pagination) |
| 17 | GET | `/master-data/:type/:id` | Public | Detail satu item master data |
| 18 | POST | `/master-data/:type` | Superadmin | Buat item master data baru |
| 19 | PUT | `/master-data/:type/:id` | Superadmin | Perbarui item master data |
| 20 | DELETE | `/master-data/:type/:id` | Superadmin | Hapus item master data |
| 21 | GET | `/categories` | Public | Daftar kategori konten |
| 22 | GET | `/categories/:id` | Public | Detail kategori |
| 23 | POST | `/categories` | Superadmin | Buat kategori baru |
| 24 | PUT | `/categories/:id` | Superadmin | Perbarui kategori |
| 25 | DELETE | `/categories/:id` | Superadmin | Hapus kategori |
| 26 | GET | `/level-configs` | Public | Daftar konfigurasi level XP |
| 27 | GET | `/level-configs/:id` | Public | Detail konfigurasi level |
| 28 | POST | `/level-configs` | Superadmin | Buat konfigurasi level baru |
| 29 | PUT | `/level-configs/:id` | Superadmin | Perbarui konfigurasi level |
| 30 | DELETE | `/level-configs/:id` | Superadmin | Hapus konfigurasi level |

---

# A. Media Upload

---

## 1. Upload File

**POST** `{{url}}/api/v1/media/upload`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (form-data)

| Key | Type | Value |
|-----|------|-------|
| `file` | File | File yang akan diunggah (maks 50MB) |

Format file yang didukung:
- **Gambar**: `jpeg, jpg, png, gif, svg, webp, bmp`
- **Video**: `mp4, webm, ogg, mov, avi, mkv, mpeg`
- **Dokumen**: `pdf, txt, csv, doc, docx, xls, xlsx, ppt, pptx, rtf, json, xml`
- **Arsip**: `zip, rar, 7z, tar, gz`

### Contoh Response (201)
```json
{
    "success": true,
    "message": "File berhasil diunggah.",
    "data": {
        "id": 512,
        "name": "diagram-alur-proyek.pdf",
        "url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/globalmedia/diagram-alur-proyek.pdf",
        "mime_type": "application/pdf",
        "size": 204800,
        "created_at": "2026-05-04T10:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Format tidak didukung
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "file": ["File harus berupa file dengan tipe: jpeg, jpg, png, gif, ..."]
    }
}
```

---

# B. Activity Log (Superadmin)

> Merekam setiap aksi user di sistem. Hanya bisa diakses oleh **Superadmin**.

---

## 2. Daftar Activity Log

**GET** `{{url}}/api/v1/activity-logs`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `search` | string | Cari berdasarkan deskripsi atau nama user |
| `sort` | string | Pengurutan (contoh: `-created_at`) |
| `per_page` | integer | Jumlah per halaman (default 15) |
| `filter[user_id]` | integer | Filter berdasarkan ID user |
| `filter[created_at]` | date | Filter berdasarkan tanggal |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Log aktivitas berhasil diambil.",
    "data": [
        {
            "id": 8801,
            "description": "updated",
            "subject_type": "Modules\\Schemes\\Models\\Course",
            "subject_id": 14,
            "causer_type": "Modules\\Auth\\Models\\User",
            "causer_id": 1,
            "causer": {
                "id": 1,
                "name": "Admin Levl",
                "email": "admin@levl.id"
            },
            "properties": {
                "old": {"title": "Manajemen Proyek"},
                "attributes": {"title": "Manajemen Proyek Sesuai Standar Industri"}
            },
            "created_at": "2026-05-04T09:00:00.000000Z"
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 1
        }
    },
    "errors": null
}
```

---

## 3. Detail Activity Log

**GET** `{{url}}/api/v1/activity-logs/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `8801` |

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Detail log aktivitas berhasil diambil.",
    "data": {
        "id": 8801,
        "description": "updated",
        "subject_type": "Modules\\Schemes\\Models\\Course",
        "subject_id": 14,
        "causer": {
            "id": 1,
            "name": "Admin Levl"
        },
        "properties": {
            "old": {
                "title": "Manajemen Proyek"
            },
            "attributes": {
                "title": "Manajemen Proyek Sesuai Standar Industri"
            }
        },
        "created_at": "2026-05-04T09:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

# C. Audit Log (Admin)

> Merekam perubahan khusus pada data penilaian dan pengumpulan. Dapat diakses oleh **Admin & Superadmin**.

---

## 4. Daftar Audit Log

**GET** `{{url}}/api/v1/audit-logs`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `search` | string | Pencarian teks bebas (maks 100 karakter) |
| `per_page` | integer | Jumlah per halaman (1–100) |
| `sort` | string | Pengurutan |
| `filter[action]` | string | Filter berdasarkan aksi (lihat endpoint 6) |
| `filter[actions]` | string | Filter beberapa aksi (comma-separated) |
| `filter[actor_id]` | integer | ID user pelaku |
| `filter[actor_type]` | string | Tipe aktor |
| `filter[subject_id]` | integer | ID objek yang diubah |
| `filter[subject_type]` | string | Tipe objek |
| `filter[assignment_id]` | integer | Filter berdasarkan ID tugas |
| `filter[student_id]` | integer | Filter berdasarkan ID student |
| `filter[created_between]` | string | Rentang tanggal (format: `2026-05-01,2026-05-31`) |
| `filter[context_contains]` | string | Cari di konteks (maks 255 karakter) |

### Contoh Request — Filter audit untuk tugas tertentu
```
GET {{url}}/api/v1/audit-logs?filter[assignment_id]=88&per_page=10
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Log audit berhasil diambil.",
    "data": [
        {
            "id": 4401,
            "action": "grading",
            "actor": {
                "id": 7,
                "name": "Instruktur Budi",
                "type": "user"
            },
            "subject": {
                "id": 701,
                "type": "submission"
            },
            "context": {
                "assignment_id": 88,
                "student_id": 42,
                "score_before": null,
                "score_after": 82.5
            },
            "created_at": "2026-05-04T09:30:00.000000Z"
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total": 1
        }
    },
    "errors": null
}
```

---

## 5. Detail Audit Log

**GET** `{{url}}/api/v1/audit-logs/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `4401` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "audit_log": {
            "id": 4401,
            "action": "grading",
            "actor": {
                "id": 7,
                "name": "Instruktur Budi"
            },
            "subject": {
                "id": 701,
                "type": "submission"
            },
            "context": {
                "assignment_id": 88,
                "student_id": 42,
                "score_before": null,
                "score_after": 82.5,
                "feedback": "Analisis sudah tepat namun masih ada celah pada bagian kesimpulan."
            },
            "created_at": "2026-05-04T09:30:00.000000Z"
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 6. Daftar Aksi Audit Tersedia

**GET** `{{url}}/api/v1/audit-logs/meta/actions`

> Mengembalikan semua nilai valid untuk `filter[action]`.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "actions": [
            "submission_created",
            "state_transition",
            "grading",
            "answer_key_change",
            "grade_override",
            "override_grant"
        ]
    },
    "meta": null,
    "errors": null
}
```

---

# D. Tag Konten

---

## 7. Daftar Tag

**GET** `{{url}}/api/v1/tags`

### Authorization
Tidak diperlukan (Public).

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 3,
            "name": "Manajemen Proyek",
            "slug": "manajemen-proyek"
        },
        {
            "id": 4,
            "name": "Gamifikasi",
            "slug": "gamifikasi"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 8. Detail Tag

**GET** `{{url}}/api/v1/tags/:slug`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `manajemen-proyek` |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 3,
        "name": "Manajemen Proyek",
        "slug": "manajemen-proyek"
    },
    "meta": null,
    "errors": null
}
```

---

## 9. Buat Tag Baru

**POST** `{{url}}/api/v1/tags`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "name": "Agile & Scrum"
}
```

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 5,
        "name": "Agile & Scrum",
        "slug": "agile-scrum"
    },
    "meta": null,
    "errors": null
}
```

---

## 10. Perbarui Tag

**PUT** `{{url}}/api/v1/tags/:slug`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `agile-scrum` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "name": "Agile, Scrum & Kanban"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 5,
        "name": "Agile, Scrum & Kanban",
        "slug": "agile-scrum-kanban"
    },
    "meta": null,
    "errors": null
}
```

---

## 11. Hapus Tag

**DELETE** `{{url}}/api/v1/tags/:slug`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

# E. Master Data (Dinamis)

> Master Data menyediakan data referensi dinamis berbasis **tipe** (`{type}`). Tipe yang tersedia dapat dilihat via endpoint `/master-data/types`.
> Endpoint CRUD (`POST`, `PUT`, `DELETE`) hanya tersedia untuk tipe yang **bukan static** dan hanya bisa dilakukan oleh **Superadmin**.

---

## 12. Tipe Master Data yang Tersedia

**GET** `{{url}}/api/v1/master-data/types`

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Tipe master data berhasil diambil.",
    "data": [
        {
            "type": "religions",
            "label": "Agama",
            "count": 6,
            "is_static": false
        },
        {
            "type": "provinces",
            "label": "Provinsi",
            "count": 38,
            "is_static": false
        },
        {
            "type": "education_levels",
            "label": "Tingkat Pendidikan",
            "count": 8,
            "is_static": true
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 3
        }
    },
    "errors": null
}
```

---

## 13. Semua Kursus (untuk Dropdown)

**GET** `{{url}}/api/v1/master-data/courses`

> Daftar kursus ringkas tanpa pagination. Cocok untuk input dropdown/select.

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `search` | string | Cari berdasarkan nama kursus |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diambil.",
    "data": [
        {
            "id": 14,
            "title": "Manajemen Proyek Sesuai Standar Industri",
            "slug": "manajemen-proyek-sesuai-standar-industri-26"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 14. Semua Siswa (untuk Dropdown — Admin)

**GET** `{{url}}/api/v1/master-data/students`

> Hanya untuk **Admin & Instruktur**. Cocok untuk input dropdown pencarian siswa.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `search` | string | Cari berdasarkan nama atau email siswa |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data siswa berhasil diambil.",
    "data": [
        {
            "id": 42,
            "name": "Budi Santoso",
            "email": "budi@email.com"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 15. Data per Tipe (Paginated)

**GET** `{{url}}/api/v1/master-data/:type`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `type` | string | `religions`, `provinces`, `education_levels` |

### Contoh Request
```
GET {{url}}/api/v1/master-data/religions
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diambil.",
    "data": [
        {"id": 1, "value": "Islam"},
        {"id": 2, "value": "Kristen Protestan"},
        {"id": 3, "value": "Kristen Katolik"},
        {"id": 4, "value": "Hindu"},
        {"id": 5, "value": "Buddha"},
        {"id": 6, "value": "Konghucu"}
    ],
    "meta": null,
    "errors": null
}
```

---

## 16. Semua Data per Tipe (Tanpa Pagination)

**GET** `{{url}}/api/v1/master-data/:type/all`

> Mengembalikan seluruh data tanpa pagination. Cocok untuk mengisi dropdown.

### Contoh Request
```
GET {{url}}/api/v1/master-data/provinces/all
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diambil.",
    "data": [
        {"id": 1, "value": "Aceh"},
        {"id": 2, "value": "Sumatera Utara"},
        {"id": 3, "value": "Sumatera Barat"}
    ],
    "meta": null,
    "errors": null
}
```

---

## 17. Detail Item Master Data

**GET** `{{url}}/api/v1/master-data/:type/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `type` | string | `religions` |
| `id` | integer | `1` |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diambil.",
    "data": {
        "id": 1,
        "value": "Islam"
    },
    "meta": null,
    "errors": null
}
```

---

## 18. Buat Item Master Data Baru (Superadmin)

**POST** `{{url}}/api/v1/master-data/:type`

> Hanya tersedia untuk tipe yang **tidak bersifat static**. Cek kolom `is_static` dari endpoint `/master-data/types`.

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Body (raw JSON)
```json
{
    "value": "Lainnya"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil dibuat.",
    "data": {
        "id": 7,
        "value": "Lainnya"
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (403) — Tipe bersifat static (tidak bisa diubah)
```json
{
    "success": false,
    "message": "Aksi ini tidak diizinkan untuk tipe data ini.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 19. Perbarui Item Master Data (Superadmin)

**PUT** `{{url}}/api/v1/master-data/:type/:id`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Body (raw JSON)
```json
{
    "value": "Agama Lainnya"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diperbarui.",
    "data": {
        "id": 7,
        "value": "Agama Lainnya"
    },
    "meta": null,
    "errors": null
}
```

---

## 20. Hapus Item Master Data (Superadmin)

**DELETE** `{{url}}/api/v1/master-data/:type/:id`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil dihapus.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

# F. Kategori Konten

---

## 21. Daftar Kategori

**GET** `{{url}}/api/v1/categories`

### Authorization
Tidak diperlukan (Public).

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Kategori berhasil diambil.",
    "data": [
        {
            "id": 1,
            "name": "Teknologi Informasi",
            "value": "teknologi-informasi",
            "description": "Kursus dan konten seputar dunia IT",
            "status": "active"
        },
        {
            "id": 2,
            "name": "Manajemen & Bisnis",
            "value": "manajemen-bisnis",
            "description": "Kursus manajemen dan bisnis profesional",
            "status": "active"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 22. Detail Kategori

**GET** `{{url}}/api/v1/categories/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `1` |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 1,
        "name": "Teknologi Informasi",
        "value": "teknologi-informasi",
        "description": "Kursus dan konten seputar dunia IT",
        "status": "active"
    },
    "meta": null,
    "errors": null
}
```

---

## 23. Buat Kategori Baru (Superadmin)

**POST** `{{url}}/api/v1/categories`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Body (raw JSON)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `name` | string | Ya | Maks 100 karakter |
| `value` | string | Ya | Unik, maks 100 karakter (slug/kode) |
| `description` | string | Tidak | Maks 255 karakter |
| `status` | string | Ya | `active` atau `inactive` |

```json
{
    "name": "Keselamatan & Kesehatan Kerja",
    "value": "k3",
    "description": "Kursus seputar K3 dan keselamatan industri",
    "status": "active"
}
```

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Kategori berhasil dibuat.",
    "data": {
        "id": 3,
        "name": "Keselamatan & Kesehatan Kerja",
        "value": "k3",
        "description": "Kursus seputar K3 dan keselamatan industri",
        "status": "active"
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Value sudah dipakai
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "value": ["Kolom value sudah digunakan."]
    }
}
```

---

## 24. Perbarui Kategori (Superadmin)

**PUT** `{{url}}/api/v1/categories/:id`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Body (raw JSON) — Field opsional
```json
{
    "name": "K3 & Keselamatan Industri",
    "status": "active"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Kategori berhasil diperbarui.",
    "data": {
        "id": 3,
        "name": "K3 & Keselamatan Industri",
        "value": "k3",
        "status": "active"
    },
    "meta": null,
    "errors": null
}
```

---

## 25. Hapus Kategori (Superadmin)

**DELETE** `{{url}}/api/v1/categories/:id`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Kategori berhasil dihapus.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

# G. Konfigurasi Level XP

> Mengatur ambang batas XP untuk setiap level pada sistem gamifikasi. Hanya bisa dimodifikasi oleh **Superadmin**.

---

## 26. Daftar Konfigurasi Level

**GET** `{{url}}/api/v1/level-configs`

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `per_page` | integer | Jumlah per halaman |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 1,
            "level": 1,
            "name": "Pemula",
            "xp_required": 0,
            "rewards": []
        },
        {
            "id": 2,
            "level": 2,
            "name": "Dasar",
            "xp_required": 100,
            "rewards": [
                {"type": "badge", "value": "pemula-badge"}
            ]
        },
        {
            "id": 3,
            "level": 3,
            "name": "Menengah",
            "xp_required": 300,
            "rewards": []
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 3
        }
    },
    "errors": null
}
```

---

## 27. Detail Konfigurasi Level

**GET** `{{url}}/api/v1/level-configs/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `2` |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 2,
        "level": 2,
        "name": "Dasar",
        "xp_required": 100,
        "rewards": [
            {"type": "badge", "value": "pemula-badge"}
        ]
    },
    "meta": null,
    "errors": null
}
```

---

## 28. Buat Konfigurasi Level Baru (Superadmin)

**POST** `{{url}}/api/v1/level-configs`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Body (raw JSON)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `level` | integer | Ya | Nomor level (unik, min 1) |
| `name` | string | Ya | Nama level, maks 255 karakter |
| `xp_required` | integer | Ya | Total XP yang dibutuhkan (min 0) |
| `rewards` | array | Tidak | Array reward saat level dicapai |
| `rewards[].type` | string | Ya (jika ada rewards) | Tipe reward (contoh: `badge`, `certificate`) |
| `rewards[].value` | mixed | Ya (jika ada rewards) | Nilai reward |

```json
{
    "level": 4,
    "name": "Mahir",
    "xp_required": 700,
    "rewards": [
        {"type": "badge", "value": "mahir-badge"},
        {"type": "certificate", "value": "sertifikat-level-4"}
    ]
}
```

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Konfigurasi level berhasil dibuat.",
    "data": {
        "id": 4,
        "level": 4,
        "name": "Mahir",
        "xp_required": 700,
        "rewards": [
            {"type": "badge", "value": "mahir-badge"},
            {"type": "certificate", "value": "sertifikat-level-4"}
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Level sudah ada
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "level": ["Kolom level sudah digunakan."]
    }
}
```

---

## 29. Perbarui Konfigurasi Level (Superadmin)

**PUT** `{{url}}/api/v1/level-configs/:id`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Body (raw JSON) — Semua field opsional
```json
{
    "name": "Mahir Lanjutan",
    "xp_required": 800
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Konfigurasi level berhasil diperbarui.",
    "data": {
        "id": 4,
        "level": 4,
        "name": "Mahir Lanjutan",
        "xp_required": 800
    },
    "meta": null,
    "errors": null
}
```

---

## 30. Hapus Konfigurasi Level (Superadmin)

**DELETE** `{{url}}/api/v1/level-configs/:id`

### Authorization
```
Bearer Token: {{access_token_superadmin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Konfigurasi level berhasil dihapus.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## Referensi

### Tabel Akses per Modul

| Modul | Baca | Tulis |
|-------|------|-------|
| Media Upload | Semua (auth) | Semua (auth) |
| Activity Log | Superadmin | — |
| Audit Log | Admin, Superadmin | — |
| Tag | Public | Admin, Instruktur |
| Master Data | Public | Superadmin |
| Kategori | Public | Superadmin |
| Level Config | Public | Superadmin |

### Format Aksi Audit Log (`filter[action]`)
| Nilai | Keterangan |
|-------|------------|
| `submission_created` | Pengumpulan tugas dibuat |
| `state_transition` | Perubahan status pengumpulan |
| `grading` | Penilaian diberikan |
| `answer_key_change` | Kunci jawaban berubah |
| `grade_override` | Nilai di-override manual |
| `override_grant` | Izin override diberikan |

### `status` Kategori
| Nilai | Keterangan |
|-------|------------|
| `active` | Kategori aktif |
| `inactive` | Kategori nonaktif |

### Format Upload File
| Tipe | Format |
|------|--------|
| Gambar | `jpeg, jpg, png, gif, svg, webp, bmp` |
| Video | `mp4, webm, ogg, mov, avi, mkv, mpeg` |
| Dokumen | `pdf, txt, csv, doc, docx, xls, xlsx, ppt, pptx, rtf, json, xml` |
| Arsip | `zip, rar, 7z, tar, gz` |
| Ukuran Maks | **50 MB** |
