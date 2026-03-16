# Enrollment Key Encryption Implementation

## Overview

Sistem enrollment key telah diupgrade dari hashing (one-way) menjadi encryption (two-way) untuk memungkinkan Superadmin, Admin, dan Instruktur melihat nilai asli enrollment key.

## Perubahan Utama

### 1. Dari Hashing ke Encryption

**Sebelumnya:**
- Enrollment key di-hash menggunakan bcrypt
- Tidak bisa di-decrypt kembali
- Hanya bisa diverifikasi (cocok/tidak cocok)

**Sekarang:**
- Enrollment key di-encrypt menggunakan AES-256-CBC
- Bisa di-decrypt untuk authorized users
- Tetap aman karena menggunakan Laravel's encryption

### 2. Struktur Database

**Kolom Baru:**
```php
'enrollment_key_encrypted' => 'text' // Encrypted enrollment key
```

**Kolom Lama (Tetap Ada untuk Backward Compatibility):**
```php
'enrollment_key_hash' => 'string(255)' // Hashed enrollment key
```

### 3. Authorization

Hanya user dengan role berikut yang bisa melihat decrypted enrollment key:
- **Superadmin**: Full access ke semua courses
- **Admin**: Full access ke semua courses  
- **Instructor**: Access ke courses yang mereka kelola

## Implementasi

### Interface & Service

**Interface:**
```php
App\Contracts\EnrollmentKeyEncrypterInterface
```

**Implementation:**
```php
App\Services\EnrollmentKeyEncrypter
```

**Methods:**
- `encrypt(string $plainKey): string` - Encrypt enrollment key
- `decrypt(string $encryptedKey): string` - Decrypt enrollment key
- `verify(string $plainKey, string $encryptedKey): bool` - Verify key match

### Model Course

**Setter (Automatic Encryption):**
```php
public function setEnrollmentKeyAttribute($value): void
{
    if ($value === null) {
        $this->attributes['enrollment_key_hash'] = null;
        $this->attributes['enrollment_key_encrypted'] = null;
    } else {
        // Keep hash for backward compatibility
        $hasher = app(\App\Contracts\EnrollmentKeyHasherInterface::class);
        $this->attributes['enrollment_key_hash'] = $hasher->hash($value);
        
        // Add encryption for decryption capability
        $encrypter = app(\App\Contracts\EnrollmentKeyEncrypterInterface::class);
        $this->attributes['enrollment_key_encrypted'] = $encrypter->encrypt($value);
    }
}
```

**Getter (Decryption):**
```php
public function getDecryptedEnrollmentKey(): ?string
{
    if (empty($this->enrollment_key_encrypted)) {
        return null;
    }

    try {
        $encrypter = app(\App\Contracts\EnrollmentKeyEncrypterInterface::class);
        return $encrypter->decrypt($this->enrollment_key_encrypted);
    } catch (\Exception $e) {
        \Log::error('Failed to decrypt enrollment key', [
            'course_id' => $this->id,
            'error' => $e->getMessage()
        ]);
        return null;
    }
}
```

### API Resource

**CourseResource** akan menambahkan `enrollment_key` field hanya untuk authorized users:

```php
if ($isManager) {
    // ... other manager fields
    
    // Add decrypted enrollment key for authorized users
    if ($this->enrollment_type?->value === 'key_based' && !empty($this->enrollment_key_encrypted)) {
        $data['enrollment_key'] = $this->getDecryptedEnrollmentKey();
    }
}
```

### Enrollment Verification

**EnrollmentLifecycleProcessor** mendukung dual verification:

```php
// Try encrypted key first (new method)
if (!empty($course->enrollment_key_encrypted)) {
    $encrypter = app(\App\Contracts\EnrollmentKeyEncrypterInterface::class);
    $isValid = $encrypter->verify($enrollmentKey, $course->enrollment_key_encrypted);
}

// Fallback to hash verification (old method)
if (!$isValid && !empty($course->enrollment_key_hash)) {
    $isValid = $this->keyHasher->verify($enrollmentKey, $course->enrollment_key_hash);
}
```

## Security Considerations

### 1. Encryption Key

Laravel menggunakan `APP_KEY` dari `.env` untuk encryption. **PENTING:**
- Jangan pernah share `APP_KEY`
- Backup `APP_KEY` dengan aman
- Jika `APP_KEY` berubah, semua encrypted data tidak bisa di-decrypt

### 2. Access Control

Enrollment key hanya ditampilkan untuk:
- Superadmin (semua courses)
- Admin (semua courses)
- Instructor (courses yang mereka kelola)

### 3. Hidden Fields

Kolom berikut di-hide dari serialization:
```php
protected $hidden = [
    'enrollment_key_hash',
    'enrollment_key_encrypted',
    'deleted_at'
];
```

## Migration Path

### Existing Courses

Courses yang sudah ada dengan `enrollment_key_hash`:
1. Tetap berfungsi dengan hash verification
2. Saat key di-update, akan otomatis ter-encrypt
3. Atau regenerate key untuk mendapatkan encrypted version

### New Courses

Courses baru otomatis akan:
1. Hash key (untuk backward compatibility)
2. Encrypt key (untuk decryption capability)

## API Response Example

### For Authorized Users (Superadmin/Admin/Instructor)

```json
{
    "id": 57,
    "code": "TO011",
    "slug": "test-outcome-123",
    "title": "Test Outcome 123",
    "enrollment_type": "key_based",
    "enrollment_key": "ABC123XYZ789",  // ← Decrypted key visible
    "status": "draft",
    ...
}
```

### For Students

```json
{
    "id": 57,
    "code": "TO011",
    "slug": "test-outcome-123",
    "title": "Test Outcome 123",
    "enrollment_type": "key_based",
    // enrollment_key NOT included
    "status": "published",
    ...
}
```

## Testing

### Test Encryption/Decryption

```php
$encrypter = app(\App\Contracts\EnrollmentKeyEncrypterInterface::class);

// Encrypt
$encrypted = $encrypter->encrypt('MY_SECRET_KEY');

// Decrypt
$decrypted = $encrypter->decrypt($encrypted);
// $decrypted === 'MY_SECRET_KEY'

// Verify
$isValid = $encrypter->verify('MY_SECRET_KEY', $encrypted);
// $isValid === true
```

### Test Course Key

```php
$course = Course::find(1);

// Set key (auto-encrypts)
$course->enrollment_key = 'NEW_KEY_123';
$course->save();

// Get decrypted key (for authorized users)
$plainKey = $course->getDecryptedEnrollmentKey();
// $plainKey === 'NEW_KEY_123'
```

## Troubleshooting

### "Unable to decrypt" Error

**Cause:** `APP_KEY` berubah atau data corrupt

**Solution:**
1. Restore original `APP_KEY`
2. Atau regenerate enrollment key untuk course tersebut

### Key Not Visible in API

**Check:**
1. User role (harus Superadmin/Admin/Instructor)
2. Course enrollment_type (harus 'key_based')
3. enrollment_key_encrypted tidak null

## Future Improvements

1. **Key Rotation**: Implement periodic key rotation
2. **Audit Log**: Track who views enrollment keys
3. **Key Expiry**: Add expiration dates to keys
4. **Bulk Migration**: Tool untuk migrate semua existing hashed keys

## References

- Laravel Encryption: https://laravel.com/docs/encryption
- AES-256-CBC: Industry standard encryption
- OpenSSL: Underlying encryption library
