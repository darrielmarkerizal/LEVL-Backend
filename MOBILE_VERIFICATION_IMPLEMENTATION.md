# Mobile Deep Link Verification Implementation

## Overview
Implementasi verifikasi email untuk mobile app menggunakan deep link dengan format `levl://verify`.

## Changes Made

### 1. Deep Link Format
Email verifikasi sekarang mengirim deep link dengan format:
```
levl://verify?userId=123&email=user@example.com&uuid=xxx-xxx-xxx&token=xxxxxxxxxxxxxxxx
```

**Parameters:**
- `userId`: ID user yang melakukan registrasi
- `email`: Email user
- `uuid`: UUID token verifikasi (format UUID v4)
- `token`: Token verifikasi 16 karakter

### 2. Modified Files

#### VerificationTokenManager.php
- Mengubah format URL verifikasi dari web URL ke mobile deep link
- Deep link: `levl://verify?userId=xxx&email=xxx&uuid=xxx&token=xxx`

#### VerificationValidator.php
- Method `verifyByToken()` sekarang mengembalikan user object lengkap dengan relationships
- Response includes: `['status' => 'ok', 'user_id' => $user->id, 'user' => $user]`

#### AuthApiController.php
- Endpoint `/auth/email/verify` sekarang mengembalikan response seperti login
- Response includes: access_token, refresh_token, expires_in, dan user profile lengkap

## API Endpoint

### POST /api/v1/auth/email/verify

**Request Body:**
```json
{
  "token": "xxxxxxxxxxxxxxxx",
  "uuid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Selamat datang! Email Anda berhasil diverifikasi. Anda sudah bisa langsung menikmati semua fitur kami.",
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "username": "johndoe",
      "email_verified_at": "2026-03-24T10:00:00.000000Z",
      "status": "active",
      "avatar_url": "https://...",
      "roles": ["Student"]
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "xxxxxxxxxxxxxxxxxxxxxxxx",
    "expires_in": 3600
  }
}
```

**Error Responses:**

1. Token tidak valid (422):
```json
{
  "success": false,
  "message": "Token verifikasi tidak valid",
  "errors": []
}
```

2. Token expired (422):
```json
{
  "success": false,
  "message": "Token verifikasi sudah kadaluarsa",
  "errors": []
}
```

3. Token tidak ditemukan (422):
```json
{
  "success": false,
  "message": "Token verifikasi tidak ditemukan",
  "errors": []
}
```

## Mobile Implementation Flow

### 1. User Registration
```
Mobile App → POST /api/v1/auth/register
← Response: { user, access_token, refresh_token, verification_uuid }
```

### 2. Email Verification Link
User menerima email dengan deep link:
```
levl://verify?userId=123&email=user@example.com&uuid=xxx&token=xxx
```

### 3. Deep Link Handling
Mobile app menangkap deep link dan extract parameters:
```dart
// Example Flutter
Uri uri = Uri.parse(deepLink);
String userId = uri.queryParameters['userId'];
String email = uri.queryParameters['email'];
String uuid = uri.queryParameters['uuid'];
String token = uri.queryParameters['token'];
```

### 4. Manual API Verification
Mobile app hit API verify dengan uuid dan token:
```
Mobile App → POST /api/v1/auth/email/verify
Body: { "token": "xxx", "uuid": "xxx" }
← Response: { user, access_token, refresh_token, expires_in }
```

### 5. Store Tokens
Mobile app menyimpan access_token dan refresh_token untuk authenticated requests.

## Security Notes

1. **Token Expiration**: Token verifikasi memiliki TTL (default 60 menit)
2. **One-time Use**: Token hanya bisa digunakan sekali
3. **Hash Verification**: Token di-hash menggunakan SHA-256 sebelum disimpan
4. **Rate Limiting**: Endpoint verify menggunakan throttle `auth` (10 requests/minute)

## Testing

### Manual Testing
```bash
# 1. Register user
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'

# 2. Check email untuk mendapatkan uuid dan token dari deep link

# 3. Verify email
curl -X POST http://localhost:8000/api/v1/auth/email/verify \
  -H "Content-Type: application/json" \
  -d '{
    "token": "xxxxxxxxxxxxxxxx",
    "uuid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
  }'
```

## Migration Notes

### Backward Compatibility
- Web frontend masih bisa menggunakan endpoint yang sama
- Response format berubah dari empty array menjadi login response
- Frontend perlu update untuk handle new response format

### Email Template
Email template di `Levl-BE/Modules/Mail/resources/views/emails/auth/verify.blade.php` sudah menggunakan deep link format baru.

## Configuration

Tidak ada konfigurasi tambahan yang diperlukan. Deep link format sudah hardcoded sebagai `levl://verify`.

Jika perlu mengubah app scheme, edit di:
- `Levl-BE/Modules/Auth/app/Services/Support/VerificationTokenManager.php` line ~35

## Related Files

- `Levl-BE/Modules/Auth/app/Services/Support/VerificationTokenManager.php`
- `Levl-BE/Modules/Auth/app/Services/Support/VerificationValidator.php`
- `Levl-BE/Modules/Auth/app/Http/Controllers/AuthApiController.php`
- `Levl-BE/Modules/Auth/routes/api.php`
- `Levl-BE/Modules/Mail/app/Mail/Auth/VerifyEmailLinkMail.php`
