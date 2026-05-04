# Dokumentasi Postman — Pembelajaran: Pengumpulan Tugas Asesi

Dokumentasi lengkap untuk seluruh endpoint pengumpulan tugas oleh **Asesi (Student)**.

> Token yang digunakan: `{{access_token_student}}`
> Base URL: `{{url}}/api/v1`

---

## Alur Kerja

```
1. Daftar Tugas → 2. Detail Tugas → 3. Kumpulkan Tugas (store)
                                           ↓
                                    4. Update Jawaban (opsional, jika draft)
                                           ↓
                                    5. Submit Final
                                           ↓
                                    6. Lihat Hasil / Riwayat
```

---

## Daftar Endpoint

| No | Method | Endpoint | Keterangan |
|----|--------|----------|------------|
| 1 | GET | `/courses/:course_slug/assignments` | Daftar tugas dalam kursus |
| 2 | GET | `/courses/:course_slug/assignments/incomplete` | Tugas yang belum selesai |
| 3 | GET | `/assignments/:assignment_id` | Detail tugas |
| 4 | GET | `/assignments/:assignment_id/submissions` | Riwayat semua pengumpulan saya |
| 5 | GET | `/assignments/:assignment_id/submissions/highest` | Pengumpulan dengan nilai terbaik |
| 6 | GET | `/assignments/:assignment_id/submissions/:submission_id` | Detail satu pengumpulan |
| 7 | POST | `/assignments/:assignment_id/submissions` | Kumpulkan tugas (buat submission) |
| 8 | PUT | `/submissions/:submission_id` | Perbarui jawaban (jika masih draft) |
| 9 | POST | `/submissions/:submission_id/submit` | Submit final |

---

## 1. Daftar Tugas dalam Kursus

