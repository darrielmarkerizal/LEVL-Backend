# Dokumentasi Postman — Informasi & Konten

> ⚠️ **Catatan Arsitektur (Diperbarui 4 Mei 2026)**
>
> Module **Content** (`/announcements`, `/news`, `/content/*`) telah **dihapus route-nya** karena seluruh fitur Informasi di FE Admin menggunakan endpoint `/admin/posts` dari module **Notifikasi**.
>
> Dokumentasi endpoint Content lama di file ini tidak lagi relevan.
> Lihat [`postman_notifikasi.md`](./postman_notifikasi.md) — **Seksi E: Post / Broadcast — Admin** untuk endpoint yang aktif digunakan.

> Base URL: `{{url}}/api/v1`
> Token Admin: `{{access_token_admin}}`

---

## Endpoint yang Digunakan FE — Fitur Manajemen Informasi

Fitur **Manajemen Informasi** di FE Admin menggunakan endpoint berikut dari `/admin/posts` (terdokumentasi lengkap di `postman_notifikasi.md` Seksi E):

| No | Method | Endpoint | Keterangan | FE |
|----|--------|----------|------------|----|
| 1 | GET | `/admin/posts` | Daftar post untuk tabel manajemen informasi | ✅ |
| 2 | GET | `/admin/posts/:uuid` | Detail post (halaman detail & form edit) | ✅ |
| 3 | POST | `/admin/posts` | Buat post baru (form tambah) | ✅ |
| 4 | PUT | `/admin/posts/:uuid` | Perbarui post (form ubah) | ✅ |
| 5 | DELETE | `/admin/posts/:uuid` | Hapus post (soft delete) | ✅ |
| 6 | POST | `/admin/posts/:uuid/publish` | Publikasikan post | ✅ |
| 7 | POST | `/admin/posts/:uuid/unpublish` | Batalkan publikasi | ✅ |
| 8 | POST | `/admin/posts/bulk-delete` | Hapus massal (halaman Draf) | ✅ |
| 9 | POST | `/admin/posts/bulk-publish` | Publikasi massal (halaman Draf) | ✅ |

---

## Status Content Module

| Komponen | Status |
|----------|--------|
| HTTP Routes (`/announcements`, `/news`, `/content/*`) | ❌ **Dihapus** |
| Controllers, Models, Services, Repositories | ✅ Masih ada (kode tidak dihapus) |
| Database Tables & Migrations | ✅ Masih ada |
| Queue Jobs (`PublishScheduledContent`) | ✅ Masih terdaftar (berjalan internal) |
| Event Listeners (NotifyAudience, NotifyAuthor) | ✅ Masih terdaftar |

> Module Content masih aktif secara internal (ServiceProvider terdaftar, DB tables ada, jobs & events masih jalan). Hanya HTTP routes-nya yang dihapus karena FE tidak menggunakannya. Jika dibutuhkan kembali, cukup tambahkan kembali routes di `Modules/Content/routes/api.php`.

---

## Referensi

- Dokumentasi endpoint aktif: [`postman_notifikasi.md`](./postman_notifikasi.md) — Seksi E
- Routes file: `Modules/Notifications/routes/api.php` (prefix `admin/posts`)
- FE Service: `Levl-FE/services/dashboard/informasi/information-management.service.ts`
- FE Hooks: `Levl-FE/hooks/api/content.ts`

---

## Arsip: Dokumentasi Content Module (Tidak Aktif)

> ⚠️ Endpoint di bawah ini **tidak lagi tersedia** di API. Disimpan sebagai referensi historis.



