# Panduan Verifikasi Email Mobile

## Ringkasan Perubahan

Sistem verifikasi email telah diubah untuk mendukung mobile app dengan deep link format `levl://verify`.

## Format Deep Link

```
levl://verify?userId=123&email=user@example.com&uuid=xxx-xxx-xxx&token=xxxxxxxxxxxxxxxx
```

### Parameter:
- `userId`: ID user
- `email`: Email user
- `uuid`: UUID token verifikasi
- `token`: Token 16 karakter

## Flow Verifikasi

### 1. Registrasi
```
POST /api/v1/auth/register
```

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": { ... },
    "access_token": "...",
    "refresh_token": "...",
    "expires_in": 3600,
    "verification_uuid": "xxx-xxx-xxx"
  }
}
```

### 2. Email Dikirim
User menerima email dengan deep link:
```
levl://verify?userId=123&email=john@example.com&uuid=xxx&token=xxx
```

### 3. User Tap Link
Mobile app menangkap deep link dan extract parameters.

### 4. Hit API Verify
```
POST /api/v1/auth/email/verify
```

**Request:**
```json
{
  "token": "xxxxxxxxxxxxxxxx",
  "uuid": "xxx-xxx-xxx"
}
```

**Response (Success):**
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

### 5. Save Tokens
Mobile app menyimpan `access_token` dan `refresh_token` untuk request selanjutnya.

## Error Handling

### Token Invalid
```json
{
  "success": false,
  "message": "Token verifikasi tidak valid",
  "errors": []
}
```
**Action**: Tampilkan error dan tombol untuk request link baru.

### Token Expired
```json
{
  "success": false,
  "message": "Token verifikasi sudah kadaluarsa",
  "errors": []
}
```
**Action**: Tampilkan error dan tombol untuk request link baru.

### Token Not Found
```json
{
  "success": false,
  "message": "Token verifikasi tidak ditemukan",
  "errors": []
}
```
**Action**: Tampilkan error dan tombol untuk request link baru.

## Request Link Baru

Jika token expired atau invalid, user bisa request link baru:

```
POST /api/v1/auth/email/verify/send
Authorization: Bearer {access_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Link verifikasi telah dikirim",
  "data": {
    "uuid": "xxx-xxx-xxx"
  }
}
```

## Testing

### 1. Register User
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'
```

### 2. Check Email
Buka MailHog di `http://localhost:8025` dan lihat email verifikasi.

### 3. Extract Token & UUID
Dari deep link di email, extract `token` dan `uuid`.

### 4. Verify
```bash
curl -X POST http://localhost:8000/api/v1/auth/email/verify \
  -H "Content-Type: application/json" \
  -d '{
    "token": "xxxxxxxxxxxxxxxx",
    "uuid": "xxx-xxx-xxx"
  }'
```

## Keamanan

1. **Token Expiry**: Default 60 menit (configurable via `auth_email_verification_ttl_minutes`)
2. **One-time Use**: Token hanya bisa digunakan sekali
3. **Rate Limiting**: 10 requests per menit
4. **Hash**: Token di-hash dengan SHA-256

## Konfigurasi

### TTL Token
Edit di database `system_settings`:
```sql
UPDATE system_settings 
SET value = '120' 
WHERE key = 'auth_email_verification_ttl_minutes';
```

### App Scheme
Jika perlu ubah dari `levl://` ke scheme lain, edit:
```php
// Levl-BE/Modules/Auth/app/Services/Support/VerificationTokenManager.php
$verifyUrl = 'your-app://verify?'.http_build_query([...]);
```

## File yang Diubah

1. `Levl-BE/Modules/Auth/app/Services/Support/VerificationTokenManager.php`
   - Ubah format URL ke deep link

2. `Levl-BE/Modules/Auth/app/Services/Support/VerificationValidator.php`
   - Return user object lengkap

3. `Levl-BE/Modules/Auth/app/Http/Controllers/AuthApiController.php`
   - Return response seperti login (dengan tokens)

## Dokumentasi Lengkap

- **Backend**: `MOBILE_VERIFICATION_IMPLEMENTATION.md`
- **Flutter Example**: `MOBILE_VERIFICATION_FLUTTER_EXAMPLE.md`
- **API Docs**: `Modules/Auth/API_AUTENTIKASI_LENGKAP.md`
