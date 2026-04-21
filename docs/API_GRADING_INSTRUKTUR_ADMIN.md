# API Documentation - Pembelajaran & Penilaian (Instruktur/Admin)

Dokumen ini merangkum endpoint penilaian untuk role Instruktur/Admin, khusus area:

- Antrean penilaian
- Detail submission
- Bulk actions
- Eksekusi nilai per submission

Base URL:

```text
{{url}}/api/v1
```

Auth:

- Semua endpoint di dokumen ini membutuhkan `Bearer {{instructor_token}}`
- Header minimum:

```http
Authorization: Bearer {{instructor_token}}
Accept: application/json
```

Format response (envelope):

```json
{
  "success": true,
  "message": "...",
  "data": {},
  "meta": null,
  "errors": null
}
```

## 1) Antrean & Bulk Actions

### 1.1 GET /grading

Lihat antrean penilaian assignment + quiz (baris quiz diekspansi menjadi baris per jawaban essay).

URL:

```text
{{url}}/api/v1/grading
```

Query params:

- `search` (string, optional, max 255)
- `page` (integer, min 1)
- `per_page` (integer, min 1, max 100, default 15)
- `filter` (object/array)

Perilaku `search`:

- Menggunakan pola `PgSearchable` (ILIKE + similarity) seperti modul lain.
- Mencari lintas field berikut:
  - nama/email/username student (`user`)
  - nama assignment (`assignment`)
  - nama quiz (`quiz`)
  - nama skema/course (`course`)

Allowed filter keys:

- `filter[status]`
- `filter[workflow_state]`
- `filter[user_id]`
- `filter[course_slug]`
- `filter[assignment_id]`
- `filter[quiz_id]`
- `filter[question_id]`
- `filter[grading_status]`
- `filter[date_from]`
- `filter[date_to]`

Allowed values:

- `filter[status]`:
  - `draft`
  - `submitted`
  - `graded`
  - `missing`
- `filter[workflow_state]`:
  - `in_progress`
  - `auto_graded`
  - `pending_manual_grading`
  - `graded`
  - `released`
  - `pending`
  - `partially_graded`
  - `waiting_for_grading`
- `filter[grading_status]`:
  - `pending`
  - `partially_graded`
  - `waiting_for_grading`
  - `graded`
- `filter[user_id]`: harus ada di tabel users
- `filter[course_slug]`: harus ada di tabel courses
- `filter[assignment_id]`: harus ada di tabel assignments
- `filter[quiz_id]`: harus ada di tabel quizzes
- `filter[question_id]`: harus ada di tabel quiz_questions
- `filter[date_from]`: format date, harus `<= filter[date_to]`
- `filter[date_to]`: format date, harus `>= filter[date_from]`

Sort:

- Parameter `sort` tidak didukung.
- Data diurutkan internal `submitted_at` descending.

Contoh query:

```text
{{url}}/api/v1/grading?page=1&per_page=20&filter[workflow_state]=partially_graded&filter[quiz_id]=87
```

```text
{{url}}/api/v1/grading?filter[question_id]=348
```

```text
{{url}}/api/v1/grading?search=FAJAR
```

Catatan penting filter lintas tipe:

- Jika ada `filter[assignment_id]`, data quiz dikecualikan.
- Jika ada `filter[quiz_id]` atau `filter[grading_status]`, data assignment dikecualikan.

---

### 1.2 GET /grading/{submission_id}

Detail submission untuk dinilai (assignment submission).

URL:

```text
{{url}}/api/v1/grading/{{submission_id}}
```

Path params:

- `submission_id` (id submission)

Query params:

- `include` (optional, comma-separated)

Allowed include values:

- `user`
- `assignment`
- `assignment.unit`
- `assignment.unit.course`
- `answers`
- `answers.question`
- `grade`

Sort:

- Tidak ada `sort` yang didukung.

Contoh:

```text
{{url}}/api/v1/grading/1556?include=user,assignment,assignment.unit.course,answers.question,grade
```

---

### 1.3 GET /grading/quiz-submissions/{quiz_submission_id}/questions/{question_id}

Lihat detail spesifik 1 jawaban essay quiz dalam sebuah quiz submission.

URL:

```text
{{url}}/api/v1/grading/quiz-submissions/{{quiz_submission_id}}/questions/{{question_id}}
```

Path params:

- `quiz_submission_id` (id quiz submission)
- `question_id` (id quiz question bertipe essay)

Sort:

- Tidak ada `sort` yang didukung.

Jika soal tidak ditemukan atau bukan essay pada submission terkait, response `404`.

Contoh response `data`:

