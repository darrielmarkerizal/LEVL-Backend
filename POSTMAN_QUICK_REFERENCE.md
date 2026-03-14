# POSTMAN QUICK REFERENCE - LEVL API
**Quick access guide untuk developer**

---

## 🎯 PILIH PLATFORM ANDA

### 📱 Saya Mobile Developer
**Folder yang perlu Anda**: 
- `📱 [MOBILE] Student App` - Semua endpoint untuk mobile app
- `🌐 [SHARED] Common APIs` - Auth, Profile, Notifications, dll

**Environment**: Development atau Staging  
**Role**: `student`

**Endpoint Paling Sering Digunakan**:
```
✅ POST [Shared] - Auth - Login
✅ GET [Mobile] - Courses - List Enrolled Courses
✅ GET [Mobile] - Lessons - Get Lesson Detail
✅ POST [Mobile] - Lessons - Mark as Complete
✅ GET [Mobile] - Gamification - My Stats
✅ GET [Mobile] - Dashboard - Overview
```

---

### 💻 Saya Admin Web Developer
**Folder yang perlu Anda**:
- `💻 [WEB] Admin Dashboard` - Semua endpoint untuk admin
- `🌐 [SHARED] Common APIs` - Auth, Profile, Notifications, dll

**Environment**: Development atau Staging  
**Role**: `admin`

**Endpoint Paling Sering Digunakan**:
```
✅ POST [Shared] - Auth - Login
✅ GET [Admin] - Users - List All Users
✅ POST [Admin] - Users - Create Student
✅ GET [Admin] - Courses - List All Courses
✅ POST [Admin] - Courses - Create Course
✅ GET [Admin] - Enrollments - List All Enrollments
✅ GET [Admin] - Reports - User Statistics
```

---

### 🎓 Saya Instructor Web Developer
**Folder yang perlu Anda**:
- `🎓 [WEB] Instructor Dashboard` - Semua endpoint untuk instructor
- `🌐 [SHARED] Common APIs` - Auth, Profile, Notifications, dll

**Environment**: Development atau Staging  
**Role**: `instructor`

**Endpoint Paling Sering Digunakan**:
```
✅ POST [Shared] - Auth - Login
✅ GET [Instructor] - Courses - List My Courses
✅ POST [Instructor] - Content - Create Lesson
✅ GET [Instructor] - Grading - List Submissions
✅ POST [Instructor] - Grading - Grade Submission
✅ GET [Instructor] - Analytics - Course Overview
```

---

## 🔑 AUTHENTICATION FLOW

### 1. Login
```http
POST {{base_url}}/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "student"
    }
  }
}
```

### 2. Simpan Token
```javascript
// Di Postman Tests tab
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.token);
    pm.environment.set("user_id", jsonData.data.user.id);
    pm.environment.set("role", jsonData.data.user.role);
}
```

### 3. Gunakan Token
Semua request selanjutnya harus include header:
```
Authorization: Bearer {{auth_token}}
```

---

## 📊 RESPONSE FORMAT STANDARD

### Success Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    // Your data here
  }
}
```

### Success Response dengan Pagination
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    // Array of items
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  },
  "links": {
    "first": "http://api.levl.id/api/courses?page=1",
    "last": "http://api.levl.id/api/courses?page=7",
    "prev": null,
    "next": "http://api.levl.id/api/courses?page=2"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## ⚠️ COMMON ERROR CODES

| Code | Meaning | Action |
|------|---------|--------|
| 200 | OK | Success |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Check your request parameters |
| 401 | Unauthorized | Login required or token expired |
| 403 | Forbidden | You don't have permission |
| 404 | Not Found | Resource not found |
| 422 | Validation Error | Check validation errors in response |
| 429 | Too Many Requests | Rate limit exceeded, wait a moment |
| 500 | Server Error | Contact backend team |

---

## 🔍 QUERY PARAMETERS UMUM

### Pagination
```
?page=1&per_page=15
```

### Sorting
```
?sort=created_at&direction=desc
```

### Filtering
```
?status=active&role=student
```

### Search
```
?search=john
```

### Include Relations
```
?include=user,course
```

### Kombinasi
```
?page=1&per_page=20&sort=name&direction=asc&status=active&search=john
```

---

## 🎮 GAMIFICATION ENDPOINTS

### Get My Stats
```http
GET {{base_url}}/gamification/stats
Authorization: Bearer {{auth_token}}
```

### Get My Badges
```http
GET {{base_url}}/gamification/badges
Authorization: Bearer {{auth_token}}
```

### Get Leaderboard
```http
GET {{base_url}}/gamification/leaderboard?period=all_time
Authorization: Bearer {{auth_token}}
```

**Periods**: `all_time`, `today`, `this_week`, `this_month`, `this_year`

---

## 📚 LEARNING ENDPOINTS

### List My Courses
```http
GET {{base_url}}/courses/enrolled
Authorization: Bearer {{auth_token}}
```

### Get Course Detail
```http
GET {{base_url}}/courses/{{course_id}}
Authorization: Bearer {{auth_token}}
```

### Mark Lesson as Complete
```http
POST {{base_url}}/lessons/{{lesson_id}}/complete
Authorization: Bearer {{auth_token}}
```

### Submit Assignment
```http
POST {{base_url}}/assignments/{{assignment_id}}/submit
Authorization: Bearer {{auth_token}}
Content-Type: multipart/form-data

