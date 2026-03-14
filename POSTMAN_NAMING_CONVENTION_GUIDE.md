# PANDUAN PENAMAAN POSTMAN - LEVL API
**Versi**: 1.0  
**Tanggal**: 2026-03-14  
**Tujuan**: Konsistensi penamaan di seluruh Postman Collection

---

## 📋 FORMAT PENAMAAN REQUEST

### Format Standar
```
[METHOD] [Platform] - [Module] - [Action]
```

### Komponen
1. **METHOD**: GET, POST, PUT, PATCH, DELETE
2. **Platform**: Mobile, Admin, Instructor, Shared
3. **Module**: Nama modul/fitur
4. **Action**: Aksi yang dilakukan

---

## 🏷️ LABEL PLATFORM

### Platform Labels
```
[Mobile]      - Khusus mobile student app
[Admin]       - Khusus admin web dashboard
[Instructor]  - Khusus instructor web dashboard
[Shared]      - Digunakan semua platform
```

### Kapan Menggunakan Label Apa?

#### Gunakan [Mobile]
- Endpoint yang HANYA digunakan di mobile app
- Contoh: Fitur yang spesifik mobile seperti push notification settings

#### Gunakan [Admin]
- Endpoint yang HANYA digunakan di admin dashboard
- Contoh: User management, system settings, reports

#### Gunakan [Instructor]
- Endpoint yang HANYA digunakan di instructor dashboard
- Contoh: Grading, course analytics

#### Gunakan [Shared]
- Endpoint yang digunakan di SEMUA platform
- Contoh: Login, profile, notifications
- Endpoint yang digunakan di 2+ platform

---

## 📚 CONTOH PENAMAAN LENGKAP

### 1. Authentication (Shared)
```
✅ POST [Shared] - Auth - Login
✅ POST [Shared] - Auth - Register
✅ POST [Shared] - Auth - Logout
✅ POST [Shared] - Auth - Refresh Token
✅ POST [Shared] - Auth - Forgot Password
✅ POST [Shared] - Auth - Reset Password
✅ POST [Shared] - Auth - Verify Email
✅ GET  [Shared] - Auth - Get Current User
```

### 2. User Management (Admin)
```
✅ GET    [Admin] - Users - List All Users
✅ GET    [Admin] - Users - Get User Detail
✅ POST   [Admin] - Users - Create User
✅ PUT    [Admin] - Users - Update User
✅ DELETE [Admin] - Users - Delete User
✅ POST   [Admin] - Users - Bulk Import
✅ POST   [Admin] - Users - Export Users

✅ GET    [Admin] - Users - List Students
✅ POST   [Admin] - Users - Create Student
✅ GET    [Admin] - Users - Get Student Detail
✅ PUT    [Admin] - Users - Update Student
✅ DELETE [Admin] - Users - Delete Student

✅ GET    [Admin] - Users - List Instructors
✅ POST   [Admin] - Users - Create Instructor
✅ GET    [Admin] - Users - Get Instructor Detail
✅ PUT    [Admin] - Users - Update Instructor
✅ DELETE [Admin] - Users - Delete Instructor

✅ GET    [Admin] - Users - List Admins
✅ POST   [Admin] - Users - Create Admin
✅ GET    [Admin] - Users - Get Admin Detail
✅ PUT    [Admin] - Users - Update Admin
✅ DELETE [Admin] - Users - Delete Admin
```

### 3. Courses (Multiple Platforms)
```
// Mobile - Student view
✅ GET [Mobile] - Courses - List Enrolled Courses
✅ GET [Mobile] - Courses - Get Course Detail
✅ GET [Mobile] - Courses - Get Course Progress

// Admin - Full management
✅ GET    [Admin] - Courses - List All Courses
✅ POST   [Admin] - Courses - Create Course
✅ GET    [Admin] - Courses - Get Course Detail
✅ PUT    [Admin] - Courses - Update Course
✅ DELETE [Admin] - Courses - Delete Course
✅ POST   [Admin] - Courses - Publish Course
✅ POST   [Admin] - Courses - Unpublish Course

// Instructor - My courses
✅ GET [Instructor] - Courses - List My Courses
✅ GET [Instructor] - Courses - Get Course Detail
✅ GET [Instructor] - Courses - Get Course Statistics
```

