# Dokumentasi Postman — Operasional

Dokumentasi lengkap untuk seluruh endpoint **Operasional**, mencakup manajemen pendaftaran kursus (enrollment), undangan kursus, laporan & ekspor, dan manajemen tempat sampah (trash bin).

> Base URL: `{{url}}/api/v1`
> Token Admin: `{{access_token_admin}}`
> Token Student: `{{access_token_student}}`

---

## Catatan

> **Mail** (`/mail`) dan **Operations** (`/operations`) saat ini masih dalam tahap pengembangan (stub — belum memiliki implementasi). Dokumentasi akan diperbarui jika sudah aktif.

---

## Struktur Folder Postman yang Direkomendasikan

```
📁 Operasional
 ┣ 📁 Pendaftaran Kursus (Enrollment)
 ┃ ┣ 📁 Shared
 ┃ ┃ ┣ 📄 [GET] Daftar Pendaftaran Saya
 ┃ ┃ ┣ 📄 [GET] Detail Pendaftaran
 ┃ ┃ ┣ 📄 [GET] Aktivitas Pendaftaran
 ┃ ┃ ┣ 📄 [GET] Status Pendaftaran di Kursus (Student)
 ┃ ┃ ┣ 📄 [POST] Daftar Kursus (Student)
 ┃ ┃ ┣ 📄 [POST] Batalkan Pendaftaran
 ┃ ┃ ┗ 📄 [POST] Tarik Diri dari Kursus
 ┃ ┗ 📁 Admin & Instruktur
 ┃   ┣ 📄 [POST] Pendaftaran Manual (Admin)
 ┃   ┣ 📄 [GET] Daftar Pendaftaran per Kursus
 ┃   ┣ 📄 [GET] Detail Pendaftaran per Kursus
 ┃   ┣ 📄 [POST] Setujui Pendaftaran
 ┃   ┣ 📄 [POST] Tolak Pendaftaran
 ┃   ┣ 📄 [POST] Keluarkan Peserta
 ┃   ┣ 📄 [POST] Setujui Massal
 ┃   ┣ 📄 [POST] Tolak Massal
 ┃   ┗ 📄 [POST] Keluarkan Massal
 ┣ 📁 Undangan Kursus (Student)
 ┃ ┣ 📄 [GET] Daftar Undangan Saya
 ┃ ┣ 📄 [GET] Detail Undangan
 ┃ ┣ 📄 [POST] Terima Undangan
 ┃ ┗ 📄 [POST] Tolak Undangan
 ┗ 📁 Tempat Sampah (Trash Bin)
   ┣ 📄 [GET] Daftar Item Terhapus
   ┣ 📄 [GET] Tipe Sumber Aktif
   ┣ 📄 [GET] Semua Tipe Sumber (Master Data)
   ┣ 📄 [PATCH] Pulihkan Satu Item
   ┣ 📄 [DELETE] Hapus Permanen Satu Item
   ┣ 📄 [PATCH] Pulihkan Semua
   ┣ 📄 [DELETE] Hapus Permanen Semua
   ┣ 📄 [PATCH] Pulihkan Massal
   ┗ 📄 [POST] Hapus Permanen Massal
```

---

## Daftar Endpoint

