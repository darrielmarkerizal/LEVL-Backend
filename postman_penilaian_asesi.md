# Dokumentasi Postman — Penilaian (Asesi)

Dokumentasi lengkap untuk seluruh endpoint yang digunakan **Asesi (Student)** dalam melihat hasil penilaian / rapor nilai.

> Token yang digunakan: `{{access_token_student}}`
> Base URL: `{{url}}/api/v1`

---

## Catatan Arsitektur

Tidak ada endpoint khusus di Grading module untuk Asesi — semua endpoint Grading module dibatasi untuk Admin & Instruktur saja. Asesi mengakses hasil penilaian melalui **Learning module** dengan response yang disesuaikan secara otomatis berdasarkan role.

Visibilitas data (skor per soal, feedback, kunci jawaban) dikontrol oleh sistem berdasarkan status penilaian:
- Data nilai/feedback hanya terlihat jika status sudah `graded` atau `released`
- `answer_key` hanya terlihat jika instruktur mengizinkan

---

## Daftar Endpoint

| No | Method | Endpoint | Keterangan |
|----|--------|----------|------------|
| 1 | GET | `/assignments/:id/submissions/:sub_id` | Rapor penilaian tugas (per pengumpulan) |
| 2 | GET | `/assignments/:id/submissions` | Daftar semua pengumpulan + ringkasan nilai |
| 3 | GET | `/assignments/:id/submissions/highest` | Pengumpulan dengan nilai terbaik |
| 4 | GET | `/quiz-submissions/:id?include=answers,quiz` | Rapor penilaian kuis (per sesi) |
| 5 | GET | `/quizzes/:id/submissions` | Daftar semua sesi kuis + ringkasan nilai |
| 6 | GET | `/quizzes/:id/submissions/highest` | Sesi kuis dengan nilai terbaik |

---

## 1. Rapor Penilaian Tugas (Per Pengumpulan)

**GET** `{{url}}/api/v1/assignments/:assignment_id/submissions/:submission_id`

> Menampilkan detail hasil penilaian untuk satu pengumpulan tugas tertentu. Termasuk skor total, feedback instruktur, dan hasil per jawaban (jika visibilitas diizinkan).

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

