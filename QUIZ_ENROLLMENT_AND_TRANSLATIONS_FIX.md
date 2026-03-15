# Quiz Enrollment Validation & Translation Fixes

## Ringkasan Perubahan

### 1. Validasi Enrollment di QuizSubmissionPolicy

**File**: `Modules/Learning/app/Policies/QuizSubmissionPolicy.php`

**Perubahan**:
- Menambahkan validasi enrollment pada method `update()` untuk student
- Memastikan student harus enrolled dengan status `active` atau `completed` untuk bisa:
  - Menjawab pertanyaan quiz (saveAnswer)
  - Submit quiz
- Menggunakan pola yang sama dengan `SubmissionPolicy` untuk konsistensi
- Load relasi lengkap: `quiz.unit.course` untuk mendapatkan course

**Logika**:
```php
if ($user->hasRole('Student')) {
    $submission->loadMissing('quiz.unit.course');
    $course = $submission->quiz?->unit?->course;
    
    if (! $course) {
        return false;
    }

    return \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->whereIn('status', ['active', 'completed'])
        ->exists();
}
```

### 2. Perbaikan Pesan Error Quiz Submission

**File**: 
- `lang/id/messages.php`
- `lang/en/messages.php`

**Perubahan**:
- Menambahkan pesan `in_progress` untuk quiz yang masih dikerjakan
- Mengubah pesan `pending_grading` menjadi lebih jelas
- Menambahkan pesan `not_enrolled` untuk quiz

**Pesan Baru**:

**Bahasa Indonesia**:
```php
'quiz_submissions' => [
    'in_progress'     => 'Anda masih memiliki kuis yang sedang dikerjakan. Selesaikan terlebih dahulu sebelum memulai yang baru.',
    'pending_grading' => 'Pengumpulan kuis sebelumnya masih menunggu penilaian.',
],

'quizzes' => [
    'not_enrolled' => 'Anda harus terdaftar di kursus ini untuk mengakses kuis.',
],
```

**English**:
```php
'quiz_submissions' => [
    'in_progress'     => 'You still have a quiz in progress. Please complete it before starting a new one.',
    'pending_grading' => 'Your previous quiz submission is awaiting grading.',
],

'quizzes' => [
    'not_enrolled' => 'You must be enrolled in this course to access the quiz.',
],
```

### 3. Update Logika di QuizSubmissionService

**File**: `Modules/Learning/app/Services/QuizSubmissionService.php`

**Perubahan**:
- Menggunakan pesan `in_progress` untuk status Draft
- Menggunakan pesan `pending_grading` untuk status Submitted
- Memperbaiki perbandingan enum menggunakan `===` langsung tanpa `.value`

**Sebelum**:
```php
if ($pendingSubmission->status === QuizSubmissionStatus::Draft->value) {
    throw ValidationException::withMessages([
        'quiz' => [__('messages.quiz_submissions.draft_exists')]
    ]);
}
```

**Sesudah**:
```php
if ($pendingSubmission->status === QuizSubmissionStatus::Draft) {
    throw ValidationException::withMessages([
        'quiz' => [__('messages.quiz_submissions.in_progress')]
    ]);
}
```

### 4. Menambahkan Translation File untuk Enums

**File Baru**:
- `lang/id/enums.php`
- `lang/en/enums.php`

**Isi**:
Translation untuk semua enum yang digunakan di Learning module:
- `quiz_question_type`: Multiple Choice, Checkbox, True/False, Essay
- `quiz_submission_status`: Draft, Submitted, Graded
- `quiz_grading_status`: Pending, Waiting for Grading, Partially Graded, Graded
- `quiz_status`: Draft, Published, Archived
- `assignment_status`: Draft, Published, Archived
- `assignment_type`: Assignment, Quiz
- `submission_type`: File, Text, Both
- `submission_status`: Draft, Submitted, Graded, Late, Missing
- `review_mode`: Immediate, After Due Date, After Graded, Never
- `randomization_type`: Static, Random, Random from Bank

**Contoh**:
```php
'quiz_question_type' => [
    'multiple_choice' => 'Pilihan Ganda',  // ID
    'multiple_choice' => 'Multiple Choice', // EN
    'checkbox' => 'Kotak Centang',         // ID
    'checkbox' => 'Checkbox',              // EN
    'true_false' => 'Benar/Salah',         // ID
    'true_false' => 'True/False',          // EN
    'essay' => 'Esai',                     // ID
    'essay' => 'Essay',                    // EN
],
```

## Validasi yang Diterapkan

### Start Quiz
1. ✅ Quiz tidak terkunci (prerequisite check)
2. ✅ Student enrolled dengan status `active` (di QuizPolicy.takeQuiz)
3. ✅ Tidak ada draft submission yang sedang berjalan
4. ✅ Tidak ada submitted submission yang menunggu grading

### Save Answer
1. ✅ Submission milik user yang login
2. ✅ Submission dalam status `draft`
3. ✅ Student enrolled dengan status `active` atau `completed`
4. ✅ Quiz tidak terkunci

### Submit Quiz
1. ✅ Submission milik user yang login
2. ✅ Submission dalam status `draft`
3. ✅ Student enrolled dengan status `active` atau `completed`
4. ✅ Quiz tidak terkunci
5. ✅ Semua pertanyaan sudah dijawab

## Testing

### Test Enrollment Validation

1. **Test dengan student yang tidak enrolled**:
   ```bash
   # Expected: 403 Forbidden
   POST /api/v1/quizzes/{quiz}/submissions/start
   ```

2. **Test dengan student enrolled tapi status pending**:
   ```bash
   # Expected: 403 Forbidden
   POST /api/v1/quizzes/{quiz}/submissions/start
   ```

3. **Test dengan student enrolled status active**:
   ```bash
   # Expected: 200 OK
   POST /api/v1/quizzes/{quiz}/submissions/start
   ```

### Test Error Messages

1. **Test start quiz saat ada draft**:
   ```bash
   # Expected: "Anda masih memiliki kuis yang sedang dikerjakan..."
   POST /api/v1/quizzes/{quiz}/submissions/start
   ```

2. **Test start quiz saat ada submitted**:
   ```bash
   # Expected: "Pengumpulan kuis sebelumnya masih menunggu penilaian."
   POST /api/v1/quizzes/{quiz}/submissions/start
   ```

### Test Translation

1. **Test type_label di quiz question**:
   ```bash
   # Expected: "type_label": "Pilihan Ganda" (bukan "enums.quiz_question_type.multiple_choice")
   GET /api/v1/quiz-submissions/{submission}/questions?page=1
   ```

## Status: ✅ Complete

Semua perubahan telah diimplementasikan dan diverifikasi tanpa error diagnostics.
