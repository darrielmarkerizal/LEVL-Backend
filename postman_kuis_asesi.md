# Dokumentasi Postman — Pembelajaran: Kuis Asesi

Dokumentasi lengkap untuk seluruh endpoint pengerjaan kuis oleh **Asesi (Student)**.

> Token yang digunakan: `{{access_token_student}}`
> Base URL: `{{url}}/api/v1`

---

## ⚠️ Catatan Penting

- **Session Token**: Beberapa endpoint (overview, questions, answers, submit) memerlukan header `X-Session-Token`. Token ini diterima saat endpoint `start` dipanggil dan disimpan di client.
- **Validasi Jawaban**: Setiap tipe soal memiliki validasi berbeda (lihat bagian Simpan Jawaban).
- **Auto-Submit**: Jika waktu habis dan status masih `draft`, endpoint `overview` akan otomatis mengumpulkan kuis.

---

## Alur Kerja

```
1. Daftar Kuis → 2. Detail Kuis → 3. Mulai Sesi → 4. Overview Soal
                                                         ↓
                                               5. Ambil Pertanyaan (per halaman)
                                                         ↓
                                               6. Simpan Jawaban (ulangi per soal)
                                                         ↓
                                               7. Submit Kuis
                                                         ↓
                                               8. Lihat Hasil
```

---

## Daftar Endpoint

| No | Method | Endpoint | Keterangan |
|----|--------|----------|------------|
| 1 | GET | `/courses/:course_slug/quizzes` | Daftar kuis dalam kursus |
| 2 | GET | `/quizzes/:quiz_id` | Detail kuis |
| 3 | GET | `/quizzes/:quiz_id/submissions` | Riwayat semua sesi saya |
| 4 | GET | `/quizzes/:quiz_id/submissions/highest` | Nilai terbaik saya |
| 5 | POST | `/quizzes/:quiz_id/submissions/start` | Mulai sesi kuis baru |
| 6 | GET | `/quiz-submissions/:submission_id/overview` | Overview semua soal + status jawaban + waktu |
| 7 | GET | `/quiz-submissions/:submission_id/questions` | Ambil pertanyaan per halaman |
| 8 | POST | `/quiz-submissions/:submission_id/answers` | Simpan jawaban |
| 9 | POST | `/quiz-submissions/:submission_id/submit` | Submit kuis |
| 10 | GET | `/quiz-submissions/:submission_id` | Detail hasil pengerjaan |
| 11 | POST | `/quiz-submissions/:submission_id/takeover` | Ambil alih sesi (resume) |

---

## 1. Daftar Kuis dalam Kursus

**GET** `{{url}}/api/v1/courses/:course_slug/quizzes`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `course_slug` | string | `manajemen-proyek-sesuai-standar-industri-26` |

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `page` | integer | Halaman (default: 1) |
| `per_page` | integer | Jumlah per halaman (default: 15) |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Contoh Request
```
GET {{url}}/api/v1/courses/manajemen-proyek-sesuai-standar-industri-26/quizzes
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": [
        {
            "id": 135,
            "title": "Evaluasi Akhir Modul 1",
            "description": "Kuis evaluasi pemahaman dasar manajemen proyek.",
            "passing_grade": 70,
            "time_limit_minutes": 44,
            "status": "published",
            "unit": {
                "id": 22,
                "title": "Modul 1: Dasar-Dasar Proyek"
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

## 2. Detail Kuis

**GET** `{{url}}/api/v1/quizzes/:quiz_id`

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
GET {{url}}/api/v1/quizzes/135
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 135,
        "title": "Evaluasi Akhir Modul 1",
        "description": "Kuis evaluasi pemahaman dasar manajemen proyek.",
        "passing_grade": 70,
        "time_limit_minutes": 44,
        "attempts_allowed": 3,
        "status": "published",
        "questions_count": 4,
        "unit": {
            "id": 22,
            "title": "Modul 1: Dasar-Dasar Proyek"
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

## 3. Riwayat Semua Sesi Saya

**GET** `{{url}}/api/v1/quizzes/:quiz_id/submissions`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `quiz_id` | integer | `135` |

### Query Parameter (Opsional)
| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `include` | string | `quiz`, `answers` (pisahkan koma) |

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
            "quiz_id": 135,
            "status": "submitted",
            "grading_status": "graded",
            "grading_status_label": "Sudah Dinilai",
            "score": 75.0,
            "final_score": 75.0,
            "passing_grade": 70,
            "is_passed": true,
            "attempt_number": 1,
            "started_at": "2026-04-29T05:04:04.000000Z",
            "submitted_at": "2026-04-29T05:47:04.000000Z",
            "time_spent_seconds": 2580
        }
    ],
    "meta": null,
    "errors": null
}
```