### 4. Gamification (Mobile & Shared)
```
// Mobile specific
✅ GET [Mobile] - Gamification - My Stats
✅ GET [Mobile] - Gamification - My Badges
✅ GET [Mobile] - Gamification - My Rank
✅ GET [Mobile] - Gamification - XP History

// Admin management
✅ GET    [Admin] - Badges - List All Badges
✅ POST   [Admin] - Badges - Create Badge
✅ PUT    [Admin] - Badges - Update Badge
✅ DELETE [Admin] - Badges - Delete Badge

✅ GET    [Admin] - Levels - List Level Configs
✅ POST   [Admin] - Levels - Create Level
✅ PUT    [Admin] - Levels - Update Level
✅ DELETE [Admin] - Levels - Delete Level

✅ GET [Admin] - Leaderboard - Global Rankings
✅ GET [Admin] - Leaderboard - Course Rankings
```

### 5. Content Management
```
// Units
✅ GET    [Admin] - Content - List Units
✅ POST   [Admin] - Content - Create Unit
✅ GET    [Admin] - Content - Get Unit Detail
✅ PUT    [Admin] - Content - Update Unit
✅ DELETE [Admin] - Content - Delete Unit
✅ POST   [Admin] - Content - Reorder Units

// Lessons
✅ GET    [Admin] - Content - List Lessons
✅ POST   [Admin] - Content - Create Lesson
✅ GET    [Admin] - Content - Get Lesson Detail
✅ PUT    [Admin] - Content - Update Lesson
✅ DELETE [Admin] - Content - Delete Lesson
✅ POST   [Admin] - Content - Reorder Lessons

// Assignments
✅ GET    [Admin] - Content - List Assignments
✅ POST   [Admin] - Content - Create Assignment
✅ GET    [Admin] - Content - Get Assignment Detail
✅ PUT    [Admin] - Content - Update Assignment
✅ DELETE [Admin] - Content - Delete Assignment

// Quizzes
✅ GET    [Admin] - Content - List Quizzes
✅ POST   [Admin] - Content - Create Quiz
✅ GET    [Admin] - Content - Get Quiz Detail
✅ PUT    [Admin] - Content - Update Quiz
✅ DELETE [Admin] - Content - Delete Quiz
✅ POST   [Admin] - Content - Add Question
✅ PUT    [Admin] - Content - Update Question
✅ DELETE [Admin] - Content - Delete Question
```

### 6. Grading (Instructor)
```
✅ GET  [Instructor] - Grading - List Submissions
✅ GET  [Instructor] - Grading - Get Submission Detail
✅ POST [Instructor] - Grading - Grade Submission
✅ PUT  [Instructor] - Grading - Update Grade
✅ POST [Instructor] - Grading - Release Grades
✅ POST [Instructor] - Grading - Add Feedback
```

### 7. Forums (Multiple Platforms)
```
// Mobile
✅ GET    [Mobile] - Forums - List Threads
✅ POST   [Mobile] - Forums - Create Thread
✅ GET    [Mobile] - Forums - Get Thread Detail
✅ POST   [Mobile] - Forums - Reply to Thread
✅ POST   [Mobile] - Forums - React to Post

// Instructor
✅ GET    [Instructor] - Forums - List Course Forums
✅ POST   [Instructor] - Forums - Pin Thread
✅ POST   [Instructor] - Forums - Lock Thread
✅ DELETE [Instructor] - Forums - Delete Thread
```

### 8. Enrollments (Admin)
```
✅ GET    [Admin] - Enrollments - List All Enrollments
✅ GET    [Admin] - Enrollments - Get Enrollment Detail
✅ POST   [Admin] - Enrollments - Enroll Student
✅ PUT    [Admin] - Enrollments - Update Enrollment
✅ DELETE [Admin] - Enrollments - Remove Enrollment
✅ POST   [Admin] - Enrollments - Bulk Enroll
✅ PUT    [Admin] - Enrollments - Activate Enrollment
✅ PUT    [Admin] - Enrollments - Suspend Enrollment
```

