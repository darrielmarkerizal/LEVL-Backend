# Dokumentasi Postman — Notifikasi

Dokumentasi lengkap untuk seluruh endpoint **Notifikasi**, termasuk manajemen notifikasi personal, preferensi, perangkat FCM, dan sistem Post (broadcast).

> Base URL: `{{url}}/api/v1`
> Token Shared: `{{access_token_student}}`
> Token Admin: `{{access_token_admin}}`

> **Keterangan kolom FE:** ✅ = aktif digunakan FE saat ini | ⬜ = tersedia di BE, belum dipakai FE

---

## Struktur Folder Postman yang Direkomendasikan

```
📁 Notifikasi
 ┣ 📁 Notifikasi Saya (Shared)
 ┃ ┣ 📄 [GET] Daftar Notifikasi            ✅ FE
 ┃ ┣ 📄 [GET] Detail Notifikasi            ⬜
 ┃ ┣ 📄 [PUT] Tandai Satu Notifikasi Dibaca ✅ FE
 ┃ ┣ 📄 [POST] Tandai Semua Dibaca         ✅ FE
 ┃ ┗ 📄 [DELETE] Hapus Notifikasi          ⬜
 ┣ 📁 Preferensi Notifikasi (Shared)       ⬜
 ┃ ┣ 📄 [GET] Lihat Preferensi
 ┃ ┣ 📄 [PUT] Perbarui Preferensi
 ┃ ┗ 📄 [POST] Reset ke Default
 ┣ 📁 Perangkat FCM (Shared)               ⬜
 ┃ ┣ 📄 [POST] Daftarkan Token FCM
 ┃ ┗ 📄 [DELETE] Hapus Token FCM
 ┣ 📁 Post / Broadcast — Student (Shared)  ⬜
 ┃ ┣ 📄 [GET] Daftar Post
 ┃ ┣ 📄 [GET] Post Tersematkan
 ┃ ┣ 📄 [GET] Detail Post
 ┃ ┗ 📄 [POST] Tandai Post Dilihat
 ┗ 📁 Post / Broadcast — Admin             ✅ FE (fitur Informasi)
   ┣ 📄 [GET] Daftar Post Admin            ✅ FE
   ┣ 📄 [GET] Detail Post Admin            ✅ FE
   ┣ 📄 [POST] Buat Post Baru              ✅ FE
   ┣ 📄 [PUT] Perbarui Post                ✅ FE
   ┣ 📄 [DELETE] Hapus Post                ✅ FE
   ┣ 📄 [POST] Publikasikan Post           ✅ FE
   ┣ 📄 [POST] Batalkan Publikasi          ✅ FE
   ┣ 📄 [POST] Hapus Massal                ✅ FE
   ┣ 📄 [POST] Publikasi Massal            ✅ FE
   ┣ 📄 [POST] Jadwalkan Post              ⬜
   ┣ 📄 [POST] Batalkan Jadwal             ⬜
   ┣ 📄 [POST] Toggle Pin Post             ⬜
    ┣ 📄 [GET] Trash Bin Post               ⬜
   ┣ 📄 [POST] Pulihkan Post               ⬜
   ┣ 📄 [DELETE] Hapus Permanen            ⬜
   ┗ 📄 [POST] Unggah Gambar Post          ⬜
```

---

## Daftar Endpoint