---

## 4. Nilai Terbaik Saya

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

### Contoh Response (200) — Nilai terbaik ditemukan
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 1422,
        "status": "submitted",
        "grading_status": "graded",
        "score": 100.0,
        "final_score": 100.0,
        "is_passed": true,
        "attempt_number": 2,
        "started_at": "2026-04-29T05:04:04.000000Z",
        "submitted_at": "2026-04-29T05:47:04.000000Z"
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

## 5. Mulai Sesi Kuis

**POST** `{{url}}/api/v1/quizzes/:quiz_id/submissions/start`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `quiz_id` | integer | `135` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body
Tidak diperlukan body (kosong).

### Contoh Request
```
POST {{url}}/api/v1/quizzes/135/submissions/start
```

### Contoh Response (201) — Berhasil mulai
```json
{
    "success": true,
    "message": "Kuis dimulai. Semangat!",
    "data": {
        "id": 1424,
        "quiz_id": 135,
        "status": "draft",
        "grading_status": null,
        "score": null,
        "final_score": null,
        "attempt_number": 2,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": null,
        "time_spent_seconds": null,
        "session_token": "eyJ0eXAiOiJKV1Qi..."
    },
    "meta": null,
    "errors": null
}
```

> ⚠️ **Simpan `session_token`** dari respons ini! Wajib disertakan sebagai header `X-Session-Token` pada endpoint questions, answers, overview, dan submit.

### Contoh Response (422) — Masih ada sesi aktif
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "quiz": ["Anda masih memiliki kuis yang sedang dikerjakan. Selesaikan terlebih dahulu sebelum memulai yang baru."]
    }
}
```

### Contoh Response (422) — Sesi sebelumnya menunggu penilaian
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "quiz": ["Pengumpulan kuis sebelumnya masih menunggu penilaian."]
    }
}
```

---

## 6. Overview Semua Soal + Status Jawaban

**GET** `{{url}}/api/v1/quiz-submissions/:submission_id/overview`

> Digunakan saat sedang mengerjakan kuis untuk melihat semua nomor soal, status jawaban, dan sisa waktu.
> Jika waktu habis dan status masih `draft`, kuis akan **otomatis dikumpulkan**.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `1424` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Header Wajib
| Header | Nilai |
|--------|-------|
| `X-Session-Token` | Session token dari response endpoint Start |

### Contoh Request
```
GET {{url}}/api/v1/quiz-submissions/1424/overview
Headers:
  X-Session-Token: eyJ0eXAiOiJKV1Qi...
```

