# Dokumentasi Postman — Forum Diskusi

Dokumentasi lengkap untuk seluruh endpoint **Forum Diskusi** per kursus, termasuk thread, balasan, reaksi, dan statistik.

> Base URL: `{{url}}/api/v1`
> Token Shared: `{{access_token_student}}`
> Token Admin/Instruktur: `{{access_token_admin}}`

---

## Catatan Penting

- **Akses Forum**: Semua endpoint forum per-kursus (`/courses/:slug/forum/...`) memerlukan user **sudah terdaftar (enrolled)** di kursus tersebut. Admin & Instruktur dikecualikan.
- **Mention**: Gunakan `@username` dalam `content` untuk mention user. Username yang tidak valid akan menghasilkan error validasi.
- **Attachment**: Setiap thread/reply dapat menyertakan hingga **5 file** (gambar, PDF, video). Maks 50MB/file.
- **Nested Replies**: Balasan mendukung hierarki bertingkat. Gunakan `parent_id` untuk membalas balasan tertentu.

---

## Struktur Folder Postman yang Direkomendasikan

```
📁 Forum
 ┣ 📁 Dashboard Forum (Admin)
 ┃ ┣ 📄 [GET] Semua Thread (Admin)
 ┃ ┣ 📄 [GET] Thread Trending (Admin)
 ┃ ┗ 📄 [GET] Thread Saya
 ┣ 📁 Thread Kursus
 ┃ ┣ 📄 [GET] Daftar Thread
 ┃ ┣ 📄 [POST] Buat Thread Baru
 ┃ ┣ 📄 [GET] Detail Thread
 ┃ ┣ 📄 [PATCH] Edit Thread
 ┃ ┣ 📄 [DELETE] Hapus Thread
 ┃ ┣ 📄 [PATCH] Pin Thread (Admin)
 ┃ ┣ 📄 [PATCH] Unpin Thread (Admin)
 ┃ ┣ 📄 [PATCH] Tutup Thread (Admin)
 ┃ ┣ 📄 [PATCH] Buka Thread (Admin)
 ┃ ┣ 📄 [PATCH] Tandai Terselesaikan
 ┃ ┗ 📄 [PATCH] Batalkan Terselesaikan
 ┣ 📁 Balasan (Replies)
 ┃ ┣ 📄 [GET] Daftar Balasan Thread
 ┃ ┣ 📄 [POST] Balas Thread / Balasan
 ┃ ┣ 📄 [GET] Balasan Anak (Children)
 ┃ ┣ 📄 [PATCH] Edit Balasan
 ┃ ┣ 📄 [DELETE] Hapus Balasan
 ┃ ┣ 📄 [PATCH] Tandai Jawaban Terbaik
 ┃ ┗ 📄 [PATCH] Batalkan Jawaban Terbaik
 ┣ 📁 Reaksi (Reactions)
 ┃ ┣ 📄 [POST] Reaksi Thread
 ┃ ┣ 📄 [DELETE] Hapus Reaksi Thread
 ┃ ┣ 📄 [POST] Reaksi Balasan
 ┃ ┗ 📄 [DELETE] Hapus Reaksi Balasan
 ┗ 📁 Statistik Forum
   ┣ 📄 [GET] Statistik Forum Kursus
   ┗ 📄 [GET] Statistik Forum Saya
```

---

## Daftar Endpoint