| No | Method | Endpoint | Role | Keterangan | FE |
|----|--------|----------|------|------------|----|
| 1 | GET | `/notifications` | Shared | Daftar notifikasi + jumlah belum dibaca | ✅ |
| 2 | GET | `/notifications/:id` | Shared | Detail satu notifikasi | ⬜ |
| 3 | PUT | `/notifications/:id` | Shared | Tandai satu notifikasi dibaca | ✅ |
| 4 | POST | `/notifications/read-all` | Shared | Tandai semua notifikasi dibaca | ✅ |
| 5 | DELETE | `/notifications/:id` | Shared | Hapus notifikasi | ⬜ |
| 6 | GET | `/notification-preferences` | Shared | Lihat preferensi notifikasi | ⬜ |
| 7 | PUT | `/notification-preferences` | Shared | Perbarui preferensi notifikasi | ⬜ |
| 8 | POST | `/notification-preferences/reset` | Shared | Reset preferensi ke default | ⬜ |
| 9 | POST | `/notification-device/fcm-token` | Shared | Daftarkan token FCM perangkat | ⬜ |
| 10 | DELETE | `/notification-device/fcm-token` | Shared | Hapus token FCM perangkat | ⬜ |
| 11 | GET | `/posts` | Shared | Daftar post (student-facing) | ⬜ |
| 12 | GET | `/posts/pinned` | Shared | Post tersematkan (student-facing) | ⬜ |
| 13 | GET | `/posts/:uuid` | Shared | Detail post (student-facing) | ⬜ |
| 14 | POST | `/posts/:uuid/view` | Shared | Tandai post sudah dilihat | ⬜ |
| 15 | GET | `/admin/posts` | Admin | Daftar post (admin view) | ✅ |
| 16 | GET | `/admin/posts/:uuid` | Admin | Detail post (admin view) | ✅ |
| 17 | POST | `/admin/posts` | Admin | Buat post baru | ✅ |
| 18 | PUT | `/admin/posts/:uuid` | Admin | Perbarui post | ✅ |
| 19 | DELETE | `/admin/posts/:uuid` | Admin | Hapus post (soft delete) | ✅ |
| 20 | POST | `/admin/posts/:uuid/publish` | Admin | Publikasikan post | ✅ |
| 21 | POST | `/admin/posts/:uuid/unpublish` | Admin | Batalkan publikasi | ✅ |
| 25 | POST | `/admin/posts/bulk-delete` | Admin | Hapus massal (maks 50) | ✅ |
| 26 | POST | `/admin/posts/bulk-publish` | Admin | Publikasi massal (maks 50) | ✅ |
| 27 | GET | `/trash-bins` | Admin / Superadmin / Instructor | Daftar Trash Bin lintas resource | ✅ |
| 28 | PATCH | `/trash-bins/{trashBinId}` | Admin / Superadmin / Instructor | Pulihkan item dari Trash Bin | ✅ |
| 29 | DELETE | `/trash-bins/{trashBinId}` | Admin / Superadmin / Instructor | Hapus permanen item Trash Bin | ✅ |

---

# A. Notifikasi Saya (Shared)

---

## 1. Daftar Notifikasi

**GET** `{{url}}/api/v1/notifications`

> Response menyertakan `unread_count` di dalam `meta` untuk badge counter.

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `per_page` | integer | `15` | Jumlah per halaman (maks 100) |

### Contoh Request
```
GET {{url}}/api/v1/notifications?per_page=20
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diambil.",
    "data": [
        {
            "id": 1042,
            "type": "grading",
            "title": "Nilai Tugas Tersedia",
            "message": "Nilai tugas 'Analisis Risiko Proyek' telah dirilis. Nilai Anda: 82.5",
            "is_read": false,
            "action_url": "/assignments/88/submissions/701",
            "data": {
                "submission_id": 701,
                "assignment_id": 88
            },
            "created_at": "2026-05-04T09:00:00.000000Z",
            "read_at": null
        },
        {
            "id": 1041,
            "type": "forum_reply_to_thread",
            "title": "Balasan Baru di Thread Anda",
            "message": "Instruktur Budi membalas thread 'Bagaimana cara menghitung critical path?'",
            "is_read": true,
            "action_url": "/courses/manajemen-proyek-26/forum/threads/46",
            "data": {
                "thread_id": 46,
                "reply_id": 201
            },
            "created_at": "2026-05-04T08:30:00.000000Z",
            "read_at": "2026-05-04T08:35:00.000000Z"
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 2
        },
        "unread_count": 1
    },
    "errors": null
}
```