### Contoh Response (200) — Sesi masih aktif
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "submission_id": 1424,
        "status": "draft",
        "started_at": "2026-05-04T08:00:00.000000Z",
        "time_limit_minutes": 44,
        "time_remaining_seconds": 2329,
        "is_time_limited": true,
        "total_questions": 4,
        "answered_count": 1,
        "unanswered_count": 3,
        "summary": [
            { "order": 1, "question_id": 537, "is_answered": true },
            { "order": 2, "question_id": 538, "is_answered": false },
            { "order": 3, "question_id": 539, "is_answered": false },
            { "order": 4, "question_id": 540, "is_answered": false }
        ],
        "questions": [
            {
                "id": 537,
                "order": 1,
                "type": "multiple_choice",
                "type_label": "Pilihan Ganda",
                "content": "Which of the following best describes dependency injection?",
                "options": ["Option A", "Option B", "Option C", "Option D"],
                "weight": "25.00",
                "max_score": "25.00",
                "is_answered": true,
                "answer": {
                    "id": 5349,
                    "content": null,
                    "selected_options": ["2"]
                }
            },
            {
                "id": 538,
                "order": 2,
                "type": "true_false",
                "type_label": "Benar/Salah",
                "content": "Laravel uses the MVC architectural pattern.",
                "options": ["True", "False"],
                "weight": "25.00",
                "max_score": "25.00",
                "is_answered": false,
                "answer": null
            },
            {
                "id": 539,
                "order": 3,
                "type": "checkbox",
                "type_label": "Kotak Centang",
                "content": "Select all valid HTTP methods:",
                "options": ["Option 1", "Option 2", "Option 3", "Option 4"],
                "weight": "25.00",
                "max_score": "25.00",
                "is_answered": false,
                "answer": null
            },
            {
                "id": 540,
                "order": 4,
                "type": "essay",
                "type_label": "Esai",
                "content": "Explain the concept of middleware in web applications.",
                "options": null,
                "weight": "25.00",
                "max_score": "25.00",
                "is_answered": false,
                "answer": null
            }
        ]
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Waktu habis, kuis otomatis dikumpulkan
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "auto_submitted": true,
        "submission_id": 1424,
        "status": "submitted",
        "grading_status": "graded",
        "grading_status_label": "Sudah Dinilai",
        "score": 50.0,
        "final_score": 50.0,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": "2026-05-04T08:44:00.000000Z",
        "time_limit_minutes": 44,
        "time_remaining_seconds": 0,
        "is_time_limited": true,
        "time_spent_seconds": 2640,
        "message": "Waktu habis. Kuis Anda telah dikumpulkan secara otomatis."
    },
    "meta": null,
    "errors": null
}
```

---

## 7. Ambil Pertanyaan (Per Halaman / Nomor Soal)

**GET** `{{url}}/api/v1/quiz-submissions/:submission_id/questions`

> Mengembalikan **satu pertanyaan** per request. Gunakan `?page=N` untuk navigasi antar soal (N dimulai dari 1).

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `1424` |

### Query Parameter
| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|------------|
| `page` | integer | Ya | Nomor soal ke-N (1-indexed) |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Header Wajib
| Header | Nilai |
|--------|-------|
| `X-Session-Token` | Session token dari response endpoint Start |

### Contoh Request — Soal ke-1
```
GET {{url}}/api/v1/quiz-submissions/1424/questions?page=1
Headers:
  X-Session-Token: eyJ0eXAiOiJKV1Qi...
```

### Contoh Response (200) — Soal belum dijawab
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "data": {
            "id": 537,
            "quiz_id": 135,
            "type": "multiple_choice",
            "type_label": "Pilihan Ganda",
            "content": "Which of the following best describes dependency injection?",
            "options": ["Option A", "Option B", "Option C", "Option D"],
            "weight": "25.00",
            "order": 1,
            "max_score": "25.00",
            "can_auto_grade": true,
            "requires_options": true
        },
        "meta": {
            "pagination": {
                "current_page": 1,
                "total": 4,
                "has_next": true,
                "has_prev": false
            }
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Soal sudah dijawab (ada field `answer`)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "data": {
            "id": 537,
            "quiz_id": 135,
            "type": "multiple_choice",
            "type_label": "Pilihan Ganda",
            "content": "Which of the following best describes dependency injection?",
            "options": ["Option A", "Option B", "Option C", "Option D"],
            "weight": "25.00",
            "order": 1,
            "max_score": "25.00",
            "can_auto_grade": true,
            "requires_options": true
        },
        "meta": {
            "pagination": {
                "current_page": 1,
                "total": 4,
                "has_next": true,
                "has_prev": false
            }
        },
        "answer": {
            "id": 5349,
            "content": null,
            "selected_options": ["2"]
        }
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Nomor halaman tidak valid
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "message": "Halaman tidak valid."
    }
}
```

---

## 8. Simpan Jawaban

**POST** `{{url}}/api/v1/quiz-submissions/:submission_id/answers`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `1424` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Header Wajib
| Header | Nilai |
|--------|-------|
| `X-Session-Token` | Session token dari response endpoint Start |

