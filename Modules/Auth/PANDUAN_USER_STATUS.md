# Panduan User Status - Quick Reference

**Untuk Developer**: Panduan cepat menggunakan user status di aplikasi

---

## 📌 4 Status User

```php
UserStatus::Pending   // 'pending'   - Baru register, belum verifikasi email
UserStatus::Active    // 'active'    - User aktif normal
UserStatus::Inactive  // 'inactive'  - Dinonaktifkan admin
UserStatus::Banned    // 'banned'    - Dibanned/diblokir
```

---

## 🔄 Lifecycle Status

### 1. User Baru (Student)
```
Register → PENDING → Verifikasi Email → ACTIVE
```

### 2. User Privileged (Admin/Instructor)
```
Register → PENDING → Login Pertama (Auto-Verify) → ACTIVE
```

### 3. Admin Actions
```
ACTIVE → Deactivate → INACTIVE → Activate → ACTIVE
ACTIVE → Ban → BANNED → Unban → ACTIVE
```

---

## 💻 Cara Pakai di Code

### 1. Cek Status User

```php
use Modules\Auth\Enums\UserStatus;

$user = auth()->user();

// Cek apakah user aktif
if ($user->status === UserStatus::Active) {
    // User aktif
}

// Cek apakah user banned
if ($user->status === UserStatus::Banned) {
    // User dibanned
}

// Match expression
$message = match($user->status) {
    UserStatus::Pending => 'Silakan verifikasi email',
    UserStatus::Active => 'Akun aktif',
    UserStatus::Inactive => 'Akun tidak aktif',
    UserStatus::Banned => 'Akun dibanned',
};
```

### 2. Query User Berdasarkan Status

```php
use Modules\Auth\Models\User;
use Modules\Auth\Enums\UserStatus;

// Ambil semua user aktif
$activeUsers = User::where('status', UserStatus::Active)->get();

// Atau pakai scope
$activeUsers = User::active()->get();
$inactiveUsers = User::active(false)->get();
$bannedUsers = User::suspended()->get();

// Filter dengan role dan status
$activeStudents = User::role('Student')
    ->where('status', UserStatus::Active)
    ->get();
```

### 3. Update Status User

```php
use Modules\Auth\Enums\UserStatus;

// JANGAN langsung update seperti ini:
// $user->status = 'active'; // ❌ SALAH

// Gunakan enum:
$user->status = UserStatus::Active; // ✅ BENAR
$user->save();

// Atau gunakan service (recommended):
app(\Modules\Auth\Contracts\Services\UserManagementServiceInterface::class)
    ->updateUser(auth()->user(), $userId, [
        'status' => UserStatus::Inactive->value
    ]);
```

### 4. Validasi Status di Request

```php
use Illuminate\Validation\Rule;
use Modules\Auth\Enums\UserStatus;

// Di FormRequest
public function rules(): array
{
    return [
        'status' => [
            'required',
            Rule::enum(UserStatus::class)->only([
                UserStatus::Active,
                UserStatus::Inactive,
                UserStatus::Banned
            ])
        ],
    ];
}
```

### 5. Bulk Update Status

```php
use Modules\Auth\Services\UserBulkService;

$bulkService = app(UserBulkService::class);

// Activate multiple users
$bulkService->bulkActivate(
    userIds: [1, 2, 3],
    changedBy: auth()->id()
);

// Deactivate multiple users
$bulkService->bulkDeactivate(
    userIds: [4, 5, 6],
    changedBy: auth()->id()
);
```

---

## 🎯 Apa yang Bisa Dilakukan per Status?

### PENDING
- ✅ Login (dapat token)
- ✅ Terima email verifikasi
- ❌ Refresh token
- ❌ Akses fitur protected

**Response Login**:
```json
{
  "user": {...},
  "access_token": "...",
  "status": "pending",
  "message": "Email belum diverifikasi",
  "verification_uuid": "..."
}
```

### ACTIVE
- ✅ Login normal
- ✅ Refresh token
- ✅ Akses semua fitur
- ✅ Semua aktivitas

**Response Login**:
```json
{
  "user": {...},
  "access_token": "...",
  "refresh_token": "..."
}
```

### INACTIVE
- ✅ Login (dapat token)
- ❌ Refresh token
- ❌ Akses fitur protected

**Response Login**:
```json
{
  "user": {...},
  "access_token": "...",
  "status": "inactive",
  "message": "Akun tidak aktif, hubungi admin"
}
```

### BANNED
- ✅ Login (dapat token)
- ❌ Refresh token
- ❌ Akses fitur protected