---

## 2. Detail Notifikasi

**GET** `{{url}}/api/v1/notifications/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `1042` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diambil.",
    "data": {
        "id": 1042,
        "type": "grading",
        "title": "Nilai Tugas Tersedia",
        "message": "Nilai tugas 'Analisis Risiko Proyek' telah dirilis. Nilai Anda: 82.5",
        "is_read": false,
        "action_url": "/assignments/88/submissions/701",
        "data": {
            "submission_id": 701,
            "assignment_id": 88
        },
        "created_at": "2026-05-04T09:00:00.000000Z",
        "read_at": null
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (404) — Tidak ditemukan / bukan milik user
```json
{
    "success": false,
    "message": "Data tidak ditemukan.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 3. Tandai Satu Notifikasi Dibaca

**PUT** `{{url}}/api/v1/notifications/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `1042` |

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
    "message": "Data berhasil diperbarui.",
    "data": {
        "id": 1042,
        "type": "grading",
        "title": "Nilai Tugas Tersedia",
        "is_read": true,
        "read_at": "2026-05-04T10:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 4. Tandai Semua Notifikasi Dibaca

**POST** `{{url}}/api/v1/notifications/read-all`

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
    "message": "Data berhasil diperbarui.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## 5. Hapus Notifikasi

**DELETE** `{{url}}/api/v1/notifications/:id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `id` | integer | `1042` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil dihapus.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

# B. Preferensi Notifikasi (Shared)

---

## 6. Lihat Preferensi Notifikasi

**GET** `{{url}}/api/v1/notification-preferences`

> Response menyertakan daftar `categories`, `channels`, dan `frequencies` yang valid di dalam `meta`.

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "category": "grading",
            "channel": "in_app",
            "enabled": true,
            "frequency": "immediate"
        },
        {
            "category": "grading",
            "channel": "email",
            "enabled": true,
            "frequency": "immediate"
        },
        {
            "category": "forum",
            "channel": "in_app",
            "enabled": true,
            "frequency": "immediate"
        },
        {
            "category": "forum",
            "channel": "push",
            "enabled": false,
            "frequency": "daily"
        }
    ],
    "meta": {
        "categories": [
            "system", "assignment", "assessment", "grading", "gamification",
            "custom", "course_completed", "course_updates", "assignments",
            "forum", "achievements", "enrollment", "forum_reply_to_thread",
            "forum_reply_to_reply", "forum_reaction_thread", "forum_reaction_reply"
        ],
        "channels": ["in_app", "email", "push"],
        "frequencies": ["immediate", "daily", "weekly", "never"]
    },
    "errors": null
}
```

---

## 7. Perbarui Preferensi Notifikasi

**PUT** `{{url}}/api/v1/notification-preferences`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (raw JSON)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `preferences` | array | Ya | Array preferensi yang akan diperbarui |
| `preferences[].category` | string | Ya | Kategori notifikasi (lihat enum) |
| `preferences[].channel` | string | Ya | Channel: `in_app`, `email`, `push` |
| `preferences[].enabled` | boolean | Ya | Aktif/nonaktif |
| `preferences[].frequency` | string | Ya | `immediate`, `daily`, `weekly`, `never` |

```json
{
    "preferences": [
        {
            "category": "grading",
            "channel": "email",
            "enabled": true,
            "frequency": "immediate"
        },
        {
            "category": "forum",
            "channel": "push",
            "enabled": false,
            "frequency": "never"
        },
        {
            "category": "assignments",
            "channel": "in_app",
            "enabled": true,
            "frequency": "immediate"
        }
    ]
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Preferensi notifikasi berhasil diperbarui.",
    "data": [
        {
            "category": "grading",
            "channel": "email",
            "enabled": true,
            "frequency": "immediate"
        },
        {
            "category": "forum",
            "channel": "push",
            "enabled": false,
            "frequency": "never"
        }
    ],
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Nilai tidak valid
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "preferences.0.channel": ["Nilai yang dipilih untuk channel tidak valid."],
        "preferences.0.frequency": ["Nilai yang dipilih untuk frequency tidak valid."]
    }
}
```

---

## 8. Reset Preferensi ke Default

**POST** `{{url}}/api/v1/notification-preferences/reset`

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
    "message": "Preferensi notifikasi berhasil direset.",
    "data": [
        {
            "category": "grading",
            "channel": "in_app",
            "enabled": true,
            "frequency": "immediate"
        },
        {
            "category": "forum",
            "channel": "in_app",
            "enabled": true,
            "frequency": "immediate"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

# C. Perangkat FCM (Push Notification)

---

## 9. Daftarkan Token FCM Perangkat

**POST** `{{url}}/api/v1/notification-device/fcm-token`

> Menyimpan token FCM untuk perangkat aktif user saat ini. Token lama akan ditimpa.

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (raw JSON)
```json
{
    "fcm_token": "eQ6K4ZpBR3OdX...firebase-messaging-token-string"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `fcm_token` | string | Ya | Token FCM dari Firebase SDK, maks 4096 karakter |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diperbarui.",
    "data": {
        "fcm_token_registered": true
    },
    "meta": null,
    "errors": null
}
```

---

## 10. Hapus Token FCM Perangkat

**DELETE** `{{url}}/api/v1/notification-device/fcm-token`

> Menghapus token FCM user. Push notification tidak akan diterima setelah ini.

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
    "message": "Data berhasil diperbarui.",
    "data": {
        "fcm_token_registered": false
    },
    "meta": null,
    "errors": null
}
```