| No | Method | Endpoint | Role | Keterangan |
|----|--------|----------|------|------------|
| 1 | GET | `/enrollments` | Admin, Student | Daftar pendaftaran |
| 2 | GET | `/enrollments/:id` | Admin, Student | Detail pendaftaran |
| 3 | GET | `/enrollments/:id/activities` | Admin, Student | Riwayat aktivitas pendaftaran |
| 4 | GET | `/courses/:slug/enrollment-status` | Student | Status pendaftaran di kursus |
| 5 | POST | `/courses/:slug/enroll` | Student | Daftar kursus |
| 6 | POST | `/enrollments/create` | Admin | Pendaftaran manual |
| 7 | POST | `/courses/:slug/cancel` | Shared | Batalkan pendaftaran |
| 8 | POST | `/courses/:slug/withdraw` | Shared | Tarik diri dari kursus |
| 9 | GET | `/courses/:slug/enrollments` | Admin | Daftar pendaftaran per kursus |
| 10 | GET | `/courses/:slug/enrollments/:id` | Admin | Detail pendaftaran per kursus |
| 11 | POST | `/enrollments/:id/approve` | Admin | Setujui satu pendaftaran |
| 12 | POST | `/enrollments/:id/decline` | Admin | Tolak satu pendaftaran |
| 13 | POST | `/enrollments/:id/remove` | Admin | Keluarkan satu peserta |
| 14 | POST | `/enrollments/approve/bulk` | Admin | Setujui massal |
| 15 | POST | `/enrollments/decline/bulk` | Admin | Tolak massal |
| 16 | POST | `/enrollments/remove/bulk` | Admin | Keluarkan massal |
| 17 | GET | `/me/enrollments/invitations` | Student | Daftar undangan kursus |
| 18 | GET | `/me/enrollments/invitations/:id` | Student | Detail undangan |
| 19 | POST | `/me/enrollments/invitations/:id/accept` | Student | Terima undangan |
| 20 | POST | `/me/enrollments/invitations/:id/decline` | Student | Tolak undangan |
| 24 | GET | `/trash-bins` | Admin | Daftar item terhapus |
| 25 | GET | `/trash-bins/source-types` | Admin | Tipe sumber aktif milik user |
| 26 | GET | `/master-data/trash-bin-source-types` | Admin | Semua tipe sumber (master data) |
| 27 | PATCH | `/trash-bins/:id` | Admin | Pulihkan satu item |
| 28 | DELETE | `/trash-bins/:id` | Admin | Hapus permanen satu item |
| 29 | PATCH | `/trash-bins` | Admin | Pulihkan semua item |
| 30 | DELETE | `/trash-bins` | Admin | Hapus permanen semua item |
| 31 | PATCH | `/trash-bins/bulk/restore` | Admin | Pulihkan massal |
| 32 | POST | `/trash-bins/bulk` | Admin | Hapus permanen massal |

---

# A. Pendaftaran Kursus (Enrollment)

---

## 1. Daftar Pendaftaran

**GET** `{{url}}/api/v1/enrollments`