**GET** `{{url}}/api/v1/courses/:course_slug/assignments`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `page` | integer | Halaman (default: 1) |
| `per_page` | integer | Jumlah per halaman |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/courses/manajemen-proyek-sesuai-standar-industri-26/assignments
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 88,
            "title": "Analisis Manajemen Risiko Proyek",
            "description": "Buatlah analisis risiko untuk proyek fiktif...",
            "submission_type": "mixed",
            "passing_grade": 70,
            "due_date": "2026-05-30T23:59:59.000000Z",
            "status": "published",
            "unit": {
                "id": 22,
                "title": "Modul 3: Manajemen Risiko"
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

## 2. Tugas yang Belum Selesai

**GET** `{{url}}/api/v1/courses/:course_slug/assignments/incomplete`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/courses/manajemen-proyek-sesuai-standar-industri-26/assignments/incomplete
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 88,
            "title": "Analisis Manajemen Risiko Proyek",
            "submission_type": "mixed",
            "due_date": "2026-05-30T23:59:59.000000Z",
            "status": "published"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 3. Detail Tugas

**GET** `{{url}}/api/v1/assignments/:assignment_id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `assignment_id` | integer | `88` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/assignments/88
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 88,
        "title": "Analisis Manajemen Risiko Proyek",
        "description": "Buatlah analisis risiko untuk proyek fiktif dengan minimal 3 risiko teridentifikasi...",
        "submission_type": "mixed",
        "passing_grade": 70,
        "due_date": "2026-05-30T23:59:59.000000Z",
        "max_attempts": 3,
        "status": "published",
        "unit": {
            "id": 22,
            "title": "Modul 3: Manajemen Risiko"
        },
        "course": {
            "id": 14,
            "title": "Manajemen Proyek Sesuai Standar Industri",
            "slug": "manajemen-proyek-sesuai-standar-industri-26"
        }
    },
    "meta": null,
    "errors": null
}
```

---

## 4. Riwayat Semua Pengumpulan Saya

**GET** `{{url}}/api/v1/assignments/:assignment_id/submissions`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `assignment_id` | integer | `88` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/assignments/88/submissions
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 701,
            "status": "graded",
            "attempt_number": 1,
            "score": 82.5,
            "submitted_at": "2026-05-10T14:30:00.000000Z",
            "graded_at": "2026-05-11T09:00:00.000000Z",
            "is_highest": true,
            "files": [
                {
                    "id": 204,
                    "name": "analisis-risiko-kelompok-3.pdf",
                    "url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/submissions/701/analisis-risiko-kelompok-3.pdf",
                    "size": 204800,
                    "mime_type": "application/pdf"
                }
            ]
        },
        {
            "id": 712,
            "status": "submitted",
            "attempt_number": 2,
            "score": null,
            "submitted_at": "2026-05-14T10:15:00.000000Z",
            "graded_at": null,
            "is_highest": false,
            "files": []
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 5. Pengumpulan dengan Nilai Terbaik

**GET** `{{url}}/api/v1/assignments/:assignment_id/submissions/highest`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `assignment_id` | integer | `88` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/assignments/88/submissions/highest
```

### Contoh Response (200) — Ditemukan
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 701,
        "status": "graded",
        "attempt_number": 1,
        "score": 82.5,
        "submitted_at": "2026-05-10T14:30:00.000000Z",
        "graded_at": "2026-05-11T09:00:00.000000Z",
        "files": [
            {
                "id": 204,
                "name": "analisis-risiko-kelompok-3.pdf",
                "url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/submissions/701/analisis-risiko-kelompok-3.pdf",
                "size": 204800,
                "mime_type": "application/pdf"
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (404) — Belum pernah mengumpulkan
```json
{
    "success": false,
    "message": "Pengumpulan tidak ditemukan.",
    "data": null,
    "meta": null,
    "errors": null
}
```

### Contoh Response (403) — Tugas terkunci (prasyarat belum terpenuhi)
```json
{
    "success": false,
    "message": "Selesaikan 2 tugas prasyarat terlebih dahulu untuk membuka tugas ini.",
    "data": null,
    "meta": null,
    "errors": {
        "missing_prerequisites_count": 2
    }
}
```

---

## 6. Detail Satu Pengumpulan

**GET** `{{url}}/api/v1/assignments/:assignment_id/submissions/:submission_id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `assignment_id` | integer | `88` |
| `submission_id` | integer | `701` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/assignments/88/submissions/701
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 701,
        "status": "graded",
        "attempt_number": 1,
        "score": 82.5,
        "submitted_at": "2026-05-10T14:30:00.000000Z",
        "graded_at": "2026-05-11T09:00:00.000000Z",
        "assignment": {
            "id": 88,
            "title": "Analisis Manajemen Risiko Proyek"
        },
        "files": [
            {
                "id": 204,
                "name": "analisis-risiko-kelompok-3.pdf",
                "url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/submissions/701/analisis-risiko-kelompok-3.pdf",
                "size": 204800,
                "mime_type": "application/pdf"
            }
        ],
        "answers": []
    },
    "meta": null,
    "errors": null
}
```

---

## 7. Kumpulkan Tugas (Buat Submission)

**POST** `{{url}}/api/v1/assignments/:assignment_id/submissions`

> Body request bergantung pada `submission_type` tugas. Cek detail tugas terlebih dahulu.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `assignment_id` | integer | `88` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Aturan Body per Tipe Tugas

| `submission_type` | Field Wajib | Field Opsional | Catatan |
|-------------------|------------|----------------|---------|
| `text` | `answer_text` (min 10 karakter) | — | `files` dilarang |
| `file` | `files` (array, min 1 file, maks 10MB/file) | `answer_text` | Format file bebas |
| `mixed` | — | `answer_text`, `files` | Minimal salah satu diisi |

---

### Body — Tipe `text` (raw JSON)
```json
{
    "answer_text": "Analisis risiko proyek mencakup tiga aspek utama: risiko teknis, risiko sumber daya, dan risiko jadwal. Risiko teknis diidentifikasi dari kompleksitas integrasi sistem..."
}
```

### Contoh Request — Tipe `text`
```
POST {{url}}/api/v1/assignments/88/submissions
Content-Type: application/json
Authorization: Bearer {{access_token_student}}

{
    "answer_text": "Analisis risiko proyek mencakup tiga aspek utama..."
}
```

---

### Body — Tipe `file` (form-data)
| Key | Type | Value |
|-----|------|-------|
| `files[0]` | File | `analisis-risiko.pdf` (maks 10MB) |
| `files[1]` | File | `lampiran-data.xlsx` (opsional, maks 10MB) |
| `answer_text` | Text | Catatan tambahan (opsional) |

### Contoh Request — Tipe `file`
```
POST {{url}}/api/v1/assignments/88/submissions
Content-Type: multipart/form-data
Authorization: Bearer {{access_token_student}}

files[0]: [FILE: analisis-risiko.pdf]
```

---

### Body — Tipe `mixed` (form-data)
| Key | Type | Value |
|-----|------|-------|
| `answer_text` | Text | Deskripsi jawaban (opsional) |
| `files[0]` | File | File pendukung (opsional, maks 10MB) |

---

### Contoh Response (201) — Berhasil
```json
{
    "success": true,
    "message": "Pengumpulan tugas berhasil dibuat.",
    "data": {
        "id": 712,
        "attempt_number": 2,
        "status": "draft",
        "score": null,
        "submitted_at": null,
        "graded_at": null,
        "files": [
            {
                "id": 215,
                "name": "analisis-risiko-v2.pdf",
                "url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/submissions/712/analisis-risiko-v2.pdf",
                "size": 307200,
                "mime_type": "application/pdf"
            }
        ],
        "answers": []
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Tipe `text`, file dikirim
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "files": ["Tugas ini hanya menerima jawaban teks, tidak menerima file."]
    }
}
```

### Contoh Response (422) — Tipe `file`, tidak ada file
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "files": ["File wajib diunggah untuk tugas jenis file."]
    }
}
```