---

# E. Post / Broadcast — Admin ✅ FE (Fitur Informasi)

Endpoint admin untuk mengelola Post/Broadcast. Seluruh fitur **Manajemen Informasi** di FE menggunakan endpoint `/admin/posts` ini.

---

## 15. Daftar Post — Admin View ✅ FE

**GET** `{{url}}/api/v1/admin/posts`

> Menampilkan daftar semua post tanpa filter role. Digunakan di halaman Manajemen Informasi.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `per_page` | integer | Jumlah per halaman (maks 100, default 15) |
| `page` | integer | Halaman |
| `search` | string | Cari berdasarkan judul |
| `status` | string | Filter: `draft`, `published`, `scheduled` |
| `category` | string | Filter berdasarkan kategori |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Data berhasil diambil.",
    "data": [
        {
            "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
            "title": "Pembaruan Sistem Levl v2.5",
            "category": {
                "value": "system",
                "label": "Sistem",
                "icon": "settings"
            },
            "status": {
                "value": "published",
                "label": "Dipublikasikan"
            },
            "is_pinned": false,
            "author_name": "Admin Levl",
            "last_editor": { "id": 1, "name": "Admin Levl" },
            "view_count": 42,
            "published_at": "2026-05-01T08:00:00.000000Z",
            "created_at": "2026-04-30T10:00:00.000000Z"
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 1,
            "last_page": 1
        }
    },
    "errors": null
}
```

---

## 16. Detail Post — Admin View ✅ FE

**GET** `{{url}}/api/v1/admin/posts/:uuid`

> Menampilkan detail lengkap post termasuk audiences, notification channels, dan editor terakhir.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `uuid` | string | `a1b2c3d4-e5f6-7890-abcd-ef1234567890` |

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
        "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "title": "Pembaruan Sistem Levl v2.5",
        "slug": "pembaruan-sistem-levl-v25",
        "content": "Kami dengan bangga mengumumkan...",
        "category": {
            "value": "system",
            "label": "Sistem",
            "icon": "settings"
        },
        "status": {
            "value": "published",
            "label": "Dipublikasikan"
        },
        "is_pinned": false,
        "author": { "id": 1, "name": "Admin Levl", "email": "admin@levl.id" },
        "last_editor": { "id": 1, "name": "Admin Levl" },
        "audiences": [
            { "role": "student", "label": "Peserta" }
        ],
        "notification_channels": [
            { "channel": "in_app", "sent_at": "2026-05-01T08:00:00.000000Z" },
            { "channel": "email", "sent_at": "2026-05-01T08:00:05.000000Z" }
        ],
        "view_count": 42,
        "scheduled_at": null,
        "published_at": "2026-05-01T08:00:00.000000Z",
        "created_at": "2026-04-30T10:00:00.000000Z",
        "updated_at": "2026-05-01T08:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (404)
```json
{
    "success": false,
    "message": "Data tidak ditemukan.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 17. Buat Post Baru (Admin)

**POST** `{{url}}/api/v1/admin/posts`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `title` | string | Ya | Maks 255 karakter |
| `content` | string | Ya | Isi post (HTML/teks) |
| `category` | string | Ya | Lihat enum `PostCategory` |
| `status` | string | Ya | `draft`, `published`, `scheduled` |
| `audiences` | array | Ya | Min 1: `student`, `instructor`, `admin` |
| `notification_channels` | array | Tidak | `in_app`, `email`, `push` |
| `is_pinned` | boolean | Tidak | Default `false` |
| `scheduled_at` | datetime | Wajib jika `status=scheduled` | ISO 8601, harus setelah sekarang |

### Body — Post langsung publish ke semua student
```json
{
    "title": "Pembaruan Sistem Levl v2.5",
    "content": "Kami dengan bangga mengumumkan pembaruan sistem Levl versi 2.5 yang membawa banyak peningkatan performa...",
    "category": "system",
    "status": "published",
    "audiences": ["student"],
    "notification_channels": ["in_app", "email"],
    "is_pinned": false
}
```

### Body — Post terjadwal untuk semua role
```json
{
    "title": "Maintenance Sistem 10 Mei 2026",
    "content": "Sistem akan mengalami maintenance pada 10 Mei 2026 pukul 00.00 - 04.00 WIB.",
    "category": "warning",
    "status": "scheduled",
    "audiences": ["student", "instructor", "admin"],
    "notification_channels": ["in_app", "push"],
    "is_pinned": true,
    "scheduled_at": "2026-05-09T17:00:00+00:00"
}
```

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Post berhasil dibuat.",
    "data": {
        "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "title": "Pembaruan Sistem Levl v2.5",
        "category": "system",
        "status": "published",
        "is_pinned": false,
        "audiences": ["student"],
        "notification_channels": ["in_app", "email"],
        "published_at": "2026-05-04T10:00:00.000000Z",
        "scheduled_at": null
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
        "audiences": ["Kolom audiences wajib diisi."],
        "scheduled_at": ["Kolom scheduled_at wajib diisi jika status adalah scheduled."]
    }
}
```

---

## 18. Perbarui Post (Admin)

**PUT** `{{url}}/api/v1/admin/posts/:uuid`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `uuid` | string | `a1b2c3d4-e5f6-7890-abcd-ef1234567890` |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON) — Semua field opsional
```json
{
    "title": "Pembaruan Sistem Levl v2.5 (Revisi)",
    "content": "Kami dengan bangga mengumumkan pembaruan sistem Levl versi 2.5...",
    "is_pinned": true,
    "resend_notification_channels": ["in_app"]
}
```

> `resend_notification_channels` digunakan untuk mengirim ulang notifikasi ke channel tertentu setelah update.

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Post berhasil diperbarui.",
    "data": {
        "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "title": "Pembaruan Sistem Levl v2.5 (Revisi)",
        "is_pinned": true,
        "status": "published"
    },
    "meta": null,
    "errors": null
}
```