### Aturan Validasi Jawaban per Tipe Soal

| Tipe Soal | Field yang Diisi | Aturan |
|-----------|-----------------|--------|
| `multiple_choice` | `selected_options` | Array 1 elemen, nilai = index opsi (0 s/d jumlah opsi - 1) |
| `true_false` | `selected_options` | Array 1 elemen, nilai hanya `"0"` (True) atau `"1"` (False) |
| `checkbox` | `selected_options` | Array index opsi yang dipilih, tidak boleh duplikat, semua harus valid |
| `essay` | `content` | String teks, tidak boleh kosong. `selected_options` tidak diterima |

### Body — Multiple Choice
```json
{
    "quiz_question_id": 537,
    "selected_options": ["2"]
}
```
> `"2"` = opsi ke-3 (0-indexed: 0=Option A, 1=Option B, 2=Option C, 3=Option D)

### Body — True/False
```json
{
    "quiz_question_id": 538,
    "selected_options": ["0"]
}
```
> `"0"` = True, `"1"` = False

### Body — Checkbox (multi-pilih)
```json
{
    "quiz_question_id": 539,
    "selected_options": ["0", "2"]
}
```
> `"0"` = opsi ke-1, `"2"` = opsi ke-3

### Body — Essay
```json
{
    "quiz_question_id": 540,
    "content": "Middleware adalah lapisan perantara dalam aplikasi web yang memproses request sebelum mencapai controller..."
}
```

### Contoh Response (200) — Berhasil disimpan
```json
{
    "success": true,
    "message": "Jawaban berhasil disimpan.",
    "data": {
        "id": 5350,
        "quiz_submission_id": 1424,
        "quiz_question_id": 538,
        "content": null,
        "selected_options": ["0"],
        "score": null,
        "feedback": null
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Opsi tidak valid (multiple_choice / checkbox)
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "selected_options": ["Opsi yang dipilih tidak valid. Opsi yang valid adalah 0 sampai 3."]
    }
}
```

### Contoh Response (422) — True/False nilai tidak valid
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "selected_options": ["Jawaban harus berupa benar (0) atau salah (1)."]
    }
}
```

### Contoh Response (422) — Essay dengan selected_options
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "selected_options": ["Pemilihan opsi tidak diterima untuk pertanyaan esai. Silakan isi jawaban dalam bentuk teks."]
    }
}
```

