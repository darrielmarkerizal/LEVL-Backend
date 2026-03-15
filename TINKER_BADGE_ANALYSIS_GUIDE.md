# Tinker Badge Analysis Guide
## Panduan Analisis Badge Student menggunakan PHP Artisan Tinker

**Tanggal**: 15 Maret 2026  
**Purpose**: Analisis student dengan badge terbanyak

---

## 🚀 QUICK START

### Method 1: Using Artisan Command (Recommended - Easiest)
```bash
cd Levl-BE

# Show top 10 students
php artisan gamification:top-badge-students

# Show top 20 students
php artisan gamification:top-badge-students --limit=20

# Show detailed badge information for top student
php artisan gamification:top-badge-students --detailed

# Combine options
php artisan gamification:top-badge-students --limit=5 --detailed
```

### Method 2: Run in Interactive Tinker
```bash
cd Levl-BE
php artisan tinker
```

Then copy-paste the content from `find_top_badge_students_tinker.php` into tinker.

### Method 3: Include Script in Tinker
```bash
cd Levl-BE
php artisan tinker
```

Then in tinker:
```php
include 'find_top_badge_students_tinker.php';
```

---

## 📊 OUTPUT YANG DIHASILKAN

Script akan menampilkan 5 analisis berbeda:

### 1. Top 10 Students by Badge Count (Method 1)
Menggunakan `withCount()` - paling efisien
```
#1 - Ahmad Rizki (ahmad@example.com)
    Badges: 25
#2 - Siti Nurhaliza (siti@example.com)
    Badges: 22
...
```

### 2. Top 10 Students using Query Builder (Method 2)
Menggunakan JOIN - untuk perbandingan
```
#1 - Ahmad Rizki (ahmad@example.com)
    Badges: 25
...
```

### 3. Detailed Badge Information for Top Student (Method 3)
Detail lengkap badge yang dimiliki student teratas
```
Top Student: Ahmad Rizki (ahmad@example.com)
Total Badges: 25

Badge List:
  1. Quiz Champion (quiz_champion)
     Category: assessment | Rarity: rare | Type: achievement
     Earned: 2026-03-10 14:30:00
  2. First Steps (first_steps)
     Category: learning | Rarity: common | Type: milestone
     Earned: 2026-03-01 10:30:00
...
```

### 4. Badge Statistics by Category (Method 4)
Breakdown badge berdasarkan kategori dan rarity
```
Badge breakdown for Ahmad Rizki:
  - Learning: 10 badges
  - Assessment: 8 badges
  - Engagement: 5 badges
  - Achievement: 2 badges

Badge breakdown by rarity:
  - Common: 12 badges
  - Uncommon: 7 badges
  - Rare: 4 badges
  - Epic: 2 badges
```

### 5. Comparison of Top 5 Students (Method 5)
Perbandingan top 5 dengan level dan XP
```
Rank Name                      Badges     Level      Total XP  
----------------------------------------------------------------------
#1   Ahmad Rizki               25         9          2850      
#2   Siti Nurhaliza            22         8          2650      
#3   Budi Santoso              20         8          2450      
#4   Dewi Lestari              18         7          2100      
#5   Eko Prasetyo              15         6          1800      
```

### 6. Summary Statistics
Statistik keseluruhan
```
Total Students: 150
Students with Badges: 85 (56.7%)
Total Badges Awarded: 450
Average Badges per Student (with badges): 5.29
```

---

## 🔍 MANUAL QUERIES IN TINKER

Jika ingin menjalankan query manual di tinker:

### Find Top Student
```php
use Modules\Auth\Models\User;

$topStudent = User::role('Student')
    ->withCount('badges')
    ->orderByDesc('badges_count')
    ->first();

echo $topStudent->name . " has " . $topStudent->badges_count . " badges\n";
```

### Get Top 10 Students
```php
$top10 = User::role('Student')
    ->withCount('badges')
    ->orderByDesc('badges_count')
    ->limit(10)
    ->get(['id', 'name', 'email']);

foreach ($top10 as $i => $student) {
    echo ($i+1) . ". " . $student->name . " - " . $student->badges_count . " badges\n";
}
```
