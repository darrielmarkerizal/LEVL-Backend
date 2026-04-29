# 📋 Pendaftaran (Enrollment) – Panduan Endpoint

> **Base URL:** `{{url}}/api/v1`

---

## Tentang Token per Role

| Folder | Token yang Digunakan |
|--------|----------------------|
| Admin & Instruktur | `{{access_token_admin}}` / `{{access_token_instructor}}` |
| Asesi / Student | `{{access_token_student}}` |

---

## Alur Enrollment

```
=== ADMIN & INSTRUKTUR ===
1. Lihat daftar semua pendaftaran              → GET  /enrollments
2. Lihat detail pendaftaran spesifik           → GET  /enrollments/:enrollment_id
3. Lihat daftar enrollment per kursus         → GET  /courses/:course_slug/enrollments
4. Setujui pendaftaran                         → POST /enrollments/:enrollment_id/approve
5. Tolak pendaftaran                           → POST /enrollments/:enrollment_id/decline
6. Hapus peserta dari kursus (Remove)         → POST /enrollments/:enrollment_id/remove
7. Setujui pendaftaran massal                 → POST /enrollments/approve/bulk
8. Tolak pendaftaran massal                   → POST /enrollments/decline/bulk
9. Hapus peserta massal                       → POST /enrollments/remove/bulk
10. Daftarkan asesi secara manual             → POST /enrollments/create

=== ASESI / STUDENT ===
11. Lihat daftar kursus/skema tersedia        → GET  /courses
12. Lihat detail kursus/skema                 → GET  /courses/:course_slug
13. Daftar ke kursus (enroll)                 → POST /courses/:course_slug/enroll
14. Lihat daftar kursus yang saya ikuti       → GET  /my-courses
15. Cek status enrollment di kursus          → GET  /courses/:course_slug/enrollment-status
16. Batalkan enrollment (Cancel)              → POST /courses/:course_slug/cancel
17. Undurkan diri dari kursus (Withdraw)      → POST /courses/:course_slug/withdraw
18. Lihat undangan enrollment saya            → GET  /me/enrollments/invitations
19. Terima undangan enrollment                → POST /me/enrollments/invitations/:id/accept
20. Tolak undangan enrollment                 → POST /me/enrollments/invitations/:id/decline
```

---

## ── ADMIN & INSTRUKTUR ──

---

## 1. [GET] Daftar Semua Pendaftaran

**Endpoint:**
```
GET {{url}}/api/v1/enrollments
```

**Authorization:** Bearer `{{access_token_admin}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `20` | Jumlah data per halaman |
| `search` | `budi` | Cari berdasarkan nama/email student |
| `filter[status]` | `active` | Filter status: `active`, `pending`, `rejected`, `cancelled`, `withdrawn` |
| `filter[course_slug]` | `analisis-data-7` | Filter berdasarkan slug kursus |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data pendaftaran berhasil diambil.",
  "data": [
    {
      "id": 123,
      "status": "active",
      "status_label": "Aktif",
      "enrolled_at": "2026-04-01T08:00:00.000000Z",
      "user": {
        "id": 201,
        "name": "Budi Santoso",
        "email": "budi@example.com"
      },
      "course": {
        "id": 7,
        "slug": "analisis-data-untuk-pengambilan-keputusan-7",
        "title": "Analisis Data untuk Pengambilan Keputusan",
        "code": "CRS0007"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  },
  "errors": null
}
```

---

## 2. [GET] Detail Pendaftaran Spesifik

**Endpoint:**
```
GET {{url}}/api/v1/enrollments/:enrollment_id
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `enrollment_id` | `123` | ID pendaftaran |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data pendaftaran berhasil diambil.",
  "data": {
    "id": 123,
    "status": "active",
    "status_label": "Aktif",
    "enrolled_at": "2026-04-01T08:00:00.000000Z",
    "user": {
      "id": 201,
      "name": "Budi Santoso",
      "email": "budi@example.com"
    },
    "course": {
      "id": 7,
      "slug": "analisis-data-untuk-pengambilan-keputusan-7",
      "title": "Analisis Data untuk Pengambilan Keputusan",
      "code": "CRS0007"
    },
    "course_progress": {
      "completion_percentage": 45.5
    }
  },
  "meta": null,
  "errors": null
}
```

---

## 3. [GET] Daftar Enrollment per Kursus

**Endpoint:**
```
GET {{url}}/api/v1/courses/:course_slug/enrollments
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus |

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `20` | Jumlah data per halaman |
| `filter[status]` | `active` | Filter status enrollment |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data pendaftaran per kursus berhasil diambil.",
  "data": [
    {
      "id": 123,
      "status": "active",
      "status_label": "Aktif",
      "enrolled_at": "2026-04-01T08:00:00.000000Z",
      "user": {
        "id": 201,
        "name": "Budi Santoso",
        "email": "budi@example.com"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 20,
    "total": 35
  },
  "errors": null
}
```