```json
{
  "type": "quiz",
  "row_type": "essay_question",
  "submission_id": 1584,
  "quiz_answer_id": 6336,
  "student_name": "Ika Handayani",
  "student_email": "peserta.390@peserta.demo.levl.id",
  "quiz_id": 87,
  "quiz_title": "Quiz ...",
  "course": {
    "id": 15,
    "slug": "pelayanan-pelanggan-berbasis-sop-15",
    "title": "Pelayanan Pelanggan Berbasis SOP",
    "code": "CRS0015"
  },
  "course_slug": "pelayanan-pelanggan-berbasis-sop-15",
  "sequence": "1.8",
  "submitted_at": "2026-04-19T22:14:09.000000Z",
  "status": "graded",
  "status_value": "graded",
  "status_label": "Dinilai",
  "grading_status": "graded",
  "grading_status_label": "Dinilai",
  "workflow_state": "graded",
  "workflow_state_value": "graded",
  "workflow_state_label": "Dinilai",
  "score": "100.00",
  "final_score": "100.00",
  "student_answer": "...",
  "question_id": 348,
  "answer_score": "20.00",
  "is_graded": true
}
```

---

### 1.4 POST /grading/bulk-release

Rilis nilai massal untuk beberapa submission.

URL:

```text
{{url}}/api/v1/grading/bulk-release
```

Body params:

- `submission_ids` (required, array, min 1)
- `submission_ids[]` (required, integer, exists in submissions)
- `async` (optional, boolean)

Sort/filter:

- Tidak berlaku.

Raw JSON:

```json
{
  "submission_ids": [1556, 1557, 1558],
  "async": true
}
```

Form-data:

```text
submission_ids[0]=1556
submission_ids[1]=1557
submission_ids[2]=1558
async=true
```

---

### 1.5 POST /grading/bulk-feedback

Tambahkan feedback massal untuk beberapa submission.

URL:

```text
{{url}}/api/v1/grading/bulk-feedback
```

Body params:

- `submission_ids` (required, array, min 1)
- `submission_ids[]` (required, integer, exists in submissions)
- `feedback` (required, string, min length 1)
- `async` (optional, boolean)

Sort/filter:

- Tidak berlaku.

Raw JSON:

```json
{
  "submission_ids": [1556, 1557],
  "feedback": "Mohon perbaiki struktur jawaban dan lengkapi referensi.",
  "async": false
}
```

Form-data:

```text
submission_ids[0]=1556
submission_ids[1]=1557
feedback=Mohon perbaiki struktur jawaban dan lengkapi referensi.
async=false
```

## 2) Eksekusi Nilai per Submission

Parent path:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades
```

Semua endpoint di bawah parent ini memakai middleware role Instruktur/Admin/Superadmin + `can:grade,submission`.

### 2.1 GET /

Cek nilai saat ini (`GradeResource`) pada submission.

URL:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades
```

Query/body:

- Tidak ada.

Sort/filter:

- Tidak ada.

---

### 2.2 GET /status

Cek status penilaian submission.

URL:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades/status
```

Response `data` fields:

- `submission_id`
- `is_complete`
- `graded_questions`
- `total_questions`
- `can_finalize`
- `can_release`

Sort/filter:

- Tidak ada.

---

### 2.3 PUT /draft

Simpan draft nilai per soal (belum final).

URL:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades/draft
```

Body params:

- `grades` (required, array, min 1)
- `grades[].question_id` (required, integer, exists in questions)
- `grades[].score` (optional nullable, numeric, min 0)
- `grades[].feedback` (optional nullable, string)

Catatan behavior:

- Jika grade sudah finalized (`is_draft=false`), request akan ditolak.
- Validasi tambahan runtime: `grades[].score` tidak boleh melebihi `max_score` soal.

Raw JSON:

```json
{
  "grades": [
    {
      "question_id": 348,
      "score": 15,
      "feedback": "Draft awal, elaborasi cukup baik."
    },
    {
      "question_id": 349,
      "score": null,
      "feedback": "Belum dinilai."
    }
  ]
}
```

Form-data:

```text
grades[0][question_id]=348
grades[0][score]=15
grades[0][feedback]=Draft awal, elaborasi cukup baik.
grades[1][question_id]=349
grades[1][score]=
grades[1][feedback]=Belum dinilai.
```

---

### 2.4 POST /

Tetapkan nilai akhir manual. Endpoint ini punya 2 mode yang saling eksklusif.

