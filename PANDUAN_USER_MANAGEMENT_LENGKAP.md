# Panduan Lengkap User Management untuk UI/UX

Dokumentasi ini berisi spesifikasi lengkap untuk semua form dan operasi user management dari sisi Management (Superadmin, Admin).

---

## Daftar Isi

1. [Create User (Buat User Baru)](#1-create-user-buat-user-baru)
2. [List Users (Daftar User)](#2-list-users-daftar-user)
3. [Show User Detail](#3-show-user-detail)
4. [Update User Status](#4-update-user-status)
5. [Delete User](#5-delete-user)
6. [Bulk Operations](#6-bulk-operations)

---

## 1. CREATE USER (Buat User Baru)

### Endpoint
```
POST /api/v1/users
```

### Authorization
- Role: Admin atau Superadmin
- Admin dapat membuat: Student, Instructor, Admin
- Superadmin dapat membuat: Student, Instructor, Admin, Superadmin

### Content-Type
`application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Nilai Default | Keterangan |
|-------|------|----------|----------|---------------|------------|
| `name` | string | ✅ Ya | max:255 | - | Nama lengkap user |
| `email` | string | ✅ Ya | email, unique, max:255 | - | Email user (harus unique) |
| `username` | string | ❌ Tidak | min:3, max:255, unique, regex | Auto-generate | Username (auto-generate jika kosong) |
| `password` | string | ❌ Tidak | min:8 | Auto-generate | Password (auto-generate jika kosong) |
| `role` | enum | ✅ Ya | Student, Instructor, Admin, Superadmin | - | Role user |
| `specialization_id` | integer | Conditional | exists:categories,id | null | **Required jika role=Instructor** |

### Nilai Enum

#### role
- `Student` - Siswa/Peserta
- `Instructor` - Pengajar/Instruktur
- `Admin` - Administrator
- `Superadmin` - Super Administrator

### Validasi Username
- Regex: `/^[a-z0-9_\.\-]+$/i`
- Karakter yang diperbolehkan: huruf (a-z, A-Z), angka (0-9), underscore (_), titik (.), dash (-)
- Minimal 3 karakter
- Maksimal 255 karakter
- Harus unique

### Auto-Generation Logic

#### Username Auto-Generate
Jika `username` tidak diisi, sistem akan generate otomatis:
1. Ambil dari `name` (sanitize: lowercase, hapus karakter spesial)
2. Jika name kosong/invalid, ambil dari email prefix
3. Cek uniqueness, jika sudah ada tambahkan counter (contoh: `john_doe1`, `john_doe2`)

**Contoh**:
- Name: "John Doe" → Username: `john_doe`
- Name: "Jane O'Brien" → Username: `jane_obrien`
- Email: "test@example.com" → Username: `test`

#### Password Auto-Generate
Jika `password` tidak diisi, sistem akan generate random 12 karakter.

### Contoh Request

#### 1. Create Student (Auto-Generate Username & Password)
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "role": "Student"
}
```

**Response**:
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 123,
    "name": "John Doe",
    "username": "john_doe",
    "email": "john.doe@example.com",
    "role": "Student",
    "status": "Active",
    "is_password_set": false,
    "created_at": "2026-03-06T10:00:00Z"
  }
}
```

#### 2. Create Student (Custom Username)
```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "username": "janesmith",
  "role": "Student"
}
```

#### 3. Create Student (Custom Username & Password)
```json
{
  "name": "Bob Wilson",
  "email": "bob.wilson@example.com",
  "username": "bobwilson",
  "password": "SecurePass123!",
  "role": "Student"
}
```

#### 4. Create Instructor (Requires Specialization)
```json
{
  "name": "Dr. Alice Brown",
  "email": "alice.brown@example.com",
  "role": "Instructor",
  "specialization_id": 5
}
```

#### 5. Create Admin
```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "username": "adminuser",
  "role": "Admin"
}
```

### Email Notification
Setelah user dibuat, sistem akan otomatis mengirim email berisi:
- Username
- Password (temporary)
- Link login
- Instruksi untuk ganti password

### Catatan Penting
- Username dan password akan dikirim via email
- User harus ganti password saat first login (`is_password_set` = false)
- Specialization wajib diisi untuk Instructor
- Email harus unique di seluruh sistem
- Username harus unique di seluruh sistem

---

## 2. LIST USERS (Daftar User)

### Endpoint
```
GET /api/v1/users
```

### Authorization
- Role: Admin atau Superadmin

### Query Parameters

| Parameter | Tipe | Required | Default | Keterangan |
|-----------|------|----------|---------|------------|
| `per_page` | integer | ❌ Tidak | 15 | Jumlah data per halaman |
| `page` | integer | ❌ Tidak | 1 | Nomor halaman |
| `search` | string | ❌ Tidak | - | Pencarian (name, email, username) |
| `role` | string | ❌ Tidak | - | Filter by role |
| `status` | string | ❌ Tidak | - | Filter by status |
| `sort_by` | string | ❌ Tidak | created_at | Field untuk sorting |
| `sort_order` | string | ❌ Tidak | desc | asc atau desc |

### Filter Options

#### role
- `Student`
- `Instructor`
- `Admin`
- `Superadmin`

#### status
- `Active` - User aktif
- `Inactive` - User non-aktif
- `Suspended` - User di-suspend
- `Pending` - User pending approval

### Contoh Request

#### 1. Get All Users (Default)
```
GET /api/v1/users
```

#### 2. Search Users
```
GET /api/v1/users?search=john
```

#### 3. Filter by Role
```
GET /api/v1/users?role=Student
```

#### 4. Filter by Status
```
GET /api/v1/users?status=Active
```

#### 5. Kombinasi Filter + Search + Pagination
```
GET /api/v1/users?search=john&role=Student&status=Active&per_page=20&page=1
```

#### 6. Sorting
```
GET /api/v1/users?sort_by=name&sort_order=asc
```

### Response Format

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "username": "john_doe",
      "email": "john.doe@example.com",
      "role": "Student",
      "status": "Active",
      "avatar": "https://example.com/avatars/john.jpg",
      "created_at": "2026-01-15T10:00:00Z",
      "last_login": "2026-03-06T08:30:00Z"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "username": "jane_smith",
      "email": "jane.smith@example.com",
      "role": "Instructor",
      "status": "Active",
      "specialization": {
        "id": 5,
        "name": "Web Development"
      },
      "avatar": null,
      "created_at": "2026-01-20T14:00:00Z",
      "last_login": "2026-03-05T16:45:00Z"
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

### Catatan Penting
- Data di-paginate secara default
- Search akan mencari di field: name, email, username
- Sorting bisa dilakukan pada field: name, email, created_at, last_login
- Admin hanya bisa melihat user dengan role Student, Instructor, Admin
- Superadmin bisa melihat semua user termasuk Superadmin lain

---

## 3. SHOW USER DETAIL

### Endpoint
```
GET /api/v1/users/{user_id}
```

### Authorization
- Role: Admin atau Superadmin

### Query Parameters (Optional Includes)

| Parameter | Tipe | Required | Keterangan |
|-----------|------|----------|------------|
| `include` | string | ❌ Tidak | Comma-separated: courses, enrollments, submissions, activities |

### Contoh Request

#### 1. Get Basic User Detail
```
GET /api/v1/users/123
```

#### 2. Get User with Courses
```
GET /api/v1/users/123?include=courses
```

#### 3. Get User with Multiple Includes
```
GET /api/v1/users/123?include=courses,enrollments,activities
```

### Response Format

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    "id": 123,
    "name": "John Doe",
    "username": "john_doe",
    "email": "john.doe@example.com",
    "role": "Student",
    "status": "Active",
    "avatar": "https://example.com/avatars/john.jpg",
    "bio": "Passionate learner interested in web development",
    "phone": "+62812345678",
    "location": "Jakarta, Indonesia",
    "is_password_set": true,
    "email_verified_at": "2026-01-15T10:30:00Z",
    "created_at": "2026-01-15T10:00:00Z",
    "updated_at": "2026-03-06T08:30:00Z",
    "last_login": "2026-03-06T08:30:00Z",
    "statistics": {
      "total_courses": 5,
      "completed_courses": 2,
      "in_progress_courses": 3,
      "total_assignments": 15,
      "completed_assignments": 10,
      "total_quizzes": 20,
      "completed_quizzes": 18
    }
  }
}
```

### Response dengan Include

#### Include: courses
```json
{
  "data": {
    "id": 123,
    "name": "John Doe",
    ...
    "courses": [
      {
        "id": 1,
        "title": "Web Development Basics",
        "slug": "web-development-basics",
        "enrollment_date": "2026-01-20T10:00:00Z",
        "progress": 65,
        "status": "in_progress"
      }
    ]
  }
}
```

### Catatan Penting
- Admin tidak bisa melihat detail Superadmin
- Superadmin bisa melihat detail semua user
- Include parameter bersifat optional untuk mengurangi payload

---

## 4. UPDATE USER STATUS

### Endpoint
```
PUT /api/v1/users/{user_id}
```

### Authorization
- Role: Admin atau Superadmin

### Content-Type
`application/json`

### Field Spesifikasi

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| `status` | enum | ✅ Ya | Active, Inactive, Suspended | Status user baru |

### Nilai Enum

#### status
- `Active` - User aktif, bisa login dan akses sistem
- `Inactive` - User non-aktif, tidak bisa login
- `Suspended` - User di-suspend, tidak bisa login (temporary)

### Contoh Request

#### 1. Activate User
```json
{
  "status": "Active"
}
```

#### 2. Deactivate User
```json
{
  "status": "Inactive"
}
```

#### 3. Suspend User
```json
{
  "status": "Suspended"
}
```

### Response Format

```json
{
  "success": true,
  "message": "User status updated successfully",
  "data": {
    "id": 123,
    "name": "John Doe",
    "username": "john_doe",
    "email": "john.doe@example.com",
    "role": "Student",
    "status": "Suspended",
    "updated_at": "2026-03-06T10:00:00Z"
  }
}
```

### Validasi Khusus
- Status tidak bisa diubah ke `Pending`
- User dengan status `Pending` tidak bisa diubah statusnya
- Admin tidak bisa mengubah status Superadmin
- User tidak bisa mengubah status diri sendiri

### Catatan Penting
- Perubahan status langsung berlaku
- User yang Inactive/Suspended tidak bisa login
- User yang Suspended bisa di-activate kembali
- Email notification akan dikirim ke user saat status berubah

---

## 5. DELETE USER

### Endpoint
```
DELETE /api/v1/users/{user_id}
```

### Authorization
- Role: **Superadmin only**

### Contoh Request

```
DELETE /api/v1/users/123
```

### Response Format

```json
{
  "success": true,
  "message": "User deleted successfully",
  "data": null
}
```

### Validasi Khusus
- Hanya Superadmin yang bisa delete user
- User tidak bisa delete diri sendiri
- Admin tidak bisa delete Superadmin

### Catatan Penting
- Delete bersifat **soft delete** (data tidak benar-benar dihapus)
- Data user masih bisa di-restore jika diperlukan
- Semua relasi user (enrollments, submissions) tetap tersimpan
- User yang di-delete tidak bisa login

---

## 6. BULK OPERATIONS

### 6.1 Bulk Export Users

#### Endpoint
```
POST /api/v1/users/bulk/export
```

#### Authorization
- Role: Admin atau Superadmin

#### Content-Type
`application/json`

#### Request Body

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `filters` | object | ❌ Tidak | Filter untuk export (role, status, search) |
| `format` | enum | ❌ Tidak | csv, xlsx (default: csv) |

#### Contoh Request

```json
{
  "filters": {
    "role": "Student",
    "status": "Active"
  },
  "format": "xlsx"
}
```

#### Response Format

```json
{
  "success": true,
  "message": "Export queued successfully",
  "data": {
    "job_id": "export-123456",
    "status": "queued",
    "download_url": null
  }
}
```

**Catatan**: Export dilakukan secara asynchronous. Download URL akan tersedia setelah proses selesai.

---

### 6.2 Bulk Activate Users

#### Endpoint
```
POST /api/v1/users/bulk/activate
```

#### Authorization
- Role: Admin atau Superadmin

#### Content-Type
`application/json`

#### Request Body

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `user_ids` | array | ✅ Ya | Array of user IDs |

#### Contoh Request

```json
{
  "user_ids": [123, 456, 789]
}
```

#### Response Format

```json
{
  "success": true,
  "message": "3 users activated successfully",
  "data": {
    "activated": 3,
    "failed": 0,
    "errors": []
  }
}
```

---

### 6.3 Bulk Deactivate Users

#### Endpoint
```
POST /api/v1/users/bulk/deactivate
```

#### Authorization
- Role: Admin atau Superadmin

#### Content-Type
`application/json`

#### Request Body

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `user_ids` | array | ✅ Ya | Array of user IDs |

#### Contoh Request

```json
{
  "user_ids": [123, 456, 789]
}
```

#### Response Format

```json
{
  "success": true,
  "message": "3 users deactivated successfully",
  "data": {
    "deactivated": 3,
    "failed": 0,
    "errors": []
  }
}
```

---

### 6.4 Bulk Delete Users

#### Endpoint
```
DELETE /api/v1/users/bulk/delete
```

#### Authorization
- Role: **Superadmin only**

#### Content-Type
`application/json`

#### Request Body

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `user_ids` | array | ✅ Ya | Array of user IDs |

#### Contoh Request

```json
{
  "user_ids": [123, 456, 789]
}
```

#### Response Format

```json
{
  "success": true,
  "message": "3 users deleted successfully",
  "data": {
    "deleted": 3,
    "failed": 0,
    "errors": []
  }
}
```

#### Validasi Khusus
- Hanya Superadmin yang bisa bulk delete
- User yang sedang login tidak bisa di-delete
- Superadmin tidak bisa di-delete via bulk operation

---

## Catatan Umum

### Authorization Matrix

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| Create User | ❌ | ❌ | ✅ (Student, Instructor, Admin) | ✅ (All roles) |
| List Users | ❌ | ❌ | ✅ | ✅ |
| Show User Detail | ❌ | ❌ | ✅ | ✅ |
| Update User Status | ❌ | ❌ | ✅ | ✅ |
| Delete User | ❌ | ❌ | ❌ | ✅ |
| Bulk Operations | ❌ | ❌ | ✅ | ✅ |

### Response Format Standar

#### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "meta": { ... }
}
```

#### Error Response
```json
{
  "success": false,
  "message": "Validation error",
  "data": null,
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### HTTP Status Codes
- `200` - Success (GET, PUT)
- `201` - Created (POST)
- `400` - Bad Request
- `401` - Unauthorized (tidak login)
- `403` - Forbidden (tidak punya akses)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

### Tips untuk UI/UX

1. **Form Create User**:
   - Username dan password bisa dikosongkan (auto-generate)
   - Tampilkan info bahwa credentials akan dikirim via email
   - Specialization field muncul conditional jika role = Instructor
   - Validasi email dan username real-time (check uniqueness)

2. **List Users**:
   - Implementasi search dengan debounce (300ms)
   - Filter dropdown untuk role dan status
   - Pagination dengan infinite scroll atau numbered pages
   - Bulk selection dengan checkbox
   - Action buttons: Edit, Activate/Deactivate, Delete

3. **User Detail**:
   - Tab navigation untuk: Profile, Courses, Activities, Statistics
   - Avatar upload dengan preview
   - Status badge dengan warna (Active=green, Inactive=gray, Suspended=red)
   - Last login timestamp

4. **Bulk Operations**:
   - Confirmation modal sebelum execute
   - Progress indicator untuk bulk operations
   - Success/error summary setelah operation
   - Undo option untuk bulk deactivate

5. **Status Management**:
   - Toggle switch untuk Active/Inactive
   - Confirmation modal untuk Suspend
   - Visual indicator untuk status changes
   - Email notification checkbox

### Workflow Rekomendasi

#### Create User Flow
1. Admin klik "Add User"
2. Form muncul dengan fields: name, email, role
3. Jika role = Instructor, tampilkan specialization dropdown
4. Username dan password optional (auto-generate)
5. Submit → Show success message
6. Tampilkan generated credentials (jika auto-generate)
7. Konfirmasi email sudah dikirim

#### Bulk Operations Flow
1. Admin select multiple users (checkbox)
2. Pilih action dari dropdown (Activate/Deactivate/Delete/Export)
3. Confirmation modal muncul
4. Execute operation
5. Show progress indicator
6. Show result summary (success/failed count)
7. Refresh list

### Security Considerations

1. **Password Security**:
   - Auto-generated password: 12 karakter random
   - Password di-hash menggunakan bcrypt
   - User harus ganti password saat first login

2. **Email Verification**:
   - Email verification link dikirim otomatis
   - User bisa resend verification email
   - Unverified user bisa login tapi dengan limited access

3. **Role-Based Access**:
   - Admin tidak bisa create/edit/delete Superadmin
   - User tidak bisa edit role sendiri
   - Audit log untuk semua user management operations

4. **Data Privacy**:
   - Sensitive data (password) tidak pernah di-return di API
   - Email dan phone di-mask untuk non-admin
   - GDPR compliance untuk data export/delete

---

## Error Handling

### Common Errors

#### 1. Email Already Exists
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

#### 2. Username Already Exists
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "username": ["The username has already been taken."]
  }
}
```

#### 3. Invalid Role
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "role": ["The selected role is invalid."]
  }
}
```

#### 4. Missing Specialization for Instructor
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "specialization_id": ["The specialization field is required when role is Instructor."]
  }
}
```

#### 5. Unauthorized Access
```json
{
  "success": false,
  "message": "Forbidden",
  "errors": {
    "authorization": ["You do not have permission to perform this action."]
  }
}
```

#### 6. Cannot Delete Self
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "account": ["You cannot delete your own account."]
  }
}
```

---

## Changelog

### Version 1.0 (6 Maret 2026)
- Initial release
- Create user dengan auto-generation username & password
- List users dengan filter dan search
- Show user detail dengan includes
- Update user status
- Delete user (soft delete)
- Bulk operations (export, activate, deactivate, delete)

---

**Versi**: 1.0  
**Terakhir Update**: 6 Maret 2026  
**Kontak**: Backend Team
