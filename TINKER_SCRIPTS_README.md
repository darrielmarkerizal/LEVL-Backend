# Tinker Scripts - Check Unlocked Assessments

Script-script PHP untuk memeriksa status assessment (quiz dan assignment) yang unlocked untuk student menggunakan Laravel Tinker.

## Available Scripts

### 1. `tinker-unlocked-simple.php`
**Deskripsi:** Menampilkan semua student yang memiliki quiz/assignment yang bisa di-start (is_locked = false)

**Cara Pakai:**
```bash
php artisan tinker < tinker-unlocked-simple.php
```

**Output:**
- List semua student dengan assessment yang unlocked
- Detail assignment dan quiz per student
- Summary total

**Contoh Output:**
```
=== STUDENTS WITH UNLOCKED ASSESSMENTS ===

Student #1: John Doe
  ID: 15
  Email: john@example.com
  Unlocked Assignments: 2
    • Laravel Basics Assignment (ID: 1) - Laravel Fundamentals > Getting Started
    • Routing Assignment (ID: 2) - Laravel Fundamentals > Getting Started
  Unlocked Quizzes: 1
    • Laravel Basics Quiz (ID: 162) - Laravel Fundamentals > Getting Started

============================================================
SUMMARY:
  Total Students: 5
  Total Unlocked Assignments: 10
  Total Unlocked Quizzes: 8
  Total Unlocked Assessments: 18
```

---

### 2. `tinker-student-assessments.php`
**Deskripsi:** Menampilkan detail lengkap assessment untuk satu student tertentu

**Cara Pakai:**
1. Edit file dan ubah `$studentId` di baris 8:
   ```php
   $studentId = 15; // Change this to the student ID you want to check
   ```

2. Jalankan:
   ```bash
   php artisan tinker < tinker-student-assessments.php
   ```

**Output:**
- Detail lengkap per course dan unit
- Status locked/unlocked per unit
- Prerequisites yang harus diselesaikan (jika locked)
- Status submission (jika sudah ada)
- Score dan passing status

**Contoh Output:**
```
=== ASSESSMENT STATUS FOR STUDENT ===
Name: John Doe
Email: john@example.com
ID: 15
============================================================

COURSE: Laravel Fundamentals
------------------------------------------------------------

  Unit 1: Getting Started [🔓 UNLOCKED]
    Assignments:
      ✅ Laravel Basics Assignment (ID: 1)
         Status: graded | Score: 85/100
    Quizzes:
      ✅ Laravel Basics Quiz (ID: 162)
         Status: graded | Score: 85/100 | PASSED

  Unit 2: Fundamentals [🔒 LOCKED]
    Prerequisites needed:
      - Lesson: Advanced Routing
      - Assignment: Routing Assignment (MUST PASS)
    Assignments:
      🔒 Middleware Assignment (ID: 5)
    Quizzes:
      🔒 Middleware Quiz (ID: 163)

============================================================

SUMMARY:
  Unlocked Assessments: 2
  Locked Assessments: 4
  Total Assessments: 6
```

---

### 3. `tinker-check-unlocked-assessments.php`
**Deskripsi:** Versi lengkap dengan detail per student dan summary

**Cara Pakai:**
```bash
php artisan tinker < tinker-check-unlocked-assessments.php
```

**Output:**
- Detail lengkap per student
- Status locked/unlocked per assessment
- Summary akhir

---

## Quick Commands

### Melihat semua student dengan unlocked assessments
```bash
php artisan tinker < tinker-unlocked-simple.php
```

### Melihat detail student tertentu
```bash
# Edit tinker-student-assessments.php, ubah $studentId
php artisan tinker < tinker-student-assessments.php
```

### Melihat student ID dari database
```bash
php artisan tinker --execute="User::role('Student')->get(['id', 'name', 'email'])"
```

---

## Understanding the Output

### Status Icons
- 🔓 UNLOCKED - Assessment bisa di-start
- 🔒 LOCKED - Assessment terkunci (prerequisites belum selesai)
- ✅ - Assessment available
- • - List item

### Assessment Status
- `draft` - Belum disubmit
- `submitted` - Sudah disubmit, menunggu grading
- `graded` - Sudah dinilai
- `late` - Terlambat
- `missing` - Tidak dikumpulkan

### Prerequisites
- Lesson: Harus diselesaikan (completed)
- Assignment: Harus PASS (score >= 60% dari max_score)
- Quiz: Harus PASS (final_score >= passing_grade)

---

## Troubleshooting

### Error: "Class not found"
Pastikan Anda menjalankan dari root directory Laravel:
```bash
cd /path/to/your/laravel/project
php artisan tinker < tinker-unlocked-simple.php
```

### Error: "Student not found"
Cek student ID yang valid:
```bash
php artisan tinker --execute="User::role('Student')->pluck('id', 'name')"
```

### Tidak ada output
Kemungkinan tidak ada student dengan active enrollment atau semua assessment terkunci.

Cek enrollment:
```bash
php artisan tinker --execute="Enrollment::where('status', 'active')->count()"
```

---

## Notes

- Script ini menggunakan `PrerequisiteService` untuk mengecek status locked
- Hanya menampilkan assessment dengan status `published`
- Hanya menampilkan enrollment dengan status `active`
- Unit order 1 selalu unlocked
- Unit order 2+ memerlukan unit sebelumnya 100% complete

---

## Related Files

- `Modules/Schemes/app/Services/PrerequisiteService.php` - Logic untuk cek prerequisites
- `Modules/Learning/app/Models/Assignment.php` - Model Assignment
- `Modules/Learning/app/Models/Quiz.php` - Model Quiz
- `Modules/Schemes/app/Models/Unit.php` - Model Unit
