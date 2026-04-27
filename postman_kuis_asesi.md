# 📚 Pembelajaran Kuis – Panduan Endpoint Asesi

> **Token yang digunakan:** `{{access_token_student}}`
> **Base URL:** `{{url}}/api/v1`

---

## Alur Pengerjaan Kuis

```
1. Lihat daftar kuis dalam skema          → GET  /courses/:slug/quizzes
2. Lihat detail kuis spesifik             → GET  /quizzes/:quiz_id
3. Mulai sesi kuis                        → POST /quizzes/:quiz_id/submissions/start
4. Ambil pertanyaan satu per satu         → GET  /quiz-submissions/:id/questions?page=N
   atau berdasarkan urutan                → GET  /quiz-submissions/:id/questions/:order
5. Simpan jawaban per pertanyaan          → POST /quiz-submissions/:id/answers
6. Kirim kuis secara final                → POST /quiz-submissions/:id/submit
7. Lihat hasil / nilai akhir              → GET  /quiz-submissions/:id
8. Lihat riwayat semua sesi kuis          → GET  /quizzes/:quiz_id/submissions
9. Lihat nilai terbaik                    → GET  /quizzes/:quiz_id/submissions/highest
```

---

## 1. [GET] Daftar Kuis dalam Skema

**Endpoint:**
```
GET {{url}}/api/v1/courses/:course_slug/quizzes
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug skema/kursus yang sudah diikuti |

**Query Params (semua opsional):**

| Key | Value Contoh | Deskripsi |
|-----|-------------|-----------|
| `page` | `1` | Nomor halaman |
| `per_page` | `20` | Jumlah data per halaman (max: 100) |

**Catatan:** Untuk asesi (Student), respons hanya menampilkan kuis berstatus `published`. Filter status tidak bisa diubah oleh asesi.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar kuis berhasil diambil.",
  "data": [
    {
      "id": 57,
      "title": "Kuis: Analisis Data Dasar",
      "order": 2,
      "sequence": "1.2",
      "description": "Kuis evaluasi pemahaman analisis data.",
      "passing_grade": 70,
      "max_score": 100,
      "time_limit_minutes": 30,
      "auto_grading": true,
      "review_mode": "after_submit",
      "randomization_type": "none",
      "question_bank_count": null,
      "status": "published",
      "status_label": "Diterbitkan",
      "is_locked": false,
      "is_completed": false,
      "unit_slug": "fundamentals-and-core-concepts-7-abc123",
      "course_slug": "analisis-data-untuk-pengambilan-keputusan-7",
      "submission_status": "draft",
      "submission_status_label": "Sedang Dikerjakan",
      "score": null,
      "submitted_at": null,
      "is_submission_completed": false,
      "attempts_used": 0,
      "xp_reward": 50,
      "xp_perfect_bonus": 25,
      "created_at": "2026-04-01T10:00:00.000000Z"
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

## 2. [GET] Detail Kuis

**Endpoint:**
```
GET {{url}}/api/v1/quizzes/:quiz_id
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `quiz_id` | `57` | ID kuis yang ingin dilihat |

**Catatan:** Asesi harus sudah terdaftar di skema yang memiliki kuis ini. Jika kuis terkunci (prasyarat belum terpenuhi), akan mengembalikan error 403.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 57,
    "title": "Kuis: Analisis Data Dasar",
    "order": 2,
    "sequence": "1.2",
    "description": "Kuis evaluasi pemahaman analisis data.",
    "passing_grade": 70,
    "max_score": 100,
    "time_limit_minutes": 30,
    "auto_grading": true,
    "review_mode": "after_submit",
    "randomization_type": "none",
    "question_bank_count": null,
    "status": "published",
    "status_label": "Diterbitkan",
    "is_locked": false,
    "is_completed": false,
    "unit_slug": "fundamentals-and-core-concepts-7-abc123",
    "course_slug": "analisis-data-untuk-pengambilan-keputusan-7",
    "submission_status": null,
    "submission_status_label": null,
    "score": null,
    "submitted_at": null,
    "is_submission_completed": false,
    "attempts_used": 0,
    "xp_reward": 50,
    "xp_perfect_bonus": 25,
    "created_at": "2026-04-01T10:00:00.000000Z"
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons Error (403 Kuis Terkunci):**
```json
{
  "success": false,
  "message": "Kuis ini masih terkunci. Selesaikan prasyarat terlebih dahulu.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## 3. [POST] Mulai Sesi Kuis

**Endpoint:**
```
POST {{url}}/api/v1/quizzes/:quiz_id/submissions/start
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `quiz_id` | `57` | ID kuis yang akan dimulai |

