# API AUTHENTICATION - MODUL AUTH

**Base URL**: `{{base_url}}/v1`

---

## 1.1 Register

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
      "email_verified_at": null
    },
    "verification_uuid": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

---

## 1.2 Login

**Endpoint**: `POST /auth/login`  
**Rate Limit**: 10 requests/minute  
**Auth**: Tidak diperlukan

**Request Body** (JSON):
```json
{
  "login": "johndoe",
  "password": "Password123!"
}
```

**Catatan**: Field `login` bisa berisi email atau username