### 9. Reports (Admin)
```
✅ GET  [Admin] - Reports - User Statistics
✅ GET  [Admin] - Reports - Course Statistics
✅ GET  [Admin] - Reports - Enrollment Statistics
✅ GET  [Admin] - Reports - Completion Rates
✅ POST [Admin] - Reports - Export User Report
✅ POST [Admin] - Reports - Export Course Report
```

### 10. Profile (Shared)
```
✅ GET    [Shared] - Profile - Get My Profile
✅ PUT    [Shared] - Profile - Update Profile
✅ POST   [Shared] - Profile - Upload Avatar
✅ DELETE [Shared] - Profile - Delete Avatar
✅ PUT    [Shared] - Profile - Change Password
```

### 11. Notifications (Shared)
```
✅ GET    [Shared] - Notifications - List Notifications
✅ GET    [Shared] - Notifications - Get Unread Count
✅ PUT    [Shared] - Notifications - Mark as Read
✅ PUT    [Shared] - Notifications - Mark All as Read
✅ DELETE [Shared] - Notifications - Delete Notification
✅ GET    [Shared] - Notifications - Get Preferences
✅ PUT    [Shared] - Notifications - Update Preferences
```

### 12. Search (Shared)
```
✅ GET    [Shared] - Search - Global Search
✅ GET    [Shared] - Search - Autocomplete
✅ GET    [Shared] - Search - Get Search History
✅ DELETE [Shared] - Search - Clear Search History
```

### 13. Media (Shared)
```
✅ POST   [Shared] - Media - Upload Image
✅ POST   [Shared] - Media - Upload Document
✅ POST   [Shared] - Media - Upload Video
✅ GET    [Shared] - Media - List My Media
✅ DELETE [Shared] - Media - Delete Media
✅ GET    [Shared] - Media - Get Media URL
```

### 14. Trash (Admin)
```
✅ GET    [Admin] - Trash - List Deleted Items
✅ GET    [Admin] - Trash - Get Item Detail
✅ PATCH  [Admin] - Trash - Restore Item
✅ DELETE [Admin] - Trash - Permanent Delete
✅ PATCH  [Admin] - Trash - Bulk Restore
✅ DELETE [Admin] - Trash - Empty Trash
```

### 15. Analytics (Instructor)
```
✅ GET  [Instructor] - Analytics - Course Overview
✅ GET  [Instructor] - Analytics - Student Progress
✅ GET  [Instructor] - Analytics - Completion Rates
✅ POST [Instructor] - Analytics - Export Report
```

---

## 🎯 ATURAN PENAMAAN

### DO's ✅

1. **Gunakan format standar**
   ```
   ✅ GET [Mobile] - Courses - List Enrolled Courses
   ```

2. **Gunakan verb yang jelas untuk action**
   ```
   ✅ List, Get, Create, Update, Delete
   ✅ Upload, Download, Export, Import
   ✅ Activate, Deactivate, Suspend
   ✅ Publish, Unpublish
   ✅ Mark, Unmark
   ✅ Pin, Unpin, Lock, Unlock
   ```

3. **Gunakan singular untuk detail, plural untuk list**
   ```
   ✅ List Users (plural)
   ✅ Get User Detail (singular)
   ✅ Create User (singular)
   ```

4. **Spesifik untuk action yang kompleks**
   ```
   ✅ POST [Admin] - Enrollments - Bulk Enroll
   ✅ POST [Admin] - Grading - Release Grades
   ✅ POST [Instructor] - Forums - Pin Thread
   ```

5. **Konsisten dengan module name**
   ```
   ✅ [Admin] - Users - ...
   ✅ [Admin] - Courses - ...
   ✅ [Admin] - Content - ...
   ```

### DON'Ts ❌

