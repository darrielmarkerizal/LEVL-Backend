# Enrollment Key Management Scripts Guide

## Overview

Dua script telah dibuat untuk membantu mengelola enrollment keys yang sudah ada:

1. **view_enrollment_keys.php** - Melihat status enrollment keys
2. **regenerate_enrollment_keys.php** - Regenerate keys untuk courses yang hanya memiliki hash

## Problem Statement

Enrollment keys yang dibuat sebelum implementasi encryption hanya di-hash (one-way). Hash tidak bisa di-decrypt, sehingga key asli tidak bisa dilihat lagi.

**Solusi:** Generate ulang enrollment key baru yang ter-encrypt sehingga bisa dilihat oleh authorized users.

## Script 1: View Enrollment Keys

### Purpose
Melihat status semua enrollment keys tanpa melakukan perubahan.

### Usage
```bash
cd Levl-BE
php view_enrollment_keys.php
```

### Output
Script akan menampilkan:
- Total courses dengan key_based enrollment
- Status setiap course:
  - Has Hash (✓/✗)
  - Has Encrypted (✓/✗)
  - Enrollment Key (jika bisa di-decrypt)
  - Viewable status
- Summary:
  - Viewable keys (dengan tabel key yang bisa dilihat)
  - Not viewable keys (dengan alasan dan action required)

### Example Output
```
=================================================
Enrollment Keys Viewer
=================================================

Found 14 course(s) with key_based enrollment:

================================================================================
Course ID: 58
Title: Coba Encrpy
Code: NC-1
Slug: coba-encrpy
Status: draft
Instructors: None assigned

Enrollment Key Status:
  Has Hash: ✓ Yes
  Has Encrypted: ✓ Yes
  Enrollment Key: NC1-2026-5YEI3I
  Viewable: ✓ Yes

================================================================================
SUMMARY
================================================================================

Total Courses: 14
Viewable Keys: 1
Not Viewable: 13

VIEWABLE ENROLLMENT KEYS:
--------------------------------------------------------------------------------
ID    | Code       | Title                     | Key            
--------------------------------------------------------------------------------
58    | NC-1       | Coba Encrpy               | NC1-2026-5YEI3I
--------------------------------------------------------------------------------
```

## Script 2: Regenerate Enrollment Keys

### Purpose
Generate ulang enrollment keys untuk courses yang hanya memiliki hash.

### Usage
```bash
cd Levl-BE
php regenerate_enrollment_keys.php
```

### Behavior
Script akan:
1. Scan semua courses dengan key_based enrollment
2. Untuk setiap course:
   - Jika sudah punya encrypted key → Skip (tampilkan key yang ada)
   - Jika hanya punya hash → Tanya konfirmasi untuk regenerate
3. Generate key baru (format: 12 karakter uppercase alphanumeric)
4. Encrypt key baru
5. Tampilkan summary dengan semua key baru

### Interactive Mode
Script akan meminta konfirmasi untuk setiap course:
```
Course ID: 37
  Title: Financial Analysis and Modeling
  Code: MVA449
  Has Hash: Yes
  Has Encrypted: No
  Status: Generating new key...
  ⚠ WARNING: This will generate a NEW key. Old key cannot be recovered.
  Continue? (y/n): 
```

### Example Output
```
=================================================
Summary
=================================================

Regenerated Keys (3):
--------------------------------------------------------------------------------
ID    | Code       | Title                          | New Key        
--------------------------------------------------------------------------------
37    | MVA449     | Financial Analysis and Modeling| ABC123XYZ789   
38    | KML293     | Ethical Hacking and Penetratio | DEF456UVW012   
36    | NVE358     | Financial Analysis and Modeling| GHI789RST345   
--------------------------------------------------------------------------------

⚠ IMPORTANT: Please communicate these new keys to the course instructors!
```

## Important Notes

### 1. Old Keys Cannot Be Recovered
⚠️ **CRITICAL**: Keys yang sudah di-hash tidak bisa di-recover. Regenerate akan membuat key BARU.

### 2. Communication Required
Setelah regenerate, Anda HARUS mengkomunikasikan key baru ke:
- Course instructors
- Students yang sudah enrolled (jika ada)
- Dokumentasi course