**Body:** Tidak diperlukan (kosong / no body)

**Catatan:**
- Membuat sesi baru dengan status `draft`
- Jika sudah ada sesi draft yang belum disubmit, akan melanjutkan sesi yang ada (tergantung policy)
- Jika kuis punya `randomization_type`, soal akan diacak dan disimpan di `question_set`
- Waktu mulai (`started_at`) dicatat saat endpoint ini dipanggil

**Contoh Respons (201 Created):**
```json
{
  "success": true,
  "message": "Sesi kuis berhasil dimulai.",
  "data": {
    "id": 1530,
    "attempt_number": 1,
    "status": "draft",
    "status_label": "Sedang Dikerjakan",
    "grading_status": null,
    "grading_status_label": null,
    "score": null,
    "final_score": null,
    "is_passed": null,
    "started_at": "2026-04-27T08:00:00.000000Z",
    "submitted_at": null,
    "time_spent_seconds": 0,
    "duration": null
  },
  "meta": null,
  "errors": null
}
```

> ⚠️ **Simpan `id` dari respons ini** sebagai `:submission_id` untuk semua endpoint berikutnya.

---

## 4. [GET] Ambil Pertanyaan Saat Ini (Per Halaman)

**Endpoint:**
```
GET {{url}}/api/v1/quiz-submissions/:submission_id/questions
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `submission_id` | `1530` | ID sesi kuis (dari endpoint Mulai Sesi) |

**Query Params:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `page` | `1` | Nomor pertanyaan yang ingin ditampilkan (1-based). Default: 1 |

**Catatan:** Untuk asesi, endpoint ini menampilkan **satu pertanyaan per halaman** beserta jawaban yang sudah disimpan (jika ada). Gunakan `meta.total` untuk mengetahui total pertanyaan.

**Contoh Respons (200 OK — Pertanyaan Pilihan Ganda):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "data": {
      "id": 225,
      "quiz_id": 57,
      "type": "multiple_choice",
      "type_label": "Pilihan Ganda",
      "content": "Apa yang dimaksud dengan mean dalam statistik?",
      "options": [
        { "text": "Nilai yang paling sering muncul" },
        { "text": "Nilai tengah dari data yang diurutkan" },
        { "text": "Nilai rata-rata dari semua data" },
        { "text": "Selisih antara nilai terbesar dan terkecil" }
      ],
      "weight": "25.00",
      "order": 1,
      "max_score": "25.00",
      "can_auto_grade": true,
      "requires_options": true,
      "created_at": "2026-04-01T10:00:00.000000Z"
    },
    "meta": {
      "current_page": 1,
      "total": 4,
      "has_next": true,
      "has_prev": false
    }
  },
  "answer": {
    "id": 891,
    "content": null,
    "selected_options": ["2"]
  },
  "errors": null
}
```

**Contoh Respons (200 OK — Pertanyaan Essay):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "data": {
      "id": 226,
      "quiz_id": 57,
      "type": "essay",
      "type_label": "Essay",
      "content": "Jelaskan perbedaan antara data kualitatif dan kuantitatif!",
      "options": null,
      "weight": "25.00",
      "order": 2,
      "max_score": "25.00",
      "can_auto_grade": false,
      "requires_options": false,
      "created_at": "2026-04-01T10:00:00.000000Z"
    },
    "meta": {
      "current_page": 2,
      "total": 4,
      "has_next": true,
      "has_prev": true
    }
  },
  "errors": null
}
```

---

## 5. [GET] Ambil Pertanyaan Berdasarkan Urutan

**Endpoint:**
```
GET {{url}}/api/v1/quiz-submissions/:submission_id/questions/:order
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `submission_id` | `1530` | ID sesi kuis |
| `order` | `1` | Urutan pertanyaan (1-based integer) |

