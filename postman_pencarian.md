# Dokumentasi Postman — Pencarian (Search)

Dokumentasi lengkap untuk seluruh endpoint **Pencarian**, termasuk pencarian terpaginasi, pencarian global, autocomplete, dan manajemen riwayat pencarian.

> Base URL: `{{url}}/api/v1`
> Semua endpoint: tidak memerlukan autentikasi untuk tipe `courses`
> Token Shared (untuk tipe `lessons`, `users`, `forums`): `{{access_token_student}}`

---

## Catatan Perilaku

| Endpoint | Auth | Tipe Tersedia |
|----------|------|---------------|
| `GET /search` | Opsional | `courses`, `units` (public) — `lessons`, `users`, `all` (auth required) |
| `GET /search/global` | Opsional | `courses` (public) — `all`, `lessons`, `users`, `forums` (auth required) |
| `GET /search/autocomplete` | Tidak perlu | Semua |
| `GET /search/history` | **Wajib** | — |
| `DELETE /search/history` | **Wajib** | — |
| `DELETE /search/history/:id` | **Wajib** | — |

**Penyimpanan Riwayat Otomatis**: Setiap pencarian dengan `q` non-kosong oleh user yang login akan otomatis disimpan ke riwayat.

---

## Daftar Endpoint

| No | Method | Endpoint | Auth | Keterangan |
|----|--------|----------|------|------------|
| 1 | GET | `/search` | Opsional | Pencarian terpaginasi per tipe |
| 2 | GET | `/search/global` | Opsional | Pencarian global (dikelompokkan, 5 per tipe) |
| 3 | GET | `/search/autocomplete` | Tidak perlu | Saran kata kunci |
| 4 | GET | `/search/history` | Wajib | Riwayat pencarian saya |
| 5 | DELETE | `/search/history` | Wajib | Bersihkan semua riwayat |
| 6 | DELETE | `/search/history/:id` | Wajib | Hapus satu item riwayat |

---

## 1. Pencarian Terpaginasi

**GET** `{{url}}/api/v1/search`

> Mencari dalam satu tipe konten tertentu dengan hasil terpaginasi dan filter lanjutan.
> Jika `type` tidak diisi atau `all`, request akan otomatis diteruskan ke endpoint Global Search (tanpa pagination).

### Authorization
```
Bearer Token: {{access_token_student}}   ← wajib untuk type: lessons, users
Atau tanpa token untuk type: courses, units
```

### Query Parameter

| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|------------|
| `q` | string | Ya | Kata kunci pencarian (min 1 karakter) |
| `type` | string | Tidak | `courses`, `units`, `lessons`, `users`, `all` |
| `per_page` | integer | Tidak | Jumlah per halaman (1–100, default 15) |
| `sort` | string | Tidak | Field pengurutan (default `created_at`) |
| `filter[*]` | mixed | Tidak | Filter tambahan tergantung tipe |

### Contoh Request — Cari kursus
```
GET {{url}}/api/v1/search?q=manajemen+proyek&type=courses&per_page=10
```

### Contoh Response (200) — Tipe `courses`
```json
{
    "success": true,
    "message": "Berhasil.",
    "data": [
        {
            "id": 14,
            "title": "Manajemen Proyek Sesuai Standar Industri",
            "slug": "manajemen-proyek-sesuai-standar-industri-26",
            "description": "Kursus komprehensif tentang manajemen proyek...",
            "thumbnail": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/courses/thumb-14.jpg",
            "status": "published"
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total": 1
        },
        "search": {
            "query": "manajemen proyek",
            "type": "courses",
            "execution_time": 0.0124
        }
    },
    "errors": null
}
```

### Contoh Request — Cari pelajaran (butuh token)
```
GET {{url}}/api/v1/search?q=critical+path&type=lessons
Authorization: Bearer {{access_token_student}}
```

### Contoh Response (200) — Tipe `lessons`
```json
{
    "success": true,
    "message": "Berhasil.",
    "data": [
        {
            "id": 88,
            "title": "Metode Critical Path (CPM)",
            "unit": {
                "id": 22,
                "title": "Modul 3: Manajemen Risiko"
            },
            "course": {
                "id": 14,
                "title": "Manajemen Proyek Sesuai Standar Industri"
            }
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 1
        },
        "search": {
            "query": "critical path",
            "type": "lessons",
            "execution_time": 0.0089
        }
    },
    "errors": null
}
```

### Contoh Request — Cari pengguna (butuh token)
```
GET {{url}}/api/v1/search?q=budi&type=users
Authorization: Bearer {{access_token_student}}
```

### Contoh Response (422) — Kueri kosong
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "q": ["Kolom q wajib diisi."]
    }
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

