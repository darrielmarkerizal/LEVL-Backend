# STRUKTUR DOKUMENTASI POSTMAN - LEVL API
**Versi**: 1.0  
**Tanggal**: 2026-03-14  
**Platform**: Mobile App, Admin Web, Instructor Web

---

## 📋 DAFTAR ISI

1. [Konsep Struktur](#konsep-struktur)
2. [Organisasi Folder](#organisasi-folder)
3. [Konvensi Penamaan](#konvensi-penamaan)
4. [Environment Variables](#environment-variables)
5. [Struktur Collection](#struktur-collection)
6. [Panduan Penggunaan](#panduan-penggunaan)

---

## 🎯 KONSEP STRUKTUR

### Prinsip Organisasi
1. **Platform-First**: Organisasi berdasarkan platform pengguna
2. **Shared Resources**: API yang digunakan semua platform di folder terpisah
3. **Module-Based**: Pengelompokan berdasarkan modul/fitur
4. **Role-Based**: Pembagian berdasarkan role user (Admin, Instructor, Student)

### Keuntungan Struktur Ini
- ✅ FE/Mobile dev langsung tahu endpoint mana yang mereka butuhkan
- ✅ BE dev mudah maintain karena terorganisir per module
- ✅ Tidak ada duplikasi dokumentasi
- ✅ Jelas mana API yang shared, mana yang spesifik platform
- ✅ Mudah untuk onboarding developer baru

---

## 📁 ORGANISASI FOLDER

```
Levl-API/
│
├── 📱 [MOBILE] Student App/
│   ├── 🔐 Authentication/
│   ├── 📚 Learning/
│   ├── 🎮 Gamification/
│   ├── 💬 Forums/
│   ├── 📊 Dashboard/
│   └── 👤 Profile/
│
├── 💻 [WEB] Admin Dashboard/
│   ├── 🔐 Authentication/
│   ├── 👥 User Management/
│   ├── 📖 Course Management/
│   ├── 📝 Content Management/
│   ├── 📊 Reports & Analytics/
│   ├── 🎯 Enrollment Management/
│   ├── 🎮 Gamification Management/
│   └── 🗑️ Trash Management/
│
├── 🎓 [WEB] Instructor Dashboard/
│   ├── 🔐 Authentication/
│   ├── 📖 My Courses/
│   ├── 📝 Content Creation/
│   ├── ✅ Grading/
│   ├── 💬 Forums/
│   ├── 📊 Course Analytics/
│   └── 👤 Profile/
│
├── 🌐 [SHARED] Common APIs/
│   ├── 🔐 Auth (Login, Register, Logout)/
│   ├── 👤 Profile Management/
│   ├── 🔔 Notifications/
│   ├── 🔍 Search/
│   ├── 📁 Media Upload/
│   └── ⚙️ System Settings/
│
└── 📚 [REFERENCE] Documentation/
    ├── 📖 API Overview/
    ├── 🔑 Authentication Guide/
    ├── 📝 Request/Response Format/
    ├── ⚠️ Error Codes/
    └── 🚀 Quick Start Guide/
```

---

## 🏷️ KONVENSI PENAMAAN

### Format Nama Request
```
[METHOD] [Platform] - [Feature] - [Action]
```

### Contoh:
- ✅ `GET [Mobile] - Courses - List My Courses`
- ✅ `POST [Admin] - Users - Create Student`
- ✅ `PUT [Instructor] - Assignments - Update`
- ✅ `GET [Shared] - Profile - Get Current User`

### Label Platform:
- `[Mobile]` - Khusus mobile app
- `[Admin]` - Khusus admin web
- `[Instructor]` - Khusus instructor web
- `[Shared]` - Digunakan semua platform
- `[All]` - Alias untuk Shared

### Label Role (Optional):
- `[Student]` - Khusus role student
- `[Admin]` - Khusus role admin
- `[Instructor]` - Khusus role instructor

---

## 🔧 ENVIRONMENT VARIABLES

### Environments yang Dibutuhkan

#### 1. Development
```json
{
  "base_url": "http://localhost:8000/api",
  "auth_token": "",
  "user_id": "",
  "role": "student"
}
```

#### 2. Staging
```json
{
  "base_url": "https://staging-api.levl.id/api",
  "auth_token": "",
  "user_id": "",
  "role": "student"
}
```

#### 3. Production
```json
{
  "base_url": "https://api.levl.id/api",
  "auth_token": "",
  "user_id": "",
  "role": "student"
}
```

### Variables yang Digunakan
- `{{base_url}}` - Base URL API
- `{{auth_token}}` - Bearer token untuk authentication
- `{{user_id}}` - ID user yang sedang login
- `{{role}}` - Role user (student, instructor, admin)
- `{{course_id}}` - ID course untuk testing
- `{{unit_id}}` - ID unit untuk testing
- `{{lesson_id}}` - ID lesson untuk testing

---

## 📦 STRUKTUR COLLECTION DETAIL

### 1. 📱 [MOBILE] Student App

#### 🔐 Authentication
- `POST [Mobile] - Auth - Login`
- `POST [Mobile] - Auth - Register Student`
- `POST [Mobile] - Auth - Logout`
- `POST [Mobile] - Auth - Refresh Token`
- `POST [Mobile] - Auth - Forgot Password`
- `POST [Mobile] - Auth - Reset Password`

#### 📚 Learning
- `GET [Mobile] - Courses - List Enrolled Courses`
- `GET [Mobile] - Courses - Get Course Detail`
- `GET [Mobile] - Units - List Course Units`
- `GET [Mobile] - Lessons - Get Lesson Detail`
- `POST [Mobile] - Lessons - Mark as Complete`
- `GET [Mobile] - Assignments - List My Assignments`
- `POST [Mobile] - Assignments - Submit Assignment`
- `GET [Mobile] - Quizzes - Get Quiz Detail`
- `POST [Mobile] - Quizzes - Submit Quiz`

#### 🎮 Gamification
- `GET [Mobile] - Gamification - My Stats`
- `GET [Mobile] - Gamification - My Badges`
- `GET [Mobile] - Gamification - Leaderboard`
- `GET [Mobile] - Gamification - My Rank`
- `GET [Mobile] - Gamification - XP History`

#### 💬 Forums
- `GET [Mobile] - Forums - List Threads`
- `POST [Mobile] - Forums - Create Thread`
- `GET [Mobile] - Forums - Thread Detail`
- `POST [Mobile] - Forums - Reply to Thread`
- `POST [Mobile] - Forums - React to Post`

#### 📊 Dashboard
- `GET [Mobile] - Dashboard - Overview`
- `GET [Mobile] - Dashboard - Recent Activities`
- `GET [Mobile] - Dashboard - Progress Summary`

#### 👤 Profile
- `GET [Mobile] - Profile - Get My Profile`
- `PUT [Mobile] - Profile - Update Profile`
- `POST [Mobile] - Profile - Upload Avatar`
- `PUT [Mobile] - Profile - Change Password`

---

### 2. 💻 [WEB] Admin Dashboard

#### 🔐 Authentication
- `POST [Admin] - Auth - Login`
- `POST [Admin] - Auth - Logout`
- `POST [Admin] - Auth - Refresh Token`

#### 👥 User Management
- `GET [Admin] - Users - List All Users`
- `POST [Admin] - Users - Create Student`
- `POST [Admin] - Users - Create Instructor`
- `POST [Admin] - Users - Create Admin`
- `GET [Admin] - Users - Get User Detail`
- `PUT [Admin] - Users - Update User`
- `DELETE [Admin] - Users - Delete User`
- `POST [Admin] - Users - Bulk Import`

#### 📖 Course Management
- `GET [Admin] - Courses - List All Courses`
- `POST [Admin] - Courses - Create Course`
- `GET [Admin] - Courses - Get Course Detail`
- `PUT [Admin] - Courses - Update Course`
- `DELETE [Admin] - Courses - Delete Course`
- `POST [Admin] - Courses - Publish Course`
- `POST [Admin] - Courses - Unpublish Course`

#### 📝 Content Management
- `GET [Admin] - Content - List Units`
- `POST [Admin] - Content - Create Unit`
- `PUT [Admin] - Content - Update Unit`
- `DELETE [Admin] - Content - Delete Unit`
- `POST [Admin] - Content - Reorder Units`
- `GET [Admin] - Content - List Lessons`
- `POST [Admin] - Content - Create Lesson`
- `PUT [Admin] - Content - Update Lesson`
- `DELETE [Admin] - Content - Delete Lesson`

#### 📊 Reports & Analytics
- `GET [Admin] - Reports - User Statistics`
- `GET [Admin] - Reports - Course Statistics`
- `GET [Admin] - Reports - Enrollment Statistics`
- `GET [Admin] - Reports - Completion Rates`
- `GET [Admin] - Reports - Export Data`

#### 🎯 Enrollment Management
- `GET [Admin] - Enrollments - List All Enrollments`
- `POST [Admin] - Enrollments - Enroll Student`
- `PUT [Admin] - Enrollments - Update Enrollment Status`
- `DELETE [Admin] - Enrollments - Remove Enrollment`
- `POST [Admin] - Enrollments - Bulk Enroll`

#### 🎮 Gamification Management
- `GET [Admin] - Gamification - List Badges`
- `POST [Admin] - Gamification - Create Badge`
- `PUT [Admin] - Gamification - Update Badge`
- `DELETE [Admin] - Gamification - Delete Badge`
- `GET [Admin] - Gamification - Badge Rules`
- `POST [Admin] - Gamification - Create Badge Rule`
- `GET [Admin] - Gamification - XP Sources`
- `PUT [Admin] - Gamification - Update XP Source`

#### 🗑️ Trash Management
- `GET [Admin] - Trash - List Deleted Items`
- `POST [Admin] - Trash - Restore Item`
- `DELETE [Admin] - Trash - Permanent Delete`
- `POST [Admin] - Trash - Empty Trash`

---

### 3. 🎓 [WEB] Instructor Dashboard

#### 🔐 Authentication
- `POST [Instructor] - Auth - Login`
- `POST [Instructor] - Auth - Logout`
- `POST [Instructor] - Auth - Refresh Token`

#### 📖 My Courses
- `GET [Instructor] - Courses - List My Courses`
- `GET [Instructor] - Courses - Get Course Detail`
- `GET [Instructor] - Courses - Course Statistics`
- `GET [Instructor] - Courses - Enrolled Students`

#### 📝 Content Creation
- `POST [Instructor] - Content - Create Unit`
- `PUT [Instructor] - Content - Update Unit`
- `POST [Instructor] - Content - Create Lesson`
- `PUT [Instructor] - Content - Update Lesson`
- `POST [Instructor] - Content - Create Assignment`
- `PUT [Instructor] - Content - Update Assignment`
- `POST [Instructor] - Content - Create Quiz`
- `PUT [Instructor] - Content - Update Quiz`

#### ✅ Grading
- `GET [Instructor] - Grading - List Submissions`
- `GET [Instructor] - Grading - Get Submission Detail`
- `POST [Instructor] - Grading - Grade Submission`
- `PUT [Instructor] - Grading - Update Grade`
- `POST [Instructor] - Grading - Release Grades`
- `POST [Instructor] - Grading - Add Feedback`

#### 💬 Forums
- `GET [Instructor] - Forums - List Course Forums`
- `GET [Instructor] - Forums - Thread Detail`
- `POST [Instructor] - Forums - Reply to Thread`
- `POST [Instructor] - Forums - Pin Thread`
- `DELETE [Instructor] - Forums - Delete Thread`

#### 📊 Course Analytics
- `GET [Instructor] - Analytics - Course Overview`
- `GET [Instructor] - Analytics - Student Progress`
- `GET [Instructor] - Analytics - Completion Rates`
- `GET [Instructor] - Analytics - Assignment Statistics`
- `GET [Instructor] - Analytics - Quiz Statistics`

#### 👤 Profile
- `GET [Instructor] - Profile - Get My Profile`
- `PUT [Instructor] - Profile - Update Profile`
- `POST [Instructor] - Profile - Upload Avatar`

---

### 4. 🌐 [SHARED] Common APIs

#### 🔐 Auth (Login, Register, Logout)
- `POST [Shared] - Auth - Login`
- `POST [Shared] - Auth - Register`
- `POST [Shared] - Auth - Logout`
- `POST [Shared] - Auth - Refresh Token`
- `POST [Shared] - Auth - Forgot Password`
- `POST [Shared] - Auth - Reset Password`
- `POST [Shared] - Auth - Verify Email`
- `GET [Shared] - Auth - Get Current User`

#### 👤 Profile Management
- `GET [Shared] - Profile - Get My Profile`
- `PUT [Shared] - Profile - Update Profile`
- `POST [Shared] - Profile - Upload Avatar`
- `PUT [Shared] - Profile - Change Password`
- `PUT [Shared] - Profile - Update Preferences`

#### 🔔 Notifications
- `GET [Shared] - Notifications - List Notifications`
- `GET [Shared] - Notifications - Unread Count`
- `PUT [Shared] - Notifications - Mark as Read`
- `PUT [Shared] - Notifications - Mark All as Read`
- `DELETE [Shared] - Notifications - Delete Notification`

#### 🔍 Search
- `GET [Shared] - Search - Global Search`
- `GET [Shared] - Search - Search Courses`
- `GET [Shared] - Search - Search Users`
- `GET [Shared] - Search - Search Content`

#### 📁 Media Upload
- `POST [Shared] - Media - Upload Image`
- `POST [Shared] - Media - Upload Document`
- `POST [Shared] - Media - Upload Video`
- `DELETE [Shared] - Media - Delete Media`
- `GET [Shared] - Media - Get Media URL`

#### ⚙️ System Settings
- `GET [Shared] - Settings - Get App Settings`
- `GET [Shared] - Settings - Get Level Configs`
- `GET [Shared] - Settings - Get XP Sources`

---

### 5. 📚 [REFERENCE] Documentation

#### 📖 API Overview
- Penjelasan umum tentang API
- Base URL dan versioning
- Rate limiting
- Pagination

#### 🔑 Authentication Guide
- Cara login dan mendapatkan token
- Cara menggunakan Bearer token
- Token refresh mechanism
- Logout process

#### 📝 Request/Response Format
- Standard request format
- Standard response format
- Success response structure
- Error response structure
- Pagination format

#### ⚠️ Error Codes
- HTTP status codes
- Custom error codes
- Error messages
- Troubleshooting guide

#### 🚀 Quick Start Guide
- Setup environment
- First API call
- Common workflows
- Best practices

---

## 📖 PANDUAN PENGGUNAAN

### Untuk Mobile Developer

1. **Fokus pada folder**: `📱 [MOBILE] Student App`
2. **Gunakan juga**: `🌐 [SHARED] Common APIs` untuk fitur umum
3. **Environment**: Gunakan `Development` atau `Staging`
4. **Role**: Set `role` variable ke `student`

### Untuk Admin Web Developer

1. **Fokus pada folder**: `💻 [WEB] Admin Dashboard`
2. **Gunakan juga**: `🌐 [SHARED] Common APIs` untuk fitur umum
3. **Environment**: Gunakan `Development` atau `Staging`
4. **Role**: Set `role` variable ke `admin`

### Untuk Instructor Web Developer

1. **Fokus pada folder**: `🎓 [WEB] Instructor Dashboard`
2. **Gunakan juga**: `🌐 [SHARED] Common APIs` untuk fitur umum
3. **Environment**: Gunakan `Development` atau `Staging`
4. **Role**: Set `role` variable ke `instructor`

### Untuk Backend Developer

1. **Maintain semua folder** sesuai dengan module yang dikerjakan
2. **Update dokumentasi** setiap ada perubahan API
3. **Pastikan konsistensi** antara platform untuk shared APIs
4. **Test semua endpoint** sebelum merge ke main branch

---

## 🎨 TIPS ORGANISASI

### 1. Gunakan Folder Warna
- 🔴 Merah: Authentication & Security
- 🟢 Hijau: Success operations (GET, List)
- 🟡 Kuning: Update operations (PUT, PATCH)
- 🔵 Biru: Create operations (POST)
- ⚫ Hitam: Delete operations (DELETE)

### 2. Gunakan Deskripsi
Setiap request harus punya deskripsi yang jelas:
```markdown
## Deskripsi
Endpoint ini digunakan untuk mendapatkan daftar course yang sudah dienroll oleh student.

## Authorization
Bearer Token required

## Query Parameters
- `page` (optional): Halaman yang ingin ditampilkan
- `per_page` (optional): Jumlah item per halaman (default: 15)
- `status` (optional): Filter berdasarkan status (active, completed)

## Response
Returns paginated list of enrolled courses with progress information.
```

### 3. Gunakan Examples
Setiap request harus punya minimal 2 examples:
- ✅ Success Response (200)
- ❌ Error Response (400, 401, 403, 404, 500)

### 4. Gunakan Tests
Tambahkan basic tests di setiap request:
```javascript
// Test: Status code is 200
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test: Response has data
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});

// Save token for next requests
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.token);
}
```

---

## 🔄 WORKFLOW MAINTENANCE

### Saat Menambah Endpoint Baru

1. **Identifikasi platform**: Apakah Mobile, Admin, Instructor, atau Shared?
2. **Tentukan folder**: Masuk ke folder yang sesuai
3. **Buat request**: Dengan naming convention yang benar
4. **Tambahkan deskripsi**: Lengkap dengan parameters dan response
5. **Tambahkan examples**: Success dan error cases
6. **Tambahkan tests**: Basic validation tests
7. **Update dokumentasi**: Jika perlu update struktur

### Saat Update Endpoint

1. **Update request**: Sesuaikan dengan perubahan
2. **Update deskripsi**: Jika ada perubahan behavior
3. **Update examples**: Jika ada perubahan response format
4. **Update tests**: Jika ada perubahan validation
5. **Notify team**: Informasikan perubahan ke team

### Saat Deprecate Endpoint

1. **Jangan hapus**: Pindahkan ke folder `[DEPRECATED]`
2. **Tambahkan warning**: Di deskripsi endpoint
3. **Berikan alternatif**: Endpoint pengganti
4. **Set timeline**: Kapan akan dihapus permanent
5. **Notify team**: Informasikan deprecation ke team

---

## 📝 TEMPLATE REQUEST

### Template untuk GET Request
```
Nama: GET [Platform] - [Feature] - [Action]

URL: {{base_url}}/[endpoint]

Method: GET

Headers:
- Authorization: Bearer {{auth_token}}
- Accept: application/json

Query Params:
- page: 1
- per_page: 15

Description:
[Deskripsi lengkap endpoint]

Tests:
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});
```

### Template untuk POST Request
```
Nama: POST [Platform] - [Feature] - [Action]

URL: {{base_url}}/[endpoint]

Method: POST

Headers:
- Authorization: Bearer {{auth_token}}
- Accept: application/json
- Content-Type: application/json

Body (JSON):
{
  "field1": "value1",
  "field2": "value2"
}

Description:
[Deskripsi lengkap endpoint]

Tests:
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});
```

---

## 🚀 NEXT STEPS

1. **Buat Postman Collection** dengan struktur ini
2. **Import ke Postman Workspace** team
3. **Setup Environments** (Dev, Staging, Production)
4. **Populate dengan existing endpoints**
5. **Train team** cara menggunakan struktur ini
6. **Maintain consistency** dalam penambahan endpoint baru

---

## 📞 KONTAK

Jika ada pertanyaan tentang struktur dokumentasi ini:
- Backend Team Lead
- API Documentation Maintainer
- DevOps Team

---

**Catatan**: Struktur ini adalah living document dan akan terus diupdate sesuai kebutuhan project.