**Response Login**:
```json
{
  "user": {...},
  "access_token": "...",
  "status": "banned",
  "message": "Akun dibanned, hubungi admin"
}
```

---

## 🔒 Validasi & Keamanan

### Aturan Status Change

```php
// ❌ TIDAK BOLEH: Set status ke Pending
$user->status = UserStatus::Pending; // Will throw exception

// ❌ TIDAK BOLEH: Ubah status dari Pending manual
// Status Pending hanya bisa diubah via email verification

// ✅ BOLEH: Ubah ke Active, Inactive, Banned
$user->status = UserStatus::Active;
$user->status = UserStatus::Inactive;
$user->status = UserStatus::Banned;
```

### Auto-Verification

```php
// Admin, Superadmin, Instructor otomatis diverifikasi saat login pertama
if ($user->hasRole(['Admin', 'Superadmin', 'Instructor'])) {
    // Status otomatis jadi Active saat login
}
```

---

## 🛠️ Helper Methods

### Di Model User

```php
// Cek apakah user aktif
$user->status === UserStatus::Active

// Get status label (translated)
$user->status->label() // "Aktif", "Pending", dll

// Get status value (string)
$user->status->value // "active", "pending", dll
```

### Di Enum UserStatus

```php
use Modules\Auth\Enums\UserStatus;

// Get all values
UserStatus::values() // ['pending', 'active', 'inactive', 'banned']

// Get validation rule
UserStatus::rule() // 'in:pending,active,inactive,banned'

// Get label
UserStatus::Active->label() // "Aktif" (translated)
```

---

## 📝 Contoh Use Cases

### 1. Filter User Aktif untuk Notifikasi

```php
use Modules\Auth\Models\User;
use Modules\Auth\Enums\UserStatus;

$recipients = User::where('status', UserStatus::Active)
    ->whereHas('enrollments', function($q) use ($courseId) {
        $q->where('course_id', $courseId);
    })
    ->get();

foreach ($recipients as $user) {
    // Send notification
}
```

### 2. Cek Status di Controller

```php
public function someAction(Request $request)
{
    $user = $request->user();
    
    if ($user->status !== UserStatus::Active) {
        return response()->json([
            'message' => 'Akun tidak aktif'
        ], 403);
    }
    
    // Process action
}
```

### 3. Deactivate User Saat Delete Account

```php
use Modules\Auth\Enums\UserStatus;

public function deleteAccount(User $user)
{
    $user->status = UserStatus::Inactive;
    $user->save();
    $user->delete(); // Soft delete
    
    // Revoke all tokens
    // Send notification
}
```

### 4. Restore User Account

```php
use Modules\Auth\Enums\UserStatus;

public function restoreAccount(int $userId)
{
    $user = User::withTrashed()->findOrFail($userId);
    $user->restore();
    $user->status = UserStatus::Active;
    $user->save();
    
    // Send welcome back email
}
```

---

## 🚨 Common Mistakes

### ❌ JANGAN:

```php
// 1. Jangan pakai string langsung
$user->status = 'active'; // ❌

// 2. Jangan set ke Pending manual
$user->status = UserStatus::Pending; // ❌ Will throw exception

// 3. Jangan lupa save
$user->status = UserStatus::Active; // ❌ Tidak disave

// 4. Jangan query tanpa filter status
User::all(); // ❌ Termasuk inactive/banned users
```

### ✅ LAKUKAN:

```php
// 1. Pakai enum
$user->status = UserStatus::Active; // ✅

// 2. Pending hanya via verification
// Biarkan sistem yang handle

// 3. Selalu save
$user->status = UserStatus::Active;
$user->save(); // ✅

// 4. Filter status saat query
User::where('status', UserStatus::Active)->get(); // ✅
// Atau
User::active()->get(); // ✅
```

---

## 📚 File Reference

- **Enum**: `Modules/Auth/app/Enums/UserStatus.php`
- **Model**: `Modules/Auth/app/Models/User.php`
- **Login Logic**: `Modules/Auth/app/Services/Support/AuthSessionProcessor.php`
- **Status Management**: `Modules/Auth/app/Services/Support/UserLifecycleProcessor.php`
- **Bulk Operations**: `Modules/Auth/app/Services/UserBulkService.php`

---

## 🔗 Related Documentation

- [USER_STATUS_AUDIT_REPORT.md](./USER_STATUS_AUDIT_REPORT.md) - Laporan audit lengkap
- [API Documentation](../../API_COMPLETE_DOCUMENTATION.md) - API endpoints

---

**Last Updated**: 14 Maret 2026