---

## 4. [POST] Setujui Pendaftaran

**Endpoint:**
```
POST {{url}}/api/v1/enrollments/:enrollment_id/approve
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `enrollment_id` | `123` | ID pendaftaran yang ingin disetujui |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pendaftaran berhasil disetujui.",
  "data": {
    "id": 123,
    "status": "active",
    "status_label": "Aktif"
  },
  "meta": null,
  "errors": null
}
```

---

## 5. [POST] Tolak Pendaftaran

**Endpoint:**
```
POST {{url}}/api/v1/enrollments/:enrollment_id/decline
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `enrollment_id` | `123` | ID pendaftaran yang ingin ditolak |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pendaftaran berhasil ditolak.",
  "data": {
    "id": 123,
    "status": "rejected",
    "status_label": "Ditolak"
  },
  "meta": null,
  "errors": null
}
```

---

## 6. [POST] Hapus Peserta dari Kursus (Remove/Expel)

**Endpoint:**
```
POST {{url}}/api/v1/enrollments/:enrollment_id/remove
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `enrollment_id` | `123` | ID pendaftaran yang ingin dihapus/dikeluarkan |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Peserta berhasil dikeluarkan dari kursus.",
  "data": {
    "id": 123,
    "status": "expelled",
    "status_label": "Dikeluarkan"
  },
  "meta": null,
  "errors": null
}
```

---

## 7. [POST] Setujui Pendaftaran Massal (Bulk Approve)

**Endpoint:**
```
POST {{url}}/api/v1/enrollments/approve/bulk
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
```json
{
  "enrollment_ids": [123, 124, 125]
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `enrollment_ids` | array of integer | ✅ | Daftar ID pendaftaran yang ingin disetujui |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Aksi massal berhasil diproses.",
  "data": {
    "processed": [
      { "id": 123, "status": "active" },
      { "id": 124, "status": "active" }
    ],
    "failed": [
      { "id": 125, "reason": "Enrollment sudah aktif." }
    ]
  },
  "meta": null,
  "errors": null
}
```

---

## 8. [POST] Tolak Pendaftaran Massal (Bulk Decline)

**Endpoint:**
```
POST {{url}}/api/v1/enrollments/decline/bulk
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
```json
{
  "enrollment_ids": [123, 124]
}
```

---

## 9. [POST] Hapus Peserta Massal (Bulk Remove)