file: [your file]
notes: "My submission notes"
```

---

## 💬 FORUM ENDPOINTS

### List Threads
```http
GET {{base_url}}/forums/{{course_id}}/threads
Authorization: Bearer {{auth_token}}
```

### Create Thread
```http
POST {{base_url}}/forums/{{course_id}}/threads
Authorization: Bearer {{auth_token}}
Content-Type: application/json

{
  "title": "Thread title",
  "content": "Thread content"
}
```

### Reply to Thread
```http
POST {{base_url}}/forums/threads/{{thread_id}}/replies
Authorization: Bearer {{auth_token}}
Content-Type: application/json

{
  "content": "Reply content"
}
```

---

## 🔔 NOTIFICATION ENDPOINTS

### List Notifications
```http
GET {{base_url}}/notifications
Authorization: Bearer {{auth_token}}
```

### Unread Count
```http
GET {{base_url}}/notifications/unread-count
Authorization: Bearer {{auth_token}}
```

### Mark as Read
```http
PUT {{base_url}}/notifications/{{notification_id}}/read
Authorization: Bearer {{auth_token}}
```

---

## 📁 MEDIA UPLOAD

### Upload Image
```http
POST {{base_url}}/media/upload
Authorization: Bearer {{auth_token}}
Content-Type: multipart/form-data

file: [your image file]
type: image
```

**Supported types**: `image`, `document`, `video`

**Max sizes**:
- Image: 5MB
- Document: 10MB
- Video: 100MB

---

## 🧪 TESTING TIPS

### 1. Setup Environment Variables
```javascript
// Setelah login, simpan token
pm.environment.set("auth_token", jsonData.data.token);
pm.environment.set("user_id", jsonData.data.user.id);

// Setelah create resource, simpan ID
pm.environment.set("course_id", jsonData.data.id);
```

### 2. Basic Tests
```javascript
// Test status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test response structure
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});

// Test response time
pm.test("Response time is less than 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});
```

### 3. Chain Requests
Gunakan Collection Runner untuk menjalankan sequence:
1. Login
2. Get Courses
3. Get Course Detail
4. Mark Lesson Complete
5. Check Progress

---

## 🚨 TROUBLESHOOTING

### Token Expired (401)
**Problem**: `{"success": false, "message": "Unauthenticated"}`  
**Solution**: Login ulang untuk mendapatkan token baru

### Validation Error (422)
**Problem**: `{"success": false, "errors": {...}}`  
**Solution**: Periksa field yang error di response dan perbaiki request

### Not Found (404)
**Problem**: `{"success": false, "message": "Resource not found"}`  
**Solution**: Periksa ID resource yang Anda gunakan

### Forbidden (403)
**Problem**: `{"success": false, "message": "Forbidden"}`  
**Solution**: Anda tidak punya permission, gunakan user dengan role yang sesuai

### Rate Limit (429)
**Problem**: `{"success": false, "message": "Too many requests"}`  
**Solution**: Tunggu beberapa saat sebelum request lagi

---

## 📖 DOKUMENTASI LENGKAP

Untuk dokumentasi lengkap, lihat:
- `POSTMAN_DOCUMENTATION_STRUCTURE.md` - Struktur lengkap collection
- `PANDUAN_*_MANAGEMENT_LENGKAP.md` - Panduan per module
- `API_COMPLETE_DOCUMENTATION.md` - Dokumentasi API lengkap

---

## 💡 BEST PRACTICES

1. **Selalu gunakan environment variables** untuk base_url dan token
2. **Simpan token setelah login** menggunakan Tests script
3. **Gunakan folder yang sesuai** dengan platform Anda
4. **Test endpoint** sebelum integrate ke aplikasi
5. **Update dokumentasi** jika ada perubahan
6. **Gunakan descriptive names** untuk request
7. **Tambahkan examples** untuk success dan error cases
8. **Write basic tests** untuk setiap request

---

## 🆘 BUTUH BANTUAN?

- **Backend Team**: Untuk pertanyaan tentang API
- **DevOps Team**: Untuk pertanyaan tentang environment
- **Documentation Team**: Untuk pertanyaan tentang dokumentasi

---

**Happy Coding! 🚀**