```
📁 Informasi & Konten
 ┣ 📁 Pengumuman (Announcements)
 ┃ ┣ 📁 Shared
 ┃ ┃ ┣ 📄 [GET] Daftar Pengumuman
 ┃ ┃ ┣ 📄 [GET] Detail Pengumuman
 ┃ ┃ ┗ 📄 [POST] Tandai Dibaca
 ┃ ┗ 📁 Admin & Instruktur
 ┃   ┣ 📄 [POST] Buat Pengumuman Baru
 ┃   ┣ 📄 [PUT] Perbarui Pengumuman
 ┃   ┣ 📄 [DELETE] Hapus Pengumuman
 ┃   ┣ 📄 [POST] Publikasikan Pengumuman
 ┃   ┗ 📄 [POST] Jadwalkan Pengumuman
 ┣ 📁 Berita (News)
 ┃ ┣ 📁 Shared
 ┃ ┃ ┣ 📄 [GET] Daftar Berita
 ┃ ┃ ┣ 📄 [GET] Berita Trending
 ┃ ┃ ┗ 📄 [GET] Detail Berita
 ┃ ┗ 📁 Admin & Instruktur
 ┃   ┣ 📄 [POST] Buat Berita Baru
 ┃   ┣ 📄 [PUT] Perbarui Berita
 ┃   ┣ 📄 [DELETE] Hapus Berita
 ┃   ┣ 📄 [POST] Publikasikan Berita
 ┃   ┗ 📄 [POST] Jadwalkan Berita
 ┣ 📁 Pengumuman Kursus
 ┃ ┣ 📄 [GET] Daftar Pengumuman Kursus
 ┃ ┗ 📄 [POST] Buat Pengumuman Kursus
 ┣ 📁 Pencarian & Statistik
 ┃ ┣ 📄 [GET] Cari Konten
 ┃ ┣ 📄 [GET] Statistik Konten (Admin)
 ┃ ┣ 📄 [GET] Statistik per Pengumuman (Admin)
 ┃ ┣ 📄 [GET] Statistik per Berita (Admin)
 ┃ ┣ 📄 [GET] Konten Trending (Admin)
 ┃ ┗ 📄 [GET] Konten Paling Dilihat (Admin)
 ┗ 📁 Persetujuan Konten (Admin)
   ┣ 📄 [POST] Ajukan Konten untuk Review
   ┣ 📄 [POST] Setujui Konten
   ┣ 📄 [POST] Tolak Konten
   ┗ 📄 [GET] Daftar Konten Pending Review
```

---

## Daftar Endpoint

| No | Method | Endpoint | Role | Keterangan |
|----|--------|----------|------|------------|
| 1 | GET | `/announcements` | Shared | Daftar pengumuman |
| 2 | GET | `/announcements/:id` | Shared | Detail pengumuman (auto-mark read) |
| 3 | POST | `/announcements/:id/read` | Shared | Tandai pengumuman dibaca |
| 4 | POST | `/announcements` | Admin | Buat pengumuman baru |
| 5 | PUT | `/announcements/:id` | Admin | Perbarui pengumuman |
| 6 | DELETE | `/announcements/:id` | Admin | Hapus pengumuman |
| 7 | POST | `/announcements/:id/publish` | Admin | Publikasikan pengumuman |
| 8 | POST | `/announcements/:id/schedule` | Admin | Jadwalkan pengumuman |
| 9 | GET | `/news` | Shared | Daftar berita |
| 10 | GET | `/news/trending` | Shared | Berita trending |
| 11 | GET | `/news/:slug` | Shared | Detail berita |
| 12 | POST | `/news` | Admin | Buat berita baru |
| 13 | PUT | `/news/:slug` | Admin | Perbarui berita |
| 14 | DELETE | `/news/:slug` | Admin | Hapus berita |
| 15 | POST | `/news/:slug/publish` | Admin | Publikasikan berita |
| 16 | POST | `/news/:slug/schedule` | Admin | Jadwalkan berita |
| 17 | GET | `/courses/:id/announcements` | Shared | Daftar pengumuman kursus |
| 18 | POST | `/courses/:id/announcements` | Admin | Buat pengumuman kursus |
| 19 | GET | `/content/search` | Shared | Cari konten |
| 20 | GET | `/content/statistics` | Admin | Ringkasan statistik konten |
| 21 | GET | `/content/statistics/announcements/:id` | Admin | Statistik per pengumuman |
| 22 | GET | `/content/statistics/news/:slug` | Admin | Statistik per berita |
| 23 | GET | `/content/statistics/trending` | Admin | Konten trending |
| 24 | GET | `/content/statistics/most-viewed` | Admin | Konten paling dilihat |
| 25 | POST | `/content/:type/:id/submit` | Shared | Ajukan konten untuk review |
| 26 | POST | `/content/:type/:id/approve` | Admin | Setujui konten |
| 27 | POST | `/content/:type/:id/reject` | Admin | Tolak konten |
| 28 | GET | `/content/pending-review` | Admin | Daftar konten pending review |