### 3. Backup Recommendation
Sebelum menjalankan regenerate script:
```bash
# Backup database
pg_dump levl_db > backup_before_key_regeneration_$(date +%Y%m%d).sql
```

### 4. Production Safety
Untuk production:
1. Jalankan `view_enrollment_keys.php` dulu untuk audit
2. Export hasil ke file untuk dokumentasi
3. Koordinasi dengan team sebelum regenerate
4. Jalankan regenerate di maintenance window

## Workflow Recommendation

### Step 1: Audit Current State
```bash
php view_enrollment_keys.php > enrollment_keys_audit_$(date +%Y%m%d).txt
```

### Step 2: Review Audit Results
- Identifikasi courses yang perlu regenerate
- Koordinasi dengan instructors
- Siapkan komunikasi plan

### Step 3: Backup Database
```bash
pg_dump levl_db > backup_before_regeneration.sql
```

### Step 4: Regenerate Keys
```bash
php regenerate_enrollment_keys.php
```

### Step 5: Document New Keys
- Save output ke file
- Update course documentation
- Kirim email ke instructors

### Step 6: Verify
```bash
php view_enrollment_keys.php
```

## API Access After Regeneration

Setelah keys di-regenerate, authorized users bisa melihat keys via API:

### GET /api/courses/{slug}

**Response (for Superadmin/Admin/Instructor):**
```json
{
    "id": 37,
    "code": "MVA449",
    "title": "Financial Analysis and Modeling",
    "enrollment_type": "key_based",
    "enrollment_key": "ABC123XYZ789",  // ← Visible!
    ...
}
```

**Response (for Students):**
```json
{
    "id": 37,
    "code": "MVA449",
    "title": "Financial Analysis and Modeling",
    "enrollment_type": "key_based",
    // enrollment_key NOT included
    ...
}
```

## Troubleshooting

### Issue: "Failed to decrypt"
**Cause:** APP_KEY berubah atau data corrupt

**Solution:**
1. Restore original APP_KEY
2. Atau regenerate key untuk course tersebut

### Issue: Script hangs
**Cause:** Waiting for user input

**Solution:** 
- Type 'y' atau 'n' dan tekan Enter
- Atau gunakan non-interactive mode (future enhancement)

### Issue: Permission denied
**Cause:** File permissions

**Solution:**
```bash
chmod +x view_enrollment_keys.php
chmod +x regenerate_enrollment_keys.php
```

## Future Enhancements

Planned improvements:
1. **Non-interactive mode** untuk automation
2. **Batch regenerate** dengan course IDs
3. **Email notification** ke instructors
4. **Audit log** untuk key changes
5. **Export to CSV** untuk reporting

## Security Considerations

### 1. Script Access
Scripts ini hanya boleh dijalankan oleh:
- System administrators
- Database administrators
- Authorized DevOps team

### 2. Output Handling
Output script berisi sensitive data (enrollment keys):
- Jangan commit ke git
- Jangan share via insecure channels
- Delete setelah selesai digunakan

### 3. Production Execution
```bash
# Good: Run in secure terminal
ssh production-server
cd /var/www/levl-be
php view_enrollment_keys.php

# Bad: Don't pipe to public logs
php view_enrollment_keys.php | tee public_log.txt  # ❌
```

## Support

Jika ada masalah:
1. Check logs: `storage/logs/laravel.log`
2. Verify APP_KEY: `.env` file
3. Test encryption: `php artisan tinker`
   ```php
   $enc = app(\App\Contracts\EnrollmentKeyEncrypterInterface::class);
   $encrypted = $enc->encrypt('TEST');
   $decrypted = $enc->decrypt($encrypted);
   ```

## Related Documentation

- [ENROLLMENT_KEY_ENCRYPTION.md](./ENROLLMENT_KEY_ENCRYPTION.md) - Technical implementation details
- [API_MANAJEMEN_SKEMA_ADMIN_LENGKAP.md](./API_MANAJEMEN_SKEMA_ADMIN_LENGKAP.md) - API documentation