### Contoh Response (422) — Ukuran file terlalu besar
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "files.0": ["Ukuran file melebihi batas maksimum yang diizinkan yaitu 10 MB."]
    }
}
```

---

## 8. Perbarui Jawaban (Update Draft)

**PUT** `{{url}}/api/v1/submissions/:submission_id`

> Hanya bisa digunakan selama submission masih berstatus `draft`. Hanya mendukung update `answer_text`.
> Untuk mengganti file, gunakan endpoint submit ulang (buat submission baru jika diizinkan).

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `712` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body (raw JSON)
```json
{
    "answer_text": "Analisis risiko proyek telah direvisi. Mencakup empat aspek utama: risiko teknis, risiko sumber daya, risiko jadwal, dan risiko komunikasi..."
}
```

### Contoh Response (200) — Berhasil diperbarui
```json
{
    "success": true,
    "message": "Pengumpulan tugas berhasil diperbarui.",
    "data": {
        "id": 712,
        "attempt_number": 2,
        "status": "draft",
        "score": null,
        "submitted_at": null,
        "graded_at": null,
        "files": [],
        "answers": []
    },
    "meta": null,
    "errors": null
}
```

---

## 9. Submit Final

**POST** `{{url}}/api/v1/submissions/:submission_id/submit`

> Mengubah status submission dari `draft` menjadi `submitted`. Setelah ini submission tidak bisa diubah lagi.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `712` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body
```json
{}
```
> Body kosong atau `answers: []` untuk tugas tanpa pertanyaan terstruktur.

### Contoh Response (200) — Berhasil dikumpulkan
```json
{
    "success": true,
    "message": "Tugas berhasil dikumpulkan.",
    "data": {
        "id": 712,
        "assignment_id": 88,
        "assignment_title": "Analisis Manajemen Risiko Proyek",
        "status": "submitted",
        "attempt_number": 2,
        "is_late": false,
        "submitted_at": "2026-05-14T10:15:00.000000Z",
        "duration": 1845,
        "duration_formatted": "30 menit 45 detik",
        "summary": {
            "total_questions": 0,
            "answered": 0,
            "pending_grade": true
        },
        "student": {
            "id": 42,
            "name": "Budi Santoso"
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Terlambat dikumpulkan
```json
{
    "success": true,
    "message": "Tugas berhasil dikumpulkan.",
    "data": {
        "id": 712,
        "assignment_id": 88,
        "assignment_title": "Analisis Manajemen Risiko Proyek",
        "status": "submitted",
        "attempt_number": 2,
        "is_late": true,
        "submitted_at": "2026-06-02T08:00:00.000000Z",
        "duration": 3600,
        "duration_formatted": "1 jam",
        "summary": {
            "total_questions": 0,
            "answered": 0,
            "pending_grade": true
        },
        "student": {
            "id": 42,
            "name": "Budi Santoso"
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (403) — Tidak berhak submit
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

## Referensi Enum

### `submission_type` (Assignment)
| Nilai | Label | Keterangan |
|-------|-------|------------|
| `text` | Teks | Jawaban hanya berupa teks. File dilarang |
| `file` | File | Wajib upload minimal 1 file. Teks opsional |
| `mixed` | Campuran | Teks dan/atau file, keduanya opsional |

### `status` (Submission)
| Nilai | Keterangan |
|-------|------------|
| `draft` | Dibuat, belum dikumpulkan. Masih bisa diubah |
| `submitted` | Sudah dikumpulkan. Menunggu penilaian |
| `graded` | Sudah dinilai |

### `state` (Submission — internal workflow)
| Nilai | Keterangan |
|-------|------------|
| `in_progress` | Dalam proses pengerjaan |
| `auto_graded` | Dinilai otomatis (jika ada soal objektif) |
| `pending_manual_grading` | Menunggu penilaian manual dari instruktur |
| `graded` | Penilaian selesai |
| `released` | Nilai sudah dirilis ke asesi |

---

## Catatan untuk Postman Collection

| Endpoint | Method | Folder | Token |
|----------|--------|--------|-------|
| `/courses/:slug/assignments` | GET | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/courses/:slug/assignments/incomplete` | GET | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/assignments/:id` | GET | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/assignments/:id/submissions` | GET | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/assignments/:id/submissions/highest` | GET | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/assignments/:id/submissions/:sub_id` | GET | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/assignments/:id/submissions` | POST | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/submissions/:id` | PUT | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
| `/submissions/:id/submit` | POST | Pembelajaran > Pengumpulan Tugas Asesi | `{{access_token_student}}` |