| No | Method | Endpoint | Role | Keterangan |
|----|--------|----------|------|------------|
| 1 | GET | `/forums/threads` | Admin | Semua thread lintas kursus |
| 2 | GET | `/forums/threads/trending` | Admin | Thread trending |
| 3 | GET | `/forums/my-threads` | Shared | Thread yang saya buat |
| 4 | GET | `/courses/:slug/forum/threads` | Enrolled | Daftar thread kursus |
| 5 | POST | `/courses/:slug/forum/threads` | Enrolled | Buat thread baru |
| 6 | GET | `/courses/:slug/forum/threads/:thread` | Enrolled | Detail thread |
| 7 | PATCH | `/courses/:slug/forum/threads/:thread` | Owner | Edit thread |
| 8 | DELETE | `/courses/:slug/forum/threads/:thread` | Owner/Admin | Hapus thread |
| 9 | PATCH | `/courses/:slug/forum/threads/:thread/pin` | Admin | Pin thread |
| 10 | PATCH | `/courses/:slug/forum/threads/:thread/unpin` | Admin | Unpin thread |
| 11 | PATCH | `/courses/:slug/forum/threads/:thread/close` | Admin | Tutup thread |
| 12 | PATCH | `/courses/:slug/forum/threads/:thread/open` | Admin | Buka thread |
| 13 | PATCH | `/courses/:slug/forum/threads/:thread/resolve` | Owner | Tandai terselesaikan |
| 14 | PATCH | `/courses/:slug/forum/threads/:thread/unresolve` | Owner | Batalkan terselesaikan |
| 15 | GET | `/courses/:slug/forum/threads/:thread/replies` | Enrolled | Daftar balasan |
| 16 | POST | `/courses/:slug/forum/threads/:thread/replies` | Enrolled | Balas thread |
| 17 | GET | `/courses/:slug/forum/threads/:thread/replies/:reply/children` | Enrolled | Balasan anak |
| 18 | PATCH | `/courses/:slug/forum/threads/:thread/replies/:reply` | Owner | Edit balasan |
| 19 | DELETE | `/courses/:slug/forum/threads/:thread/replies/:reply` | Owner/Admin | Hapus balasan |
| 20 | PATCH | `/courses/:slug/forum/threads/:thread/replies/:reply/accept` | Thread Owner | Tandai jawaban terbaik |
| 21 | PATCH | `/courses/:slug/forum/threads/:thread/replies/:reply/unaccept` | Thread Owner | Batalkan jawaban terbaik |
| 22 | POST | `/courses/:slug/forum/threads/:thread/reactions` | Enrolled | Reaksi ke thread |
| 23 | DELETE | `/courses/:slug/forum/threads/:thread/reactions/:reaction` | Owner | Hapus reaksi thread |
| 24 | POST | `/courses/:slug/forum/threads/:thread/replies/:reply/reactions` | Enrolled | Reaksi ke balasan |
| 25 | DELETE | `/courses/:slug/forum/threads/:thread/replies/:reply/reactions/:reaction` | Owner | Hapus reaksi balasan |
| 26 | GET | `/courses/:slug/forum/statistics` | Enrolled | Statistik forum kursus |
| 27 | GET | `/courses/:slug/forum/my-statistics` | Enrolled | Statistik forum saya |

---

# A. Dashboard Forum (Global)

---

## 1. Semua Thread Lintas Kursus (Admin)

**GET** `{{url}}/api/v1/forums/threads`

> Hanya untuk Admin & Instruktur. Melihat semua thread dari semua kursus.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `per_page` | integer | Jumlah per halaman (default: 20) |
| `search` | string | Kata kunci pencarian |

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar thread berhasil diambil.",
    "data": [
        {
            "id": 45,
            "title": "Bagaimana cara menghitung critical path?",
            "content": "Saya sedang mempelajari metode CPM...",
            "is_pinned": false,
            "is_closed": false,
            "is_resolved": false,
            "replies_count": 3,
            "views": 28,
            "created_at": "2026-05-03T10:00:00.000000Z",
            "author": {
                "id": 42,
                "name": "Budi Santoso",
                "username": "budi.santoso"
            },
            "course": {
                "id": 14,
                "title": "Manajemen Proyek",
                "slug": "manajemen-proyek-sesuai-standar-industri-26"
            }
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1
        }
    },
    "errors": null
}
```

---

## 2. Thread Trending (Admin)

**GET** `{{url}}/api/v1/forums/threads/trending`

> Hanya untuk Admin & Instruktur.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread trending berhasil diambil.",
    "data": [
        {
            "id": 45,
            "title": "Bagaimana cara menghitung critical path?",
            "replies_count": 12,
            "views": 98,
            "reactions_count": 7
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1
        }
    },
    "errors": null
}
```

