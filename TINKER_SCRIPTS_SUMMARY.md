# Summary: Tinker Scripts untuk Check Unlocked Assessments

## Yang Telah Diselesaikan

### 1. Perbaikan Model Unit
**File:** `Modules/Schemes/app/Models/Unit.php`

Menambahkan relationship yang hilang:
```php
public function assignments()
{
    return $this->hasMany(\Modules\Learning\Models\Assignment::class);
}

public function quizzes()
{
    return $this->hasMany(\Modules\Learning\Models\Quiz::class);
}
```

**Alasan:** Model Unit tidak memiliki relationship ke Assignment dan Quiz, padahal setelah refactoring, assignments dan quizzes sekarang berada di level unit (bukan polymorphic lagi).

---

### 2. Script Tinker yang Dibuat

#### A. `tinker-unlocked-simple.php`
**Fungsi:** Menampilkan semua student yang memiliki assessment (quiz/assignment) yang unlocked (is_locked = false)

**Fitur:**
- List semua student dengan active enrollment
- Menampilkan assignment dan quiz yang unlocked per student
- Summary total unlocked assessments
- Menggunakan `PrerequisiteService` untuk cek status locked

**Cara Pakai:**
```bash
php artisan tinker < tinker-unlocked-simple.php
```

---

#### B. `tinker-student-assessments.php`
**Fungsi:** Menampilkan detail lengkap assessment untuk satu student tertentu

**Fitur:**
- Detail per course dan unit
- Status locked/unlocked per unit
- Prerequisites yang harus diselesaikan (jika locked)
- Status submission (jika sudah ada)
- Score dan passing status
- Summary unlocked vs locked assessments

**Cara Pakai:**
1. Edit file, ubah `$studentId = 15;` ke ID student yang ingin dicek
2. Jalankan:
```bash
php artisan tinker < tinker-student-assessments.php
```

---

#### C. `tinker-debug-data.php`
**Fungsi:** Debug script untuk memeriksa data di database

**Fitur:**
- Cek jumlah students dengan role 'Student'
- Cek active enrollments
- Cek published assignments dan quizzes
- Cek units
- Test PrerequisiteService

**Cara Pakai:**
```bash
php artisan tinker < tinker-debug-data.php
```

---

#### D. `TINKER_SCRIPTS_README.md`
Dokumentasi lengkap cara menggunakan semua script

---

## Hasil Eksekusi

### Output Script (0 Results)
```
=== STUDENTS WITH UNLOCKED ASSESSMENTS ===

============================================================
SUMMARY:
  Total Students: 0
  Total Unlocked Assignments: 0
  Total Unlocked Quizzes: 0
  Total Unlocked Assessments: 0
```

### Kemungkinan Penyebab 0 Results:

1. **Tidak ada student dengan active enrollment**
   - Cek: `Enrollment::where('status', 'active')->count()`

2. **Tidak ada published assignments/quizzes**
   - Cek: `Assignment::where('status', 'published')->count()`
   - Cek: `Quiz::where('status', 'published')->count()`

3. **Semua assessment terkunci (locked)**
   - Unit order 2+ memerlukan unit sebelumnya 100% complete
   - Prerequisites: ALL lessons completed + ALL assignments/quizzes passed

4. **Data seeder belum dijalankan**
   - Jalankan: `php artisan db:seed`

---

## Cara Debugging

### 1. Cek Data Dasar
```bash
php artisan tinker < tinker-debug-data.php
```

### 2. Cek Manual di Tinker
```bash
php artisan tinker
```

Kemudian jalankan:
```php
// Cek students
\Modules\Auth\Models\User::role('Student')->count()

// Cek enrollments
\Modules\Schemes\Models\Enrollment::where('status', 'active')->count()

// Cek assignments
\Modules\Learning\Models\Assignment::where('status', 'published')->count()

// Cek quizzes
\Modules\Learning\Models\Quiz::where('status', 'published')->count()

// Cek unit pertama (selalu unlocked)
$unit = \Modules\Schemes\Models\Unit::where('order', 1)->first()
$student = \Modules\Auth\Models\User::role('Student')->first()
$service = app(\Modules\Schemes\Services\PrerequisiteService::class)
$service->checkUnitAccess($unit, $student->id) // Should return false (unlocked)
```

---

## Perbaikan yang Dilakukan

### Issue 1: Namespace Error
**Error:** `Target class [Modules\Schemes\app\Services\PrerequisiteService] does not exist`

**Fix:** Menggunakan namespace yang benar:
```php
// ❌ Wrong
use Modules\Schemes\app\Services\PrerequisiteService;

// ✅ Correct
\Modules\Schemes\Services\PrerequisiteService::class
```

---

### Issue 2: Relationship Not Found
**Error:** `Call to undefined relationship [assignments] on model [Modules\Schemes\Models\Unit]`

**Fix:** Menambahkan relationship di `Unit.php`:
```php
public function assignments()
{
    return $this->hasMany(\Modules\Learning\Models\Assignment::class);
}

public function quizzes()
{
    return $this->hasMany(\Modules\Learning\Models\Quiz::class);
}
```

---

### Issue 3: Type Error
**Error:** `Argument #2 ($userId) must be of type int, Modules\Auth\Models\User given`

**Fix:** Mengirim user ID, bukan object User:
```php
// ❌ Wrong
$prerequisiteService->checkUnitAccess($unit, $student)

// ✅ Correct
$prerequisiteService->checkUnitAccess($unit, $student->id)
```

---

## Next Steps

1. **Jalankan debug script** untuk melihat data yang ada:
   ```bash
   php artisan tinker < tinker-debug-data.php
   ```

2. **Jika tidak ada data**, jalankan seeder:
   ```bash
   php artisan db:seed
   ```

3. **Jika ada data tapi semua locked**, cek prerequisites:
   - Unit 1 selalu unlocked
   - Unit 2+ memerlukan unit sebelumnya complete
   - Complete = ALL lessons + ALL assignments/quizzes passed

4. **Test dengan student tertentu**:
   ```bash
   # Edit tinker-student-assessments.php, set $studentId
   php artisan tinker < tinker-student-assessments.php
   ```

---

## Files Created

1. `tinker-unlocked-simple.php` - Main script untuk list students
2. `tinker-student-assessments.php` - Detail script per student
3. `tinker-debug-data.php` - Debug script
4. `TINKER_SCRIPTS_README.md` - Dokumentasi lengkap
5. `TINKER_SCRIPTS_SUMMARY.md` - Summary ini

---

## Technical Notes

- Script menggunakan `PrerequisiteService::checkUnitAccess()` untuk cek locked status
- Hanya menampilkan assessment dengan status `published`
- Hanya menampilkan enrollment dengan status `active`
- Unit order 1 selalu unlocked (tidak perlu prerequisites)
- Unit order 2+ memerlukan unit sebelumnya 100% complete

---

**Created:** 2026-03-03  
**Status:** ✅ Scripts ready, waiting for data verification
