# Fix Translation untuk Validation Error

## Masalah
Ketika terjadi validation error (misalnya password yang bocor), response API menampilkan translation key yang tidak ter-resolve:

```json
{
  "status": "error",
  "message": "messages.validation.failed",
  "errors": {
    "password": [
      "kata sandi yang diberikan telah muncul dalam kebocoran data. Silakan pilih kata sandi yang berbeda."
    ]
  }
}
```

## Root Cause
File `Modules/Common/app/Http/Requests/Concerns/HasApiValidation.php` menggunakan translation key dengan dot notation:
```php
'message' => __('messages.validation.failed')
```

Namun di file `lang/id/messages.php` dan `lang/en/messages.php`, key tersebut didefinisikan dengan underscore:
```php
'validation_failed' => 'Data yang dikirim tidak valid...'
```

Laravel tidak bisa menemukan key `messages.validation.failed` karena strukturnya tidak nested.

## Solusi
Menambahkan struktur nested `validation` di kedua file translation:

### File: `lang/id/messages.php`
```php
// Validation messages (nested structure)
'validation' => [
    'failed' => 'Data yang dikirim tidak valid. Silakan periksa kembali isian Anda.',
],
```

### File: `lang/en/messages.php`
```php
// Validation messages (nested structure)
'validation' => [
    'failed' => 'The provided data is invalid. Please check your input.',
],
```

## Testing

### Test Case 1: Password Compromised (Bahasa Indonesia)
```bash
POST /api/v1/auth/register
Content-Language: id
{
  "email": "test@example.com",
  "password": "password123",  # Password yang bocor
  "name": "Test User"
}

# Expected Response:
{
  "status": "error",
  "message": "Data yang dikirim tidak valid. Silakan periksa kembali isian Anda.",
  "errors": {
    "password": [
      "kata sandi yang diberikan telah muncul dalam kebocoran data. Silakan pilih kata sandi yang berbeda."
    ]
  }
}
```

### Test Case 2: Password Compromised (Bahasa Inggris)
```bash
POST /api/v1/auth/register
Content-Language: en
{
  "email": "test@example.com",
  "password": "password123",
  "name": "Test User"
}

# Expected Response:
{
  "status": "error",
  "message": "The provided data is invalid. Please check your input.",
  "errors": {
    "password": [
      "The given password has appeared in a data leak. Please choose a different password."
    ]
  }
}
```

### Test Case 3: Validation Error Lainnya
```bash
POST /api/v1/auth/register
{
  "email": "invalid-email",
  "password": "short"
}

# Expected Response:
{
  "status": "error",
  "message": "Data yang dikirim tidak valid. Silakan periksa kembali isian Anda.",
  "errors": {
    "email": ["Format email tidak valid."],
    "password": ["kata sandi minimal harus 8 karakter."]
  }
}
```

## Password Validation Rules
Laravel menggunakan rule `Password::defaults()` yang mencakup:
- Minimum 8 karakter
- Harus mengandung huruf
- Harus mengandung angka (optional, tergantung konfigurasi)
- Harus mengandung simbol (optional, tergantung konfigurasi)
- **Uncompromised check**: Memeriksa apakah password pernah bocor di database haveibeenpwned.com

Translation untuk semua password rules sudah tersedia di `lang/id/validation.php`:
```php
'password' => [
    'letters' => ':attribute harus mengandung setidaknya satu huruf.',
    'mixed' => ':attribute harus mengandung huruf besar dan kecil.',
    'numbers' => ':attribute harus mengandung setidaknya satu angka.',
    'symbols' => ':attribute harus mengandung setidaknya satu simbol.',
    'uncompromised' => ':attribute yang diberikan telah muncul dalam kebocoran data. Silakan pilih :attribute yang berbeda.',
],
```

## Status
✅ **FIXED** - Translation key `messages.validation.failed` sekarang ter-resolve dengan benar.

## Files Modified
- `Levl-BE/lang/id/messages.php` - Menambahkan nested structure `validation.failed`
- `Levl-BE/lang/en/messages.php` - Menambahkan nested structure `validation.failed`

## Notes
- Key lama `validation_failed` (dengan underscore) tetap dipertahankan untuk backward compatibility
- Nested structure `validation.failed` (dengan dot) sekarang juga tersedia
- Semua validation error sekarang akan menampilkan pesan yang proper, bukan translation key