### Contoh Response (422) — Waktu habis
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "submission": ["Batas waktu untuk percobaan kuis ini telah habis."]
    }
}
```

---

## 9. Submit Kuis

**POST** `{{url}}/api/v1/quiz-submissions/:submission_id/submit`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `1424` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Header Wajib
| Header | Nilai |
|--------|-------|
| `X-Session-Token` | Session token dari response endpoint Start |

### Body
Tidak diperlukan body (kosong).

### Contoh Response (200) — Berhasil dikumpulkan & langsung dinilai
```json
{
    "success": true,
    "message": "Kuis berhasil dikumpulkan.",
    "data": {
        "id": 1424,
        "quiz_id": 135,
        "status": "submitted",
        "grading_status": "graded",
        "grading_status_label": "Sudah Dinilai",
        "score": 75.0,
        "final_score": 75.0,
        "passing_grade": 70,
        "is_passed": true,
        "attempt_number": 2,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": "2026-05-04T08:38:00.000000Z",
        "time_spent_seconds": 2280
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (200) — Ada soal esai, menunggu penilaian manual
```json
{
    "success": true,
    "message": "Kuis berhasil dikumpulkan.",
    "data": {
        "id": 1424,
        "quiz_id": 135,
        "status": "submitted",
        "grading_status": "waiting_for_grading",
        "grading_status_label": "Menunggu Penilaian",
        "score": 50.0,
        "final_score": null,
        "passing_grade": 70,
        "is_passed": false,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": "2026-05-04T08:38:00.000000Z",
        "time_spent_seconds": 2280
    },
    "meta": null,
    "errors": null
}
```

### Contoh Response (422) — Ada soal yang belum dijawab
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": null,
    "meta": null,
    "errors": {
        "answers": ["Anda harus menjawab semua 3 pertanyaan sebelum mengumpulkan."]
    }
}
```

---

## 10. Detail Hasil Pengerjaan

**GET** `{{url}}/api/v1/quiz-submissions/:submission_id`

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `1424` |

### Query Parameter (Opsional)
| Parameter | Tipe | Nilai | Keterangan |
|-----------|------|-------|------------|
| `include` | string | `answers,quiz` | Sertakan data relasi |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Header Wajib
| Header | Nilai |
|--------|-------|
| `X-Session-Token` | Session token dari response endpoint Start |

### Contoh Request
```
GET {{url}}/api/v1/quiz-submissions/1424?include=answers,quiz
```

### Contoh Response (200)
```json
{
    "success": true,
    "message": "Permintaan berhasil diproses.",
    "data": {
        "id": 1424,
        "quiz_id": 135,
        "status": "submitted",
        "grading_status": "graded",
        "grading_status_label": "Sudah Dinilai",
        "score": 75.0,
        "final_score": 75.0,
        "passing_grade": 70,
        "is_passed": true,
        "attempt_number": 2,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": "2026-05-04T08:38:00.000000Z",
        "time_spent_seconds": 2280,
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
                "content": "Middleware adalah lapisan perantara...",
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

---

## 11. Ambil Alih Sesi (Resume / Takeover)

**POST** `{{url}}/api/v1/quiz-submissions/:submission_id/takeover`

> Digunakan jika sesi kuis tiba-tiba terputus (misalnya browser ditutup) dan asesi ingin melanjutkan dari perangkat/sesi lain.

### Path Parameter
| Parameter | Tipe | Contoh |
|-----------|------|--------|
| `submission_id` | integer | `1424` |

### Authorization
```
Bearer Token: {{access_token_student}}
```

### Body
Tidak diperlukan body (kosong).

### Contoh Response (200) — Berhasil takeover
```json
{
    "success": true,
    "message": "Sesi kuis berhasil diambil alih.",
    "data": {
        "id": 1424,
        "quiz_id": 135,
        "status": "draft",
        "grading_status": null,
        "score": null,
        "final_score": null,
        "started_at": "2026-05-04T08:00:00.000000Z",
        "submitted_at": null,
        "session_token": "eyJ0eXAiOiJKV1Qi..."
    },
    "meta": null,
    "errors": null
}
```

> ⚠️ **Perbarui `session_token`** yang disimpan di client dengan token baru dari response takeover ini.

---

## Referensi Enum

### `status` (QuizSubmission)
| Nilai | Keterangan |
|-------|------------|
| `draft` | Sedang dikerjakan |
| `submitted` | Sudah dikumpulkan |

### `grading_status`
| Nilai | Label | Keterangan |
|-------|-------|------------|
| `pending` | Menunggu | Belum diproses |
| `waiting_for_grading` | Menunggu Penilaian | Ada soal esai, menunggu penilaian manual |
| `graded` | Sudah Dinilai | Semua soal sudah dinilai |
| `pending_manual_grading` | Menunggu Penilaian Manual | Sebagian masih butuh penilaian manual |

### `type` (QuizQuestion)
| Nilai | Label | Keterangan |
|-------|-------|------------|
| `multiple_choice` | Pilihan Ganda | Satu pilihan benar, gunakan index opsi |
| `true_false` | Benar/Salah | `"0"` = True, `"1"` = False |
| `checkbox` | Kotak Centang | Bisa lebih dari satu pilihan |
| `essay` | Esai | Jawaban teks bebas |

---

## Bug / Catatan pada Koleksi

| No | Endpoint | Masalah | Status |
|----|----------|---------|--------|
| 1 | `[GET] Overview Semua Soal` | Token masih `{{access_token_superadmin}}` | ⚠️ Perlu diperbaiki → `{{access_token_student}}` |
| 2 | `questions/:order` | Tidak ada di backend — gunakan `questions?page=N` | ℹ️ Bukan endpoint terpisah |