> Student hanya melihat pendaftaran milik sendiri. Admin/Superadmin melihat semua pendaftaran.

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `per_page` | integer | Jumlah per halaman (default 15) |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar pendaftaran berhasil diambil.",
    "data": [
        {
            "id": 301,
            "status": "active",
            "enrolled_at": "2026-04-01T08:00:00.000000Z",
            "course": {
                "id": 14,
                "title": "Manajemen Proyek Sesuai Standar Industri",
                "slug": "manajemen-proyek-sesuai-standar-industri-26"
            },
            "user": {
                "id": 42,
                "name": "Budi Santoso"
            }
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

## 2. Detail Pendaftaran

**GET** `{{url}}/api/v1/enrollments/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `301` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data pendaftaran berhasil diambil.",
    "data": {
        "id": 301,
        "status": "active",
        "enrolled_at": "2026-04-01T08:00:00.000000Z",
        "course": {
            "id": 14,
            "title": "Manajemen Proyek Sesuai Standar Industri"
        },
        "user": {
            "id": 42,
            "name": "Budi Santoso"
        },
        "course_progress": {
            "percentage": 35,
            "completed_lessons": 7,
            "total_lessons": 20
        },
        "assignment_submissions": [],
        "quiz_submissions": []
    },
    "meta": null,
    "errors": null
}
```

---

## 3. Riwayat Aktivitas Pendaftaran

**GET** `{{url}}/api/v1/enrollments/:id/activities`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `301` |

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `per_page` | integer | Jumlah per halaman |
| `sort` | string | `occurred_at`, `event_type` (prefix `-` = desc) |
| `filter[event_type]` | string | Filter berdasarkan tipe event |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Aktivitas pendaftaran berhasil diambil.",
    "data": [
        {
            "id": 5501,
            "event_type": "lesson_completed",
            "occurred_at": "2026-05-03T14:00:00.000000Z",
            "lesson": {
                "id": 33,
                "title": "Pengenalan Metode CPM"
            },
            "quiz": null,
            "assignment": null
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

## 4. Status Pendaftaran di Kursus

**GET** `{{url}}/api/v1/courses/:slug/enrollment-status`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200) — Sudah terdaftar
```json
{
    "success": true,
    "message": "Status pendaftaran berhasil diambil.",
    "data": {
        "status": "active",
        "enrollment": {
            "id": 301,
            "status": "active",
            "enrolled_at": "2026-04-01T08:00:00.000000Z"
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Belum terdaftar
```json
{
    "success": true,
    "message": "Pengguna belum terdaftar di kursus ini.",
    "data": {
        "status": "not_enrolled",
        "enrollment": null
    },
    "meta": null,
    "errors": null
}
```

---

## 5. Daftar Kursus (Student)

**POST** `{{url}}/api/v1/courses/:slug/enroll`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body
Tidak diperlukan.

### Contoh Response (201) — Langsung aktif
```json
{
    "success": true,
    "message": "Pendaftaran berhasil.",
    "data": {
        "id": 302,
        "status": "active",
        "enrolled_at": "2026-05-04T10:00:00.000000Z",
        "course": {
            "id": 14,
            "title": "Manajemen Proyek Sesuai Standar Industri"
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (201) — Membutuhkan persetujuan
```json
{
    "success": true,
    "message": "Permintaan pendaftaran berhasil dikirim dan menunggu persetujuan.",
    "data": {
        "id": 303,
        "status": "pending",
        "enrolled_at": "2026-05-04T10:01:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 6. Pendaftaran Manual (Admin)

**POST** `{{url}}/api/v1/enrollments/create`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `student_id` | integer | Ya | ID user yang akan didaftarkan |
| `course_slug` | string | Ya | Slug kursus tujuan |
| `initial_status` | string | Ya | `active` atau `pending` |
| `enrollment_date` | date | Tidak | `YYYY-MM-DD`, tidak boleh sebelum hari ini |
| `is_notify_student` | boolean | Tidak | Kirim notifikasi ke student (default: false) |

```json
{
    "student_id": 42,
    "course_slug": "manajemen-proyek-sesuai-standar-industri-26",
    "initial_status": "active",
    "enrollment_date": "2026-05-04",
    "is_notify_student": true
}
```

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Pendaftaran berhasil.",
    "data": {
        "id": 304,
        "status": "active",
        "enrolled_at": "2026-05-04T00:00:00.000000Z",
        "user": {
            "id": 42,
            "name": "Budi Santoso"
        },
        "course": {
            "id": 14,
            "title": "Manajemen Proyek Sesuai Standar Industri"
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 7. Batalkan Pendaftaran

**POST** `{{url}}/api/v1/courses/:slug/cancel`

> Membatalkan pendaftaran yang masih berstatus `pending`.

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pendaftaran berhasil dibatalkan.",
    "data": {
        "id": 303,
        "status": "cancelled"
    },
    "meta": null,
    "errors": null
}
```

---

## 8. Tarik Diri dari Kursus

**POST** `{{url}}/api/v1/courses/:slug/withdraw`

> Student menarik diri dari kursus yang sudah aktif.

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Berhasil keluar dari kursus.",
    "data": {
        "id": 302,
        "status": "cancelled"
    },
    "meta": null,
    "errors": null
}
```

---

## 9. Daftar Pendaftaran per Kursus (Admin)

**GET** `{{url}}/api/v1/courses/:slug/enrollments`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar pendaftaran kursus berhasil diambil.",
    "data": [
        {
            "id": 301,
            "status": "active",
            "enrolled_at": "2026-04-01T08:00:00.000000Z",
            "user": {
                "id": 42,
                "name": "Budi Santoso",
                "email": "budi@email.com"
            }
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

## 10. Detail Pendaftaran per Kursus (Admin)

**GET** `{{url}}/api/v1/courses/:slug/enrollments/:enrollment_id`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200) — Sama dengan endpoint Detail Pendaftaran (#2), difilter per kursus.

---

## 11. Setujui Satu Pendaftaran

**POST** `{{url}}/api/v1/enrollments/:id/approve`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pendaftaran berhasil disetujui.",
    "data": {
        "id": 303,
        "status": "active"
    },
    "meta": null,
    "errors": null
}
```

---

## 12. Tolak Satu Pendaftaran

**POST** `{{url}}/api/v1/enrollments/:id/decline`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pendaftaran berhasil ditolak.",
    "data": {
        "id": 303,
        "status": "cancelled"
    },
    "meta": null,
    "errors": null
}
```

---

## 13. Keluarkan Satu Peserta

**POST** `{{url}}/api/v1/enrollments/:id/remove`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Peserta berhasil dikeluarkan dari kursus.",
    "data": {
        "id": 301,
        "status": "cancelled"
    },
    "meta": null,
    "errors": null
}
```

---

## 14. Setujui Massal

**POST** `{{url}}/api/v1/enrollments/approve/bulk`

> Menyetujui beberapa pendaftaran `pending` sekaligus.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "enrollment_ids": [303, 304, 305]
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Aksi massal berhasil dilakukan.",
    "data": {
        "processed": [
            {"id": 303, "status": "active"},
            {"id": 304, "status": "active"}
        ],
        "failed": [305]
    },
    "meta": null,
    "errors": null
}
```

---

## 15. Tolak Massal

**POST** `{{url}}/api/v1/enrollments/decline/bulk`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "enrollment_ids": [306, 307]
}
```

### Contoh Response (200) — Sama dengan pola Setujui Massal dengan status `cancelled`.

---

## 16. Keluarkan Massal

**POST** `{{url}}/api/v1/enrollments/remove/bulk`

> Bisa digunakan pada status `pending` maupun `active`.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "enrollment_ids": [301, 302]
}
```

### Contoh Response (200) — Sama dengan pola Setujui Massal dengan status `cancelled`.

---

# B. Undangan Kursus (Student)

> Ketika Admin mendaftarkan student secara manual dengan `initial_status: pending`, student menerima **undangan** yang harus diterima atau ditolak.

---

## 17. Daftar Undangan Kursus Saya

**GET** `{{url}}/api/v1/me/enrollments/invitations`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `per_page` | integer | Jumlah per halaman (default 15) |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar undangan berhasil diambil.",
    "data": [
        {
            "id": 304,
            "status": "pending",
            "enrolled_at": "2026-05-04T00:00:00.000000Z",
            "course": {
                "id": 14,
                "title": "Manajemen Proyek Sesuai Standar Industri",
                "slug": "manajemen-proyek-sesuai-standar-industri-26"
            }
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

## 18. Detail Undangan

**GET** `{{url}}/api/v1/me/enrollments/invitations/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `304` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200) — Sama dengan item dalam daftar undangan.

---

## 19. Terima Undangan Kursus

**POST** `{{url}}/api/v1/me/enrollments/invitations/:id/accept`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `304` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body
Tidak diperlukan.

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Undangan berhasil diterima.",
    "data": {
        "id": 304,
        "status": "active",
        "enrolled_at": "2026-05-04T00:00:00.000000Z",
        "course": {
            "id": 14,
            "title": "Manajemen Proyek Sesuai Standar Industri"
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 20. Tolak Undangan Kursus

**POST** `{{url}}/api/v1/me/enrollments/invitations/:id/decline`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Undangan berhasil ditolak.",
    "data": {
        "id": 304,
        "status": "cancelled"
    },
    "meta": null,
    "errors": null
}
```

---

# D. Tempat Sampah (Trash Bin)

> Menyimpan semua item yang telah dihapus (soft-deleted) dari seluruh modul. Admin dapat memulihkan atau menghapus permanen.

---

## 24. Daftar Item Terhapus

**GET** `{{url}}/api/v1/trash-bins`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `per_page` | integer | Jumlah per halaman |
| `resource_type` | string | Filter berdasarkan tipe sumber (lihat endpoint 25) |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar tempat sampah berhasil diambil.",
    "data": [
        {
            "id": 1101,
            "resource_type": "course",
            "resource_id": 99,
            "label": "Kursus K3 Industri (Uji Coba)",
            "deleted_at": "2026-05-03T11:00:00.000000Z"
        },
        {
            "id": 1102,
            "resource_type": "lesson",
            "resource_id": 201,
            "label": "Materi Pengenalan OSHA",
            "deleted_at": "2026-05-03T11:05:00.000000Z"
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 2
        }
    },
    "errors": null
}
```

---

## 25. Tipe Sumber Aktif (milik user)

**GET** `{{url}}/api/v1/trash-bins/source-types`

> Hanya menampilkan tipe yang memiliki item terhapus milik user yang sedang login.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Tipe sumber tempat sampah berhasil diambil.",
    "data": [
        {"type": "course", "label": "Kursus", "count": 1},
        {"type": "lesson", "label": "Materi", "count": 1}
    ],
    "meta": null,
    "errors": null
}
```

---

## 26. Semua Tipe Sumber (Master Data)

**GET** `{{url}}/api/v1/master-data/trash-bin-source-types`

> Menampilkan semua tipe yang didukung sistem (tidak bergantung pada item yang ada).

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Tipe sumber tempat sampah berhasil diambil.",
    "data": [
        {"type": "course", "label": "Kursus"},
        {"type": "unit", "label": "Unit"},
        {"type": "lesson", "label": "Materi"},
        {"type": "assignment", "label": "Tugas"},
        {"type": "quiz", "label": "Kuis"}
    ],
    "meta": null,
    "errors": null
}
```

---

## 27. Pulihkan Satu Item

**PATCH** `{{url}}/api/v1/trash-bins/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `1101` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200) — Langsung dipulihkan
```json
{
    "success": true,
    "message": "Item berhasil dipulihkan.",
    "data": null,
    "meta": null,
    "errors": null
}
```

### Contoh Response (202) — Diproses via queue
```json
{
    "success": true,
    "message": "Pemulihan sedang diproses.",
    "data": {
        "queued": true
    },
    "meta": null,
    "errors": null
}
```

---

## 28. Hapus Permanen Satu Item

**DELETE** `{{url}}/api/v1/trash-bins/:id`

> **Tidak dapat dibatalkan.**

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Item berhasil dihapus secara permanen.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 29. Pulihkan Semua Item

**PATCH** `{{url}}/api/v1/trash-bins`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `resource_type` | string | Pulihkan hanya tipe ini (jika tidak diisi, semua dipulihkan) |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Semua item berhasil dipulihkan.",
    "data": {
        "restored_count": 2
    },
    "meta": null,
    "errors": null
}
```

---

## 30. Hapus Permanen Semua Item

**DELETE** `{{url}}/api/v1/trash-bins`

> **Tidak dapat dibatalkan.** Hapus semua item di tempat sampah.

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `resource_type` | string | Hapus hanya tipe ini (jika tidak diisi, semua dihapus) |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Semua item berhasil dihapus secara permanen.",
    "data": {
        "deleted_count": 2
    },
    "meta": null,
    "errors": null
}
```

---

## 31. Pulihkan Massal

**PATCH** `{{url}}/api/v1/trash-bins/bulk/restore`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "ids": [1101, 1102]
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Item berhasil dipulihkan secara massal.",
    "data": {
        "restored_count": 2,
        "failed_ids": []
    },
    "meta": null,
    "errors": null
}
```