**Endpoint:**
```
POST {{url}}/api/v1/enrollments/remove/bulk
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
```json
{
  "enrollment_ids": [123, 124]
}
```

---

## 10. [POST] Daftarkan Asesi Secara Manual (Create Manual)

**Endpoint:**
```
POST {{url}}/api/v1/enrollments/create
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
```json
{
  "course_slug": "analisis-data-untuk-pengambilan-keputusan-7",
  "user_id": 201
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `course_slug` | string | ✅ | Slug kursus tujuan |
| `user_id` | integer | ✅ | ID user/asesi yang akan didaftarkan |

**Contoh Respons (201 Created):**
```json
{
  "success": true,
  "message": "Asesi berhasil didaftarkan secara manual.",
  "data": {
    "id": 456,
    "status": "active",
    "status_label": "Aktif",
    "enrolled_at": "2026-04-29T10:00:00.000000Z",
    "user": {
      "id": 201,
      "name": "Budi Santoso"
    },
    "course": {
      "id": 7,
      "slug": "analisis-data-untuk-pengambilan-keputusan-7",
      "title": "Analisis Data untuk Pengambilan Keputusan"
    }
  },
  "meta": null,
  "errors": null
}
```

---

## ── ASESI / STUDENT ──

---

## 11. [GET] Daftar Kursus/Skema Tersedia

**Endpoint:**
```
GET {{url}}/api/v1/courses
```

**Authorization:** Bearer `{{access_token_student}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `20` | Jumlah data per halaman |
| `search` | `analisis` | Cari berdasarkan judul/kode kursus |
| `all` | *(tanpa value)* | Ambil semua data tanpa paginasi |
| `filter[status]` | `published` | Filter status: `draft`, `published`, `archived` |
| `filter[type]` | `kluster` | Filter tipe: `kluster`, `okupasi` |
| `filter[level_tag]` | `dasar` | Filter tingkat: `dasar`, `menengah`, `mahir` |
| `filter[category_id]` | `4` | Filter berdasarkan ID kategori |
| `include` | `units,instructors,tags` | Sertakan relasi: `tags`, `category`, `units`, `outcomes`, `instructors` |
| `sort` | `title` | Urutan: `code`, `title`, `created_at`, `updated_at`, `published_at` |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 7,
      "code": "CRS0007",
      "slug": "analisis-data-untuk-pengambilan-keputusan-7",
      "title": "Analisis Data untuk Pengambilan Keputusan",
      "short_desc": "Pelajari teknik analisis data untuk mendukung pengambilan keputusan bisnis.",
      "type": "okupasi",
      "type_label": "Okupasi",
      "level_tag": "dasar",
      "level_tag_label": "Dasar",
      "enrollment_type": "auto_accept",
      "enrollment_type_label": "Otomatis Diterima",
      "status": "published",
      "status_label": "Aktif",
      "enrollment_status": null,
      "enrollment_status_label": null,
      "is_enrolled": false,
      "published_at": "2026-01-10T00:00:00.000000Z",
      "created_at": "2026-01-05T00:00:00.000000Z",
      "updated_at": "2026-04-01T00:00:00.000000Z",
      "thumbnail": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/courses/7/thumbnail/data-analyst.png"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 50
  },
  "errors": null
}
```

---

## 12. [GET] Detail Kursus/Skema

**Endpoint:**
```
GET {{url}}/api/v1/courses/:course_slug
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus |

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `include` | `units,instructors,tags` | Sertakan relasi: `units`, `instructors`, `tags`, `outcomes`, `category` |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 7,
    "code": "CRS0007",
    "slug": "analisis-data-untuk-pengambilan-keputusan-7",
    "title": "Analisis Data untuk Pengambilan Keputusan",
    "short_desc": "Pelajari teknik analisis data untuk mendukung pengambilan keputusan bisnis.",
    "type": "okupasi",
    "type_label": "Okupasi",
    "level_tag": "dasar",
    "level_tag_label": "Dasar",
    "enrollment_type": "auto_accept",
    "enrollment_type_label": "Otomatis Diterima",
    "status": "published",
    "status_label": "Aktif",
    "enrollment_status": null,
    "enrollment_status_label": null,
    "is_enrolled": false,
    "published_at": "2026-01-10T00:00:00.000000Z",
    "thumbnail": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/courses/7/thumbnail/data-analyst.png",
    "banner": null,
    "tags": [
      { "id": 5, "name": "Data", "slug": "data" }
    ],
    "learning_outcomes": [
      "Memahami konsep dasar analisis data"
    ],
    "instructor_list": [
      { "id": 12, "name": "Dr. Rina Wati", "email": "rina@levl.id" }
    ],
    "units": [
      {
        "id": 40,
        "slug": "fundamentals-and-core-concepts-47-c2a7aa",
        "title": "Pengantar Analisis Data",
        "order": 1,
        "status": "published"
      }
    ]
  },
  "meta": null,
  "errors": null
}
```

---

## 13. [POST] Daftar ke Kursus (Enroll)

**Endpoint:**
```
POST {{url}}/api/v1/courses/:course_slug/enroll
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus yang ingin diikuti |

**Body (JSON) — Opsional, hanya diperlukan jika `enrollment_type = key_based`:**
```json
{
  "enrollment_key": "KUNCI123"
}
```

| Field | Tipe | Keterangan |
|-------|------|------------|
| `enrollment_key` | string | Wajib jika tipe pendaftaran adalah `key_based` |

