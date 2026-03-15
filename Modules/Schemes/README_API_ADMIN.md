# DOKUMENTASI API ADMIN - MANAJEMEN SKEMA & KONTEN
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Schemes - Complete Admin Management  
**Platform**: Web Admin Dashboard

---

## 🎯 OVERVIEW

Dokumentasi lengkap untuk Admin dalam mengelola skema pembelajaran (course), unit kompetensi, dan elemen kompetensi (lesson) di platform Levl.

### Cakupan Dokumentasi
- ✅ **Manajemen Skema** - CRUD course dengan pengaturan lengkap
- ✅ **Manajemen Unit** - CRUD unit kompetensi dalam skema
- ✅ **Manajemen Elemen** - CRUD elemen kompetensi/lesson dalam unit
- ✅ **Manajemen Media** - Upload dan kelola media files
- ✅ **Bulk Operations** - Operasi massal untuk efisiensi
- ✅ **Statistics** - Analytics dan reporting

---

## 📚 STRUKTUR DOKUMENTASI

Dokumentasi dibagi menjadi beberapa file untuk kemudahan navigasi:

### 1. Index & Overview
📄 **`API_ADMIN_INDEX.md`**
- Overview lengkap semua endpoint
- Ringkasan fitur per module
- Quick navigation ke dokumentasi detail

### 2. Manajemen Skema (Course)
📄 **`API_MANAJEMEN_SKEMA_ADMIN_LENGKAP.md`**
- CRUD operations untuk skema
- Publish/unpublish workflow
- Duplicate & statistics
- Settings & instructor management
- **Total**: 12 endpoints

### 3. Manajemen Unit Kompetensi
📄 **`API_MANAJEMEN_UNIT_ADMIN_LENGKAP.md`**
- CRUD operations untuk unit
- Reorder functionality
- Duplicate & statistics
- Bulk operations
- **Total**: 9 endpoints

### 4. Manajemen Elemen Kompetensi (Lesson)
📄 **`API_MANAJEMEN_ELEMEN_ADMIN_LENGKAP.md`**
- CRUD operations untuk lesson
- Content upload & management
- Reorder functionality
- Duplicate & statistics
- **Total**: 10 endpoints

### 5. Quick Reference
📄 **`API_ADMIN_QUICK_REFERENCE.md`**
- Cheat sheet semua endpoint
- Common query parameters
- Response format
- Quick use cases

---

## 🚀 QUICK START

### Prerequisites
1. Admin/Superadmin account
2. Valid Bearer Token
3. Postman atau API client lainnya

### Setup Environment Variables
```json
{
  "base_url": "http://localhost:8000/api/v1",
  "auth_token": "your_admin_token_here",
  "course_id": "",
  "unit_id": "",
  "lesson_id": ""
}
```

### First API Call
```bash
# Get all courses
GET {{base_url}}/admin/courses
Authorization: Bearer {{auth_token}}
```

---

## 📖 CARA MENGGUNAKAN DOKUMENTASI

### Untuk Pemula
1. Mulai dari **`API_ADMIN_INDEX.md`** untuk overview
2. Baca **`API_ADMIN_QUICK_REFERENCE.md`** untuk cheat sheet
3. Pilih dokumentasi detail sesuai kebutuhan

### Untuk Developer Berpengalaman
1. Langsung ke **`API_ADMIN_QUICK_REFERENCE.md`**
2. Gunakan sebagai reference saat development
3. Buka dokumentasi detail jika perlu contoh lengkap

### Untuk Testing
1. Buka dokumentasi detail per module
2. Copy Postman examples
3. Ikuti complete use case scenarios

---

## 🎯 COMMON WORKFLOWS

### Workflow 1: Membuat Course Lengkap
```
1. POST /admin/courses
   → Create course (draft)
   
2. POST /admin/courses/{id}/units
   → Create units (repeat for multiple units)
   
3. POST /admin/units/{id}/lessons
   → Create lessons in each unit
   
4. POST /admin/lessons/{id}/upload-content
   → Upload content for each lesson
   
5. POST /admin/courses/{id}/publish
   → Publish course
```

### Workflow 2: Mengatur Ulang Konten
```
1. GET /admin/courses/{id}/units
   → Get current unit order
   
2. POST /admin/units/reorder
   → Reorder units via drag & drop
   
3. GET /admin/units/{id}/lessons
   → Get current lesson order
   
4. POST /admin/lessons/reorder
   → Reorder lessons via drag & drop
```