**Catatan:** Berbeda dengan endpoint per halaman, ini mengambil pertanyaan langsung berdasarkan urutan (bukan pagination). Juga mengembalikan informasi navigasi (prev/next).

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "question": {
      "id": 225,
      "quiz_id": 57,
      "type": "multiple_choice",
      "type_label": "Pilihan Ganda",
      "content": "Apa yang dimaksud dengan mean dalam statistik?",
      "options": [
        { "text": "Nilai yang paling sering muncul" },
        { "text": "Nilai tengah dari data yang diurutkan" },
        { "text": "Nilai rata-rata dari semua data" },
        { "text": "Selisih antara nilai terbesar dan terkecil" }
      ],
      "weight": "25.00",
      "order": 1,
      "max_score": "25.00",
      "can_auto_grade": true,
      "requires_options": true,
      "created_at": "2026-04-01T10:00:00.000000Z"
    },
    "navigation": {
      "current": 1,
      "total": 4,
      "prev_order": null,
      "next_order": 2
    }
  },
  "errors": null
}
```

---

## 6. [POST] Simpan Jawaban Pertanyaan

**Endpoint:**
```
POST {{url}}/api/v1/quiz-submissions/:submission_id/answers
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `submission_id` | `1530` | ID sesi kuis yang sedang aktif |

**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| `quiz_question_id` | integer | ✅ Ya | ID pertanyaan yang dijawab |
| `content` | string | ❌ Nullable | Isi jawaban untuk tipe **essay** |
| `selected_options` | array of string | ❌ Nullable | Indeks jawaban (0-based) untuk tipe **multiple_choice**, **true_false**, atau **checkbox** |

---

### Contoh Body — Pilihan Ganda (multiple_choice)

```json
{
  "quiz_question_id": 225,
  "selected_options": ["2"]
}
```
> Pilih opsi ke-3 (index 2 dari 0). Hanya boleh satu nilai.

---

### Contoh Body — True/False (true_false)

```json
{
  "quiz_question_id": 226,
  "selected_options": ["0"]
}
```
> Index `"0"` = opsi pertama (biasanya "Benar"), `"1"` = opsi kedua (biasanya "Salah").

---

### Contoh Body — Checkbox / Multi-Answer

```json
{
  "quiz_question_id": 227,
  "selected_options": ["0", "2", "4"]
}
```
> Boleh pilih lebih dari satu opsi.

---

### Contoh Body — Essay

```json
{
  "quiz_question_id": 228,
  "content": "Data kualitatif adalah data yang bersifat deskriptif dan tidak dapat diukur dengan angka, seperti warna, rasa, atau pendapat. Sedangkan data kuantitatif adalah data yang dapat diukur secara numerik, seperti tinggi badan, berat badan, atau jumlah produk."
}
```