---

## 3. Thread yang Saya Buat

**GET** `{{url}}/api/v1/forums/my-threads`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread saya berhasil diambil.",
    "data": [
        {
            "id": 45,
            "title": "Bagaimana cara menghitung critical path?",
            "is_resolved": false,
            "replies_count": 3,
            "created_at": "2026-05-03T10:00:00.000000Z",
            "course": {
                "id": 14,
                "slug": "manajemen-proyek-sesuai-standar-industri-26"
            }
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1
        }
    },
    "errors": null
}
```

---

# B. Thread Kursus

> Semua endpoint di bawah menggunakan base path: `/api/v1/courses/:course_slug/forum/`
> User harus **terdaftar (enrolled)** di kursus tersebut.

---

## 4. Daftar Thread Kursus

**GET** `{{url}}/api/v1/courses/:course_slug/forum/threads`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `search` | string | Cari berdasarkan judul/konten |
| `per_page` | integer | Jumlah per halaman (default: 20) |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/courses/manajemen-proyek-sesuai-standar-industri-26/forum/threads
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar thread berhasil diambil.",
    "data": [
        {
            "id": 45,
            "title": "Bagaimana cara menghitung critical path?",
            "content": "Saya sedang mempelajari metode CPM dan ingin mengetahui...",
            "is_pinned": true,
            "is_closed": false,
            "is_resolved": false,
            "replies_count": 3,
            "views": 28,
            "attachments": [],
            "reactions": {
                "like": 2,
                "helpful": 1,
                "solved": 0
            },
            "created_at": "2026-05-03T10:00:00.000000Z",
            "updated_at": "2026-05-03T10:00:00.000000Z",
            "author": {
                "id": 42,
                "name": "Budi Santoso",
                "username": "budi.santoso",
                "avatar": null
            }
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1
        }
    },
    "errors": null
}
```

---

## 5. Buat Thread Baru

**POST** `{{url}}/api/v1/courses/:course_slug/forum/threads`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (form-data)

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `title` | text | Ya | Min 3, maks 255 karakter |
| `content` | text | Ya | Min 1, maks 5000 karakter. Dukung `@mention` |
| `attachments[]` | file | Tidak | Maks 5 file, maks 50MB/file |

Format file yang didukung: `jpeg, png, jpg, gif, pdf, mp4, webm, ogg, mov, avi`

### Body — Tanpa attachment (raw JSON)
```json
{
    "title": "Bagaimana cara menghitung critical path?",
    "content": "Saya sedang mempelajari metode CPM dan ingin mengetahui cara menghitung critical path dari sebuah jaringan proyek. @instruktur.budi apakah bisa bantu?"
}
```

