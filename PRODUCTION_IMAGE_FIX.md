# Production Image Access Issue - Solusi

## 🚀 Quick Start

**Gambar tidak muncul di production?** Ikuti langkah ini:

1. **Diagnosa** (5 menit): Jalankan `./scripts/check-storage-bucket.sh` di VPS
2. **Fix** (2 menit): Update `.env` sesuai rekomendasi script
3. **Test** (1 menit): Restart app dan cek gambar

📖 **Lihat:** [QUICK_FIX_IMAGES.md](./QUICK_FIX_IMAGES.md) untuk panduan singkat

---

## Masalah
Di production, gambar tidak bisa diakses dengan error:
```xml
<Error>
  <Code>NoSuchKey</Code>
  <BucketName>levl-assets</BucketName>
</Error>
```

Padahal di local dengan database local bisa akses gambar.

## Analisis Root Cause

### 1. Bucket Mismatch
- **Database production** menyimpan URL gambar yang mengarah ke bucket `levl-assets`
- **Konfigurasi production** seharusnya menggunakan bucket `prep-lsp` (sesuai `.env.example`)
- File gambar tidak ada di bucket `levl-assets` atau bucket tersebut tidak accessible

### 2. Kemungkinan Skenario
- Database production memiliki data lama dari bucket `levl-assets` (bucket lama)
- File gambar belum di-migrate ke bucket baru
- Environment variable production tidak sesuai dengan data di database

## Solusi

### Opsi 1: Update Environment Production (RECOMMENDED - AMAN)
Jika bucket `levl-assets` masih ada dan berisi file:

**Ini solusi paling aman karena TIDAK mengubah data di database.**

1. Update `.env` di production:
```env
DO_BUCKET=levl-assets
DO_CDN_URL=https://levl-assets.sgp1.cdn.digitaloceanspaces.com
```

2. Restart aplikasi untuk load environment baru:
```bash
# Jika pakai Octane
php artisan octane:reload

# Atau restart service
sudo systemctl restart levl-backend
```

3. Test akses gambar

**Keuntungan:**
- ✅ Tidak mengubah database
- ✅ Tidak ada risiko data loss
- ✅ Bisa rollback dengan mudah (tinggal ganti .env lagi)
- ✅ Instant fix tanpa downtime

### Opsi 2: Migrate Data ke Bucket Baru (KOMPLEKS - BERISIKO)
⚠️ **HANYA gunakan jika Opsi 1 tidak memungkinkan**

Jika ingin menggunakan bucket `prep-lsp` dan bucket `levl-assets` akan dihapus:

#### Step 1: Backup SEMUA
```bash
# Backup database
cd Levl-BE
php artisan db:backup

# Export database
pg_dump levl > levl_backup_$(date +%Y%m%d).sql

# Backup bucket (download semua file)
# Gunakan DigitalOcean Spaces UI atau CLI
```

#### Step 2: Copy Files dari Bucket Lama ke Baru
```bash
# Install AWS CLI (compatible dengan DO Spaces)
pip install awscli

# Configure
aws configure set aws_access_key_id YOUR_DO_ACCESS_KEY
aws configure set aws_secret_access_key YOUR_DO_SECRET_KEY

# Copy semua files (bisa memakan waktu lama!)
aws s3 sync s3://levl-assets s3://prep-lsp \
  --endpoint-url https://sgp1.digitaloceanspaces.com \
  --acl public-read
```

#### Step 3: Verify Files Copied
```bash
# Check file count
aws s3 ls s3://levl-assets --recursive --endpoint-url https://sgp1.digitaloceanspaces.com | wc -l
aws s3 ls s3://prep-lsp --recursive --endpoint-url https://sgp1.digitaloceanspaces.com | wc -l

# Should be same number
```

#### Step 4: Update Database URLs
⚠️ **JANGAN jalankan migration ini sebelum Step 2 & 3 selesai!**

Buat Artisan command (lebih aman dari migration):

```bash
php artisan make:command UpdateMediaBucketUrls
```

```php
// app/Console/Commands/UpdateMediaBucketUrls.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateMediaBucketUrls extends Command
{
    protected $signature = 'media:update-bucket-urls 
                          {--old=levl-assets : Old bucket name}
                          {--new=prep-lsp : New bucket name}
                          {--dry-run : Show what would be updated without actually updating}';

    protected $description = 'Update media bucket URLs in database';

    public function handle()
    {
        $oldBucket = $this->option('old');
        $newBucket = $this->option('new');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Check media table
        $mediaCount = DB::table('media')
            ->where('disk', 'do')
            ->where(DB::raw('custom_properties::text'), 'like', "%{$oldBucket}%")
            ->count();

        $this->info("Found {$mediaCount} media records to update");

        if (!$dryRun && $this->confirm('Update media table?')) {
            DB::table('media')
                ->where('disk', 'do')
                ->update([
                    'custom_properties' => DB::raw("custom_properties::jsonb || jsonb_build_object('bucket', '{$newBucket}')")
                ]);
            $this->info('✓ Media table updated');
        }

        // Check content tables
        $tables = [
            'news' => 'content',
            'announcements' => 'content',
            'posts' => 'content',
            'courses' => 'description',
            'units' => 'description',
            'lessons' => 'description',
        ];

        foreach ($tables as $table => $column) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            $count = DB::table($table)
                ->whereNotNull($column)
                ->where($column, 'like', "%{$oldBucket}%")
                ->count();

            if ($count > 0) {
                $this->info("Found {$count} records in {$table}.{$column}");

                if (!$dryRun && $this->confirm("Update {$table}?")) {
                    DB::table($table)
                        ->whereNotNull($column)
                        ->where($column, 'like', "%{$oldBucket}%")
                        ->update([
                            $column => DB::raw("REPLACE({$column}, '{$oldBucket}', '{$newBucket}')")
                        ]);
                    $this->info("✓ {$table} updated");
                }
            }
        }

        $this->info('Done!');
        
        if ($dryRun) {
            $this->warn('This was a DRY RUN. Run without --dry-run to apply changes.');
        }
    }
}
```