---

# A. Pengumuman (Announcements)

---

## 1. Daftar Pengumuman

**GET** `{{url}}/api/v1/announcements`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Contoh | Keterangan |
|-----------|------|--------|------------|
| `filter[unread]` | boolean | `true` | Tampilkan hanya yang belum dibaca |
| `filter[target_type]` | string | `all` | Filter by target: `all`, `role`, `course` |
| `filter[priority]` | string | `high` | Filter by prioritas: `low`, `normal`, `high` |
| `sort` | string | `-published_at` | Urutkan (prefix `-` = descending) |
| `per_page` | integer | `15` | Jumlah per halaman |

### Contoh Request
```
GET {{url}}/api/v1/announcements?filter[unread]=true&per_page=10
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 12,
            "title": "Pembaruan Sistem Penjadwalan Kursus",
            "content": "Kami telah memperbarui sistem penjadwalan...",
            "status": "published",
            "priority": "high",
            "target_type": "all",
            "target_value": null,
            "is_read": false,
            "published_at": "2026-05-01T08:00:00.000000Z",
            "author": {
                "id": 1,
                "name": "Admin Levl"
            }
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

## 2. Detail Pengumuman

**GET** `{{url}}/api/v1/announcements/:id`

> Otomatis menandai pengumuman sebagai **dibaca** dan menambah hitungan `views`.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `12` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/announcements/12
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "announcement": {
            "id": 12,
            "title": "Pembaruan Sistem Penjadwalan Kursus",
            "content": "Kami telah memperbarui sistem penjadwalan kursus. Mulai 1 Juni 2026, semua perubahan jadwal akan diumumkan H-3 sebelum pelaksanaan.",
            "status": "published",
            "priority": "high",
            "target_type": "all",
            "target_value": null,
            "views": 142,
            "published_at": "2026-05-01T08:00:00.000000Z",
            "scheduled_at": null,
            "author": {
                "id": 1,
                "name": "Admin Levl"
            },
            "course": null,
            "revisions": []
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 3. Tandai Pengumuman Dibaca

**POST** `{{url}}/api/v1/announcements/:id/read`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `12` |

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
    "message": "Pengumuman ditandai sebagai sudah dibaca.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## 4. Buat Pengumuman Baru

**POST** `{{url}}/api/v1/announcements`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `title` | string | Ya | Maks 255 karakter |
| `content` | string | Ya | Isi pengumuman (HTML/teks) |
| `target_type` | string | Ya | `all`, `role`, atau `course` |
| `target_value` | string | Tidak | Nama role / ID kursus (jika `target_type` bukan `all`) |
| `course_id` | integer | Tidak | ID kursus (jika `target_type = course`) |
| `priority` | string | Tidak | `low`, `normal`, `high` (default: `normal`) |
| `status` | string | Tidak | `draft`, `published`, `scheduled` |
| `scheduled_at` | datetime | Tidak | Wajib jika `status = scheduled`, harus setelah sekarang |

### Body — Pengumuman untuk semua pengguna, langsung dipublikasikan
```json
{
    "title": "Libur Nasional 1 Juni 2026",
    "content": "Sehubungan dengan libur nasional Hari Lahir Pancasila, aktivitas pembelajaran diliburkan pada 1 Juni 2026.",
    "target_type": "all",
    "priority": "high",
    "status": "published"
}
```

### Body — Pengumuman untuk role tertentu, dijadwalkan
```json
{
    "title": "Panduan Upload Tugas Terbaru",
    "content": "Terdapat pembaruan cara upload tugas. Silakan baca panduan terbaru di menu Bantuan.",
    "target_type": "role",
    "target_value": "Student",
    "priority": "normal",
    "status": "scheduled",
    "scheduled_at": "2026-05-10T08:00:00+07:00"
}
```

### Body — Pengumuman untuk kursus tertentu, disimpan sebagai draft
```json
{
    "title": "Jadwal Sesi Live Modul 3",
    "content": "Sesi live Modul 3 akan dilaksanakan pada Sabtu, 17 Mei 2026 pukul 13.00 WIB.",
    "target_type": "course",
    "course_id": 14,
    "priority": "high",
    "status": "draft"
}
```

### Contoh Response (201) — Berhasil dibuat
```json
{
    "success": true,
    "message": "Pengumuman berhasil dibuat.",
    "data": {
        "announcement": {
            "id": 13,
            "title": "Libur Nasional 1 Juni 2026",
            "content": "Sehubungan dengan libur nasional...",
            "status": "published",
            "priority": "high",
            "target_type": "all",
            "target_value": null,
            "published_at": "2026-05-04T08:31:00.000000Z",
            "scheduled_at": null,
            "author": {
                "id": 1,
                "name": "Admin Levl"
            }
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Validasi gagal
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "title": ["Kolom judul wajib diisi."],
        "target_type": ["Kolom tipe target wajib diisi."]
    }
}
```

---

## 5. Perbarui Pengumuman

**PUT** `{{url}}/api/v1/announcements/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `13` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON) — Semua field opsional
```json
{
    "title": "Libur Nasional 1 Juni 2026 (Diperbarui)",
    "content": "Sehubungan dengan libur nasional Hari Lahir Pancasila, aktivitas pembelajaran diliburkan. Semua deadline tugas digeser 1 hari.",
    "priority": "high",
    "target_type": "all"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pengumuman berhasil diperbarui.",
    "data": {
        "announcement": {
            "id": 13,
            "title": "Libur Nasional 1 Juni 2026 (Diperbarui)",
            "content": "Sehubungan dengan libur nasional Hari Lahir Pancasila...",
            "status": "published",
            "priority": "high",
            "target_type": "all"
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 6. Hapus Pengumuman

**DELETE** `{{url}}/api/v1/announcements/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `13` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pengumuman berhasil dihapus.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## 7. Publikasikan Pengumuman