---

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Jawaban berhasil disimpan.",
  "data": {
    "id": 891,
    "quiz_question_id": 225,
    "content": null,
    "selected_options": ["2"],
    "score": null,
    "feedback": null
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons Error (422 — Kuis Tidak Dalam Status Draft):**
```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid.",
  "data": null,
  "meta": null,
  "errors": {
    "submission": ["Sesi kuis ini sudah dikumpulkan dan tidak dapat diubah."]
  }
}
```

> ⚠️ **Endpoint ini dapat dipanggil berulang kali** untuk memperbarui jawaban. Jika pertanyaan yang sama sudah dijawab sebelumnya, jawaban akan diperbarui (upsert).

---

## 7. [POST] Kirim Kuis (Submit Final)

**Endpoint:**
```
POST {{url}}/api/v1/quiz-submissions/:submission_id/submit
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `submission_id` | `1530` | ID sesi kuis yang akan dikumpulkan |

**Body:** Tidak diperlukan (kosong / no body)

**Catatan:**
- Semua pertanyaan harus sudah dijawab sebelum submit. Jika ada yang belum dijawab, akan mengembalikan error 422.
- Setelah submit, status berubah dari `draft` → `submitted`.
- Auto-grading dijalankan otomatis untuk soal pilihan ganda, true/false, dan checkbox.
- Soal essay akan menunggu penilaian manual dari instruktur.
- Waktu pengerjaan (`time_spent_seconds`) dihitung otomatis.

**Contoh Respons (200 OK — Auto Graded Penuh):**
```json
{
  "success": true,
  "message": "Kuis berhasil dikumpulkan.",
  "data": {
    "id": 1530,
    "attempt_number": 1,
    "status": "submitted",
    "status_label": "Dikumpulkan",
    "grading_status": "graded",
    "grading_status_label": "Sudah Dinilai",
    "score": "87.50",
    "final_score": null,
    "is_passed": true,
    "started_at": "2026-04-27T08:00:00.000000Z",
    "submitted_at": "2026-04-27T08:28:15.000000Z",
    "time_spent_seconds": 1695,
    "duration": null
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons (200 OK — Ada Essay, Perlu Penilaian Manual):**
```json
{
  "success": true,
  "message": "Kuis berhasil dikumpulkan.",
  "data": {
    "id": 1530,
    "attempt_number": 1,
    "status": "submitted",
    "status_label": "Dikumpulkan",
    "grading_status": "pending_manual_grading",
    "grading_status_label": "Menunggu Penilaian",
    "score": "62.50",
    "final_score": null,
    "is_passed": null,
    "started_at": "2026-04-27T08:00:00.000000Z",
    "submitted_at": "2026-04-27T08:28:15.000000Z",
    "time_spent_seconds": 1695,
    "duration": null
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons Error (422 — Ada Pertanyaan Belum Dijawab):**
```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid.",
  "data": null,
  "meta": null,
  "errors": {
    "answers": ["Masih ada 2 pertanyaan yang belum dijawab."]
  }
}
```

---

## 8. [GET] Detail Hasil Pengerjaan Kuis

**Endpoint:**
```
GET {{url}}/api/v1/quiz-submissions/:submission_id
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `submission_id` | `1530` | ID sesi kuis |

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `include` | `answers` | Menyertakan daftar jawaban asesi |
| `include` | `answers,quiz` | Menyertakan jawaban dan detail kuis |

**Contoh URL dengan include:**
```
GET {{url}}/api/v1/quiz-submissions/1530?include=answers,quiz
```

**Contoh Respons (200 OK — Tanpa Include):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1530,
    "attempt_number": 1,
    "status": "submitted",
    "status_label": "Dikumpulkan",
    "grading_status": "graded",
    "grading_status_label": "Sudah Dinilai",
    "score": "87.50",
    "final_score": "87.50",
    "is_passed": true,
    "started_at": "2026-04-27T08:00:00.000000Z",
    "submitted_at": "2026-04-27T08:28:15.000000Z",
    "time_spent_seconds": 1695,
    "duration": null
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons (200 OK — Dengan `?include=answers`):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1530,
    "attempt_number": 1,
    "status": "submitted",
    "status_label": "Dikumpulkan",
    "grading_status": "graded",
    "grading_status_label": "Sudah Dinilai",
    "score": "87.50",
    "final_score": "87.50",
    "is_passed": true,
    "started_at": "2026-04-27T08:00:00.000000Z",
    "submitted_at": "2026-04-27T08:28:15.000000Z",
    "time_spent_seconds": 1695,
    "duration": null,
    "answers": [
      {
        "id": 891,
        "quiz_question_id": 225,
        "content": null,
        "selected_options": ["2"],
        "score": "25.00",
        "feedback": null
      },
      {
        "id": 892,
        "quiz_question_id": 226,
        "content": null,
        "selected_options": ["0"],
        "score": "25.00",
        "feedback": null
      },
      {
        "id": 893,
        "quiz_question_id": 227,
        "content": null,
        "selected_options": ["0", "2", "4"],
        "score": "25.00",
        "feedback": null
      },
      {
        "id": 894,
        "quiz_question_id": 228,
        "content": "Data kualitatif adalah data yang bersifat deskriptif...",
        "selected_options": null,
        "score": "12.50",
        "feedback": "Jawaban cukup baik, namun perlu lebih detail."
      }
    ]
  },
  "meta": null,
  "errors": null
}
```

---

## 9. [GET] Riwayat Semua Sesi Pengerjaan Kuis Saya

**Endpoint:**
```
GET {{url}}/api/v1/quizzes/:quiz_id/submissions
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `quiz_id` | `57` | ID kuis |

**Query Params (opsional):**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `include` | `answers` | Sertakan jawaban di setiap sesi |
| `include` | `quiz` | Sertakan detail kuis |