### Body — Dengan attachment (form-data)
| Key | Type | Value |
|-----|------|-------|
| `title` | Text | `Bagaimana cara menghitung critical path?` |
| `content` | Text | `Saya sedang mempelajari metode CPM...` |
| `attachments[0]` | File | `[FILE: diagram-cpm.png]` (maks 50MB) |

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Thread berhasil dibuat.",
    "data": {
        "id": 46,
        "title": "Bagaimana cara menghitung critical path?",
        "content": "Saya sedang mempelajari metode CPM...",
        "is_pinned": false,
        "is_closed": false,
        "is_resolved": false,
        "replies_count": 0,
        "views": 0,
        "attachments": [],
        "reactions": {
            "like": 0,
            "helpful": 0,
            "solved": 0
        },
        "created_at": "2026-05-04T09:00:00.000000Z",
        "author": {
            "id": 42,
            "name": "Budi Santoso",
            "username": "budi.santoso"
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Mention username tidak valid
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "content": ["Pengguna yang disebutkan tidak ditemukan: @username.tidak.ada"]
    }
}
```

### Contoh Response (403) — Belum enrolled
```json
{
    "success": false,
    "message": "Aksi ini tidak diizinkan.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 6. Detail Thread

**GET** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread berhasil diambil.",
    "data": {
        "id": 46,
        "title": "Bagaimana cara menghitung critical path?",
        "content": "Saya sedang mempelajari metode CPM...",
        "is_pinned": false,
        "is_closed": false,
        "is_resolved": false,
        "replies_count": 2,
        "views": 15,
        "attachments": [
            {
                "id": 88,
                "name": "diagram-cpm.png",
                "url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/forums/diagram-cpm.png",
                "mime_type": "image/png"
            }
        ],
        "reactions": {
            "like": 3,
            "helpful": 1,
            "solved": 0
        },
        "created_at": "2026-05-04T09:00:00.000000Z",
        "author": {
            "id": 42,
            "name": "Budi Santoso",
            "username": "budi.santoso"
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 7. Edit Thread

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id`

> Hanya bisa dilakukan oleh **pembuat thread**.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (raw JSON) — Minimal salah satu field diisi
```json
{
    "title": "Cara menghitung critical path di jaringan proyek kompleks",
    "content": "Saya sedang mempelajari metode CPM yang lebih detail..."
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread berhasil diperbarui.",
    "data": {
        "id": 46,
        "title": "Cara menghitung critical path di jaringan proyek kompleks",
        "content": "Saya sedang mempelajari metode CPM yang lebih detail...",
        "updated_at": "2026-05-04T09:30:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 8. Hapus Thread

**DELETE** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id`

> Bisa dilakukan oleh **pembuat thread** atau **Admin/Instruktur**.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread berhasil dihapus.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 9. Pin Thread (Admin/Instruktur)

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/pin`

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
    "message": "Thread berhasil dipasang pin.",
    "data": {
        "id": 46,
        "is_pinned": true
    },
    "meta": null,
    "errors": null
}
```

---

## 10. Unpin Thread (Admin/Instruktur)

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/unpin`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Pin thread berhasil dilepas.",
    "data": {
        "id": 46,
        "is_pinned": false
    },
    "meta": null,
    "errors": null
}
```

---

## 11. Tutup Thread (Admin/Instruktur)

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/close`

> Thread yang ditutup tidak bisa menerima balasan baru.

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread berhasil ditutup.",
    "data": {
        "id": 46,
        "is_closed": true
    },
    "meta": null,
    "errors": null
}
```

---

## 12. Buka Thread (Admin/Instruktur)

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/open`

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread berhasil dibuka.",
    "data": {
        "id": 46,
        "is_closed": false
    },
    "meta": null,
    "errors": null
}
```

---

## 13. Tandai Thread Terselesaikan

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/resolve`

> Hanya bisa dilakukan oleh **pembuat thread**.

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Thread berhasil ditandai terselesaikan.",
    "data": {
        "id": 46,
        "is_resolved": true
    },
    "meta": null,
    "errors": null
}
```

---

## 14. Batalkan Terselesaikan

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/unresolve`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Status terselesaikan berhasil dibatalkan.",
    "data": {
        "id": 46,
        "is_resolved": false
    },
    "meta": null,
    "errors": null
}
```

---

# C. Balasan (Replies)

---

## 15. Daftar Balasan Thread

**GET** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |

### Query Parameter (Opsional)
| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `page` | integer | `1` | Halaman |
| `per_page` | integer | `20` | Jumlah per halaman |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar balasan berhasil diambil.",
    "data": [
        {
            "id": 201,
            "content": "Untuk menghitung critical path, pertama-tama buat daftar semua aktivitas...",
            "is_accepted_answer": true,
            "children_count": 1,
            "attachments": [],
            "reactions": {
                "like": 5,
                "helpful": 3,
                "solved": 1
            },
            "created_at": "2026-05-04T10:00:00.000000Z",
            "author": {
                "id": 7,
                "name": "Instruktur Budi",
                "username": "instruktur.budi"
            }
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1
        }
    },
    "errors": null
}
```