1. **Jangan gunakan singkatan**
   ```
   ❌ GET [Mobile] - Crse - Lst
   ✅ GET [Mobile] - Courses - List Enrolled Courses
   ```

2. **Jangan gunakan URL sebagai nama**
   ```
   ❌ GET /api/v1/users
   ✅ GET [Admin] - Users - List All Users
   ```

3. **Jangan inkonsisten dengan verb**
   ```
   ❌ GET [Admin] - Users - Fetch All Users
   ❌ GET [Admin] - Users - Retrieve All Users
   ✅ GET [Admin] - Users - List All Users
   ```

4. **Jangan terlalu panjang**
   ```
   ❌ GET [Admin] - Users - Get All Users From Database With Pagination
   ✅ GET [Admin] - Users - List All Users
   ```

5. **Jangan duplikasi platform di action**
   ```
   ❌ GET [Mobile] - Courses - List Mobile Courses
   ✅ GET [Mobile] - Courses - List Enrolled Courses
   ```

---

## 📊 MAPPING METHOD KE ACTION

### GET - Untuk Retrieve Data
```
✅ List ...        - Untuk collection/array
✅ Get ... Detail  - Untuk single item
✅ Get ... Count   - Untuk count
✅ Get ... Stats   - Untuk statistics
```

### POST - Untuk Create & Actions
```
✅ Create ...      - Untuk create new resource
✅ Upload ...      - Untuk upload file
✅ Import ...      - Untuk import data
✅ Export ...      - Untuk export data
✅ Send ...        - Untuk send email/notification
✅ Generate ...    - Untuk generate something
✅ Bulk ...        - Untuk bulk operations
```

### PUT/PATCH - Untuk Update
```
✅ Update ...      - Untuk update resource
✅ Activate ...    - Untuk activate
✅ Deactivate ...  - Untuk deactivate
✅ Publish ...     - Untuk publish
✅ Unpublish ...   - Untuk unpublish
✅ Mark ...        - Untuk mark status
✅ Release ...     - Untuk release
```

### DELETE - Untuk Delete
```
✅ Delete ...      - Untuk soft delete
✅ Permanent Delete ... - Untuk hard delete
✅ Remove ...      - Untuk remove relation
```

---

## 🗂️ STRUKTUR FOLDER

### Level 1: Platform
```
📱 [MOBILE] Student App
💻 [WEB] Admin Dashboard
🎓 [WEB] Instructor Dashboard
🌐 [SHARED] Common APIs
```

### Level 2: Module/Feature
```
Contoh untuk Admin:
├── 🔐 1. Authentication
├── 👥 2. User Management
├── 📖 3. Course Management
├── 📝 4. Content Management
├── 📊 5. Reports & Analytics
├── 🎯 6. Enrollment Management
├── 🎮 7. Gamification Management
├── 🗑️ 8. Trash Management
├── ⚙️ 9. System Settings
└── 📋 10. Activity & Audit Logs
```

### Level 3: Sub-Module (Optional)
```
Contoh untuk User Management:
├── 2.1 Users CRUD
├── 2.2 Students
├── 2.3 Instructors
├── 2.4 Admins
└── 2.5 Roles & Permissions
```

### Level 4: Requests
```
Contoh untuk Students:
├── GET [Admin] - Users - List Students
├── POST [Admin] - Users - Create Student
├── GET [Admin] - Users - Get Student Detail
├── PUT [Admin] - Users - Update Student
└── DELETE [Admin] - Users - Delete Student
```

---

## 🎨 EMOJI UNTUK FOLDER

### Platform
```
📱 Mobile
💻 Web Admin
🎓 Web Instructor
🌐 Shared
```

### Modules
```
🔐 Authentication
👥 Users
📖 Courses
📝 Content
📊 Reports/Analytics
🎯 Enrollments
🎮 Gamification
💬 Forums
✅ Grading
🗑️ Trash
⚙️ Settings
📋 Logs
🔔 Notifications
🔍 Search
📁 Media
👤 Profile
```

---

## 📝 TEMPLATE REQUEST

### Nama Request
```
[METHOD] [Platform] - [Module] - [Action]
```

