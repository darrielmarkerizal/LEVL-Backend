# 📊 Dasbor – Panduan Endpoint

> **Base URL:** `{{url}}/api/v1`
> **Dasbor bersifat Shared** — Respons otomatis disesuaikan berdasarkan role yang login.

---

## Tentang Token

| Folder | Token yang Digunakan |
|--------|----------------------|
| Semua endpoint Dasbor | `{{access_token_student}}` / `{{access_token_admin}}` / `{{access_token_instructor}}` |

> Endpoint dasbor bersifat **Shared** — gunakan token role apapun yang ingin diuji. Respons akan otomatis menyesuaikan role pengguna yang sedang login.

---

## Alur Dasbor (Semua Role)

```
1. Lihat ringkasan dasbor utama           → GET /dashboard
2. Lihat pembelajaran terakhir (Asesi)    → GET /dashboard/recent-learning
3. Lihat pencapaian terakhir (Asesi)      → GET /dashboard/recent-achievements
4. Lihat rekomendasi kursus (Asesi)       → GET /dashboard/recommended-courses
```

---

## 1. [GET] Ringkasan Dasbor Utama

**Endpoint:**
```
GET {{url}}/api/v1/dashboard
```

**Authorization:** Bearer `{{access_token_student}}` *(atau token role lain)*

> Respons akan berbeda tergantung role yang login:
> - **Asesi/Student**: Menampilkan progres kursus, XP, badge, dan statistik pribadi
> - **Admin**: Menampilkan statistik platform secara keseluruhan
> - **Instruktur**: Menampilkan statistik kursus yang diampu

**Contoh Respons (200 OK) — Role Asesi:**
```json
{
  "success": true,
  "message": "Data dasbor berhasil diambil.",
  "data": {
    "total_enrolled_courses": 3,
    "completed_courses": 1,
    "in_progress_courses": 2,
    "total_xp": 1200,
    "current_level": 5,
    "pending_assignments": 4,
    "pending_quizzes": 2,
    "badges_earned": 3,
    "completion_rate": 33.33
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons (200 OK) — Role Admin:**
```json
{
  "success": true,
  "message": "Data dasbor berhasil diambil.",
  "data": {
    "total_users": 500,
    "total_courses": 20,
    "total_enrollments": 1200,
    "active_students": 350,
    "pending_submissions": 45,
    "recent_registrations": 12
  },
  "meta": null,
  "errors": null
}
```

---

## 2. [GET] Pembelajaran Terakhir (Asesi)

**Endpoint:**
```
GET {{url}}/api/v1/dashboard/recent-learning
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan daftar kursus/materi yang terakhir diakses oleh asesi.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `limit` | `5` | Jumlah item yang ditampilkan (maks 10, default 1) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data pembelajaran terakhir berhasil diambil.",
  "data": [
    {
      "course_id": 7,
      "course_title": "Analisis Data untuk Pengambilan Keputusan",
      "course_slug": "analisis-data-untuk-pengambilan-keputusan-7",
      "last_accessed_at": "2026-04-28T15:00:00.000000Z",
      "progress_percentage": 45.5,
      "last_lesson": {
        "id": 101,
        "title": "Pengantar Statistik Deskriptif",
        "slug": "pengantar-statistik-deskriptif"
      }
    }
  ],
  "meta": null,
  "errors": null
}
```

---

## 3. [GET] Pencapaian Terakhir (Asesi)

**Endpoint:**
```
GET {{url}}/api/v1/dashboard/recent-achievements
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan badge dan level yang baru-baru ini diraih oleh asesi.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `limit` | `4` | Jumlah item yang ditampilkan (maks 20, default 4) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data pencapaian terakhir berhasil diambil.",
  "data": {
    "recent_badges": [
      {
        "id": 1,
        "name": "Quiz Master",
        "icon_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/badges/quiz-master.png",
        "earned_at": "2026-04-25T09:00:00.000000Z"
      },
      {
        "id": 3,
        "name": "Fast Learner",
        "icon_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/badges/fast-learner.png",
        "earned_at": "2026-04-22T14:30:00.000000Z"
      }
    ],
    "current_level": {
      "level": 5,
      "xp": 1200,
      "xp_to_next_level": 300,
      "next_level": 6
    }
  },
  "meta": null,
  "errors": null
}
```

---

## 4. [GET] Rekomendasi Kursus (Asesi)

**Endpoint:**
```
GET {{url}}/api/v1/dashboard/recommended-courses
```

**Authorization:** Bearer `{{access_token_student}}`

> Menampilkan daftar kursus yang direkomendasikan untuk asesi berdasarkan aktivitas dan minat.

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `limit` | `2` | Jumlah kursus yang direkomendasikan (maks 10, default 2) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data rekomendasi kursus berhasil diambil.",
  "data": [
    {
      "id": 34,
      "code": "CRS0034",
      "slug": "perawatan-mesin-produksi-34",
      "title": "Perawatan Mesin Produksi",
      "short_desc": "Pelajari teknik perawatan mesin industri secara profesional.",
      "type": "okupasi",
      "type_label": "Okupasi",
      "level_tag": "menengah",
      "level_tag_label": "Menengah",
      "enrollment_type": "auto_accept",
      "enrollment_type_label": "Otomatis Diterima",
      "status": "published",
      "status_label": "Aktif",
      "is_enrolled": false,
      "thumbnail": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/courses/34/thumbnail/perawatan-mesin.png"
    }
  ],
  "meta": null,
  "errors": null
}
```

---

## Catatan Penting

> **Token yang digunakan di Postman:**
> - Untuk pengujian sebagai **Asesi**: gunakan `{{access_token_student}}`
> - Untuk pengujian sebagai **Admin**: gunakan `{{access_token_admin}}`
> - Untuk pengujian sebagai **Instruktur**: gunakan `{{access_token_instructor}}`

> Semua endpoint Dasbor berada di folder **Dasbor** pada koleksi Postman. Atur Authorization folder dengan *Inherit auth from parent* dan set token di folder level.