---

## 16. Balas Thread / Balas Balasan

**POST** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body — Balas thread langsung (form-data atau raw JSON)
```json
{
    "content": "Terima kasih atas penjelasannya @instruktur.budi! Sangat membantu."
}
```

### Body — Balas balasan tertentu (nested reply)
```json
{
    "content": "Saya setuju, metode ini juga bisa dikombinasikan dengan PERT.",
    "parent_id": 201
}
```

### Body — Dengan attachment (form-data)
| Key | Type | Value |
|-----|------|-------|
| `content` | Text | `Berikut diagram yang saya buat...` |
| `parent_id` | Text | `201` (opsional) |
| `attachments[0]` | File | `[FILE: diagram-pert.pdf]` (maks 50MB) |

### Contoh Response (201)
```json
{
    "success": true,
    "message": "Balasan berhasil dibuat.",
    "data": {
        "id": 202,
        "content": "Terima kasih atas penjelasannya @instruktur.budi! Sangat membantu.",
        "parent_id": null,
        "is_accepted_answer": false,
        "children_count": 0,
        "attachments": [],
        "reactions": {
            "like": 0,
            "helpful": 0,
            "solved": 0
        },
        "created_at": "2026-05-04T10:15:00.000000Z",
        "author": {
            "id": 42,
            "name": "Budi Santoso",
            "username": "budi.santoso"
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Thread ditutup
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "thread": ["Thread ini sudah ditutup dan tidak menerima balasan baru."]
    }
}
```

---

## 17. Daftar Balasan Anak (Children)

**GET** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies/:reply_id/children`

> Mengambil semua balasan turunan dari satu balasan tertentu.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |
| `reply_id` | integer | `201` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Daftar balasan berhasil diambil.",
    "data": [
        {
            "id": 203,
            "content": "Saya setuju, metode ini juga bisa dikombinasikan dengan PERT.",
            "parent_id": 201,
            "is_accepted_answer": false,
            "children_count": 0,
            "reactions": {"like": 1, "helpful": 0, "solved": 0},
            "created_at": "2026-05-04T10:20:00.000000Z",
            "author": {
                "id": 43,
                "name": "Siti Rahayu",
                "username": "siti.rahayu"
            }
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 18. Edit Balasan

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies/:reply_id`

> Hanya bisa dilakukan oleh **pembuat balasan**.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |
| `reply_id` | integer | `202` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (raw JSON)
```json
{
    "content": "Terima kasih atas penjelasannya yang sangat detail @instruktur.budi! Saya sudah mencoba dan berhasil."
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Balasan berhasil diperbarui.",
    "data": {
        "id": 202,
        "content": "Terima kasih atas penjelasannya yang sangat detail @instruktur.budi!",
        "updated_at": "2026-05-04T10:30:00.000000Z"
    },
    "meta": null,
    "errors": null
}
```

---

## 19. Hapus Balasan

**DELETE** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies/:reply_id`

> Bisa dilakukan oleh **pembuat balasan** atau **Admin/Instruktur**.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |
| `reply_id` | integer | `202` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Balasan berhasil dihapus.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 20. Tandai sebagai Jawaban Terbaik

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies/:reply_id/accept`

