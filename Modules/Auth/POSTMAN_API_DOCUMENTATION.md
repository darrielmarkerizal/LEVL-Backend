# DOKUMENTASI API - MODUL AUTH

**Base URL**: `{{base_url}}/v1`  
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Bahasa**: Indonesia

---

## 📋 DAFTAR ISI

1. [Authentication](#1-authentication)
2. [Profile Management](#2-profile-management)
3. [User Management](#3-user-management)
4. [Bulk Operations](#4-bulk-operations)
5. [Password Management](#5-password-management)
6. [Public Profile](#6-public-profile)
7. [User Status](#7-user-status)
8. [Privacy Settings](#8-privacy-settings)
9. [Activity History](#9-activity-history)

---

## 🔐 1. AUTHENTICATION

### 1.1 Register

**Endpoint**: `POST /auth/register`  
**Rate Limit**: 10 requests/minute  
**Auth**: Tidak diperlukan

**Request Body** (JSON):
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "username": "johndoe",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

**Validasi**:
- `name`: required, string, max:255
- `email`: required, email, unique
- `username`: required, string, min:3, max:50, unique, alphanumeric + underscore
- `password`: required, min:8, confirmed

**Response Success** (201):
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "username": "johndoe",
      "status": "pending",
      "email_verified_at": null,
      "created_at": "2026-03-15T10:00:00.000000Z"
    },
    "verification_uuid": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

**Response Error** (422):
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "email": ["Email sudah terdaftar"],
    "username": ["Username sudah digunakan"]
  }
}
```

**Catatan**:
- User baru akan memiliki status `pending` sampai verifikasi email
- Email verifikasi otomatis dikirim setelah registrasi
- `verification_uuid` digunakan untuk tracking status verifikasi

---

### 1.2 Login

**Endpoint**: `POST /auth/login`  
**Rate Limit**: 10 requests/minute  
**Auth**: Tidak diperlukan