---

## 19. Hapus Post (Soft Delete) (Admin)

**DELETE** `{{url}}/api/v1/admin/posts/:uuid`

> Post dipindahkan ke Trash Bin (soft delete). Bisa dipulihkan.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Post berhasil dihapus.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## 20. Publikasikan Post (Admin)

**POST** `{{url}}/api/v1/admin/posts/:uuid/publish`

> Mengubah status post dari `draft` atau `scheduled` menjadi `published`.

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
    "message": "Post berhasil dipublikasikan.",
    "data": {
        "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "status": "published",
        "published_at": "2026-05-04T11:00:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 21. Batalkan Publikasi (Admin)

**POST** `{{url}}/api/v1/admin/posts/:uuid/unpublish`

> Mengembalikan post ke status `draft`.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Publikasi post berhasil dibatalkan.",
    "data": {
        "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "status": "draft",
        "published_at": null
    },
    "meta": null,
    "errors": null
}
```

---

## 22. Jadwalkan Post (Admin)

## 25. Hapus Massal (Admin)

**POST** `{{url}}/api/v1/admin/posts/bulk-delete`

> Menghapus (soft delete) hingga 50 post sekaligus. Diproses secara asinkron (queue).

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "post_uuids": [
        "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "b2c3d4e5-f6a7-8901-bcde-f01234567891"
    ]
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Penghapusan massal sedang diproses.",
    "data": [],
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Melebihi batas 50
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "post_uuids": ["Jumlah post melebihi batas maksimum."]
    }
}
```

