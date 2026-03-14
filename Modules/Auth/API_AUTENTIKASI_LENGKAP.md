# DOKUMENTASI API AUTENTIKASI LENGKAP - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Auth  
**Platform**: Shared (Semua Platform)

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [Rate Limiting](#rate-limiting)
4. [Endpoints Autentikasi](#endpoints-autentikasi)
   - [1. Register](#1-post-shared-autentikasi---register)
   - [2. Login](#2-post-shared-autentikasi---login)
   - [3. Logout](#3-post-shared-autentikasi---logout)
   - [4. Refresh Token](#4-post-shared-autentikasi---refresh-token)
   - [5. Lupa Password](#5-post-shared-autentikasi---lupa-password)
   - [6. Konfirmasi Lupa Password](#6-post-shared-autentikasi---konfirmasi-lupa-password)
   - [7. Reset Password](#7-post-shared-autentikasi---reset-password)
   - [8. Verifikasi Email](#8-post-shared-autentikasi---verifikasi-email)
   - [9. Kirim Ulang Verifikasi Email](#9-post-shared-autentikasi---kirim-ulang-verifikasi-email)
   - [10. Data User Saat Ini](#10-get-shared-autentikasi---data-user-saat-ini)
   - [11. Set Username](#11-post-shared-autentikasi---set-username)
   - [12. Set Password](#12-post-shared-autentikasi---set-password)
5. [Response Format](#response-format)
6. [Error Codes](#error-codes)
7. [Contoh Use Case](#contoh-use-case)

---

## 🎯 RINGKASAN

API Autentikasi Levl menyediakan endpoint untuk manajemen autentikasi pengguna termasuk registrasi, login, logout, refresh token, reset password, dan verifikasi email. Semua endpoint menggunakan JSON format untuk request dan response.

### Fitur Utama
- ✅ Registrasi pengguna baru
- ✅ Login dengan email/username
- ✅ JWT Token dengan refresh token
- ✅ Reset password via email
- ✅ Verifikasi email
- ✅ Rate limiting untuk keamanan
- ✅ Multi-platform support (Mobile, Web Admin, Web Instruktur)

---

## 🌐 BASE URL & HEADERS

### Base URL
```
Development:  http://localhost:8000/api/v1
Staging:      https://staging-api.levl.id/api/v1
Production:   https://api.levl.id/api/v1
```

### Headers Standar

#### Untuk Public Endpoints (Register, Login, Forgot Password)
```http
Content-Type: application/json
Accept: application/json
Accept-Language: id
```

#### Untuk Protected Endpoints (Logout, Refresh, dll)
```http
Content-Type: application/json
Accept: application/json
Accept-Language: id
Authorization: Bearer {{auth_token}}
```

---

## ⏱️ RATE LIMITING

### Auth Endpoints (Register, Login, Forgot Password)
- **Limit**: 10 requests per minute
- **Throttle Key**: `auth`
- **Response saat limit**: HTTP 429 Too Many Requests

### API Endpoints (Protected)
- **Limit**: 60 requests per minute
- **Throttle Key**: `api`
- **Response saat limit**: HTTP 429 Too Many Requests

---

## 🔐 ENDPOINTS AUTENTIKASI


### 1. POST [Shared] Autentikasi - Register

Mendaftarkan pengguna baru ke sistem.

#### Endpoint
```
POST /auth/register
```

#### Authorization
```
Public (No authentication required)
```

#### Rate Limit
```
10 requests per minute
```

#### Request Body (JSON)
```json
{
  "name": "string",
  "username": "string",
  "email": "string",
  "password": "string",
  "password_confirmation": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `name` | string | ✅ Yes | max:255 | Nama lengkap pengguna |
| `username` | string | ✅ Yes | min:3, max:50, regex:/^[a-z0-9_\.\-]+$/i, unique | Username unik (huruf, angka, underscore, titik, dash) |
| `email` | string | ✅ Yes | email, max:255, unique | Email valid dan unik |
| `password` | string | ✅ Yes | min:8, confirmed | Password minimal 8 karakter |
| `password_confirmation` | string | ✅ Yes | same:password | Konfirmasi password harus sama |

#### Valid Values

**name**:
- Minimal: 1 karakter
- Maksimal: 255 karakter
- Contoh: `"John Doe"`, `"Ahmad Rizki"`

**username**:
- Minimal: 3 karakter
- Maksimal: 50 karakter
- Format: Hanya huruf (a-z, A-Z), angka (0-9), underscore (_), titik (.), dan dash (-)
- Contoh valid: `"john_doe"`, `"ahmad.rizki"`, `"user123"`
- Contoh invalid: `"john doe"` (ada spasi), `"user@123"` (ada @)

**email**:
- Format email valid (RFC)
- Maksimal: 255 karakter
- Harus unik (belum terdaftar)
- Contoh: `"john@example.com"`, `"ahmad.rizki@gmail.com"`

**password**:
- Minimal: 8 karakter
- Harus sama dengan password_confirmation
- Contoh: `"password123"`, `"MySecureP@ss"`

#### Response Success (201 Created)
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "john_doe",
      "email": "john@example.com",
      "email_verified_at": null,
      "status": "active",
      "is_password_set": true,
      "created_at": "2026-03-15T10:00:00.000000Z",
      "updated_at": "2026-03-15T10:00:00.000000Z",
      "roles": [
        {
          "id": 1,
          "name": "Student"
        }
      ]
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502004a8b3c...",
    "token_type": "Bearer",
    "expires_in": 3600
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
    "username": [
      "The username has already been taken."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

#### Postman Example
```javascript
// Pre-request Script
// (none)

// Request Body
{
  "name": "John Doe",
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

// Tests
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Response has access token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('access_token');
    pm.expect(jsonData.data).to.have.property('refresh_token');
});

// Save tokens
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.access_token);
    pm.environment.set("refresh_token", jsonData.data.refresh_token);
    pm.environment.set("user_id", jsonData.data.user.id);
}
```

---

### 2. POST [Shared] Autentikasi - Login

Login ke sistem menggunakan email/username dan password.

#### Endpoint
```
POST /auth/login
```

#### Authorization
```
Public (No authentication required)
```

#### Rate Limit
```
10 requests per minute
```

#### Request Body (JSON)
```json
{
  "login": "string",
  "password": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `login` | string | ✅ Yes | max:255 | Email atau username |
| `password` | string | ✅ Yes | min:8 | Password pengguna |

#### Valid Values

**login**:
- Bisa berupa email atau username
- Maksimal: 255 karakter
- Contoh: `"john@example.com"` atau `"john_doe"`

**password**:
- Minimal: 8 karakter
- Contoh: `"password123"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "john_doe",
      "email": "john@example.com",
      "email_verified_at": "2026-03-15T10:00:00.000000Z",
      "status": "active",
      "is_password_set": true,
      "avatar_url": "https://api.levl.id/storage/avatars/john.jpg",
      "created_at": "2026-03-15T10:00:00.000000Z",
      "updated_at": "2026-03-15T10:00:00.000000Z",
      "roles": [
        {
          "id": 1,
          "name": "Student"
        }
      ]
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502004a8b3c...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {}
}
```

#### Response Error (403 Forbidden - User Inactive)
```json
{
  "success": false,
  "message": "Your account has been deactivated. Please contact administrator.",
  "errors": {}
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "login": [
      "The login field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

#### Postman Example
```javascript
// Request Body
{
  "login": "john@example.com",
  "password": "password123"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has tokens", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('access_token');
    pm.expect(jsonData.data).to.have.property('refresh_token');
    pm.expect(jsonData.data).to.have.property('user');
});

// Save tokens and user data
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.access_token);
    pm.environment.set("refresh_token", jsonData.data.refresh_token);
    pm.environment.set("user_id", jsonData.data.user.id);
    pm.environment.set("user_role", jsonData.data.user.roles[0].name);
}
```

---

### 3. POST [Shared] Autentikasi - Logout

Logout dari sistem dan invalidate token.

#### Endpoint
```
POST /auth/logout
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
  "refresh_token": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `refresh_token` | string | ❌ No | string | Refresh token yang akan di-invalidate (optional) |

#### Valid Values

**refresh_token**:
- Optional field
- Jika diberikan, refresh token akan di-invalidate
- Contoh: `"def502004a8b3c..."`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": []
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

// Request Body (optional)
{
  "refresh_token": "{{refresh_token}}"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Logout successful", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

// Clear tokens
if (pm.response.code === 200) {
    pm.environment.unset("auth_token");
    pm.environment.unset("refresh_token");
}
```

---

### 4. POST [Shared] Autentikasi - Refresh Token

Refresh access token menggunakan refresh token.

#### Endpoint
```
POST /auth/refresh
```

#### Authorization
```
Bearer Token (Expired token allowed)
```

#### Rate Limit
```
10 requests per minute
```

#### Request Body (JSON)
```json
{
  "refresh_token": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `refresh_token` | string | ❌ No | string | Refresh token untuk generate access token baru |

#### Valid Values

**refresh_token**:
- Optional (bisa diambil dari cookie atau body)
- Contoh: `"def502004a8b3c..."`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502004a8b3c...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "success": false,
  "message": "Invalid or expired refresh token",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Request Body
{
  "refresh_token": "{{refresh_token}}"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("New tokens received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('access_token');
    pm.expect(jsonData.data).to.have.property('refresh_token');
});

// Update tokens
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.access_token);
    pm.environment.set("refresh_token", jsonData.data.refresh_token);
}
```

---


### 5. POST [Shared] Autentikasi - Lupa Password

Request reset password link via email.

#### Endpoint
```
POST /auth/password/forgot
```

#### Authorization
```
Public (No authentication required)
```

#### Rate Limit
```
10 requests per minute
```

#### Request Body (JSON)
```json
{
  "login": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `login` | string | ✅ Yes | string | Email atau username pengguna |

#### Valid Values

**login**:
- Bisa berupa email atau username
- Contoh: `"john@example.com"` atau `"john_doe"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Password reset link has been sent to your email",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "User not found",
  "errors": {}
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "login": [
      "The login field is required."
    ]
  }
}
```

#### Postman Example
```javascript
// Request Body
{
  "login": "john@example.com"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("UUID received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('uuid');
});

// Save UUID for next step
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("reset_uuid", jsonData.data.uuid);
}
```

---

### 6. POST [Shared] Autentikasi - Konfirmasi Lupa Password

Konfirmasi forgot password dengan token dari email.

#### Endpoint
```
POST /auth/password/forgot/confirm
```

#### Authorization
```
Public (No authentication required)
```

#### Rate Limit
```
10 requests per minute
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
| `uuid` | string | ✅ Yes | uuid | UUID dari response forgot password |
| `token` | string | ✅ Yes | string | Token dari email (6 digit code) |

#### Valid Values

**uuid**:
- Format UUID valid
- Contoh: `"550e8400-e29b-41d4-a716-446655440000"`

**token**:
- 6 digit code dari email
- Contoh: `"123456"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Token verified successfully. You can now reset your password.",
  "data": {
    "reset_token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "errors": {}
}
```

#### Postman Example
```javascript
// Request Body
{
  "uuid": "{{reset_uuid}}",
  "token": "123456"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Reset token received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('reset_token');
});

// Save reset token
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("reset_token", jsonData.data.reset_token);
}
```

---

### 7. POST [Shared] Autentikasi - Reset Password

Reset password menggunakan reset token.

#### Endpoint
```
POST /auth/password/reset
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
  "token": "string",
  "password": "string",
  "password_confirmation": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `token` | string | ✅ Yes | min:32 | Reset token dari confirm forgot password |
| `password` | string | ✅ Yes | min:8, confirmed | Password baru |
| `password_confirmation` | string | ✅ Yes | same:password | Konfirmasi password baru |

#### Valid Values

**token**:
- Minimal 32 karakter
- Token dari response confirm forgot password
- Contoh: `"a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"`

**password**:
- Minimal 8 karakter
- Harus sama dengan password_confirmation
- Contoh: `"newpassword123"`

**password_confirmation**:
- Harus sama dengan password
- Contoh: `"newpassword123"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Password has been reset successfully",
  "data": []
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "token": [
      "Invalid or expired reset token."
    ],
    "password": [
      "The password must be at least 8 characters.",
      "The password confirmation does not match."
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
  "token": "{{reset_token}}",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Password reset successful", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

// Clear reset tokens
if (pm.response.code === 200) {
    pm.environment.unset("reset_uuid");
    pm.environment.unset("reset_token");
}
```

---

### 8. POST [Shared] Autentikasi - Verifikasi Email

Verifikasi email menggunakan token dari email.

#### Endpoint
```
POST /auth/email/verify
```

#### Authorization
```
Public (No authentication required)
```

#### Rate Limit
```
10 requests per minute
```

#### Request Body (JSON)
```json
{
  "uuid": "string",
  "token": "string",
  "code": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `uuid` | string | ⚠️ Conditional | required_without:token | UUID dari email verifikasi |
| `token` | string | ⚠️ Conditional | required_without:uuid | Token alternatif |
| `code` | string | ✅ Yes | string | Verification code (6 digit) |

#### Valid Values

**uuid**:
- Format UUID valid
- Required jika token tidak ada
- Contoh: `"550e8400-e29b-41d4-a716-446655440000"`

**token**:
- String token
- Required jika uuid tidak ada
- Contoh: `"abc123def456"`

**code**:
- 6 digit verification code dari email
- Contoh: `"123456"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Email verified successfully",
  "data": []
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Invalid or expired verification code",
  "errors": {}
}
```

#### Postman Example
```javascript
// Request Body
{
  "uuid": "{{verification_uuid}}",
  "code": "123456"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Email verified", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});
```

---

### 9. POST [Shared] Autentikasi - Kirim Ulang Verifikasi Email

Kirim ulang email verifikasi.

#### Endpoint
```
POST /auth/email/verify/send
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
  "message": "Verification link has been sent to your email",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

#### Response Error (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "Email already verified",
  "errors": {}
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

// No body required

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("UUID received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('uuid');
});

// Save verification UUID
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("verification_uuid", jsonData.data.uuid);
}
```

---

### 10. GET [Shared] Autentikasi - Data User Saat Ini

Mendapatkan data user yang sedang login.

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
    "permissions": []
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

pm.test("User data received", function () {
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

### 11. POST [Shared] Autentikasi - Set Username

Set username untuk user yang belum memiliki username (OAuth users).

#### Endpoint
```
POST /auth/set-username
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
  "username": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `username` | string | ✅ Yes | min:3, max:50, regex:/^[a-z0-9_\.\-]+$/i, unique | Username unik |

#### Valid Values

**username**:
- Minimal: 3 karakter
- Maksimal: 50 karakter
- Format: Hanya huruf, angka, underscore, titik, dan dash
- Harus unik
- Contoh: `"john_doe"`, `"user123"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Username set successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "john_doe",
    "email": "john@example.com",
    "status": "active",
    "created_at": "2026-03-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "username": [
      "The username has already been taken.",
      "The username must be at least 3 characters."
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
  "username": "john_doe"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Username set", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.username).to.equal("john_doe");
});
```

---

### 12. POST [Shared] Autentikasi - Set Password

Set password untuk user yang belum memiliki password (OAuth users).

#### Endpoint
```
POST /auth/set-password
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
10 requests per minute
```

#### Request Body (JSON)
```json
{
  "password": "string",
  "password_confirmation": "string"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `password` | string | ✅ Yes | min:8, confirmed | Password baru |
| `password_confirmation` | string | ✅ Yes | same:password | Konfirmasi password |

#### Valid Values

**password**:
- Minimal 8 karakter
- Harus sama dengan password_confirmation
- Contoh: `"password123"`

**password_confirmation**:
- Harus sama dengan password
- Contoh: `"password123"`

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Password set successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "john_doe",
    "email": "john@example.com",
    "is_password_set": true,
    "status": "active",
    "created_at": "2026-03-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z"
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Password already set",
  "errors": {
    "password": [
      "Password has already been set for this account."
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
  "password": "password123",
  "password_confirmation": "password123"
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Password set", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.is_password_set).to.be.true;
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

### Token Response Structure
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "def502004a8b3c...",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

### User Object Structure
```json
{
  "id": 1,
  "name": "John Doe",
  "username": "john_doe",
  "email": "john@example.com",
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
  ]
}
```

---

## ⚠️ ERROR CODES

### HTTP Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request berhasil |
| 201 | Created | Resource berhasil dibuat (Register) |
| 400 | Bad Request | Request tidak valid |
| 401 | Unauthorized | Authentication gagal atau token invalid |
| 403 | Forbidden | User tidak memiliki akses (inactive, banned) |
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
    "password": ["The password must be at least 8 characters."]
  }
}
```

#### Rate Limit Error
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "errors": {}
}
```

#### User Status Errors
```json
{
  "success": false,
  "message": "Your account has been deactivated. Please contact administrator.",
  "errors": {}
}
```

---


## 📖 CONTOH USE CASE

### Use Case 1: Complete Registration Flow

```javascript
// Step 1: Register
POST /auth/register
{
  "name": "John Doe",
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
// Response: access_token, refresh_token, user data

// Step 2: Check email for verification code

// Step 3: Verify Email
POST /auth/email/verify
{
  "uuid": "{{verification_uuid}}",
  "code": "123456"
}
// Response: Email verified

// Step 4: Get Profile
GET /profile
// Response: Complete user data with verified email
```

### Use Case 2: Login Flow

```javascript
// Step 1: Login
POST /auth/login
{
  "login": "john@example.com",
  "password": "password123"
}
// Response: access_token, refresh_token, user data
// Save tokens to environment/storage

// Step 2: Access Protected Resources
GET /profile
Headers: Authorization: Bearer {{access_token}}
// Response: User profile data
```

### Use Case 3: Forgot Password Flow

```javascript
// Step 1: Request Password Reset
POST /auth/password/forgot
{
  "login": "john@example.com"
}
// Response: uuid
// Email sent with 6-digit code

// Step 2: Confirm Reset Token
POST /auth/password/forgot/confirm
{
  "uuid": "{{reset_uuid}}",
  "token": "123456"
}
// Response: reset_token

// Step 3: Reset Password
POST /auth/password/reset
Headers: Authorization: Bearer {{access_token}}
{
  "token": "{{reset_token}}",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
// Response: Password reset successful

// Step 4: Login with New Password
POST /auth/login
{
  "login": "john@example.com",
  "password": "newpassword123"
}
// Response: New tokens
```

### Use Case 4: Token Refresh Flow

```javascript
// Step 1: Access token expired, use refresh token
POST /auth/refresh
Headers: Authorization: Bearer {{expired_access_token}}
{
  "refresh_token": "{{refresh_token}}"
}
// Response: New access_token and refresh_token

// Step 2: Update stored tokens
// Save new tokens to environment/storage

// Step 3: Retry original request with new token
GET /profile
Headers: Authorization: Bearer {{new_access_token}}
// Response: User profile data
```

### Use Case 5: OAuth User Setup Flow

```javascript
// Step 1: User logs in via Google OAuth
// Backend creates user without username/password

// Step 2: Set Username
POST /auth/set-username
Headers: Authorization: Bearer {{access_token}}
{
  "username": "john_doe"
}
// Response: User data with username

// Step 3: Set Password (Optional)
POST /auth/set-password
Headers: Authorization: Bearer {{access_token}}
{
  "password": "password123",
  "password_confirmation": "password123"
}
// Response: User data with password set
```

---

## 🔒 SECURITY BEST PRACTICES

### For Frontend/Mobile Developers

1. **Token Storage**
   - Web: Store tokens in httpOnly cookies or secure localStorage
   - Mobile: Use secure storage (Keychain/Keystore)
   - Never store tokens in plain text

2. **Token Refresh**
   - Implement automatic token refresh before expiration
   - Handle 401 errors gracefully with token refresh
   - Logout user if refresh token is invalid

3. **Password Handling**
   - Never log passwords
   - Use HTTPS for all requests
   - Implement password strength indicator
   - Validate password on client side before sending

4. **Rate Limiting**
   - Implement exponential backoff for failed requests
   - Show user-friendly messages for rate limit errors
   - Cache responses when appropriate

5. **Error Handling**
   - Handle all possible error responses
   - Show user-friendly error messages
   - Log errors for debugging (without sensitive data)

### For Backend Developers

1. **Token Management**
   - Use short-lived access tokens (1 hour)
   - Use long-lived refresh tokens (30 days)
   - Implement token rotation
   - Invalidate tokens on logout

2. **Password Security**
   - Hash passwords with bcrypt
   - Implement password strength requirements
   - Rate limit password reset attempts
   - Send password reset emails securely

3. **Email Verification**
   - Use time-limited verification codes
   - Implement rate limiting for resend
   - Track verification attempts

4. **User Status**
   - Check user status on every request
   - Block inactive/banned users
   - Log status changes

---

## 📝 POSTMAN COLLECTION SETUP

### Environment Variables

```json
{
  "base_url": "http://localhost:8000/api/v1",
  "auth_token": "",
  "refresh_token": "",
  "user_id": "",
  "verification_uuid": "",
  "reset_uuid": "",
  "reset_token": ""
}
```

### Pre-request Script (Collection Level)

```javascript
// Set base URL
pm.variables.set("base_url", pm.environment.get("base_url"));

// Add timestamp for debugging
pm.variables.set("timestamp", new Date().toISOString());
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
```

---

## 🎯 QUICK REFERENCE

### Public Endpoints (No Auth Required)
```
POST   /auth/register
POST   /auth/login
POST   /auth/password/forgot
POST   /auth/password/forgot/confirm
POST   /auth/email/verify
```

### Protected Endpoints (Auth Required)
```
POST   /auth/logout
POST   /auth/refresh
POST   /auth/set-username
POST   /auth/set-password
POST   /auth/password/reset
POST   /auth/email/verify/send
GET    /profile
```

### Rate Limits
```
Auth endpoints:  10 requests/minute
API endpoints:   60 requests/minute
```

### Token Expiration
```
Access Token:   1 hour (3600 seconds)
Refresh Token:  30 days
```

---

## 📞 SUPPORT & TROUBLESHOOTING

### Common Issues

**Issue: 401 Unauthorized**
- Solution: Check if token is valid and not expired
- Solution: Try refreshing token
- Solution: Login again if refresh fails

**Issue: 422 Validation Error**
- Solution: Check request body format
- Solution: Verify all required fields are present
- Solution: Check field validation rules

**Issue: 429 Too Many Requests**
- Solution: Wait before retrying
- Solution: Implement exponential backoff
- Solution: Check rate limit headers

**Issue: Email not verified**
- Solution: Resend verification email
- Solution: Check spam folder
- Solution: Contact support if issue persists

### Debug Tips

1. Check request headers (Authorization, Content-Type)
2. Verify request body format (valid JSON)
3. Check environment variables
4. Review response error messages
5. Check API logs for detailed errors

---

**Dokumentasi ini mencakup semua endpoint autentikasi yang tersedia di Levl API.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