#### Step 5: Test dengan Dry Run
```bash
# Test dulu tanpa mengubah data
php artisan media:update-bucket-urls --dry-run

# Jika sudah yakin, jalankan
php artisan media:update-bucket-urls
```

#### Step 6: Update Environment
```env
DO_BUCKET=prep-lsp
DO_CDN_URL=https://prep-lsp.sgp1.cdn.digitaloceanspaces.com
```

#### Step 7: Test & Verify
```bash
# Restart app
php artisan octane:reload

# Test upload gambar baru
# Test akses gambar lama
```

### Opsi 3: Quick Fix - Update Bucket Permissions
Jika file ada di `levl-assets` tapi tidak accessible:

1. Login ke DigitalOcean Spaces
2. Buka bucket `levl-assets`
3. Set permissions ke **Public** atau configure CORS
4. Verify CDN settings

## Cara Diagnosa di VPS

### Metode 1: Menggunakan Script Otomatis (RECOMMENDED)

```bash
# SSH ke production server
cd /path/to/Levl-BE

# Buat script executable
chmod +x scripts/check-storage-bucket.sh

# Jalankan diagnostic script
./scripts/check-storage-bucket.sh
```

Script ini akan otomatis:
- ✓ Cek konfigurasi .env
- ✓ Cek database media records
- ✓ Test koneksi ke bucket
- ✓ Deteksi mismatch antara database dan config
- ✓ Berikan rekomendasi fix

### Metode 2: Menggunakan Laravel Command

```bash
# SSH ke production server
cd /path/to/Levl-BE

# Run diagnostic command
php artisan storage:diagnose

# Dengan test upload (optional)
php artisan storage:diagnose --test-upload
```

### Metode 3: Manual Check

```bash
# SSH ke production server
cd /path/to/Levl-BE

# Check environment
cat .env | grep DO_BUCKET
cat .env | grep DO_CDN_URL
```

### 2. Check Database URLs (Setelah Diagnosa)

Jika script menunjukkan mismatch, cek detail di database:

```sql
-- Check media table
SELECT disk, COUNT(*) 
FROM media 
GROUP BY disk;

-- Check sample URLs
SELECT id, disk, file_name, custom_properties 
FROM media 
LIMIT 5;

-- Check content with embedded images
SELECT id, content 
FROM news 
WHERE content LIKE '%levl-assets%' 
LIMIT 5;
```

### 3. Test Image Access
```bash
# Test URL dari error message
curl -I https://levl-assets.sgp1.cdn.digitaloceanspaces.com/path/to/image.jpg

# Test dengan bucket baru
curl -I https://prep-lsp.sgp1.cdn.digitaloceanspaces.com/path/to/image.jpg
```

## Rekomendasi

### Untuk Quick Fix (Hari Ini):
✅ **Gunakan Opsi 1** - Update `.env` production ke bucket `levl-assets`
- Tidak ada risiko
- Tidak mengubah data
- Instant fix
- Bisa rollback kapan saja

### Untuk Long-term (Nanti):
Jika memang ingin migrate ke bucket baru:
1. Lakukan di waktu maintenance window
2. Backup semua data dulu
3. Copy files dulu, verify dulu
4. Baru update database
5. Test thoroughly sebelum production

### JANGAN:
❌ Jalankan migration langsung di production tanpa backup
❌ Update database sebelum files di-copy
❌ Lakukan saat jam sibuk
❌ Skip testing phase

## Testing Checklist

- [ ] Verify bucket exists dan accessible
- [ ] Check file permissions di bucket
- [ ] Test image URL dari browser
- [ ] Verify CDN configuration
- [ ] Check CORS settings jika diakses dari FE
- [ ] Test upload gambar baru
- [ ] Verify existing images dapat diakses

## Tools & Scripts

### 1. Bash Diagnostic Script
**File:** `scripts/check-storage-bucket.sh`

Comprehensive bash script untuk diagnosa di VPS:
```bash
chmod +x scripts/check-storage-bucket.sh
./scripts/check-storage-bucket.sh
```

Features:
- ✓ Check .env configuration
- ✓ Test database connection
- ✓ Count media records by bucket
- ✓ Test bucket connectivity
- ✓ Detect configuration mismatch
- ✓ Provide actionable recommendations

### 2. Laravel Artisan Command
**File:** `app/Console/Commands/DiagnoseStorageBucket.php`

Laravel command untuk detailed diagnostics:
```bash
php artisan storage:diagnose
php artisan storage:diagnose --test-upload
```

Features:
- ✓ Environment config check
- ✓ Database media analysis
- ✓ Bucket connectivity test
- ✓ Sample URL testing
- ✓ Optional upload test

### 3. Quick Fix Guide
**File:** `QUICK_FIX_IMAGES.md`

Step-by-step guide untuk quick fix tanpa technical details.

### 4. Database Update Command (Optional)
**File:** `app/Console/Commands/UpdateMediaBucketUrls.php`

Untuk migrate bucket URLs di database (hanya jika diperlukan):
```bash
php artisan media:update-bucket-urls --dry-run
php artisan media:update-bucket-urls --old=levl-assets --new=prep-lsp
```
