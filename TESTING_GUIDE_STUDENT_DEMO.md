# PANDUAN TESTING - STUDENT DEMO USER
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Tujuan**: Testing API Student Learning Journey

---

## 👤 USER TESTING CREDENTIALS

### Student Demo User
```
ID: 8
Name: Student Demo
Email: student.demo@test.com
Username: student_demo
Password: password
Status: active
Email Verified: Yes

Enrolled Courses (4):
✅ Digital Marketing Mastery (active)
✅ Financial Analysis and Modeling (active)
✅ Project Management Professional (PMP) Prep (active)
✅ Laravel PHP Framework Masterclass (active)
```

### Kenapa User Ini Cocok?
1. ✅ **Status Active** - Bisa langsung login dan akses semua fitur
2. ✅ **Email Verified** - Tidak perlu verifikasi email
3. ✅ **Sudah Enrolled** - Punya 4 kursus aktif untuk testing learning journey
4. ✅ **Password Simple** - `password` mudah diingat untuk testing
5. ✅ **Role Student** - Sesuai dengan dokumentasi API Student Learning

---

## 🚀 QUICK START - GENERATE TOKEN

### Method 1: Login Manual (Recommended for Production Testing)

```bash
# Login Request
POST http://localhost:8000/api/v1/auth/login
Content-Type: application/json

{
  "login": "student.demo@test.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 8,
      "name": "Student Demo",
      "email": "student.demo@test.com",
      "username": "student_demo"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502004a8b3c...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### Method 2: Dev Token Generator (Quick Testing)

```bash
# Generate token untuk user ID 8
GET http://localhost:8000/api/v1/dev/tokens?user_id=8
```

**Response:**
```json
{
  "success": true,
  "message": "Dev tokens generated successfully.",
  "data": {
    "user": {
      "id": 8,
      "name": "Student Demo",
      "email": "student.demo@test.com",
      "username": "student_demo",
      "role": "Student"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502004a8b3c...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

**⚠️ PENTING**: Endpoint `/dev/tokens` hanya untuk development! Hapus sebelum production.

---

## 📋 POSTMAN SETUP

### 1. Create Environment

Buat environment baru di Postman dengan nama `Levl API - Student Demo`:

```json
{
  "base_url": "http://localhost:8000/api/v1",
  "auth_token": "",
  "refresh_token": "",
  "user_id": "8",
  "user_email": "student.demo@test.com",
  "user_password": "password",
  "course_slug": "",
  "unit_slug": "",
  "lesson_slug": "",
  "assignment_id": "",
  "quiz_id": ""
}
```

### 2. Generate Token (Pilih salah satu)

#### Option A: Login Manual
```javascript
// Request
POST {{base_url}}/auth/login
Body:
{
  "login": "{{user_email}}",
  "password": "{{user_password}}"
}

// Tests (Auto-save tokens)
pm.test("Status 200", () => pm.response.to.have.status(200));
const data = pm.response.json().data;
pm.environment.set("auth_token", data.access_token);
pm.environment.set("refresh_token", data.refresh_token);
pm.environment.set("user_id", data.user.id);
```

#### Option B: Dev Token (Faster)
```javascript
// Request
GET {{base_url}}/dev/tokens?user_id={{user_id}}

// Tests (Auto-save tokens)
pm.test("Status 200", () => pm.response.to.have.status(200));
const data = pm.response.json().data;
pm.environment.set("auth_token", data.access_token);
pm.environment.set("refresh_token", data.refresh_token);
```

### 3. Set Authorization Header

Di Collection atau Folder level, set:
```
Type: Bearer Token
Token: {{auth_token}}
```

---

## 🧪 TESTING FLOW - STUDENT LEARNING JOURNEY

### 🌐 PUBLIC vs PROTECTED ENDPOINTS

**PUBLIC Endpoints** (No Bearer token required):
- ✅ `GET /courses` - Browse all courses
- ✅ `GET /courses/{slug}` - View course detail
- ✅ `GET /search` - Search (untuk courses)

**PROTECTED Endpoints** (Bearer token required):
- 🔒 `GET /my-courses` - My enrolled courses
- 🔒 `GET /courses/{slug}/enrollment-status` - Check enrollment
- 🔒 `POST /courses/{slug}/enroll` - Enroll to course
- 🔒 `GET /courses/{slug}/units` - View course structure
- 🔒 `POST /lessons/{slug}/complete` - Mark lesson complete
- 🔒 All learning, assignment, and quiz endpoints

---

### STEP 1: Browse & Search Courses (PUBLIC - No Token Required)

```javascript
// 🌐 PUBLIC ENDPOINTS - Dapat diakses TANPA Bearer token
// Authorization header OPTIONAL untuk endpoints ini

// 1.1. Browse all courses (NO TOKEN NEEDED)
GET {{base_url}}/courses?per_page=15&sort=-published_at
// Headers: (No Authorization header required)

// 1.2. Search courses (NO TOKEN NEEDED)
GET {{base_url}}/courses?search=laravel&filter[level_tag]=beginner
// Headers: (No Authorization header required)

// 1.3. View course detail (NO TOKEN NEEDED)
GET {{base_url}}/courses/laravel-php-framework-masterclass
// Headers: (No Authorization header required)

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Public access works without token", () => {
    // Endpoint ini bisa diakses tanpa authentication
    pm.expect(pm.response.code).to.equal(200);
});
if (pm.response.json().data.length > 0) {
    pm.environment.set("course_slug", pm.response.json().data[0].slug);
}

// 💡 TIP: Jika menggunakan Bearer token, response akan include
// informasi enrollment status user untuk setiap kursus
```

### STEP 2: Check Enrollment & My Courses

```javascript
// 2.1. Check enrollment status
GET {{base_url}}/courses/{{course_slug}}/enrollment-status

// 2.2. View my enrolled courses
GET {{base_url}}/my-courses?filter[status]=active

// Tests
pm.test("Has enrolled courses", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
    pm.expect(data.length).to.be.above(0);
});
```

### STEP 3: View Course Structure

```javascript
// 3.1. View course units
GET {{base_url}}/courses/{{course_slug}}/units

// 3.2. View unit contents
GET {{base_url}}/courses/{{course_slug}}/units/{{unit_slug}}/contents

// 3.3. View course progress
GET {{base_url}}/courses/{{course_slug}}/progress

// Tests - Save unit slug
if (pm.response.json().data.length > 0) {
    pm.environment.set("unit_slug", pm.response.json().data[0].slug);
}
```

### STEP 4: Learn Lessons

```javascript
// 4.1. View lessons in unit
GET {{base_url}}/courses/{{course_slug}}/units/{{unit_slug}}/lessons

// 4.2. View lesson detail
GET {{base_url}}/courses/{{course_slug}}/units/{{unit_slug}}/lessons/{{lesson_slug}}

// 4.3. Mark lesson complete (GET XP!)
POST {{base_url}}/lessons/{{lesson_slug}}/complete
Body: {}

// Tests - Check XP awarded
pm.test("XP awarded", () => {
    const xp = pm.response.json().xp_info;
    pm.expect(xp.awarded).to.be.true;
    pm.expect(xp.amount).to.be.above(0);
    console.log(`✅ Earned ${xp.amount} XP! Total: ${xp.total_xp}`);
});

// Save lesson slug
if (pm.response.json().data.length > 0) {
    pm.environment.set("lesson_slug", pm.response.json().data[0].slug);
}
```

### STEP 5: Submit Assignments

```javascript
// 5.1. View assignments
GET {{base_url}}/courses/{{course_slug}}/assignments

// 5.2. View assignment detail
GET {{base_url}}/assignments/{{assignment_id}}

// 5.3. Submit assignment (GET XP!)
POST {{base_url}}/assignments/{{assignment_id}}/submissions
Body:
{
  "answers": {
    "1": "My answer to question 1",
    "2": "My answer to question 2"
  }
}

// 5.4. View submission
GET {{base_url}}/assignments/{{assignment_id}}/submissions/{{submission_id}}

// Tests - Check XP
pm.test("Assignment submitted", () => {
    const xp = pm.response.json().xp_info;
    pm.expect(xp.awarded).to.be.true;
    console.log(`✅ Earned ${xp.amount} XP for assignment!`);
});
```

### STEP 6: Take Quizzes

```javascript
// 6.1. View quizzes
GET {{base_url}}/courses/{{course_slug}}/quizzes

// 6.2. View quiz detail
GET {{base_url}}/quizzes/{{quiz_id}}

// 6.3. Start quiz attempt (GET XP!)
POST {{base_url}}/quizzes/{{quiz_id}}/submissions/start
Body: {}

// 6.4. Answer questions
POST {{base_url}}/quiz-submissions/{{submission_id}}/answers
Body:
{
  "question_id": 1,
  "answer": "A"
}

// 6.5. Submit quiz (GET XP!)
POST {{base_url}}/quiz-submissions/{{submission_id}}/submit
Body: {}

// 6.6. View results
GET {{base_url}}/quiz-submissions/{{submission_id}}

// Tests - Check score and XP
pm.test("Quiz completed", () => {
    const data = pm.response.json().data;
    const xp = pm.response.json().xp_info;
    console.log(`✅ Score: ${data.score}/${data.max_score}`);
    console.log(`✅ Earned ${xp.amount} XP!`);
});
```

### STEP 7: Track Progress & Gamification

```javascript
// 7.1. View overall progress
GET {{base_url}}/courses/{{course_slug}}/progress

// 7.2. View my XP and level
GET {{base_url}}/gamification/my-stats

// 7.3. View my badges
GET {{base_url}}/gamification/my-badges

// 7.4. View leaderboard
GET {{base_url}}/gamification/leaderboard

// Tests
pm.test("Progress tracked", () => {
    const progress = pm.response.json().data.progress;
    console.log(`📊 Course Progress: ${progress.overall_percentage}%`);
    console.log(`📚 Lessons: ${progress.completed_lessons}/${progress.total_lessons}`);
    console.log(`⭐ Total XP: ${progress.xp_earned}`);
});
```

---

## 📊 EXPECTED RESULTS

### After Complete Testing Flow:

```
✅ Browsed courses successfully
✅ Viewed enrolled courses (4 courses)
✅ Accessed course structure (units & lessons)
✅ Completed lessons → Earned XP (+10 per lesson)
✅ Submitted assignments → Earned XP (+20 per submission)
✅ Completed quizzes → Earned XP (+30 per quiz)
✅ Progress tracked in real-time
✅ XP accumulated and level increased
✅ Badges unlocked (if criteria met)
```

### XP Breakdown:
- **Lesson Complete**: +10 XP each
- **Assignment Submit**: +20 XP each
- **Quiz Complete**: +30 XP each
- **High Score Bonus**: +10-20 XP (score ≥ 80%)
- **Perfect Score**: +20 XP bonus (score = 100%)

---

## 🔧 TROUBLESHOOTING

### "Akses ke resource ini tidak diizinkan" saat akses course detail

**Penyebab**: Course yang diakses statusnya bukan `published`

**Solusi**:
```bash
# 1. Cek status course
GET {{base_url}}/courses
# Pastikan course memiliki "status": "published"

# 2. Jika course status = "draft", hanya bisa diakses oleh:
#    - Superadmin
#    - Admin  
#    - Instructor (pemilik course)

# 3. Untuk testing, gunakan course yang published
GET {{base_url}}/courses?filter[status]=published
```

**Course Policy Rules**:
- ✅ **Published course**: Bisa diakses siapa saja (public)
- 🔒 **Draft course**: Hanya Superadmin, Admin, atau Instructor pemilik
- 🔒 **Archived course**: Hanya Superadmin, Admin, atau Instructor pemilik

**Quick Fix** (jika perlu publish course untuk testing):
```bash
# Via Tinker
php artisan tinker --execute="
\$course = \Modules\Schemes\Models\Course::where('slug', 'course-slug-here')->first();
if(\$course) {
    \$course->status = 'published';
    \$course->published_at = now();
    \$course->save();
    echo 'Course published: ' . \$course->title;
} else {
    echo 'Course not found';
}
"

# Atau via API (jika sudah login sebagai Admin/Instructor)
PUT {{base_url}}/courses/{{course_slug}}/publish
```

### Token Expired
```javascript
// Refresh token
POST {{base_url}}/auth/refresh
Body:
{
  "refresh_token": "{{refresh_token}}"
}

// Or re-generate dev token
GET {{base_url}}/dev/tokens?user_id=8
```

### Course Not Found
```bash
# Check available courses
GET {{base_url}}/courses

# Check enrolled courses
GET {{base_url}}/my-courses
```

### Enrollment Required
```bash
# Enroll to course first
POST {{base_url}}/courses/{{course_slug}}/enroll
Body: {}
```

### No Lessons Available
```bash
# Check if course has units
GET {{base_url}}/courses/{{course_slug}}/units

# Check if unit has lessons
GET {{base_url}}/courses/{{course_slug}}/units/{{unit_slug}}/lessons
```

---

## 📝 TESTING CHECKLIST

### Pre-Testing
- [ ] Database seeded dengan `php artisan db:seed`
- [ ] Student Demo user exists (ID: 8)
- [ ] User has 4 enrolled courses
- [ ] API server running (`php artisan serve`)
- [ ] Understand PUBLIC vs PROTECTED endpoints

### Authentication
- [ ] Login successful dengan email/password
- [ ] Token generated successfully
- [ ] Token saved to environment variables
- [ ] Authorization header set correctly

### Discovery Phase (PUBLIC - No Token)
- [ ] Browse courses endpoint works WITHOUT token
- [ ] Search courses with filters works WITHOUT token
- [ ] View course detail works WITHOUT token
- [ ] Course data complete (instructor, stats, etc)
- [ ] Public access confirmed (no 401 errors)

### Enrollment Phase
- [ ] Check enrollment status works
- [ ] View my courses shows 4 enrolled courses
- [ ] Enrollment data includes progress

### Learning Phase
- [ ] View course units works
- [ ] View unit contents works
- [ ] View lesson detail works
- [ ] Mark lesson complete works
- [ ] XP awarded for lesson completion
- [ ] Progress updated correctly

### Assessment Phase
- [ ] View assignments works
- [ ] Submit assignment works
- [ ] XP awarded for assignment submission
- [ ] View quizzes works
- [ ] Start quiz attempt works
- [ ] Answer questions works
- [ ] Submit quiz works
- [ ] XP awarded for quiz completion
- [ ] View quiz results works

### Gamification
- [ ] XP accumulated correctly
- [ ] Level info updated
- [ ] Progress percentage accurate
- [ ] Badges unlocked (if applicable)

---

## 🎯 NEXT STEPS

1. **Import Postman Collection** dari `Levl-BE/postman/collections/`
2. **Create Environment** dengan credentials di atas
3. **Generate Token** menggunakan salah satu method
4. **Run Collection** untuk test complete flow
5. **Check Results** di Postman Test Results tab

---

## 📚 RELATED DOCUMENTATION

- [API Pembelajaran Student Lengkap](Modules/Learning/API_PEMBELAJARAN_STUDENT_LENGKAP.md)
- [API Autentikasi Lengkap](Modules/Auth/API_AUTENTIKASI_LENGKAP.md)
- [API Pencarian Lengkap](Modules/Common/API_PENCARIAN_LENGKAP.md)
- [Postman Struktur Lengkap](POSTMAN_STRUKTUR_LENGKAP.md)

---

**Happy Testing! 🚀**

**User**: Student Demo (student.demo@test.com)  
**Password**: password  
**Enrolled Courses**: 4 active courses  
**Ready to Learn**: ✅