**POST** `{{url}}/api/v1/announcements/:id/publish`

> Mengubah status pengumuman dari `draft` atau `scheduled` menjadi `published`.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `13` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body
Tidak diperlukan.

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pengumuman berhasil dipublikasikan.",
    "data": {
        "announcement": {
            "id": 13,
            "title": "Libur Nasional 1 Juni 2026",
            "status": "published",
            "published_at": "2026-05-04T09:00:00.000000Z"
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 8. Jadwalkan Pengumuman

**POST** `{{url}}/api/v1/announcements/:id/schedule`

> Mengatur waktu publikasi otomatis di masa depan. Status akan berubah menjadi `scheduled`.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `13` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "scheduled_at": "2026-05-10T08:00:00+07:00"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pengumuman berhasil dijadwalkan.",
    "data": {
        "announcement": {
            "id": 13,
            "title": "Libur Nasional 1 Juni 2026",
            "status": "scheduled",
            "scheduled_at": "2026-05-10T01:00:00.000000Z",
            "published_at": null
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Waktu di masa lalu
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "scheduled_at": ["Kolom waktu jadwal harus setelah now."]
    }
}
```

---

# B. Berita (News)

---

## 9. Daftar Berita

**GET** `{{url}}/api/v1/news`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Contoh | Keterangan |
|-----------|------|--------|------------|
| `filter[featured]` | boolean | `true` | Tampilkan hanya berita unggulan |
| `sort` | string | `-published_at` | Urutkan |
| `per_page` | integer | `15` | Jumlah per halaman |

### Contoh Request
```
GET {{url}}/api/v1/news?filter[featured]=true&per_page=10
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 5,
            "title": "Levl Raih Penghargaan EdTech Terbaik 2026",
            "slug": "levl-raih-penghargaan-edtech-terbaik-2026",
            "excerpt": "Platform Levl berhasil meraih penghargaan EdTech Award 2026 kategori Best LMS.",
            "featured_image": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/news/levl-award.jpg",
            "is_featured": true,
            "status": "published",
            "published_at": "2026-04-20T07:00:00.000000Z",
            "views": 890
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

## 10. Berita Trending

**GET** `{{url}}/api/v1/news/trending`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `limit` | integer | `10` | Jumlah berita trending yang ditampilkan |

### Contoh Request
```
GET {{url}}/api/v1/news/trending?limit=5
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 5,
            "title": "Levl Raih Penghargaan EdTech Terbaik 2026",
            "slug": "levl-raih-penghargaan-edtech-terbaik-2026",
            "views": 890,
            "published_at": "2026-04-20T07:00:00.000000Z"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 11. Detail Berita

**GET** `{{url}}/api/v1/news/:slug`

> Otomatis menambah hitungan `views`.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `levl-raih-penghargaan-edtech-terbaik-2026` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/news/levl-raih-penghargaan-edtech-terbaik-2026
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 5,
        "title": "Levl Raih Penghargaan EdTech Terbaik 2026",
        "slug": "levl-raih-penghargaan-edtech-terbaik-2026",
        "excerpt": "Platform Levl berhasil meraih penghargaan EdTech Award 2026 kategori Best LMS.",
        "content": "Platform Levl berhasil meraih penghargaan EdTech Award 2026...",
        "featured_image": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/news/levl-award.jpg",
        "is_featured": true,
        "status": "published",
        "views": 891,
        "published_at": "2026-04-20T07:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 12. Buat Berita Baru

**POST** `{{url}}/api/v1/news`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (form-data)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `title` | text | Ya | Maks 255 karakter |
| `content` | text | Ya | Isi berita (HTML/teks) |
| `slug` | text | Tidak | Jika kosong, dibuat otomatis dari title |
| `excerpt` | text | Tidak | Ringkasan singkat |
| `featured_image` | file | Tidak | Gambar utama, maks 5MB, format gambar |
| `is_featured` | boolean | Tidak | `true` / `false` |
| `status` | text | Tidak | `draft`, `published`, `scheduled` |
| `scheduled_at` | text | Tidak | ISO 8601 datetime, harus setelah sekarang |
| `category_ids[]` | integer | Tidak | ID kategori konten |
| `tag_ids[]` | integer | Tidak | ID tag |

### Body — Berita langsung publish, dengan gambar (form-data)
| Key | Type | Value |
|-----|------|-------|
| `title` | Text | `Levl Luncurkan Fitur Kuis Adaptif` |
| `content` | Text | `Hari ini, Levl secara resmi meluncurkan fitur Kuis Adaptif...` |
| `excerpt` | Text | `Fitur terbaru Levl menghadirkan pengalaman kuis yang disesuaikan.` |
| `featured_image` | File | `[FILE: kuis-adaptif.jpg]` (maks 5MB) |
| `is_featured` | Text | `true` |
| `status` | Text | `published` |

### Body — Berita draft, tanpa gambar (raw JSON jika tidak ada file)
```json
{
    "title": "Levl Luncurkan Fitur Kuis Adaptif",
    "content": "Hari ini, Levl secara resmi meluncurkan fitur Kuis Adaptif yang memungkinkan...",
    "excerpt": "Fitur terbaru Levl menghadirkan pengalaman kuis yang disesuaikan.",
    "is_featured": false,
    "status": "draft"
}
```

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Berita berhasil dibuat.",
    "data": {
        "id": 6,
        "title": "Levl Luncurkan Fitur Kuis Adaptif",
        "slug": "levl-luncurkan-fitur-kuis-adaptif",
        "excerpt": "Fitur terbaru Levl menghadirkan pengalaman kuis yang disesuaikan.",
        "featured_image": null,
        "is_featured": false,
        "status": "draft",
        "published_at": null
    },
    "meta": null,
    "errors": null
}
```

---

## 13. Perbarui Berita

**PUT** `{{url}}/api/v1/news/:slug`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `levl-luncurkan-fitur-kuis-adaptif` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (form-data atau raw JSON)
```json
{
    "title": "Levl Luncurkan Fitur Kuis Adaptif (Update)",
    "excerpt": "Fitur terbaru telah ditingkatkan dengan AI engine.",
    "is_featured": true
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Berita berhasil diperbarui.",
    "data": {
        "id": 6,
        "title": "Levl Luncurkan Fitur Kuis Adaptif (Update)",
        "slug": "levl-luncurkan-fitur-kuis-adaptif",
        "is_featured": true,
        "status": "draft"
    },
    "meta": null,
    "errors": null
}
```

---

## 14. Hapus Berita

**DELETE** `{{url}}/api/v1/news/:slug`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `levl-luncurkan-fitur-kuis-adaptif` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Berita berhasil dihapus.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 15. Publikasikan Berita

**POST** `{{url}}/api/v1/news/:slug/publish`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `levl-luncurkan-fitur-kuis-adaptif` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body
Tidak diperlukan.

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Berita berhasil dipublikasikan.",
    "data": {
        "id": 6,
        "title": "Levl Luncurkan Fitur Kuis Adaptif (Update)",
        "slug": "levl-luncurkan-fitur-kuis-adaptif",
        "status": "published",
        "published_at": "2026-05-04T09:30:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 16. Jadwalkan Berita

**POST** `{{url}}/api/v1/news/:slug/schedule`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `levl-luncurkan-fitur-kuis-adaptif` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "scheduled_at": "2026-05-15T07:00:00+07:00"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Berita berhasil dijadwalkan.",
    "data": {
        "id": 6,
        "status": "scheduled",
        "scheduled_at": "2026-05-15T00:00:00.000000Z",
        "published_at": null
    },
    "meta": null,
    "errors": null
}
```

---

# C. Pengumuman Kursus

---

## 17. Daftar Pengumuman Kursus

**GET** `{{url}}/api/v1/courses/:course_id/announcements`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_id` | integer | `14` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/courses/14/announcements
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 14,
            "title": "Jadwal Sesi Live Modul 3",
            "content": "Sesi live Modul 3 akan dilaksanakan pada Sabtu, 17 Mei 2026 pukul 13.00 WIB.",
            "status": "published",
            "priority": "high",
            "target_type": "course",
            "published_at": "2026-05-04T09:00:00.000000Z"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 18. Buat Pengumuman Kursus

**POST** `{{url}}/api/v1/courses/:course_id/announcements`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_id` | integer | `14` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "title": "Perubahan Jadwal UAS Kursus Manajemen Proyek",
    "content": "Ujian Akhir Skema (UAS) untuk kursus Manajemen Proyek diundur ke tanggal 25 Mei 2026.",
    "priority": "high",
    "status": "published"
}
```

> `target_type` dan `course_id` diisi otomatis oleh sistem dari path parameter.

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Pengumuman kursus berhasil dibuat.",
    "data": {
        "id": 15,
        "title": "Perubahan Jadwal UAS Kursus Manajemen Proyek",
        "content": "Ujian Akhir Skema (UAS) untuk kursus...",
        "status": "published",
        "priority": "high",
        "target_type": "course",
        "course_id": 14,
        "published_at": "2026-05-04T09:15:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

# D. Pencarian & Statistik

---

## 19. Cari Konten

**GET** `{{url}}/api/v1/content/search`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `q` | string | Kata kunci pencarian |

### Contoh Request
```
GET {{url}}/api/v1/content/search?q=jadwal+live
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 14,
            "type": "announcement",
            "title": "Jadwal Sesi Live Modul 3",
            "excerpt": "Sesi live Modul 3 akan dilaksanakan...",
            "published_at": "2026-05-04T09:00:00.000000Z"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 20. Ringkasan Statistik Konten (Admin)

**GET** `{{url}}/api/v1/content/statistics`

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
        "total_announcements": 15,
        "total_news": 6,
        "published_announcements": 12,
        "published_news": 5,
        "total_views": 2341,
        "unread_total": 184
    },
    "meta": null,
    "errors": null
}
```

---

## 21. Statistik per Pengumuman (Admin)

**GET** `{{url}}/api/v1/content/statistics/announcements/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `12` |

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
        "id": 12,
        "title": "Pembaruan Sistem Penjadwalan Kursus",
        "views": 142,
        "read_count": 98,
        "unread_count": 44,
        "published_at": "2026-05-01T08:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 22. Statistik per Berita (Admin)

**GET** `{{url}}/api/v1/content/statistics/news/:slug`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `slug` | string | `levl-raih-penghargaan-edtech-terbaik-2026` |

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
        "id": 5,
        "title": "Levl Raih Penghargaan EdTech Terbaik 2026",
        "slug": "levl-raih-penghargaan-edtech-terbaik-2026",
        "views": 891,
        "published_at": "2026-04-20T07:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 23. Konten Trending (Admin)

**GET** `{{url}}/api/v1/content/statistics/trending`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 5,
            "type": "news",
            "title": "Levl Raih Penghargaan EdTech Terbaik 2026",
            "views": 891
        },
        {
            "id": 12,
            "type": "announcement",
            "title": "Pembaruan Sistem Penjadwalan Kursus",
            "views": 142
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 24. Konten Paling Dilihat (Admin)

**GET** `{{url}}/api/v1/content/statistics/most-viewed`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 5,
            "type": "news",
            "title": "Levl Raih Penghargaan EdTech Terbaik 2026",
            "views": 891
        }
    ],
    "meta": null,
    "errors": null
}
```

---

# E. Persetujuan Konten (Admin)

---

## 25. Ajukan Konten untuk Review

**POST** `{{url}}/api/v1/content/:type/:id/submit`

### Path Parameter
| Parameter | Tipe | Nilai Valid | Contoh |
|-----------|------|------------|--------|
| `type` | string | `announcement`, `news` | `announcement` |
| `id` | integer | ID konten | `13` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body
Tidak diperlukan.

### Contoh Request
```
POST {{url}}/api/v1/content/announcement/13/submit
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Konten berhasil diajukan untuk review.",
    "data": {
        "id": 13,
        "status": "submitted"
    },
    "meta": null,
    "errors": null
}
```

---

## 26. Setujui Konten

**POST** `{{url}}/api/v1/content/:type/:id/approve`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `type` | string | `announcement` |
| `id` | integer | `13` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body
Tidak diperlukan.

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Konten berhasil disetujui.",
    "data": {
        "id": 13,
        "status": "approved"
    },
    "meta": null,
    "errors": null
}
```

---

## 27. Tolak Konten

**POST** `{{url}}/api/v1/content/:type/:id/reject`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `type` | string | `news` |
| `id` | integer | `6` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body
Tidak diperlukan.

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Konten berhasil ditolak.",
    "data": {
        "id": 6,
        "status": "rejected"
    },
    "meta": null,
    "errors": null
}
```

---

## 28. Daftar Konten Pending Review

**GET** `{{url}}/api/v1/content/pending-review`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 13,
            "type": "announcement",
            "title": "Libur Nasional 1 Juni 2026",
            "status": "submitted",
            "submitted_by": {
                "id": 3,
                "name": "Instruktur Budi"
            },
            "created_at": "2026-05-04T08:00:00.000000Z"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## Referensi Enum

### `status` (ContentStatus)
| Nilai | Keterangan |
|-------|------------|
| `draft` | Draft, belum dipublikasikan |
| `submitted` | Diajukan untuk review |
| `in_review` | Sedang ditinjau |
| `approved` | Disetujui |
| `rejected` | Ditolak |
| `scheduled` | Terjadwal otomatis |
| `published` | Sudah dipublikasikan |
| `archived` | Diarsipkan |

### `target_type` (Announcement)
| Nilai | Keterangan |
|-------|------------|
| `all` | Semua pengguna |
| `role` | Role tertentu (isi `target_value` dengan nama role) |
| `course` | Kursus tertentu (isi `course_id`) |

### `priority` (Announcement)
| Nilai | Keterangan |
|-------|------------|
| `low` | Prioritas rendah |
| `normal` | Prioritas normal (default) |
| `high` | Prioritas tinggi |
