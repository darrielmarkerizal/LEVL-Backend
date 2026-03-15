# Tinker Badge Analysis Guide
## Panduan Analisis Badge Student menggunakan PHP Artisan Tinker

**Tanggal**: 15 Maret 2026  
**Purpose**: Analisis student dengan badge terbanyak

---

## 🚀 QUICK START

### Method 1: Run Script File (Recommended)
```bash
cd Levl-BE
php artisan tinker < find_top_badge_students.php
```

### Method 2: Run in Interactive Tinker
```bash
cd Levl-BE
php artisan tinker
```

Then in tinker:
```php
include 'find_top_badge_students.php';
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
