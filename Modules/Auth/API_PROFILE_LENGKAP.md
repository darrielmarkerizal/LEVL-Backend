# DOKUMENTASI API MANAJEMEN PROFIL LENGKAP - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Auth - Profile  
**Platform**: Shared (Semua Platform)

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [Endpoints Profil](#endpoints-profil)
   - [1. Lihat Profil](#1-get-shared-profil---data-profil-saya)
   - [2. Update Profil](#2-put-shared-profil---update-profil)
   - [3. Upload Avatar](#3-post-shared-profil---upload-avatar)
   - [4. Hapus Avatar](#4-delete-shared-profil---hapus-avatar)
   - [5. Ganti Password](#5-put-shared-profil---ganti-password)
   - [6. Request Ganti Email](#6-post-shared-profil---request-ganti-email)
   - [7. Verifikasi Ganti Email](#7-post-shared-profil---verifikasi-ganti-email)
4. [Endpoints Privacy](#endpoints-privacy)
   - [8. Lihat Pengaturan Privacy](#8-get-shared-profil---lihat-pengaturan-privacy)
   - [9. Update Pengaturan Privacy](#9-put-shared-profil---update-pengaturan-privacy)
5. [Endpoints Account](#endpoints-account)
   - [10. Request Hapus Akun](#10-post-shared-profil---request-hapus-akun)
   - [11. Konfirmasi Hapus Akun](#11-post-shared-profil---konfirmasi-hapus-akun)
   - [12. Restore Akun](#12-post-shared-profil---restore-akun)
6. [Response Format](#response-format)
7. [Error Codes](#error-codes)
8. [Contoh Use Case](#contoh-use-case)

---

## 🎯 RINGKASAN

API Manajemen Profil Levl menyediakan endpoint untuk mengelola profil pengguna termasuk update data profil, upload avatar, ganti password, pengaturan privacy, dan manajemen akun. Semua endpoint memerlukan autentikasi.

### Fitur Utama
- ✅ View & update profil pengguna
- ✅ Upload & delete avatar
- ✅ Ganti password dengan validasi
- ✅ Ganti email dengan verifikasi
- ✅ Pengaturan privacy profil
- ✅ Request & konfirmasi hapus akun
- ✅ Restore akun yang dihapus

---

## 🌐 BASE URL & HEADERS

### Base URL
```
Development:  http://localhost:8000/api/v1
Staging:      https://staging-api.levl.id/api/v1
Production:   https://api.levl.id/api/v1
```

### Headers Standar (Semua Endpoint)
```http
Content-Type: application/json
Accept: application/json
Accept-Language: id
Authorization: Bearer {{auth_token}}
```

### Headers untuk Upload Avatar
```http
Content-Type: multipart/form-data
Accept: application/json
Accept-Language: id
Authorization: Bearer {{auth_token}}
```

---

## 👤 ENDPOINTS PROFIL

### 1. GET [Shared] Profil - Data Profil Saya

Mendapatkan data profil lengkap user yang sedang login.

#### Endpoint
```
GET /profile
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters
```
No query parameters
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "john_doe",
    "email": "john@example.com",
    "phone": "+6281234567890",
    "bio": "Software Developer passionate about learning",
    "location": "Jakarta, Indonesia",
    "email_verified_at": "2026-03-15T10:00:00.000000Z",
    "status": "active",
    "is_password_set": true,
    "avatar_url": "https://api.levl.id/storage/avatars/john.jpg",
    "created_at": "2026-03-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z",
    "roles": [
      {
        "id": 1,
        "name": "Student",
        "display_name": "Partisipan"
      }
    ],
    "permissions": [],
    "statistics": {
      "total_courses": 5,
      "completed_courses": 2,
      "total_xp": 1250,
      "current_level": 5
    }
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Profile data received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('id');
    pm.expect(jsonData.data).to.have.property('email');
    pm.expect(jsonData.data).to.have.property('roles');
});

pm.test("Response time < 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});
```

---

### 2. PUT [Shared] Profil - Update Profil

Update data profil pengguna.

#### Endpoint
```
PUT /profile
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (JSON)
```json
{
  "name": "string",
  "email": "string",
  "phone": "string",
  "bio": "string",
  "location": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `name` | string | ❌ No | max:100 | Nama lengkap pengguna |
| `email` | string | ❌ No | email, max:191, unique | Email valid dan unik |
| `phone` | string | ❌ No | max:20, regex:/^[0-9+\-\s()]+$/ | Nomor telepon |
| `bio` | string | ❌ No | max:1000 | Bio/deskripsi singkat |
| `location` | string | ❌ No | max:255 | Lokasi/alamat |

#### Valid Values

**name**:
- Maksimal: 100 karakter
- Contoh: `"John Doe"`, `"Ahmad Rizki Pratama"`

**email**:
- Format email valid
- Maksimal: 191 karakter
- Harus unik (belum digunakan user lain)
- Contoh: `"john.doe@example.com"`

**phone**:
- Maksimal: 20 karakter
- Format: Hanya angka, +, -, spasi, dan tanda kurung
- Contoh: `"+6281234567890"`, `"(021) 1234-5678"`

**bio**:
- Maksimal: 1000 karakter
- Contoh: `"Software Developer passionate about learning new technologies"`

**location**:
- Maksimal: 255 karakter
- Contoh: `"Jakarta, Indonesia"`, `"Bandung, Jawa Barat"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "username": "john_doe",
    "email": "john@example.com",
    "phone": "+6281234567890",
    "bio": "Updated bio text",
    "location": "Jakarta, Indonesia",
    "email_verified_at": "2026-03-15T10:00:00.000000Z",
    "status": "active",
    "avatar_url": "https://api.levl.id/storage/avatars/john.jpg",
    "updated_at": "2026-03-15T11:00:00.000000Z"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "phone": [
      "The phone format is invalid."
    ],
    "bio": [
      "The bio must not be greater than 1000 characters."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "name": "John Doe Updated",
  "phone": "+6281234567890",
  "bio": "Software Developer passionate about learning",
  "location": "Jakarta, Indonesia"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Profile updated", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data).to.have.property('name');
});
```

---

### 3. POST [Shared] Profil - Upload Avatar

Upload foto avatar pengguna.

#### Endpoint
```
POST /profile/avatar
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (multipart/form-data)
```
avatar: file
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `avatar` | file | ✅ Yes | image, mimes:jpeg,png,jpg,gif, max:2048 | File gambar avatar |

#### Valid Values

**avatar**:
- Type: Image file
- Format: JPEG, PNG, JPG, GIF
- Maksimal size: 2MB (2048 KB)
- Recommended: Square image (1:1 ratio)
- Contoh: `avatar.jpg`, `profile.png`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Avatar uploaded successfully",
  "data": {
    "avatar_url": "https://api.levl.id/storage/avatars/1/avatar-1234567890.jpg"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "avatar": [
      "The avatar must be an image.",
      "The avatar must not be greater than 2048 kilobytes."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}
Content-Type: multipart/form-data

// Body (form-data)
// Key: avatar
// Type: File
// Value: [Select file]

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Avatar URL received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('avatar_url');
});

// Save avatar URL
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("avatar_url", jsonData.data.avatar_url);
}
```

---

### 4. DELETE [Shared] Profil - Hapus Avatar

Hapus foto avatar pengguna.

#### Endpoint
```
DELETE /profile/avatar
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body
```
No body required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Avatar deleted successfully",
  "data": null
}
```

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "Avatar not found",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// No body required

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Avatar deleted", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

// Clear avatar URL
if (pm.response.code === 200) {
    pm.environment.unset("avatar_url");
}
```

---

### 5. PUT [Shared] Profil - Ganti Password

Ganti password pengguna dengan validasi password lama.

#### Endpoint
```
PUT /profile/password
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (JSON)
```json
{
  "current_password": "string",
  "new_password": "string",
  "new_password_confirmation": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `current_password` | string | ✅ Yes | string | Password saat ini |
| `new_password` | string | ✅ Yes | min:8, confirmed | Password baru |
| `new_password_confirmation` | string | ✅ Yes | same:new_password | Konfirmasi password baru |

#### Valid Values

**current_password**:
- Password saat ini yang valid
- Contoh: `"oldpassword123"`

**new_password**:
- Minimal: 8 karakter
- Harus berbeda dengan password lama
- Harus sama dengan new_password_confirmation
- Contoh: `"newpassword123"`

**new_password_confirmation**:
- Harus sama dengan new_password
- Contoh: `"newpassword123"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Password changed successfully",
  "data": null
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "current_password": [
      "The current password is incorrect."
    ],
    "new_password": [
      "The new password must be at least 8 characters.",
      "The new password confirmation does not match."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Password changed", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});
```

---

### 6. POST [Shared] Profil - Request Ganti Email

Request untuk mengganti email dengan verifikasi.

#### Endpoint
```
POST /profile/email/change
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (JSON)
```json
{
  "new_email": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `new_email` | string | ✅ Yes | email, max:191, unique | Email baru yang valid dan unik |

#### Valid Values

**new_email**:
- Format email valid
- Maksimal: 191 karakter
- Harus unik (belum digunakan)
- Berbeda dengan email saat ini
- Contoh: `"newemail@example.com"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Email change request sent. Please check your new email for verification code.",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "new_email": [
      "The new email has already been taken.",
      "The new email must be a valid email address."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "new_email": "newemail@example.com"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("UUID received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('uuid');
});

// Save UUID for verification
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("email_change_uuid", jsonData.data.uuid);
}
```

---

### 7. POST [Shared] Profil - Verifikasi Ganti Email

Verifikasi perubahan email dengan token dari email.

#### Endpoint
```
POST /profile/email/change/verify
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (JSON)
```json
{
  "uuid": "string",
  "token": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `uuid` | string | ✅ Yes | uuid | UUID dari response request email change |
| `token` | string | ✅ Yes | size:16 | Token 16 karakter dari email |

#### Valid Values

**uuid**:
- Format UUID valid
- Contoh: `"550e8400-e29b-41d4-a716-446655440000"`

**token**:
- Exactly 16 karakter
- Token dari email verifikasi
- Contoh: `"a1b2c3d4e5f6g7h8"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Email changed successfully",
  "data": []
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Invalid or expired verification token",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "uuid": "{{email_change_uuid}}",
  "token": "a1b2c3d4e5f6g7h8"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Email changed", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

// Clear UUID
if (pm.response.code === 200) {
    pm.environment.unset("email_change_uuid");
}
```

---

## 🔒 ENDPOINTS PRIVACY

### 8. GET [Shared] Profil - Lihat Pengaturan Privacy

Mendapatkan pengaturan privacy profil pengguna.

#### Endpoint
```
GET /profile/privacy
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters
```
No query parameters
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Privacy settings retrieved successfully",
  "data": {
    "profile_visibility": "public",
    "show_email": false,
    "show_phone": false,
    "show_activity_history": true,
    "show_achievements": true,
    "show_statistics": true
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Privacy settings received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('profile_visibility');
    pm.expect(jsonData.data).to.have.property('show_email');
});
```

---

### 9. PUT [Shared] Profil - Update Pengaturan Privacy

Update pengaturan privacy profil pengguna.

#### Endpoint
```
PUT /profile/privacy
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (JSON)
```json
{
  "profile_visibility": "string",
  "show_email": boolean,
  "show_phone": boolean,
  "show_activity_history": boolean,
  "show_achievements": boolean,
  "show_statistics": boolean
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `profile_visibility` | string | ❌ No | in:public,private,friends | Visibilitas profil |
| `show_email` | boolean | ❌ No | boolean | Tampilkan email di profil |
| `show_phone` | boolean | ❌ No | boolean | Tampilkan telepon di profil |
| `show_activity_history` | boolean | ❌ No | boolean | Tampilkan riwayat aktivitas |
| `show_achievements` | boolean | ❌ No | boolean | Tampilkan pencapaian |
| `show_statistics` | boolean | ❌ No | boolean | Tampilkan statistik |

#### Valid Values

**profile_visibility**:
- `"public"` - Profil dapat dilihat semua orang
- `"private"` - Profil hanya dapat dilihat sendiri
- `"friends"` - Profil dapat dilihat teman
- Default: `"public"`

**show_email**:
- `true` - Email ditampilkan di profil publik
- `false` - Email disembunyikan
- Default: `false`

**show_phone**:
- `true` - Telepon ditampilkan di profil publik
- `false` - Telepon disembunyikan
- Default: `false`

**show_activity_history**:
- `true` - Riwayat aktivitas ditampilkan
- `false` - Riwayat aktivitas disembunyikan
- Default: `true`

**show_achievements**:
- `true` - Pencapaian/badges ditampilkan
- `false` - Pencapaian disembunyikan
- Default: `true`

**show_statistics**:
- `true` - Statistik (XP, level, dll) ditampilkan
- `false` - Statistik disembunyikan
- Default: `true`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Privacy settings updated successfully",
  "data": {
    "profile_visibility": "private",
    "show_email": false,
    "show_phone": false,
    "show_activity_history": false,
    "show_achievements": true,
    "show_statistics": true
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "profile_visibility": [
      "The selected profile visibility is invalid."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "profile_visibility": "private",
  "show_email": false,
  "show_phone": false,
  "show_activity_history": false,
  "show_achievements": true,
  "show_statistics": true
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Privacy settings updated", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data.profile_visibility).to.equal("private");
});
```

---

## 🗑️ ENDPOINTS ACCOUNT

### 10. POST [Shared] Profil - Request Hapus Akun

Request untuk menghapus akun dengan verifikasi password.

#### Endpoint
```
POST /profile/account/delete/request
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (JSON)
```json
{
  "password": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `password` | string | ✅ Yes | string | Password saat ini untuk konfirmasi |

#### Valid Values

**password**:
- Password saat ini yang valid
- Contoh: `"password123"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Account deletion request sent. Please check your email for confirmation.",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "password": [
      "The password is incorrect."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "password": "password123"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("UUID received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('uuid');
});

// Save UUID for confirmation
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("deletion_uuid", jsonData.data.uuid);
}
```

---

### 11. POST [Shared] Profil - Konfirmasi Hapus Akun

Konfirmasi penghapusan akun dengan token dari email.

#### Endpoint
```
POST /profile/account/delete/confirm
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body (JSON)
```json
{
  "uuid": "string",
  "token": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `uuid` | string | ✅ Yes | uuid | UUID dari response request deletion |
| `token` | string | ✅ Yes | size:16 | Token 16 karakter dari email |

#### Valid Values

**uuid**:
- Format UUID valid
- Contoh: `"550e8400-e29b-41d4-a716-446655440000"`

**token**:
- Exactly 16 karakter
- Token dari email konfirmasi
- Contoh: `"a1b2c3d4e5f6g7h8"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Account deleted successfully",
  "data": []
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Account deletion failed. Invalid or expired token.",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "uuid": "{{deletion_uuid}}",
  "token": "a1b2c3d4e5f6g7h8"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Account deleted", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

// Clear all tokens and data
if (pm.response.code === 200) {
    pm.environment.unset("auth_token");
    pm.environment.unset("refresh_token");
    pm.environment.unset("user_id");
    pm.environment.unset("deletion_uuid");
}
```

---

### 12. POST [Shared] Profil - Restore Akun

Restore akun yang telah dihapus (soft delete).

#### Endpoint
```
POST /profile/account/restore
```

#### Authorization
```
Bearer Token Required (dari akun yang dihapus)
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body
```
No body required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Account restored successfully",
  "data": null
}
```

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "Account not found or already active",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// No body required

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Account restored", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});
```

---


## 📊 RESPONSE FORMAT

### Success Response Structure
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // Response data
  }
}
```

### Error Response Structure
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

### Profile Object Structure
```json
{
  "id": 1,
  "name": "John Doe",
  "username": "john_doe",
  "email": "john@example.com",
  "phone": "+6281234567890",
  "bio": "Software Developer",
  "location": "Jakarta, Indonesia",
  "email_verified_at": "2026-03-15T10:00:00.000000Z",
  "status": "active",
  "is_password_set": true,
  "avatar_url": "https://api.levl.id/storage/avatars/john.jpg",
  "created_at": "2026-03-15T10:00:00.000000Z",
  "updated_at": "2026-03-15T10:00:00.000000Z",
  "roles": [
    {
      "id": 1,
      "name": "Student",
      "display_name": "Partisipan"
    }
  ],
  "statistics": {
    "total_courses": 5,
    "completed_courses": 2,
    "total_xp": 1250,
    "current_level": 5
  }
}
```

### Privacy Settings Structure
```json
{
  "profile_visibility": "public",
  "show_email": false,
  "show_phone": false,
  "show_activity_history": true,
  "show_achievements": true,
  "show_statistics": true
}
```

---

## ⚠️ ERROR CODES

### HTTP Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request berhasil |
| 400 | Bad Request | Request tidak valid |
| 401 | Unauthorized | Authentication gagal atau token invalid |
| 403 | Forbidden | User tidak memiliki akses |
| 404 | Not Found | Resource tidak ditemukan |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Common Error Messages

#### Authentication Errors
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": {}
}
```

#### Validation Errors
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email has already been taken."],
    "phone": ["The phone format is invalid."]
  }
}
```

#### Password Errors
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "current_password": ["The current password is incorrect."],
    "new_password": ["The new password must be at least 8 characters."]
  }
}
```

#### File Upload Errors
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "avatar": [
      "The avatar must be an image.",
      "The avatar must not be greater than 2048 kilobytes."
    ]
  }
}
```

---

## 📖 CONTOH USE CASE

### Use Case 1: Complete Profile Setup

```javascript
// Step 1: Get current profile
GET /profile
// Response: Current profile data

// Step 2: Update profile information
PUT /profile
{
  "name": "John Doe",
  "phone": "+6281234567890",
  "bio": "Software Developer passionate about learning",
  "location": "Jakarta, Indonesia"
}
// Response: Updated profile

// Step 3: Upload avatar
POST /profile/avatar
// Form-data: avatar = [file]
// Response: avatar_url

// Step 4: Update privacy settings
PUT /profile/privacy
{
  "profile_visibility": "public",
  "show_email": false,
  "show_phone": false,
  "show_achievements": true
}
// Response: Updated privacy settings
```

### Use Case 2: Change Password Flow

```javascript
// Step 1: Change password
PUT /profile/password
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
// Response: Password changed successfully

// Step 2: Logout (optional - force re-login)
POST /auth/logout
// Response: Logged out

// Step 3: Login with new password
POST /auth/login
{
  "login": "john@example.com",
  "password": "newpassword123"
}
// Response: New tokens
```

### Use Case 3: Change Email Flow

```javascript
// Step 1: Request email change
POST /profile/email/change
{
  "new_email": "newemail@example.com"
}
// Response: uuid
// Email sent to new email address

// Step 2: Check new email for verification token

// Step 3: Verify email change
POST /profile/email/change/verify
{
  "uuid": "{{email_change_uuid}}",
  "token": "a1b2c3d4e5f6g7h8"
}
// Response: Email changed successfully

// Step 4: Get updated profile
GET /profile
// Response: Profile with new email
```

### Use Case 4: Delete Account Flow

```javascript
// Step 1: Request account deletion
POST /profile/account/delete/request
{
  "password": "password123"
}
// Response: uuid
// Email sent with confirmation link

// Step 2: Check email for deletion token

// Step 3: Confirm deletion
POST /profile/account/delete/confirm
{
  "uuid": "{{deletion_uuid}}",
  "token": "a1b2c3d4e5f6g7h8"
}
// Response: Account deleted
// User is logged out automatically
```

### Use Case 5: Update Avatar Flow

```javascript
// Step 1: Upload new avatar
POST /profile/avatar
// Form-data: avatar = [new_file]
// Response: new avatar_url

// Step 2: Verify avatar updated
GET /profile
// Response: Profile with new avatar_url

// Optional: Delete avatar if needed
DELETE /profile/avatar
// Response: Avatar deleted
// Profile will use default avatar
```

---

## 🔒 SECURITY BEST PRACTICES

### For Frontend/Mobile Developers

1. **Profile Data**
   - Cache profile data locally
   - Refresh on app start or pull-to-refresh
   - Update cache after profile changes
   - Clear cache on logout

2. **Avatar Upload**
   - Validate file size before upload (max 2MB)
   - Validate file type (images only)
   - Show upload progress
   - Compress images before upload if possible
   - Handle upload errors gracefully

3. **Password Change**
   - Implement password strength indicator
   - Validate password on client side
   - Confirm password change with user
   - Force logout after password change (optional)
   - Clear stored credentials

4. **Email Change**
   - Confirm with user before requesting change
   - Show verification instructions clearly
   - Handle verification timeout
   - Update stored email after successful change

5. **Account Deletion**
   - Show strong warning before deletion
   - Require password confirmation
   - Explain consequences (data loss, etc)
   - Provide grace period for restoration
   - Clear all local data after deletion

### For Backend Developers

1. **Profile Updates**
   - Validate all input data
   - Sanitize user input
   - Check for duplicate email/username
   - Log profile changes
   - Invalidate cache after updates

2. **Avatar Management**
   - Validate file type and size
   - Scan for malware
   - Optimize/resize images
   - Use secure storage
   - Clean up old avatars

3. **Password Security**
   - Verify current password before change
   - Enforce password strength requirements
   - Hash passwords with bcrypt
   - Rate limit password change attempts
   - Notify user of password changes

4. **Email Verification**
   - Use time-limited tokens
   - Send to new email only
   - Verify token before changing email
   - Log email changes
   - Notify old email of change

5. **Account Deletion**
   - Implement soft delete
   - Provide restoration period (30 days)
   - Anonymize data after grace period
   - Log deletion requests
   - Notify user via email

---

## 📝 POSTMAN COLLECTION SETUP

### Environment Variables

```json
{
  "base_url": "http://localhost:8000/api/v1",
  "auth_token": "",
  "user_id": "",
  "avatar_url": "",
  "email_change_uuid": "",
  "deletion_uuid": ""
}
```

### Pre-request Script (Collection Level)

```javascript
// Set base URL
pm.variables.set("base_url", pm.environment.get("base_url"));

// Add timestamp for debugging
pm.variables.set("timestamp", new Date().toISOString());

// Check if auth token exists
if (!pm.environment.get("auth_token")) {
    console.warn("Warning: auth_token not set. This request may fail.");
}
```

### Tests Script (Collection Level)

```javascript
// Log response time
console.log("Response time:", pm.response.responseTime + "ms");

// Check if response is JSON
pm.test("Response is JSON", function () {
    pm.response.to.be.json;
});

// Check response structure
pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
});

// Log errors if any
if (pm.response.code !== 200 && pm.response.code !== 201) {
    var jsonData = pm.response.json();
    console.error("Error:", jsonData.message);
    if (jsonData.errors) {
        console.error("Validation errors:", JSON.stringify(jsonData.errors, null, 2));
    }
}
```

---

## 🎯 QUICK REFERENCE

### Profile Endpoints
```
GET    /profile                          - Lihat profil
PUT    /profile                          - Update profil
POST   /profile/avatar                   - Upload avatar
DELETE /profile/avatar                   - Hapus avatar
PUT    /profile/password                 - Ganti password
POST   /profile/email/change             - Request ganti email
POST   /profile/email/change/verify      - Verifikasi ganti email
```

### Privacy Endpoints
```
GET    /profile/privacy                  - Lihat pengaturan privacy
PUT    /profile/privacy                  - Update pengaturan privacy
```

### Account Endpoints
```
POST   /profile/account/delete/request   - Request hapus akun
POST   /profile/account/delete/confirm   - Konfirmasi hapus akun
POST   /profile/account/restore          - Restore akun
```

### Rate Limits
```
All endpoints: 60 requests/minute
```

### File Upload Limits
```
Avatar: Max 2MB (JPEG, PNG, JPG, GIF)
```

### Privacy Visibility Options
```
public   - Dapat dilihat semua orang
private  - Hanya dapat dilihat sendiri
friends  - Dapat dilihat teman
```

---

## 📞 SUPPORT & TROUBLESHOOTING

### Common Issues

**Issue: 401 Unauthorized**
- Solution: Check if token is valid and not expired
- Solution: Login again to get new token

**Issue: 422 Validation Error on Profile Update**
- Solution: Check field formats (email, phone)
- Solution: Verify email is unique
- Solution: Check field length limits

**Issue: Avatar Upload Failed**
- Solution: Check file size (max 2MB)
- Solution: Verify file type (images only)
- Solution: Check network connection
- Solution: Try compressing image

**Issue: Password Change Failed**
- Solution: Verify current password is correct
- Solution: Check new password meets requirements (min 8 chars)
- Solution: Ensure password confirmation matches

**Issue: Email Change Not Working**
- Solution: Check new email is unique
- Solution: Verify token from email
- Solution: Check token hasn't expired
- Solution: Check spam folder for verification email

**Issue: Account Deletion Failed**
- Solution: Verify password is correct
- Solution: Check token from email
- Solution: Ensure token hasn't expired

### Debug Tips

1. Check request headers (Authorization, Content-Type)
2. Verify request body format (valid JSON)
3. Check environment variables
4. Review response error messages
5. Check API logs for detailed errors
6. Verify file upload format (multipart/form-data)
7. Check file size and type for avatar uploads

---

**Dokumentasi ini mencakup semua endpoint manajemen profil yang tersedia di Levl API.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
