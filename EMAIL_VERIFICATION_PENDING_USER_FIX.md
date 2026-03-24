# Email Verification Fix untuk User Pending

## Masalah
Ketika user baru register dengan status `pending`, endpoint `/api/v1/auth/email/verify/send` mengembalikan error 403:
```json
{
  "status": "error",
  "message": "Alamat email Anda belum diverifikasi. Silakan cek kotak masuk Anda.",
  "user_status": "pending"
}
```

Padahal seharusnya endpoint ini HARUS bisa diakses oleh user pending untuk mengirim email verifikasi.

## Root Cause
Middleware `EnsureUserActive` diterapkan secara global pada semua authenticated API requests (di `bootstrap/app.php` line 51):
```php
$middleware->appendToGroup('api', \Modules\Auth\Http\Middleware\EnsureUserActive::class);
```

Middleware ini memblokir semua user yang bukan `Active`, termasuk user dengan status `Pending`.

## Solusi
Modifikasi middleware `EnsureUserActive` untuk mengecualikan beberapa endpoint yang HARUS bisa diakses oleh user pending:

### File: `Levl-BE/Modules/Auth/app/Http/Middleware/EnsureUserActive.php`

**Perubahan:**
- Tambahkan whitelist endpoint untuk user pending
- User pending sekarang bisa mengakses:
  - `/api/v1/auth/email/verify/send` - Kirim email verifikasi
  - `/api/v1/auth/email/verify` - Verifikasi email dengan token
  - `/api/v1/auth/logout` - Logout
  - `/api/v1/profile` (GET) - Cek status profil

```php
// Allow pending users to access email verification endpoints
if ($user->status === UserStatus::Pending) {
    $allowedPendingRoutes = [
        'api/v1/auth/email/verify/send',
        'api/v1/auth/email/verify',
        'api/v1/auth/logout',
        'api/v1/profile',
    ];

    foreach ($allowedPendingRoutes as $route) {
        if ($request->is($route)) {
            return $next($request);
        }
    }
}
```

## Testing

### Test Case 1: User Pending Kirim Email Verifikasi
```bash
# Login sebagai user pending
POST /api/v1/auth/login
{
  "login": "pending@example.com",
  "password": "password"
}

# Kirim email verifikasi (seharusnya berhasil sekarang)
POST /api/v1/auth/email/verify/send
Authorization: Bearer {token}

# Expected Response:
{
  "status": "success",
  "message": "Link verifikasi telah dikirim ke email Anda.",
  "data": {
    "uuid": "..."
  }
}
```

### Test Case 2: User Pending Verifikasi Email
```bash
POST /api/v1/auth/email/verify
{
  "token": "verification_token",
  "uuid": "user_uuid"
}

# Expected: User status berubah menjadi Active dan mendapat token login
```

### Test Case 3: User Pending Logout
```bash
POST /api/v1/auth/logout
Authorization: Bearer {token}

# Expected: Berhasil logout
```

### Test Case 4: User Pending Akses Endpoint Lain (Tetap Diblokir)
```bash
GET /api/v1/courses
Authorization: Bearer {token}

# Expected: 403 Forbidden
{
  "status": "error",
  "message": "Alamat email Anda belum diverifikasi...",
  "user_status": "pending"
}
```

## Flow User Registration & Verification

1. **Register** → User dibuat dengan status `pending`
2. **Login** → User pending bisa login dan dapat token
3. **Send Verification** → User pending bisa request email verifikasi (✅ FIXED)
4. **Verify Email** → User klik link di email, status berubah jadi `active`
5. **Access System** → User active bisa akses semua endpoint

## Status
✅ **FIXED** - User pending sekarang bisa mengirim email verifikasi tanpa error 403.

## Files Modified
- `Levl-BE/Modules/Auth/app/Http/Middleware/EnsureUserActive.php`