### Description Template
```markdown
## Deskripsi
[Penjelasan singkat tentang endpoint ini]

## Authorization
Bearer Token required / Public

## Path Parameters
- `id` (required): ID dari resource

## Query Parameters
- `page` (optional): Halaman yang ingin ditampilkan (default: 1)
- `per_page` (optional): Jumlah item per halaman (default: 15)
- `search` (optional): Keyword pencarian
- `sort` (optional): Field untuk sorting (default: created_at)
- `direction` (optional): Arah sorting (asc/desc, default: desc)

## Request Body
```json
{
  "field1": "value1",
  "field2": "value2"
}
```

## Response Success (200/201)
```json
{
  "success": true,
  "message": "Success message",
  "data": {}
}
```

## Response Error (400/401/403/404/422/500)
```json
{
  "success": false,
  "message": "Error message",
  "errors": {}
}
```
```

### Tests Template
```javascript
// Test: Status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test: Response structure
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});

// Test: Response time
pm.test("Response time is less than 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});

// Save variables (if needed)
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("resource_id", jsonData.data.id);
}
```

---

## ✅ CHECKLIST SEBELUM SAVE REQUEST

- [ ] Nama mengikuti format: `[METHOD] [Platform] - [Module] - [Action]`
- [ ] Platform label sudah benar (Mobile/Admin/Instructor/Shared)
- [ ] Module name konsisten dengan folder
- [ ] Action verb jelas dan spesifik
- [ ] URL menggunakan `{{base_url}}`
- [ ] Headers sudah include Authorization (jika perlu)
- [ ] Request body sudah ada (untuk POST/PUT)
- [ ] Description sudah lengkap
- [ ] Examples sudah ada (success & error)
- [ ] Tests sudah ditambahkan
- [ ] Variables sudah disimpan (jika perlu)

---

## 📖 CONTOH LENGKAP REQUEST

### Request: Create Student

**Nama**:
```
POST [Admin] - Users - Create Student
```

**URL**:
```
{{base_url}}/v1/admin/users/students
```

**Method**: POST

**Headers**:
```
Authorization: Bearer {{auth_token}}
Content-Type: application/json
Accept: application/json
```

**Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890",
  "date_of_birth": "2000-01-01",
  "gender": "male"
}
```

**Description**:
```markdown
## Deskripsi
Endpoint untuk membuat user baru dengan role Student.

## Authorization
Bearer Token required (Admin only)

## Request Body
- `name` (required): Nama lengkap student
- `email` (required): Email student (unique)
- `password` (required): Password (min 8 characters)
- `password_confirmation` (required): Konfirmasi password
- `phone` (optional): Nomor telepon
- `date_of_birth` (optional): Tanggal lahir (YYYY-MM-DD)
- `gender` (optional): Jenis kelamin (male/female)

## Response Success (201)
```json
{
  "success": true,
  "message": "Student created successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "Student",
    "created_at": "2026-03-14T10:00:00.000000Z"
  }
}
```

## Response Error (422)
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
```

**Tests**:
```javascript
// Test: Status code is 201
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

// Test: Response has data
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.have.property('id');
});

// Test: User role is Student
pm.test("User role is Student", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.role).to.equal('Student');
});

// Save user ID for next requests
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("student_id", jsonData.data.id);
}
```

---

## 🎯 QUICK REFERENCE

### Format Cepat
```
GET    [Platform] - Module - List Items
GET    [Platform] - Module - Get Item Detail
POST   [Platform] - Module - Create Item
PUT    [Platform] - Module - Update Item
DELETE [Platform] - Module - Delete Item
```

### Platform Cepat
```
[Mobile]      - Student app
[Admin]       - Admin dashboard
[Instructor]  - Instructor dashboard
[Shared]      - All platforms
```

### Module Umum
```
Auth, Users, Courses, Content, Grading, Forums,
Gamification, Enrollments, Reports, Notifications,
Profile, Search, Media, Trash, Settings
```

---

**Gunakan panduan ini sebagai patokan untuk SEMUA request di Postman Collection!**
