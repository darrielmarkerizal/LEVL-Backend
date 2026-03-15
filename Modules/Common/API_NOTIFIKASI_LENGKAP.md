# DOKUMENTASI API NOTIFIKASI LENGKAP - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Common - Notifications  
**Platform**: Shared (Semua Platform)

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [Endpoints Daftar Notifikasi](#endpoints-daftar-notifikasi)
   - [1. Daftar Notifikasi](#1-get-shared-notifikasi---daftar-notifikasi)
   - [2. Jumlah Belum Dibaca](#2-get-shared-notifikasi---jumlah-belum-dibaca)
   - [3. Detail Notifikasi](#3-get-shared-notifikasi---detail-notifikasi)
4. [Endpoints Aksi Notifikasi](#endpoints-aksi-notifikasi)
   - [4. Tandai Dibaca](#4-put-shared-notifikasi---tandai-dibaca)
   - [5. Tandai Semua Dibaca](#5-put-shared-notifikasi---tandai-semua-dibaca)
   - [6. Hapus Notifikasi](#6-delete-shared-notifikasi---hapus-notifikasi)
   - [7. Hapus Semua](#7-delete-shared-notifikasi---hapus-semua)
5. [Endpoints Preferensi Notifikasi](#endpoints-preferensi-notifikasi)
   - [8. Lihat Preferensi](#8-get-shared-notifikasi---lihat-preferensi)
   - [9. Update Preferensi](#9-put-shared-notifikasi---update-preferensi)
   - [10. Reset Preferensi](#10-post-shared-notifikasi---reset-preferensi)
6. [Response Format](#response-format)
7. [Notification Types](#notification-types)
8. [Error Codes](#error-codes)
9. [Contoh Use Case](#contoh-use-case)

---

## 🎯 RINGKASAN

API Notifikasi Levl menyediakan endpoint untuk mengelola notifikasi pengguna termasuk melihat daftar notifikasi, menandai sebagai dibaca, menghapus notifikasi, dan mengatur preferensi notifikasi. Semua endpoint memerlukan autentikasi.

### Fitur Utama
- ✅ View daftar notifikasi dengan pagination
- ✅ Filter notifikasi berdasarkan status (read/unread)
- ✅ Hitung jumlah notifikasi belum dibaca
- ✅ Tandai notifikasi sebagai dibaca (single/bulk)
- ✅ Hapus notifikasi (single/bulk)
- ✅ Kelola preferensi notifikasi per channel
- ✅ Support multiple notification types
- ✅ Real-time notification count

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

---

## 📬 ENDPOINTS DAFTAR NOTIFIKASI

### 1. GET [Shared] Notifikasi - Daftar Notifikasi

Mendapatkan daftar notifikasi pengguna dengan pagination dan filter.

#### Endpoint
```
GET /notifications
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

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | ❌ No | 1 | Nomor halaman |
| `per_page` | integer | ❌ No | 15 | Jumlah item per halaman (max: 100) |
| `status` | string | ❌ No | all | Filter status: `all`, `read`, `unread` |
| `type` | string | ❌ No | all | Filter tipe notifikasi |
| `sort` | string | ❌ No | created_at | Field untuk sorting |
| `direction` | string | ❌ No | desc | Arah sorting: `asc`, `desc` |

#### Valid Values

**status**:
- `all` - Semua notifikasi
- `read` - Hanya notifikasi yang sudah dibaca
- `unread` - Hanya notifikasi yang belum dibaca

**type**:
- `all` - Semua tipe
- `course_enrollment` - Notifikasi pendaftaran kursus
- `assignment_graded` - Notifikasi nilai tugas
- `badge_earned` - Notifikasi lencana diperoleh
- `level_up` - Notifikasi naik level
- `forum_reply` - Notifikasi balasan forum
- `announcement` - Notifikasi pengumuman
- `system` - Notifikasi sistem

**sort**:
- `created_at` - Urutkan berdasarkan tanggal dibuat
- `read_at` - Urutkan berdasarkan tanggal dibaca

**direction**:
- `asc` - Ascending (terlama ke terbaru)
- `desc` - Descending (terbaru ke terlama)

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Notifications retrieved successfully",
  "data": [
    {
      "id": "9a5f8e7d-1234-5678-90ab-cdef12345678",
      "type": "badge_earned",
      "title": "Lencana Baru Diperoleh!",
      "message": "Selamat! Anda telah mendapatkan lencana 'First Step'",
      "data": {
        "badge_id": 1,
        "badge_name": "First Step",
        "badge_icon": "https://api.levl.id/storage/badges/first-step.png"
      },
      "read_at": null,
      "created_at": "2026-03-15T10:00:00.000000Z"
    },
    {
      "id": "9a5f8e7d-1234-5678-90ab-cdef12345679",
      "type": "assignment_graded",
      "title": "Tugas Telah Dinilai",
      "message": "Tugas 'Introduction to Programming' telah dinilai dengan nilai 85",
      "data": {
        "assignment_id": 10,
        "assignment_title": "Introduction to Programming",
        "grade": 85,
        "max_grade": 100
      },
      "read_at": "2026-03-15T11:00:00.000000Z",
      "created_at": "2026-03-15T09:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 73
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

// Query Params
page: 1
per_page: 15
status: unread
type: all
sort: created_at
direction: desc

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Notifications array received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.be.an('array');
});

pm.test("Pagination meta exists", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.meta).to.have.property('total');
    pm.expect(jsonData.meta).to.have.property('current_page');
});

pm.test("Response time < 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});
```

---

### 2. GET [Shared] Notifikasi - Jumlah Belum Dibaca

Mendapatkan jumlah notifikasi yang belum dibaca.

#### Endpoint
```
GET /notifications/unread-count
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
  "message": "Unread count retrieved successfully",
  "data": {
    "unread_count": 12,
    "by_type": {
      "badge_earned": 3,
      "assignment_graded": 5,
      "forum_reply": 2,
      "level_up": 1,
      "announcement": 1
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

pm.test("Unread count received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('unread_count');
    pm.expect(jsonData.data.unread_count).to.be.a('number');
});

pm.test("By type breakdown exists", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('by_type');
});

// Save unread count
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("unread_count", jsonData.data.unread_count);
}
```

---

### 3. GET [Shared] Notifikasi - Detail Notifikasi

Mendapatkan detail notifikasi berdasarkan ID.

#### Endpoint
```
GET /notifications/{id}
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | uuid | ✅ Yes | UUID notifikasi |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Notification retrieved successfully",
  "data": {
    "id": "9a5f8e7d-1234-5678-90ab-cdef12345678",
    "type": "badge_earned",
    "title": "Lencana Baru Diperoleh!",
    "message": "Selamat! Anda telah mendapatkan lencana 'First Step'",
    "data": {
      "badge_id": 1,
      "badge_name": "First Step",
      "badge_description": "Menyelesaikan pelajaran pertama",
      "badge_icon": "https://api.levl.id/storage/badges/first-step.png",
      "earned_at": "2026-03-15T10:00:00.000000Z"
    },
    "read_at": null,
    "created_at": "2026-03-15T10:00:00.000000Z"
  }
}
```

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "Notification not found",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/notifications/{{notification_id}}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Notification detail received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('id');
    pm.expect(jsonData.data).to.have.property('type');
    pm.expect(jsonData.data).to.have.property('title');
    pm.expect(jsonData.data).to.have.property('message');
});
```

---

## ✅ ENDPOINTS AKSI NOTIFIKASI

### 4. PUT [Shared] Notifikasi - Tandai Dibaca

Menandai notifikasi sebagai sudah dibaca.

#### Endpoint
```
PUT /notifications/{id}/read
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | uuid | ✅ Yes | UUID notifikasi |

#### Request Body
```
No body required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": {
    "id": "9a5f8e7d-1234-5678-90ab-cdef12345678",
    "read_at": "2026-03-15T12:00:00.000000Z"
  }
}
```

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "Notification not found",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/notifications/{{notification_id}}/read

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Notification marked as read", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data.read_at).to.not.be.null;
});
```

---

### 5. PUT [Shared] Notifikasi - Tandai Semua Dibaca

Menandai semua notifikasi sebagai sudah dibaca.

#### Endpoint
```
PUT /notifications/read-all
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
  "message": "All notifications marked as read",
  "data": {
    "marked_count": 12
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

pm.test("All notifications marked", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data).to.have.property('marked_count');
});

// Clear unread count
if (pm.response.code === 200) {
    pm.environment.set("unread_count", 0);
}
```

---

### 6. DELETE [Shared] Notifikasi - Hapus Notifikasi

Menghapus notifikasi berdasarkan ID.

#### Endpoint
```
DELETE /notifications/{id}
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | uuid | ✅ Yes | UUID notifikasi |

#### Request Body
```
No body required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Notification deleted successfully",
  "data": null
}
```

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "Notification not found",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/notifications/{{notification_id}}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Notification deleted", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

// Clear notification ID
if (pm.response.code === 200) {
    pm.environment.unset("notification_id");
}
```

---

### 7. DELETE [Shared] Notifikasi - Hapus Semua

Menghapus semua notifikasi pengguna.

#### Endpoint
```
DELETE /notifications
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

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `status` | string | ❌ No | all | Filter status: `all`, `read`, `unread` |
| `type` | string | ❌ No | all | Filter tipe notifikasi |

#### Valid Values

**status**:
- `all` - Hapus semua notifikasi
- `read` - Hapus hanya notifikasi yang sudah dibaca
- `unread` - Hapus hanya notifikasi yang belum dibaca

**type**:
- `all` - Hapus semua tipe
- Atau tipe spesifik: `badge_earned`, `assignment_graded`, dll

#### Request Body
```
No body required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Notifications deleted successfully",
  "data": {
    "deleted_count": 25
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

// Query Params (optional)
status: read
type: all

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Notifications deleted", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data).to.have.property('deleted_count');
});
```

---

## ⚙️ ENDPOINTS PREFERENSI NOTIFIKASI

### 8. GET [Shared] Notifikasi - Lihat Preferensi

Mendapatkan pengaturan preferensi notifikasi pengguna.

#### Endpoint
```
GET /notifications/preferences
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
  "message": "Notification preferences retrieved successfully",
  "data": {
    "email_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": true,
      "announcement": true,
      "system": true
    },
    "push_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": false,
      "announcement": true,
      "system": true
    },
    "in_app_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": true,
      "announcement": true,
      "system": true
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

pm.test("Preferences received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('email_notifications');
    pm.expect(jsonData.data).to.have.property('push_notifications');
    pm.expect(jsonData.data).to.have.property('in_app_notifications');
});
```

---

### 9. PUT [Shared] Notifikasi - Update Preferensi

Update pengaturan preferensi notifikasi pengguna.

#### Endpoint
```
PUT /notifications/preferences
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
  "email_notifications": {
    "course_enrollment": boolean,
    "assignment_graded": boolean,
    "badge_earned": boolean,
    "level_up": boolean,
    "forum_reply": boolean,
    "announcement": boolean,
    "system": boolean
  },
  "push_notifications": {
    "course_enrollment": boolean,
    "assignment_graded": boolean,
    "badge_earned": boolean,
    "level_up": boolean,
    "forum_reply": boolean,
    "announcement": boolean,
    "system": boolean
  },
  "in_app_notifications": {
    "course_enrollment": boolean,
    "assignment_graded": boolean,
    "badge_earned": boolean,
    "level_up": boolean,
    "forum_reply": boolean,
    "announcement": boolean,
    "system": boolean
  }
}
```

#### Field Validation

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email_notifications` | object | ❌ No | Preferensi notifikasi email |
| `push_notifications` | object | ❌ No | Preferensi notifikasi push |
| `in_app_notifications` | object | ❌ No | Preferensi notifikasi in-app |

#### Valid Values

**Notification Types** (untuk setiap channel):
- `course_enrollment` - Notifikasi pendaftaran kursus
- `assignment_graded` - Notifikasi nilai tugas
- `badge_earned` - Notifikasi lencana diperoleh
- `level_up` - Notifikasi naik level
- `forum_reply` - Notifikasi balasan forum
- `announcement` - Notifikasi pengumuman
- `system` - Notifikasi sistem

**Values**:
- `true` - Aktifkan notifikasi untuk tipe ini
- `false` - Nonaktifkan notifikasi untuk tipe ini

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Notification preferences updated successfully",
  "data": {
    "email_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": false,
      "announcement": true,
      "system": true
    },
    "push_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": false,
      "announcement": true,
      "system": true
    },
    "in_app_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": true,
      "announcement": true,
      "system": true
    }
  }
}
```

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email_notifications.course_enrollment": [
      "The email notifications.course enrollment field must be true or false."
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
  "email_notifications": {
    "course_enrollment": true,
    "assignment_graded": true,
    "badge_earned": true,
    "level_up": true,
    "forum_reply": false,
    "announcement": true,
    "system": true
  },
  "push_notifications": {
    "course_enrollment": true,
    "assignment_graded": true,
    "badge_earned": true,
    "level_up": true,
    "forum_reply": false,
    "announcement": true,
    "system": true
  }
}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Preferences updated", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});
```

---

### 10. POST [Shared] Notifikasi - Reset Preferensi

Reset preferensi notifikasi ke pengaturan default.

#### Endpoint
```
POST /notifications/preferences/reset
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
  "message": "Notification preferences reset to default",
  "data": {
    "email_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": true,
      "announcement": true,
      "system": true
    },
    "push_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": true,
      "announcement": true,
      "system": true
    },
    "in_app_notifications": {
      "course_enrollment": true,
      "assignment_graded": true,
      "badge_earned": true,
      "level_up": true,
      "forum_reply": true,
      "announcement": true,
      "system": true
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

// No body required

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Preferences reset", function () {
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

### Notification Object Structure
```json
{
  "id": "9a5f8e7d-1234-5678-90ab-cdef12345678",
  "type": "badge_earned",
  "title": "Lencana Baru Diperoleh!",
  "message": "Selamat! Anda telah mendapatkan lencana 'First Step'",
  "data": {
    "badge_id": 1,
    "badge_name": "First Step",
    "badge_icon": "https://api.levl.id/storage/badges/first-step.png"
  },
  "read_at": "2026-03-15T11:00:00.000000Z",
  "created_at": "2026-03-15T10:00:00.000000Z"
}
```

### Pagination Meta Structure
```json
{
  "current_page": 1,
  "from": 1,
  "last_page": 5,
  "per_page": 15,
  "to": 15,
  "total": 73
}
```

### Preferences Object Structure
```json
{
  "email_notifications": {
    "course_enrollment": true,
    "assignment_graded": true,
    "badge_earned": true,
    "level_up": true,
    "forum_reply": true,
    "announcement": true,
    "system": true
  },
  "push_notifications": {
    "course_enrollment": true,
    "assignment_graded": true,
    "badge_earned": true,
    "level_up": true,
    "forum_reply": true,
    "announcement": true,
    "system": true
  },
  "in_app_notifications": {
    "course_enrollment": true,
    "assignment_graded": true,
    "badge_earned": true,
    "level_up": true,
    "forum_reply": true,
    "announcement": true,
    "system": true
  }
}
```

---

## 🔔 NOTIFICATION TYPES

### Notification Type Details

#### 1. course_enrollment
**Trigger**: User terdaftar di kursus baru
```json
{
  "type": "course_enrollment",
  "title": "Pendaftaran Kursus Berhasil",
  "message": "Anda telah terdaftar di kursus 'Introduction to Programming'",
  "data": {
    "course_id": 1,
    "course_title": "Introduction to Programming",
    "enrollment_id": 10
  }
}
```

#### 2. assignment_graded
**Trigger**: Tugas telah dinilai oleh instruktur
```json
{
  "type": "assignment_graded",
  "title": "Tugas Telah Dinilai",
  "message": "Tugas 'Introduction to Programming' telah dinilai dengan nilai 85",
  "data": {
    "assignment_id": 10,
    "assignment_title": "Introduction to Programming",
    "grade": 85,
    "max_grade": 100,
    "feedback": "Good work!"
  }
}
```

#### 3. badge_earned
**Trigger**: User mendapatkan lencana baru
```json
{
  "type": "badge_earned",
  "title": "Lencana Baru Diperoleh!",
  "message": "Selamat! Anda telah mendapatkan lencana 'First Step'",
  "data": {
    "badge_id": 1,
    "badge_name": "First Step",
    "badge_description": "Menyelesaikan pelajaran pertama",
    "badge_icon": "https://api.levl.id/storage/badges/first-step.png"
  }
}
```

#### 4. level_up
**Trigger**: User naik level
```json
{
  "type": "level_up",
  "title": "Level Up!",
  "message": "Selamat! Anda naik ke Level 5",
  "data": {
    "old_level": 4,
    "new_level": 5,
    "total_xp": 1250,
    "next_level_xp": 1500
  }
}
```

#### 5. forum_reply
**Trigger**: Ada balasan di thread forum user
```json
{
  "type": "forum_reply",
  "title": "Balasan Baru di Forum",
  "message": "Ada balasan baru di thread 'How to learn programming?'",
  "data": {
    "thread_id": 15,
    "thread_title": "How to learn programming?",
    "reply_id": 50,
    "replier_name": "John Doe"
  }
}
```

#### 6. announcement
**Trigger**: Admin membuat pengumuman
```json
{
  "type": "announcement",
  "title": "Pengumuman Penting",
  "message": "Sistem akan maintenance pada tanggal 20 Maret 2026",
  "data": {
    "announcement_id": 5,
    "priority": "high",
    "scheduled_at": "2026-03-20T00:00:00.000000Z"
  }
}
```

#### 7. system
**Trigger**: Notifikasi sistem (update, maintenance, dll)
```json
{
  "type": "system",
  "title": "Update Sistem",
  "message": "Sistem telah diupdate ke versi 2.0",
  "data": {
    "version": "2.0",
    "release_notes_url": "https://levl.id/release-notes/v2.0"
  }
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
| 404 | Not Found | Notifikasi tidak ditemukan |
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

#### Not Found Errors
```json
{
  "success": false,
  "message": "Notification not found",
  "errors": {}
}
```

#### Validation Errors
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "status": ["The selected status is invalid."],
    "type": ["The selected type is invalid."]
  }
}
```

---

## 📖 CONTOH USE CASE

### Use Case 1: View Unread Notifications

```javascript
// Step 1: Get unread count
GET /notifications/unread-count
// Response: { unread_count: 12 }

// Step 2: Get unread notifications
GET /notifications?status=unread&per_page=20
// Response: List of unread notifications

// Step 3: Mark specific notification as read
PUT /notifications/{id}/read
// Response: Notification marked as read

// Step 4: Get updated unread count
GET /notifications/unread-count
// Response: { unread_count: 11 }
```

### Use Case 2: Manage Notification Preferences

```javascript
// Step 1: Get current preferences
GET /notifications/preferences
// Response: Current preferences

// Step 2: Update preferences (disable forum replies)
PUT /notifications/preferences
{
  "email_notifications": {
    "forum_reply": false
  },
  "push_notifications": {
    "forum_reply": false
  }
}
// Response: Updated preferences

// Step 3: Verify preferences updated
GET /notifications/preferences
// Response: Preferences with forum_reply disabled
```

### Use Case 3: Clean Up Old Notifications

```javascript
// Step 1: Get all read notifications
GET /notifications?status=read&per_page=100
// Response: List of read notifications

// Step 2: Delete all read notifications
DELETE /notifications?status=read
// Response: { deleted_count: 45 }

// Step 3: Verify notifications deleted
GET /notifications?status=read
// Response: Empty list or fewer notifications
```

### Use Case 4: Real-time Notification Flow (Mobile App)

```javascript
// Step 1: App starts - get unread count
GET /notifications/unread-count
// Response: { unread_count: 5 }
// Display badge on notification icon

// Step 2: User opens notification screen
GET /notifications?status=unread&per_page=20
// Response: List of unread notifications
// Display in notification list

// Step 3: User taps on notification
GET /notifications/{id}
// Response: Notification detail
// Navigate to relevant screen (course, assignment, etc)

// Step 4: Mark as read automatically
PUT /notifications/{id}/read
// Response: Notification marked as read

// Step 5: Update unread count
GET /notifications/unread-count
// Response: { unread_count: 4 }
// Update badge on notification icon

// Step 6: User marks all as read
PUT /notifications/read-all
// Response: { marked_count: 4 }

// Step 7: Update UI
GET /notifications/unread-count
// Response: { unread_count: 0 }
// Remove badge from notification icon
```

### Use Case 5: Filter Notifications by Type

```javascript
// Step 1: Get all badge notifications
GET /notifications?type=badge_earned&per_page=20
// Response: List of badge notifications

// Step 2: Get all assignment graded notifications
GET /notifications?type=assignment_graded&per_page=20
// Response: List of assignment graded notifications

// Step 3: Delete all forum reply notifications
DELETE /notifications?type=forum_reply
// Response: { deleted_count: 10 }
```

---

## 🔒 SECURITY BEST PRACTICES

### For Frontend/Mobile Developers

1. **Notification Polling**
   - Poll unread count every 30-60 seconds when app is active
   - Use exponential backoff on errors
   - Stop polling when app is in background
   - Resume polling when app returns to foreground

2. **Real-time Updates**
   - Implement WebSocket/SSE for real-time notifications
   - Fall back to polling if WebSocket unavailable
   - Update UI immediately on new notification
   - Play sound/vibration based on user preferences

3. **Notification Display**
   - Show unread count badge on notification icon
   - Highlight unread notifications in list
   - Auto-mark as read when user views detail
   - Group notifications by type/date

4. **Performance**
   - Cache notification list locally
   - Implement infinite scroll/pagination
   - Lazy load notification details
   - Optimize images in notification data

5. **User Experience**
   - Provide swipe-to-delete gesture
   - Implement pull-to-refresh
   - Show loading states
   - Handle empty states gracefully
   - Provide clear action buttons

### For Backend Developers

1. **Notification Creation**
   - Queue notification creation for async processing
   - Batch create notifications for multiple users
   - Check user preferences before sending
   - Implement retry logic for failed sends

2. **Performance**
   - Index notification tables properly
   - Implement pagination for large lists
   - Cache unread counts
   - Archive old notifications (>90 days)

3. **Security**
   - Validate user owns notification before actions
   - Rate limit notification endpoints
   - Sanitize notification content
   - Prevent notification spam

4. **Monitoring**
   - Log notification creation/delivery
   - Track notification open rates
   - Monitor failed deliveries
   - Alert on unusual patterns

5. **Cleanup**
   - Soft delete notifications
   - Archive old notifications
   - Clean up orphaned notifications
   - Implement retention policies

---

## 📝 POSTMAN COLLECTION SETUP

### Environment Variables

```json
{
  "base_url": "http://localhost:8000/api/v1",
  "auth_token": "",
  "user_id": "",
  "notification_id": "",
  "unread_count": 0
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

### Notification List Endpoints
```
GET    /notifications                     - Daftar notifikasi
GET    /notifications/unread-count        - Jumlah belum dibaca
GET    /notifications/{id}                - Detail notifikasi
```

### Notification Action Endpoints
```
PUT    /notifications/{id}/read           - Tandai dibaca
PUT    /notifications/read-all            - Tandai semua dibaca
DELETE /notifications/{id}                - Hapus notifikasi
DELETE /notifications                     - Hapus semua
```

### Notification Preference Endpoints
```
GET    /notifications/preferences         - Lihat preferensi
PUT    /notifications/preferences         - Update preferensi
POST   /notifications/preferences/reset   - Reset preferensi
```

### Rate Limits
```
All endpoints: 60 requests/minute
```

### Notification Types
```
course_enrollment   - Pendaftaran kursus
assignment_graded   - Nilai tugas
badge_earned        - Lencana diperoleh
level_up            - Naik level
forum_reply         - Balasan forum
announcement        - Pengumuman
system              - Notifikasi sistem
```

### Notification Channels
```
email_notifications    - Notifikasi via email
push_notifications     - Notifikasi push mobile
in_app_notifications   - Notifikasi in-app
```

### Filter Options
```
status: all, read, unread
type: all, course_enrollment, assignment_graded, badge_earned, level_up, forum_reply, announcement, system
sort: created_at, read_at
direction: asc, desc
```

---

## 📞 SUPPORT & TROUBLESHOOTING

### Common Issues

**Issue: 401 Unauthorized**
- Solution: Check if token is valid and not expired
- Solution: Login again to get new token

**Issue: Notifications Not Loading**
- Solution: Check network connection
- Solution: Verify API endpoint is correct
- Solution: Check if user has any notifications

**Issue: Unread Count Not Updating**
- Solution: Call unread-count endpoint after marking as read
- Solution: Refresh notification list
- Solution: Check if notification was actually marked as read

**Issue: Preferences Not Saving**
- Solution: Verify request body format is correct
- Solution: Check all boolean values are true/false
- Solution: Ensure all notification types are included

**Issue: Cannot Delete Notification**
- Solution: Verify notification ID is correct
- Solution: Check if notification belongs to user
- Solution: Ensure notification exists

### Debug Tips

1. Check request headers (Authorization, Content-Type)
2. Verify request body format (valid JSON)
3. Check environment variables
4. Review response error messages
5. Check API logs for detailed errors
6. Verify notification ID format (UUID)
7. Test with different filter combinations

---

## 🚀 IMPLEMENTATION TIPS

### Mobile App Implementation

```javascript
// Notification Service Example
class NotificationService {
  constructor() {
    this.pollingInterval = null;
    this.unreadCount = 0;
  }

  // Start polling for new notifications
  startPolling() {
    this.pollingInterval = setInterval(() => {
      this.fetchUnreadCount();
    }, 30000); // Poll every 30 seconds
  }

  // Stop polling
  stopPolling() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
  }

  // Fetch unread count
  async fetchUnreadCount() {
    try {
      const response = await fetch('/notifications/unread-count');
      const data = await response.json();
      this.unreadCount = data.data.unread_count;
      this.updateBadge(this.unreadCount);
    } catch (error) {
      console.error('Failed to fetch unread count:', error);
    }
  }

  // Update badge UI
  updateBadge(count) {
    // Update notification icon badge
    // Platform-specific implementation
  }

  // Mark notification as read
  async markAsRead(notificationId) {
    try {
      await fetch(`/notifications/${notificationId}/read`, {
        method: 'PUT'
      });
      this.fetchUnreadCount(); // Refresh count
    } catch (error) {
      console.error('Failed to mark as read:', error);
    }
  }
}
```

### Web App Implementation

```javascript
// React Hook Example
import { useState, useEffect } from 'react';

export function useNotifications() {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);

  // Fetch notifications
  const fetchNotifications = async (params = {}) => {
    setLoading(true);
    try {
      const queryString = new URLSearchParams(params).toString();
      const response = await fetch(`/notifications?${queryString}`);
      const data = await response.json();
      setNotifications(data.data);
      return data;
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
    } finally {
      setLoading(false);
    }
  };

  // Fetch unread count
  const fetchUnreadCount = async () => {
    try {
      const response = await fetch('/notifications/unread-count');
      const data = await response.json();
      setUnreadCount(data.data.unread_count);
    } catch (error) {
      console.error('Failed to fetch unread count:', error);
    }
  };

  // Mark as read
  const markAsRead = async (notificationId) => {
    try {
      await fetch(`/notifications/${notificationId}/read`, {
        method: 'PUT'
      });
      await fetchUnreadCount();
      await fetchNotifications({ status: 'unread' });
    } catch (error) {
      console.error('Failed to mark as read:', error);
    }
  };

  // Mark all as read
  const markAllAsRead = async () => {
    try {
      await fetch('/notifications/read-all', {
        method: 'PUT'
      });
      await fetchUnreadCount();
      await fetchNotifications();
    } catch (error) {
      console.error('Failed to mark all as read:', error);
    }
  };

  // Delete notification
  const deleteNotification = async (notificationId) => {
    try {
      await fetch(`/notifications/${notificationId}`, {
        method: 'DELETE'
      });
      await fetchNotifications();
      await fetchUnreadCount();
    } catch (error) {
      console.error('Failed to delete notification:', error);
    }
  };

  // Poll for new notifications
  useEffect(() => {
    fetchUnreadCount();
    const interval = setInterval(fetchUnreadCount, 30000);
    return () => clearInterval(interval);
  }, []);

  return {
    notifications,
    unreadCount,
    loading,
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification
  };
}
```

### Backend Implementation Tips

```php
// Laravel Notification Example
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class BadgeEarnedNotification extends Notification
{
    protected $badge;

    public function __construct($badge)
    {
        $this->badge = $badge;
    }

    // Notification channels
    public function via($notifiable)
    {
        $channels = ['database']; // Always store in database

        // Check user preferences
        if ($notifiable->notificationPreferences->email_notifications['badge_earned'] ?? true) {
            $channels[] = 'mail';
        }

        if ($notifiable->notificationPreferences->push_notifications['badge_earned'] ?? true) {
            $channels[] = 'fcm'; // Firebase Cloud Messaging
        }

        return $channels;
    }

    // Database notification
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'badge_earned',
            'title' => 'Lencana Baru Diperoleh!',
            'message' => "Selamat! Anda telah mendapatkan lencana '{$this->badge->name}'",
            'data' => [
                'badge_id' => $this->badge->id,
                'badge_name' => $this->badge->name,
                'badge_description' => $this->badge->description,
                'badge_icon' => $this->badge->icon_url,
            ]
        ];
    }

    // Email notification
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Lencana Baru Diperoleh!')
            ->line("Selamat! Anda telah mendapatkan lencana '{$this->badge->name}'")
            ->line($this->badge->description)
            ->action('Lihat Lencana', url("/badges/{$this->badge->id}"));
    }
}
```

---

## 📊 PERFORMANCE OPTIMIZATION

### Caching Strategy

```javascript
// Cache unread count for 30 seconds
const CACHE_TTL = 30000; // 30 seconds
let cachedUnreadCount = null;
let cacheTimestamp = null;

async function getUnreadCount(forceRefresh = false) {
  const now = Date.now();
  
  // Return cached value if still valid
  if (!forceRefresh && cachedUnreadCount !== null && 
      cacheTimestamp && (now - cacheTimestamp) < CACHE_TTL) {
    return cachedUnreadCount;
  }
  
  // Fetch fresh data
  const response = await fetch('/notifications/unread-count');
  const data = await response.json();
  
  // Update cache
  cachedUnreadCount = data.data.unread_count;
  cacheTimestamp = now;
  
  return cachedUnreadCount;
}
```

### Batch Operations

```javascript
// Mark multiple notifications as read
async function markMultipleAsRead(notificationIds) {
  // Instead of calling PUT /notifications/{id}/read multiple times,
  // use mark all as read if marking many notifications
  
  if (notificationIds.length > 5) {
    // Use bulk operation
    await fetch('/notifications/read-all', { method: 'PUT' });
  } else {
    // Mark individually
    await Promise.all(
      notificationIds.map(id => 
        fetch(`/notifications/${id}/read`, { method: 'PUT' })
      )
    );
  }
}
```

---

## 🎨 UI/UX RECOMMENDATIONS

### Notification List UI

```
┌─────────────────────────────────────┐
│  Notifikasi              [12]       │
├─────────────────────────────────────┤
│  [Filter: Semua ▼]  [Tandai Semua] │
├─────────────────────────────────────┤
│  ● Lencana Baru Diperoleh!         │
│    Selamat! Anda telah mendapat... │
│    5 menit yang lalu                │
├─────────────────────────────────────┤
│  ○ Tugas Telah Dinilai             │
│    Tugas 'Introduction to...'      │
│    2 jam yang lalu                  │
├─────────────────────────────────────┤
│  ○ Level Up!                        │
│    Selamat! Anda naik ke Level 5   │
│    1 hari yang lalu                 │
└─────────────────────────────────────┘

Legend:
● = Unread (bold text)
○ = Read (normal text)
```

### Notification Badge

```
┌─────┐
│ 🔔  │  <- Show badge with count
│ 12  │
└─────┘

Rules:
- Show count if > 0
- Show "99+" if count > 99
- Hide badge if count = 0
- Update in real-time
```

### Notification Actions

```
Swipe Left:  [Delete]
Swipe Right: [Mark as Read]
Tap:         Open detail
Long Press:  Show options menu
```

---

**Dokumentasi ini mencakup semua endpoint notifikasi yang tersedia di Levl API.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
```