### Contoh Response (200) — Sudah dinilai, nilai dirilis
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 701,
        "assignment": {
            "id": 88,
            "title": "Analisis Manajemen Risiko Proyek"
        },
        "status": "graded",
        "status_label": "Sudah Dinilai",
        "workflow_state": "released",
        "workflow_state_label": "Sudah Dirilis",
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
        ],
        "answers": [
            {
                "id": 881,
                "content": "Identifikasi risiko mencakup tiga hal: risiko teknis, risiko jadwal...",
                "selected_options": null,
                "file_paths": null,
                "score": 82.5,
                "is_auto_graded": false,
                "feedback": "Analisis sudah komprehensif. Perlu tambahkan matriks risiko untuk visualisasi yang lebih baik.",
                "question": {
                    "id": 312,
                    "content": "Buatlah analisis risiko untuk sebuah proyek fiktif!",
                    "type": "essay",
                    "options": null,
                    "max_score": 100,
                    "answer_key": null
                },
                "is_correct": null
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Sudah dinilai, soal pilihan ganda (dengan kunci jawaban)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 702,
        "assignment": {
            "id": 89,
            "title": "Evaluasi Pemahaman Modul 2"
        },
        "status": "graded",
        "status_label": "Sudah Dinilai",
        "workflow_state": "released",
        "workflow_state_label": "Sudah Dirilis",
        "attempt_number": 1,
        "score": 75.0,
        "submitted_at": "2026-05-12T10:00:00.000000Z",
        "graded_at": "2026-05-12T10:01:00.000000Z",
        "files": [],
        "answers": [
            {
                "id": 882,
                "content": null,
                "selected_options": ["2"],
                "file_paths": null,
                "score": 25.0,
                "is_auto_graded": true,
                "feedback": null,
                "question": {
                    "id": 313,
                    "content": "Apa yang dimaksud dengan Work Breakdown Structure?",
                    "type": "multiple_choice",
                    "options": ["A", "B", "C", "D"],
                    "max_score": 25,
                    "answer_key": [2]
                },
                "is_correct": true
            },
            {
                "id": 883,
                "content": null,
                "selected_options": ["1"],
                "file_paths": null,
                "score": 0.0,
                "is_auto_graded": true,
                "feedback": null,
                "question": {
                    "id": 314,
                    "content": "Metode apa yang digunakan untuk estimasi durasi proyek?",
                    "type": "multiple_choice",
                    "options": ["A", "B", "C", "D"],
                    "max_score": 25,
                    "answer_key": [3]
                },
                "is_correct": false
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Belum dinilai (menunggu)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 712,
        "assignment": {
            "id": 88,
            "title": "Analisis Manajemen Risiko Proyek"
        },
        "status": "submitted",
        "status_label": "Sudah Dikumpulkan",
        "workflow_state": "pending_manual_grading",
        "workflow_state_label": "Menunggu Penilaian Manual",
        "attempt_number": 2,
        "score": null,
        "submitted_at": "2026-05-14T10:15:00.000000Z",
        "graded_at": null,
        "files": [],
        "answers": [
            {
                "id": 884,
                "content": "Revisi analisis risiko saya mencakup empat aspek...",
                "selected_options": null,
                "file_paths": null,
                "score": null,
                "is_auto_graded": false,
                "feedback": null,
                "question": {
                    "id": 312,
                    "content": "Buatlah analisis risiko untuk sebuah proyek fiktif!",
                    "type": "essay",
                    "options": null,
                    "max_score": 100,
                    "answer_key": null
                }
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (403) — Tugas terkunci (prasyarat belum terpenuhi)
```json
{
    "success": false,
    "message": "Selesaikan 1 tugas prasyarat terlebih dahulu untuk membuka tugas ini.",
    "data": null,
    "meta": null,
    "errors": {
        "missing_prerequisites_count": 1
    }
}
```

---

## 2. Daftar Semua Pengumpulan Tugas + Ringkasan Nilai

**GET** `{{url}}/api/v1/assignments/:assignment_id/submissions`

> Menampilkan semua percobaan pengumpulan tugas milik asesi, berikut status penilaian dan skor. Flag `is_highest` menandai percobaan dengan nilai tertinggi.

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

## 3. Pengumpulan Tugas dengan Nilai Terbaik

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

---

## 4. Rapor Penilaian Kuis (Per Sesi)

**GET** `{{url}}/api/v1/quiz-submissions/:submission_id?include=answers,quiz`

> Menampilkan detail hasil penilaian untuk satu sesi kuis. Sertakan `include=answers,quiz` untuk mendapatkan data jawaban dan detail kuis.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `1424` |

### Query Parameter
| Parameter | Nilai | Keterangan |
|-----------|-------|------------|
| `include` | `answers,quiz` | Sertakan jawaban dan detail kuis |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Header Wajib
| Header | Nilai |
|--------|-------|
| `X-Session-Token` | Session token dari endpoint Start / Takeover |

### Contoh Request
```
GET {{url}}/api/v1/quiz-submissions/1424?include=answers,quiz
Headers:
  X-Session-Token: eyJ0eXAiOiJKV1Qi...
```

### Contoh Response (200) — Sudah dinilai sepenuhnya
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 1424,
        "attempt_number": 2,
        "status": "submitted",
        "status_label": "Sudah Dikumpulkan",
        "grading_status": "graded",
        "grading_status_label": "Sudah Dinilai",
        "score": 75.0,
        "final_score": 75.0,
        "is_passed": true,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": "2026-05-04T08:38:00.000000Z",
        "time_spent_seconds": 2280,
        "duration": "38 menit",
        "quiz": {
            "id": 135,
            "title": "Evaluasi Akhir Modul 1",
            "passing_grade": 70,
            "time_limit_minutes": 44
        },
        "answers": [
            {
                "id": 5349,
                "quiz_question_id": 537,
                "content": null,
                "selected_options": ["2"],
                "score": 25.0,
                "feedback": null
            },
            {
                "id": 5350,
                "quiz_question_id": 538,
                "content": null,
                "selected_options": ["0"],
                "score": 25.0,
                "feedback": null
            },
            {
                "id": 5351,
                "quiz_question_id": 539,
                "content": null,
                "selected_options": ["0", "2"],
                "score": 0.0,
                "feedback": null
            },
            {
                "id": 5352,
                "quiz_question_id": 540,
                "content": "Middleware adalah lapisan perantara dalam aplikasi web...",
                "selected_options": null,
                "score": 25.0,
                "feedback": "Penjelasan sudah tepat dan komprehensif."
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Ada soal esai, menunggu penilaian manual
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 1424,
        "attempt_number": 2,
        "status": "submitted",
        "status_label": "Sudah Dikumpulkan",
        "grading_status": "waiting_for_grading",
        "grading_status_label": "Menunggu Penilaian",
        "score": 50.0,
        "final_score": null,
        "is_passed": null,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": "2026-05-04T08:38:00.000000Z",
        "time_spent_seconds": 2280,
        "duration": "38 menit",
        "quiz": {
            "id": 135,
            "title": "Evaluasi Akhir Modul 1",
            "passing_grade": 70,
            "time_limit_minutes": 44
        },
        "answers": [
            {
                "id": 5349,
                "quiz_question_id": 537,
                "content": null,
                "selected_options": ["2"],
                "score": 25.0,
                "feedback": null
            },
            {
                "id": 5352,
                "quiz_question_id": 540,
                "content": "Middleware adalah lapisan perantara dalam aplikasi web...",
                "selected_options": null,
                "score": null,
                "feedback": null
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

---

## 5. Daftar Semua Sesi Kuis + Ringkasan Nilai

**GET** `{{url}}/api/v1/quizzes/:quiz_id/submissions`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `quiz_id` | integer | `135` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/quizzes/135/submissions
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 1422,
            "attempt_number": 1,
            "status": "submitted",
            "status_label": "Sudah Dikumpulkan",
            "grading_status": "graded",
            "grading_status_label": "Sudah Dinilai",
            "score": 50.0,
            "final_score": 50.0,
            "is_passed": false,
            "started_at": "2026-04-28T07:00:00.000000Z",
            "submitted_at": "2026-04-28T07:44:00.000000Z",
            "time_spent_seconds": 2640,
            "duration": "44 menit"
        },
        {
            "id": 1424,
            "attempt_number": 2,
            "status": "submitted",
            "status_label": "Sudah Dikumpulkan",
            "grading_status": "graded",
            "grading_status_label": "Sudah Dinilai",
            "score": 75.0,
            "final_score": 75.0,
            "is_passed": true,
            "started_at": "2026-05-04T08:00:00.000000Z",
            "submitted_at": "2026-05-04T08:38:00.000000Z",
            "time_spent_seconds": 2280,
            "duration": "38 menit"
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 6. Sesi Kuis dengan Nilai Terbaik

**GET** `{{url}}/api/v1/quizzes/:quiz_id/submissions/highest`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `quiz_id` | integer | `135` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/quizzes/135/submissions/highest
```

### Contoh Response (200) — Ditemukan
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 1424,
        "attempt_number": 2,
        "status": "submitted",
        "status_label": "Sudah Dikumpulkan",
        "grading_status": "graded",
        "grading_status_label": "Sudah Dinilai",
        "score": 75.0,
        "final_score": 75.0,
        "is_passed": true,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": "2026-05-04T08:38:00.000000Z",
        "time_spent_seconds": 2280,
        "duration": "38 menit"
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Belum pernah mengerjakan
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

## Referensi Enum

### `status` (Submission — Tugas)
| Nilai | Label | Keterangan |
|-------|-------|------------|
| `draft` | Draft | Disimpan, belum dikumpulkan |
| `submitted` | Sudah Dikumpulkan | Dikumpulkan, menunggu penilaian |
| `graded` | Sudah Dinilai | Sudah dinilai oleh instruktur |

### `workflow_state` (Submission — Tugas, internal)
| Nilai | Label | Keterangan |
|-------|-------|------------|
| `in_progress` | Dalam Proses | Sedang dikerjakan |
| `auto_graded` | Dinilai Otomatis | Dinilai sistem, menunggu rilis |
| `pending_manual_grading` | Menunggu Penilaian Manual | Instruktur belum menilai |
| `graded` | Dinilai | Penilaian selesai |
| `released` | Dirilis | Nilai sudah dapat dilihat asesi |

### `grading_status` (QuizSubmission — Kuis)
| Nilai | Label | Keterangan |
|-------|-------|------------|
| `pending` | Menunggu | Belum diproses |
| `waiting_for_grading` | Menunggu Penilaian | Ada soal esai yang perlu dinilai manual |
| `graded` | Sudah Dinilai | Semua soal sudah dinilai |
| `pending_manual_grading` | Menunggu Penilaian Manual | Sebagian masih perlu dinilai manual |

### `is_passed`
| Nilai | Kondisi |
|-------|---------|
| `true` | `final_score >= passing_grade` |
| `false` | `final_score < passing_grade` |
| `null` | Belum dinilai (`grading_status` bukan final) |

---

## Catatan untuk Postman Collection

Buat sub-folder baru **`Asesi`** di dalam folder **`Penilaian (Admin dan Instructor)`** yang sudah ada, dengan isi:

| Request | Method | Endpoint | Token |
|---------|--------|----------|-------|
| `[GET] Rapor Penilaian Tugas` | GET | `/assignments/:id/submissions/:sub_id` | `{{access_token_student}}` |
| `[GET] Daftar Nilai Tugas Saya` | GET | `/assignments/:id/submissions` | `{{access_token_student}}` |
| `[GET] Nilai Terbaik Tugas` | GET | `/assignments/:id/submissions/highest` | `{{access_token_student}}` |
| `[GET] Rapor Penilaian Kuis` | GET | `/quiz-submissions/:id?include=answers,quiz` | `{{access_token_student}}` |
| `[GET] Daftar Nilai Kuis Saya` | GET | `/quizzes/:id/submissions` | `{{access_token_student}}` |
| `[GET] Nilai Terbaik Kuis` | GET | `/quizzes/:id/submissions/highest` | `{{access_token_student}}` |

> **Catatan**: Endpoint Rapor Kuis memerlukan header `X-Session-Token`.