**Catatan:** Asesi hanya melihat riwayat miliknya sendiri. Mengembalikan semua sesi dari percobaan pertama hingga terakhir.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1530,
      "attempt_number": 1,
      "status": "submitted",
      "status_label": "Dikumpulkan",
      "grading_status": "graded",
      "grading_status_label": "Sudah Dinilai",
      "score": "87.50",
      "final_score": "87.50",
      "is_passed": true,
      "started_at": "2026-04-27T08:00:00.000000Z",
      "submitted_at": "2026-04-27T08:28:15.000000Z",
      "time_spent_seconds": 1695,
      "duration": null
    },
    {
      "id": 1543,
      "attempt_number": 2,
      "status": "submitted",
      "status_label": "Dikumpulkan",
      "grading_status": "graded",
      "grading_status_label": "Sudah Dinilai",
      "score": "100.00",
      "final_score": "100.00",
      "is_passed": true,
      "started_at": "2026-04-28T09:00:00.000000Z",
      "submitted_at": "2026-04-28T09:22:00.000000Z",
      "time_spent_seconds": 1320,
      "duration": null
    }
  ],
  "meta": null,
  "errors": null
}
```

---

## 10. [GET] Nilai Terbaik / Submission Tertinggi

**Endpoint:**
```
GET {{url}}/api/v1/quizzes/:quiz_id/submissions/highest
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**

| Key | Value | Deskripsi |
|-----|-------|-----------|
| `quiz_id` | `57` | ID kuis |

**Catatan:** Mengembalikan satu sesi dengan skor tertinggi. Jika belum pernah mengerjakan kuis, mengembalikan `data: null`.

**Contoh Respons (200 OK — Ada Data):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1543,
    "attempt_number": 2,
    "status": "submitted",
    "status_label": "Dikumpulkan",
    "grading_status": "graded",
    "grading_status_label": "Sudah Dinilai",
    "score": "100.00",
    "final_score": "100.00",
    "is_passed": true,
    "started_at": "2026-04-28T09:00:00.000000Z",
    "submitted_at": "2026-04-28T09:22:00.000000Z",
    "time_spent_seconds": 1320,
    "duration": null
  },
  "meta": null,
  "errors": null
}
```

**Contoh Respons (200 OK — Belum Pernah Mengerjakan):**
```json
{
  "success": true,
  "message": null,
  "data": null,
  "meta": null,
  "errors": null
}
```

---

## Referensi Nilai Enum

### Status Sesi Kuis (`status`)
| Value | Label | Keterangan |
|-------|-------|------------|
| `draft` | Sedang Dikerjakan | Sesi aktif, belum dikumpulkan |
| `submitted` | Dikumpulkan | Sudah disubmit, menunggu/sudah dinilai |

### Status Penilaian (`grading_status`)
| Value | Label | Keterangan |
|-------|-------|------------|
| `in_progress` | Sedang Dikerjakan | Draft, belum selesai |
| `auto_graded` | Dinilai Otomatis | Semua soal bisa auto-grade |
| `pending_manual_grading` | Menunggu Penilaian | Ada essay yang perlu dinilai manual |
| `partially_graded` | Dinilai Sebagian | Sebagian sudah dinilai |
| `graded` | Sudah Dinilai | Semua soal sudah dinilai |
| `released` | Nilai Dirilis | Nilai sudah dirilis ke asesi |

### Tipe Pertanyaan (`type`)
| Value | Label | `selected_options` | `content` |
|-------|-------|-------------------|-----------|
| `multiple_choice` | Pilihan Ganda | 1 elemen (index 0-based) | Tidak digunakan |
| `true_false` | Benar/Salah | 1 elemen (`"0"` atau `"1"`) | Tidak digunakan |
| `checkbox` | Checkbox | Bisa lebih dari 1 elemen | Tidak digunakan |
| `essay` | Essay | Tidak digunakan | Teks jawaban |

---

## Ringkasan Semua Endpoint

| # | Method | Endpoint | Deskripsi |
|---|--------|----------|-----------|
| 1 | `GET` | `/courses/:course_slug/quizzes` | Daftar kuis dalam skema |
| 2 | `GET` | `/quizzes/:quiz_id` | Detail kuis |
| 3 | `POST` | `/quizzes/:quiz_id/submissions/start` | Mulai sesi kuis |
| 4 | `GET` | `/quiz-submissions/:id/questions?page=N` | Ambil pertanyaan per halaman |
| 5 | `GET` | `/quiz-submissions/:id/questions/:order` | Ambil pertanyaan berdasarkan urutan |
| 6 | `POST` | `/quiz-submissions/:id/answers` | Simpan jawaban |
| 7 | `POST` | `/quiz-submissions/:id/submit` | Submit kuis final |
| 8 | `GET` | `/quiz-submissions/:id` | Detail hasil sesi kuis |
| 9 | `GET` | `/quizzes/:quiz_id/submissions` | Riwayat semua sesi kuis saya |
| 10 | `GET` | `/quizzes/:quiz_id/submissions/highest` | Nilai / sesi terbaik saya |