### Contoh Response (401) — Type `lessons` / `users` tanpa token
```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 2. Pencarian Global (Dikelompokkan)

**GET** `{{url}}/api/v1/search/global`

> Mengembalikan hasil pencarian dikelompokkan per tipe (maks **5 hasil per tipe**), tanpa pagination.
> Cocok untuk search bar dengan dropdown preview hasil.

### Authorization
```
Bearer Token: {{access_token_student}}   ← wajib untuk type all / lessons / users / forums
Atau tanpa token → hanya menampilkan courses
```

### Query Parameter

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `q` | string | Kata kunci pencarian |
| `type` | string | `courses`, `units`, `lessons`, `users`, `forums`, `all` (default: `all`) |

### Contoh Request — Pencarian global terautentikasi
```
GET {{url}}/api/v1/search/global?q=manajemen
Authorization: Bearer {{access_token_student}}
```

### Contoh Response (200) — `type=all`, terautentikasi
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "users": [
            {
                "id": 7,
                "name": "Instruktur Budi",
                "username": "instruktur.budi",
                "avatar": null
            }
        ],
        "courses": [
            {
                "id": 14,
                "title": "Manajemen Proyek Sesuai Standar Industri",
                "slug": "manajemen-proyek-sesuai-standar-industri-26",
                "thumbnail": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/courses/thumb-14.jpg"
            }
        ],
        "forums": [
            {
                "id": 46,
                "title": "Bagaimana cara menghitung critical path?",
                "replies_count": 3,
                "created_at": "2026-05-04T09:00:00.000000Z",
                "author": {
                    "id": 42,
                    "name": "Budi Santoso"
                }
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Tanpa token (hanya courses)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "users": [],
        "courses": [
            {
                "id": 14,
                "title": "Manajemen Proyek Sesuai Standar Industri",
                "slug": "manajemen-proyek-sesuai-standar-industri-26"
            }
        ],
        "forums": []
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Tipe tidak valid
```json
{
    "success": false,
    "message": "Tipe pencarian tidak valid.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 3. Autocomplete / Saran Kata Kunci

**GET** `{{url}}/api/v1/search/autocomplete`

> Mengembalikan daftar saran kata kunci berdasarkan input parsial. Tidak memerlukan autentikasi.
> Cocok untuk fitur **search-as-you-type**.

### Authorization
Tidak diperlukan.

### Query Parameter

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `q` | string | `""` | Kata kunci parsial |
| `limit` | integer | `10` | Jumlah saran yang dikembalikan |

### Contoh Request
```
GET {{url}}/api/v1/search/autocomplete?q=mana&limit=5
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        "manajemen proyek",
        "manajemen risiko",
        "manajemen waktu",
        "manajemen sumber daya",
        "manajemen kualitas"
    ],
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Kueri kosong
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## 4. Riwayat Pencarian Saya

**GET** `{{url}}/api/v1/search/history`

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Query Parameter

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `limit` | integer | `20` | Jumlah riwayat yang dikembalikan |

### Contoh Request
```
GET {{url}}/api/v1/search/history?limit=10
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 301,
            "query": "critical path",
            "filters": {
                "type": "lessons"
            },
            "total_results": 1,
            "searched_at": "2026-05-04T09:15:00.000000Z"
        },
        {
            "id": 300,
            "query": "manajemen proyek",
            "filters": {
                "type": "courses"
            },
            "total_results": 3,
            "searched_at": "2026-05-04T09:00:00.000000Z"
        },
        {
            "id": 299,
            "query": "manajemen",
            "filters": {
                "type": "all"
            },
            "total_results": 8,
            "searched_at": "2026-05-03T14:30:00.000000Z"
        }
    ],
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Riwayat kosong
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [],
    "meta": null,
    "errors": null
}
```

---

## 5. Bersihkan Semua Riwayat Pencarian

**DELETE** `{{url}}/api/v1/search/history`

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
    "message": "Riwayat pencarian berhasil dibersihkan.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 6. Hapus Satu Item Riwayat

**DELETE** `{{url}}/api/v1/search/history/:id`

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
    "message": "Item riwayat pencarian berhasil dihapus.",
    "data": null,
    "meta": null,
    "errors": null
}
```

### Contoh Response (404) — ID tidak ditemukan / bukan milik user
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

## Referensi

### Nilai Valid `type`

| Nilai | Keterangan | Auth Required |
|-------|------------|---------------|
| `courses` | Kursus | Tidak |
| `units` | Unit / Modul dalam kursus | Tidak |
| `lessons` | Materi / Pelajaran | Ya |
| `users` | Pengguna (nama, username) | Ya |
| `forums` | Thread forum diskusi | Ya |
| `all` | Semua tipe (global search) | Ya (untuk tipe restricted) |

### Perbedaan `/search` vs `/search/global`

| Aspek | `GET /search` | `GET /search/global` |
|-------|---------------|----------------------|
| Hasil | Terpaginasi (per tipe) | Dikelompokkan, maks 5/tipe |
| Penggunaan | Halaman hasil pencarian penuh | Dropdown preview real-time |
| `type=all` | Diteruskan ke `/search/global` | Selalu mode global |
| Filter lanjutan | Didukung (`filter[*]`, `sort`) | Tidak |

### Penyimpanan Riwayat Otomatis

Riwayat disimpan otomatis jika:
1. User dalam kondisi **terautentikasi**
2. Parameter `q` **tidak kosong**

Riwayat menyimpan: `query`, `filters` yang digunakan, dan `total_results` yang ditemukan.
