# Quick Fix - Production Images Not Loading

## TL;DR - Langkah Cepat

### 1. Diagnosa (5 menit)
```bash
# SSH ke VPS production
ssh user@your-vps-ip

# Masuk ke project directory
cd /path/to/Levl-BE

# Jalankan diagnostic
chmod +x scripts/check-storage-bucket.sh
./scripts/check-storage-bucket.sh
```

### 2. Fix Berdasarkan Hasil Diagnosa

#### Jika script menunjukkan: "Database uses levl-assets but .env uses prep-lsp"

**Fix:**
```bash
# Edit .env
nano .env

# Ubah baris ini:
DO_BUCKET=levl-assets
DO_CDN_URL=https://levl-assets.sgp1.cdn.digitaloceanspaces.com

# Save (Ctrl+O, Enter, Ctrl+X)

# Restart aplikasi
php artisan octane:reload
# atau
sudo systemctl restart levl-backend
```

#### Jika script menunjukkan: "Database uses prep-lsp but .env uses levl-assets"

**Fix:**
```bash
# Edit .env
nano .env

# Ubah baris ini:
DO_BUCKET=prep-lsp
DO_CDN_URL=https://prep-lsp.sgp1.cdn.digitaloceanspaces.com

# Save (Ctrl+O, Enter, Ctrl+X)

# Restart aplikasi
php artisan octane:reload
```

### 3. Test (2 menit)
```bash
# Test dari VPS
curl -I https://levl-assets.sgp1.cdn.digitaloceanspaces.com

# Test dari browser
# Buka aplikasi dan cek apakah gambar muncul
```

## Troubleshooting

### Gambar masih tidak muncul setelah fix?

1. **Clear cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan octane:reload
```

2. **Check permissions bucket:**
   - Login ke DigitalOcean Dashboard
   - Spaces → pilih bucket yang digunakan
   - Settings → pastikan "File Listing" = Public atau Private dengan CDN enabled

3. **Check CORS (jika diakses dari FE):**
   - Spaces → bucket → Settings → CORS Configurations
   - Add rule:
     ```
     Origins: *
     Allowed Methods: GET, HEAD
     Allowed Headers: *
     ```

### Error "NoSuchKey" masih muncul?

Berarti file memang tidak ada di bucket. Opsi:

1. **Upload ulang gambar** (untuk gambar baru)
2. **Copy dari bucket lama** (jika ada backup)
3. **Restore dari backup database** (jika ada)

## Files Created

Diagnostic tools yang sudah dibuat:
- `scripts/check-storage-bucket.sh` - Bash script untuk diagnosa
- `app/Console/Commands/DiagnoseStorageBucket.php` - Laravel command
- `PRODUCTION_IMAGE_FIX.md` - Dokumentasi lengkap

## Need Help?

Jika masih ada masalah, jalankan diagnostic dan share output:
```bash
./scripts/check-storage-bucket.sh > diagnostic-output.txt
cat diagnostic-output.txt
```