**Contoh Respons (201 Created) — enrollment_type: auto_accept:**
```json
{
  "success": true,
  "message": "Berhasil mendaftar ke kursus.",
  "data": {
    "id": 456,
    "status": "active",
    "status_label": "Aktif",
    "enrolled_at": "2026-04-29T10:00:00.000000Z",
    "course": {
      "id": 7,
      "slug": "analisis-data-untuk-pengambilan-keputusan-7",
      "title": "Analisis Data untuk Pengambilan Keputusan",
      "code": "CRS0007"
    }
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons (201 Created) — enrollment_type: approval:**
```json
{
  "success": true,
  "message": "Pendaftaran Anda sedang menunggu persetujuan admin.",
  "data": {
    "id": 457,
    "status": "pending",
    "status_label": "Menunggu Persetujuan",
    "enrolled_at": "2026-04-29T10:05:00.000000Z",
    "course": {
      "id": 13,
      "slug": "operasional-gudang-dan-logistik-13",
      "title": "Operasional Gudang dan Logistik",
      "code": "CRS0013"
    }
  },
  "meta": null,
  "errors": null
}
```

---

## 14. [GET] Daftar Kursus yang Saya Ikuti (My Courses)

**Endpoint:**
```
GET {{url}}/api/v1/my-courses
```

**Authorization:** Bearer `{{access_token_student}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `20` | Jumlah data per halaman |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "enrollment_id": 456,
      "enrollment_status": "active",
      "enrollment_status_label": "Aktif",
      "enrolled_at": "2026-04-01T08:00:00.000000Z",
      "progress_percentage": 45.5,
      "course": {
        "id": 7,
        "code": "CRS0007",
        "slug": "analisis-data-untuk-pengambilan-keputusan-7",
        "title": "Analisis Data untuk Pengambilan Keputusan",
        "type": "okupasi",
        "type_label": "Okupasi",
        "level_tag": "dasar",
        "level_tag_label": "Dasar",
        "thumbnail": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/courses/7/thumbnail/data-analyst.png"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  },
  "errors": null
}
```

---

## 15. [GET] Cek Status Enrollment di Kursus

**Endpoint:**
```
GET {{url}}/api/v1/courses/:course_slug/enrollment-status
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus |

**Contoh Respons (200 OK) — sudah terdaftar:**
```json
{
  "success": true,
  "message": "Status enrollment berhasil diambil.",
  "data": {
    "status": "active",
    "enrollment": {
      "id": 456,
      "status": "active",
      "status_label": "Aktif",
      "enrolled_at": "2026-04-01T08:00:00.000000Z"
    }
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons (200 OK) — belum terdaftar:**
```json
{
  "success": true,
  "message": "Pengguna belum mendaftar ke kursus ini.",
  "data": {
    "status": "not_enrolled",
    "enrollment": null
  },
  "meta": null,
  "errors": null
}
```

---

## 16. [POST] Batalkan Enrollment (Cancel)

**Endpoint:**
```
POST {{url}}/api/v1/courses/:course_slug/cancel
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Enrollment berhasil dibatalkan.",
  "data": {
    "id": 456,
    "status": "cancelled",
    "status_label": "Dibatalkan"
  },
  "meta": null,
  "errors": null
}
```

---

## 17. [POST] Undurkan Diri dari Kursus (Withdraw)

**Endpoint:**
```
POST {{url}}/api/v1/courses/:course_slug/withdraw
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Berhasil mengundurkan diri dari kursus.",
  "data": {
    "id": 456,
    "status": "withdrawn",
    "status_label": "Mengundurkan Diri"
  },
  "meta": null,
  "errors": null
}
```

---

## 18. [GET] Daftar Undangan Enrollment Saya

**Endpoint:**
```
GET {{url}}/api/v1/me/enrollments/invitations
```

**Authorization:** Bearer `{{access_token_student}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `15` | Jumlah data per halaman |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar undangan berhasil diambil.",
  "data": [
    {
      "id": 789,
      "status": "pending",
      "status_label": "Menunggu",
      "invited_at": "2026-04-28T09:00:00.000000Z",
      "course": {
        "id": 13,
        "slug": "operasional-gudang-dan-logistik-13",
        "title": "Operasional Gudang dan Logistik"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  },
  "errors": null
}
```

---

## 19. [POST] Terima Undangan Enrollment

**Endpoint:**
```
POST {{url}}/api/v1/me/enrollments/invitations/:enrollment_id/accept
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `enrollment_id` | `789` | ID undangan enrollment |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Undangan berhasil diterima.",
  "data": {
    "id": 789,
    "status": "active",
    "status_label": "Aktif"
  },
  "meta": null,
  "errors": null
}
```

---

## 20. [POST] Tolak Undangan Enrollment

**Endpoint:**
```
POST {{url}}/api/v1/me/enrollments/invitations/:enrollment_id/decline
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `enrollment_id` | `789` | ID undangan enrollment |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Undangan berhasil ditolak.",
  "data": {
    "id": 789,
    "status": "rejected",
    "status_label": "Ditolak"
  },
  "meta": null,
  "errors": null
}
```

---

## Catatan Penting

> **Tipe Enrollment Kursus (`enrollment_type`):**
> - `auto_accept` — Asesi langsung aktif setelah daftar
> - `key_based` — Asesi harus memasukkan kunci pendaftaran yang valid
> - `approval` — Pendaftaran menunggu persetujuan admin, status awal `pending`

> **Status Enrollment:**
> - `active` — Terdaftar dan aktif
> - `pending` — Menunggu persetujuan (khusus tipe `approval`)
> - `rejected` — Ditolak admin
> - `cancelled` — Dibatalkan oleh asesi (sebelum aktif)
> - `withdrawn` — Mengundurkan diri (setelah aktif)
> - `expelled` — Dikeluarkan oleh admin