URL:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades
```

Rule utama:

- Wajib pilih salah satu: `grades` atau `score`.
- Tidak boleh kirim keduanya sekaligus.

#### Mode A - Per Soal (umum untuk quiz essay/manual per question)

Body params:

- `grades` (array, min 1)
- `grades[].question_id` (required_with grades, integer, exists in questions)
- `grades[].score` (required_with grades, numeric, min 0)
- `grades[].feedback` (optional nullable, string)
- `feedback` (optional overall feedback)

Runtime behavior:

- Semua answer yang relevan harus sudah punya skor sebelum finalisasi.
- Tiap skor soal tidak boleh > `max_score` soal.

Raw JSON:

```json
{
  "grades": [
    {
      "question_id": 348,
      "score": 20,
      "feedback": "Argumen lengkap dan runtut."
    },
    {
      "question_id": 349,
      "score": 18,
      "feedback": "Baik, namun contoh bisa ditambah."
    }
  ],
  "feedback": "Secara keseluruhan sangat baik."
}
```

Form-data:

```text
grades[0][question_id]=348
grades[0][score]=20
grades[0][feedback]=Argumen lengkap dan runtut.
grades[1][question_id]=349
grades[1][score]=18
grades[1][feedback]=Baik, namun contoh bisa ditambah.
feedback=Secara keseluruhan sangat baik.
```

#### Mode B - Override Skor Global (umum assignment)

Body params:

- `score` (numeric, min 0)
- `feedback` (optional)

Raw JSON:

```json
{
  "score": 87,
  "feedback": "Nilai akhir ditetapkan berdasarkan rubrik tugas."
}
```

Form-data:

```text
score=87
feedback=Nilai akhir ditetapkan berdasarkan rubrik tugas.
```

---

### 2.5 PATCH /

Timpa nilai sebelumnya (override grade).

URL:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades
```

Body params:

- `score` (required, numeric, min 0, max 100)
- `reason` (required, string, min 10, max 1000)

Raw JSON:

```json
{
  "score": 92,
  "reason": "Penyesuaian nilai setelah verifikasi rubrik dan bukti tambahan."
}
```

Form-data:

```text
score=92
reason=Penyesuaian nilai setelah verifikasi rubrik dan bukti tambahan.
```

---

### 2.6 PATCH /release

Rilis nilai ke asesi.

URL:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades/release
```

Body/query:

- Tidak ada.

Syarat runtime:

- Grade harus sudah ada.
- Grade tidak boleh draft.
- Submission harus state `graded` atau `released`.

---

### 2.7 PATCH /return-to-queue

Batalkan kondisi graded dan kembalikan ke antrean penilaian.

URL:

```text
{{url}}/api/v1/submissions/{{submission_id}}/grades/return-to-queue
```

Body/query:

- Tidak ada.

Syarat runtime:

- Submission harus dalam state `graded`.

Efek:

- State submission menjadi `pending_manual_grading`.
- Grade terkait di-set `is_draft=true` (jika ada).

## 3) Ringkasan Allowed Filter, Include, Sort

### 3.1 Allowed Filter untuk GET /grading

- `status`
- `workflow_state`
- `user_id`
- `course_slug`
- `assignment_id`
- `quiz_id`
- `question_id`
- `grading_status`
- `date_from`
- `date_to`

Jika mengirim key filter di luar daftar ini, API melempar error invalid filter.

### 3.2 Allowed Include

Hanya untuk GET `/grading/{submission_id}`:

- `user`
- `assignment`
- `assignment.unit`
- `assignment.unit.course`
- `answers`
- `answers.question`
- `grade`

### 3.3 Allowed Sort

- GET `/grading`: tidak menerima `sort` dari query, sorting internal fixed by `submitted_at desc`.
- Endpoint lainnya: tidak memiliki dukungan `sort`.

## 4) Checklist Penggunaan Cepat

### Case A - Nilai Essay Quiz per Soal

1. Ambil antrean quiz essay: `GET /grading?filter[quiz_id]=...`
2. Ambil detail soal spesifik: `GET /grading/quiz-submissions/{quiz_submission_id}/questions/{question_id}`
3. Simpan draft/final sesuai workflow grading internal.

### Case B - Nilai Assignment

1. Ambil antrean assignment: `GET /grading?filter[assignment_id]=...`
2. Detail submission: `GET /grading/{submission_id}`
3. Final grade: `POST /submissions/{submission_id}/grades` dengan mode `score` atau `grades`.
4. Release: `PATCH /submissions/{submission_id}/grades/release`.

## 5) Referensi Source Code

- Routes: `Modules/Grading/routes/api.php`
- Controller: `Modules/Grading/app/Http/Controllers/GradingController.php`
- Requests:
  - `Modules/Grading/app/Http/Requests/GradingQueueRequest.php`
  - `Modules/Grading/app/Http/Requests/BulkReleaseGradesRequest.php`
  - `Modules/Grading/app/Http/Requests/BulkFeedbackRequest.php`
  - `Modules/Grading/app/Http/Requests/ManualGradeRequest.php`
  - `Modules/Grading/app/Http/Requests/SaveDraftGradeRequest.php`
  - `Modules/Grading/app/Http/Requests/OverrideGradeRequest.php`
- Service: `Modules/Grading/app/Services/GradingQueueService.php`
- Resources:
  - `Modules/Grading/app/Http/Resources/GradingQueueItemResource.php`
  - `Modules/Grading/app/Http/Resources/GradeResource.php`
- Enums:
  - `Modules/Learning/app/Enums/SubmissionState.php`
  - `Modules/Learning/app/Enums/SubmissionStatus.php`
  - `Modules/Learning/app/Enums/QuizGradingStatus.php`
  - `Modules/Learning/app/Enums/QuizSubmissionStatus.php`