---

## 32. Hapus Permanen Massal

**POST** `{{url}}/api/v1/trash-bins/bulk`

> **Tidak dapat dibatalkan.**

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "ids": [1101, 1102]
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Item berhasil dihapus secara permanen secara massal.",
    "data": {
        "deleted_count": 2,
        "failed_ids": []
    },
    "meta": null,
    "errors": null
}
```

---

## Referensi Enum

### `status` (EnrollmentStatus)
| Nilai | Keterangan |
|-------|------------|
| `pending` | Menunggu persetujuan / undangan belum diterima |
| `active` | Aktif terdaftar |
| `completed` | Kursus selesai |
| `cancelled` | Dibatalkan / ditolak / ditarik diri |

### `initial_status` (Pendaftaran Manual)
| Nilai | Keterangan |
|-------|------------|
| `active` | Langsung aktif tanpa persetujuan student |
| `pending` | Dikirim sebagai undangan, menunggu student |

### Perbedaan Cancel vs Withdraw
| Aksi | Kondisi | Keterangan |
|------|---------|------------|
| `cancel` | Status `pending` | Membatalkan permintaan yang belum disetujui |
| `withdraw` | Status `active` | Menarik diri dari kursus yang sudah aktif |

### Trash Bin — Tipe Sumber Umum
| Nilai | Keterangan |
|-------|------------|
| `course` | Kursus |
| `unit` | Unit / Modul |
| `lesson` | Materi pembelajaran |
| `assignment` | Tugas |
| `quiz` | Kuis |