> Hanya bisa dilakukan oleh **pembuat thread**. Menandai satu balasan sebagai jawaban yang paling membantu.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |
| `reply_id` | integer | `201` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Balasan berhasil ditandai.",
    "data": {
        "id": 201,
        "is_accepted_answer": true
    },
    "meta": null,
    "errors": null
}
```

---

## 21. Batalkan Jawaban Terbaik

**PATCH** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies/:reply_id/unaccept`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Balasan berhasil ditandai.",
    "data": {
        "id": 201,
        "is_accepted_answer": false
    },
    "meta": null,
    "errors": null
}
```

---

# D. Reaksi (Reactions)

> Tipe reaksi yang tersedia: `like`, `helpful`, `solved`

---

## 22. Tambah Reaksi ke Thread

**POST** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/reactions`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (raw JSON)
```json
{
    "type": "helpful"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Reaksi berhasil ditambahkan.",
    "data": {
        "id": 501,
        "type": "helpful"
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Tipe tidak valid
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "type": ["Nilai yang dipilih untuk type tidak valid."]
    }
}
```

---

## 23. Hapus Reaksi dari Thread

**DELETE** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/reactions/:reaction_id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |
| `reaction_id` | integer | `501` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Reaksi berhasil dihapus.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 24. Tambah Reaksi ke Balasan

**POST** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies/:reply_id/reactions`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |
| `reply_id` | integer | `201` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (raw JSON)
```json
{
    "type": "like"
}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Reaksi berhasil ditambahkan.",
    "data": {
        "id": 502,
        "type": "like"
    },
    "meta": null,
    "errors": null
}
```

---

## 25. Hapus Reaksi dari Balasan

**DELETE** `{{url}}/api/v1/courses/:course_slug/forum/threads/:thread_id/replies/:reply_id/reactions/:reaction_id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |
| `thread_id` | integer | `46` |
| `reply_id` | integer | `201` |
| `reaction_id` | integer | `502` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Reaksi berhasil dihapus.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

# E. Statistik Forum

---

## 26. Statistik Forum Kursus

**GET** `{{url}}/api/v1/courses/:course_slug/forum/statistics`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `filter[user_id]` | integer | Filter statistik per user tertentu |
| `filter[period_start]` | date | Tanggal mulai filter |
| `filter[period_end]` | date | Tanggal akhir filter |

### Authorization
```
Bearer Token: {{access_token_admin}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Statistik forum berhasil diambil.",
    "data": {
        "total_threads": 12,
        "total_replies": 48,
        "resolved_threads": 7,
        "unresolved_threads": 5,
        "active_users": 18,
        "total_reactions": 94,
        "most_active_user": {
            "id": 42,
            "name": "Budi Santoso",
            "posts_count": 8
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 27. Statistik Forum Saya

**GET** `{{url}}/api/v1/courses/:course_slug/forum/my-statistics`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `filter[period_start]` | date | Tanggal mulai |
| `filter[period_end]` | date | Tanggal akhir |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Statistik forum pengguna berhasil diambil.",
    "data": {
        "threads_created": 3,
        "replies_posted": 12,
        "accepted_answers": 2,
        "reactions_received": 18,
        "reactions_given": 9
    },
    "meta": null,
    "errors": null
}
```

---

## Referensi

### Tipe Reaksi (`type`)
| Nilai | Keterangan |
|-------|------------|
| `like` | Suka / setuju |
| `helpful` | Membantu |
| `solved` | Menyelesaikan masalah |

### Format Attachment
| Field | Nilai |
|-------|-------|
| Format file | `jpeg, png, jpg, gif, pdf, mp4, webm, ogg, mov, avi` |
| Maks file per post | 5 file |
| Maks ukuran per file | 50 MB |

### Aturan Mention
- Gunakan `@username` di dalam `content`
- Username yang tidak terdaftar akan menghasilkan error validasi
- Contoh: `"content": "Halo @instruktur.budi, bisa bantu saya?"`

### Nested Reply
- `parent_id` diisi ID balasan yang ingin dibalas
- Kedalaman nesting terbatas (maksimum level ditentukan sistem)
- `children_count` menunjukkan jumlah balasan turunan