---

## 26. Publikasi Massal (Admin)

**POST** `{{url}}/api/v1/admin/posts/bulk-publish`

> Mempublikasikan hingga 50 post sekaligus. Diproses secara asinkron (queue).

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Body (raw JSON)
```json
{
    "post_uuids": [
        "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "b2c3d4e5-f6a7-8901-bcde-f01234567891"
    ]
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Publikasi massal sedang diproses.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## 27. Trash Bin Lintas Resource

Gunakan endpoint trash-bins yang disediakan modul Trash untuk melihat, memulihkan, dan menghapus permanen item di Trash Bin lintas resource, termasuk post.

---

## Referensi Enum

### `type` (NotificationType) — Kategori notifikasi sistem
| Nilai | Keterangan |
|-------|------------|
| `system` | Notifikasi sistem umum |
| `assignment` | Terkait tugas |
| `assessment` | Terkait penilaian |
| `grading` | Nilai sudah dirilis |
| `gamification` | XP, lencana, level |
| `course_completed` | Kursus selesai |
| `course_updates` | Pembaruan kursus |
| `assignments` | Pengumpulan tugas |
| `forum` | Aktivitas forum |
| `achievements` | Pencapaian baru |
| `enrollment` | Status pendaftaran kursus |
| `forum_reply_to_thread` | Balasan di thread Anda |
| `forum_reply_to_reply` | Balasan di balasan Anda |
| `forum_reaction_thread` | Reaksi di thread Anda |
| `forum_reaction_reply` | Reaksi di balasan Anda |
| `custom` | Notifikasi kustom |

### `channel` (NotificationChannel)
| Nilai | Keterangan |
|-------|------------|
| `in_app` | Notifikasi dalam aplikasi |
| `email` | Email |
| `push` | Push notification (FCM) |

### `frequency` (NotificationFrequency)
| Nilai | Keterangan |
|-------|------------|
| `immediate` | Langsung saat terjadi |
| `daily` | Rangkuman harian |
| `weekly` | Rangkuman mingguan |
| `never` | Dinonaktifkan |

### `category` (PostCategory)
| Nilai | Keterangan |
|-------|------------|
| `announcement` | Pengumuman |
| `information` | Informasi umum |
| `warning` | Peringatan |
| `system` | Sistem |
| `award` | Penghargaan |
| `gamification` | Gamifikasi |

### `audiences` (PostAudienceRole)
| Nilai | Keterangan |
|-------|------------|
| `student` | Asesi / Student |
| `instructor` | Instruktur |
| `admin` | Admin |

### `status` (PostStatus)
| Nilai | Keterangan |
|-------|------------|
| `draft` | Draft, belum dipublikasikan |
| `scheduled` | Terjadwal otomatis |
| `published` | Sudah dipublikasikan |