### Workflow 3: Duplikasi Course
```
1. GET /admin/courses/{id}
   → Review course to duplicate
   
2. POST /admin/courses/{id}/duplicate
   → Duplicate entire course with all content
   
3. PUT /admin/courses/{new_id}
   → Modify duplicated course as needed
   
4. POST /admin/courses/{new_id}/publish
   → Publish when ready
```

---

## 📊 ENDPOINT SUMMARY

| Category | Endpoints | Documentation |
|----------|-----------|---------------|
| Skema (Course) | 12 | `API_MANAJEMEN_SKEMA_ADMIN_LENGKAP.md` |
| Unit Kompetensi | 9 | `API_MANAJEMEN_UNIT_ADMIN_LENGKAP.md` |
| Elemen Kompetensi | 10 | `API_MANAJEMEN_ELEMEN_ADMIN_LENGKAP.md` |
| Media Management | 6 | (Included in main docs) |
| **TOTAL** | **37** | - |

---

## 🔐 AUTHORIZATION

### Required Headers
```http
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept: application/json
Accept-Language: id
```

### Required Roles
- ✅ Admin
- ✅ Superadmin

### Permissions
- Full CRUD access to all course content
- Publish/unpublish capabilities
- Bulk operations
- Statistics access

---

## 📝 RESPONSE FORMAT

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 50,
      "last_page": 4
    }
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

---

## ⚠️ IMPORTANT NOTES

### Soft Delete
- Semua DELETE operations adalah soft delete
- Data masuk ke trash dan bisa di-restore
- Permanent delete hanya via trash management

### Validation
- Title dan code harus unique
- Published course harus memiliki minimal 1 unit
- Setiap unit harus memiliki minimal 1 lesson
- Instructor harus di-assign sebelum publish

### Performance
- Gunakan pagination untuk list endpoints
- Gunakan `include` parameter dengan bijak
- Bulk operations untuk efisiensi

### Best Practices
- Selalu test di draft mode dulu
- Gunakan duplicate untuk template
- Check statistics sebelum publish
- Backup sebelum bulk operations

---

## 🔗 RELATED DOCUMENTATION

### Student API
- `Modules/Learning/API_PEMBELAJARAN_STUDENT_LENGKAP.md`
- Student learning journey dan progress

### Gamification API
- `Modules/Gamification/API_GAMIFIKASI_STUDENT_LENGKAP.md`
- XP, badges, levels, leaderboard

### Authentication API
- `Modules/Auth/API_AUTENTIKASI_LENGKAP.md`
- Login, register, password management

---

## 📞 SUPPORT & CONTACT

### Documentation Issues
Jika menemukan kesalahan atau ketidakjelasan dalam dokumentasi:
- Create issue di repository
- Contact: backend@levl.id

### API Issues
Jika menemukan bug atau masalah API:
- Report via issue tracker
- Include: endpoint, request, response, expected behavior

### Feature Requests
Untuk request fitur baru:
- Diskusikan dengan team lead
- Submit proposal via proper channel

---

## 📅 VERSION HISTORY

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 15 Mar 2026 | Initial release - Complete admin API documentation |

---

## 🎓 LEARNING PATH

### Beginner
1. Read `API_ADMIN_INDEX.md`
2. Try simple CRUD operations
3. Practice with Postman examples

### Intermediate
1. Explore bulk operations
2. Implement reorder functionality
3. Work with statistics endpoints

### Advanced
1. Optimize with includes & filters
2. Implement complex workflows
3. Build admin dashboard integration

---

## ✅ CHECKLIST UNTUK DEVELOPER

### Setup
- [ ] Environment variables configured
- [ ] Admin token obtained
- [ ] Postman collection imported

### Basic Operations
- [ ] Can list courses
- [ ] Can create course
- [ ] Can update course
- [ ] Can delete course

### Advanced Operations
- [ ] Can publish/unpublish
- [ ] Can reorder content
- [ ] Can duplicate course
- [ ] Can view statistics

### Integration
- [ ] Error handling implemented
- [ ] Loading states handled
- [ ] Success feedback shown
- [ ] Validation errors displayed

---

**Dokumentasi ini adalah bagian dari Levl API Documentation Suite.**

**Maintainer**: Backend Team  
**Last Update**: 15 Maret 2026  
**Contact**: backend@levl.id
