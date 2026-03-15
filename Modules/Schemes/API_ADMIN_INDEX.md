# DOKUMENTASI API ADMIN - MANAJEMEN SKEMA & KONTEN
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Schemes - Admin Management  
**Platform**: Web Admin Dashboard

---

## 📚 DAFTAR DOKUMENTASI

Dokumentasi API Admin untuk manajemen skema dan konten dibagi menjadi beberapa file untuk kemudahan navigasi:

### 1. Manajemen Skema (Course)
📄 **File**: `API_MANAJEMEN_SKEMA_ADMIN_LENGKAP.md`

**Endpoint Coverage**:
- ✅ GET `/admin/courses` - Daftar semua skema
- ✅ GET `/admin/courses/{id}` - Detail skema
- ✅ POST `/admin/courses` - Tambah skema baru
- ✅ PUT `/admin/courses/{id}` - Update skema
- ✅ DELETE `/admin/courses/{id}` - Hapus skema (soft delete)
- ✅ POST `/admin/courses/{id}/publish` - Publikasikan skema
- ✅ POST `/admin/courses/{id}/unpublish` - Batalkan publikasi
- ✅ POST `/admin/courses/{id}/duplicate` - Duplikasi skema
- ✅ GET `/admin/courses/{id}/statistics` - Statistik skema
- ✅ PUT `/admin/courses/{id}/settings` - Update pengaturan
- ✅ PUT `/admin/courses/{id}/instructor` - Assign instructor
- ✅ POST `/admin/courses/bulk-delete` - Bulk delete

**Total**: 12 endpoints

---

### 2. Manajemen Unit Kompetensi
📄 **File**: `API_MANAJEMEN_UNIT_ADMIN_LENGKAP.md`

**Endpoint Coverage**:
- ✅ GET `/admin/courses/{course_id}/units` - Daftar unit dalam skema
- ✅ GET `/admin/units/{id}` - Detail unit
- ✅ POST `/admin/courses/{course_id}/units` - Tambah unit baru
- ✅ PUT `/admin/units/{id}` - Update unit
- ✅ DELETE `/admin/units/{id}` - Hapus unit (soft delete)
- ✅ POST `/admin/units/reorder` - Urutkan ulang unit
- ✅ POST `/admin/units/{id}/duplicate` - Duplikasi unit
- ✅ GET `/admin/units/{id}/statistics` - Statistik unit
- ✅ POST `/admin/units/bulk-delete` - Bulk delete

**Total**: 9 endpoints

---

### 3. Manajemen Elemen Kompetensi (Lesson)
📄 **File**: `API_MANAJEMEN_ELEMEN_ADMIN_LENGKAP.md`

**Endpoint Coverage**:
- ✅ GET `/admin/units/{unit_id}/lessons` - Daftar elemen dalam unit
- ✅ GET `/admin/lessons/{id}` - Detail elemen
- ✅ POST `/admin/units/{unit_id}/lessons` - Tambah elemen baru
- ✅ PUT `/admin/lessons/{id}` - Update elemen
- ✅ DELETE `/admin/lessons/{id}` - Hapus elemen (soft delete)
- ✅ POST `/admin/lessons/reorder` - Urutkan ulang elemen
- ✅ POST `/admin/lessons/{id}/duplicate` - Duplikasi elemen
- ✅ POST `/admin/lessons/{id}/upload-content` - Upload konten
- ✅ GET `/admin/lessons/{id}/statistics` - Statistik elemen
- ✅ POST `/admin/lessons/bulk-delete` - Bulk delete

**Total**: 10 endpoints

---

### 4. Manajemen Media & File
📄 **File**: `API_MANAJEMEN_MEDIA_ADMIN_LENGKAP.md`

**Endpoint Coverage**:
- ✅ POST `/admin/media/upload-image` - Upload gambar
- ✅ POST `/admin/media/upload-document` - Upload dokumen
- ✅ POST `/admin/media/upload-video` - Upload video
- ✅ GET `/admin/media` - Daftar media
- ✅ DELETE `/admin/media/{id}` - Hapus media
- ✅ POST `/admin/media/bulk-upload` - Bulk upload

**Total**: 6 endpoints

---

## 📊 RINGKASAN TOTAL

| Module | Endpoints | Status |
|--------|-----------|--------|
| Manajemen Skema | 12 | ✅ Complete |
| Manajemen Unit | 9 | ✅ Complete |
| Manajemen Elemen | 10 | ✅ Complete |
| Manajemen Media | 6 | ✅ Complete |
| **TOTAL** | **37** | **✅ Complete** |

---

## 🎯 FITUR UTAMA

### CRUD Operations
- ✅ Create, Read, Update, Delete untuk semua resource
- ✅ Soft delete dengan kemampuan restore
- ✅ Bulk operations (delete, reorder)
- ✅ Duplicate/clone functionality

### Content Management
- ✅ Rich text editor support (markdown)
- ✅ Media upload (images, videos, documents)
- ✅ Content versioning
- ✅ Draft & publish workflow

### Organization
- ✅ Hierarchical structure (Course → Unit → Lesson)
- ✅ Drag & drop reordering
- ✅ Category & tag management
- ✅ Instructor assignment

### Analytics
- ✅ Statistics per course, unit, lesson
- ✅ Student progress tracking
- ✅ Completion rates
- ✅ Engagement metrics

---

## 🔐 AUTHORIZATION

Semua endpoint memerlukan:
- ✅ Bearer Token authentication
- ✅ Role: Admin atau Superadmin
- ✅ Active user status

---

## 🌐 BASE URL

```
Development:  http://localhost:8000/api/v1
Staging:      https://staging-api.levl.id/api/v1
Production:   https://api.levl.id/api/v1
```

---

## 📖 CARA PENGGUNAAN

### 1. Baca Dokumentasi Sesuai Kebutuhan
Pilih file dokumentasi yang sesuai dengan fitur yang ingin digunakan.

### 2. Import ke Postman
Setiap dokumentasi menyertakan contoh Postman yang bisa langsung digunakan.

### 3. Setup Environment Variables
```json
{
  "base_url": "http://localhost:8000/api/v1",
  "auth_token": "your_admin_token",
  "course_id": "",
  "unit_id": "",
  "lesson_id": ""
}
```

### 4. Test Endpoints
Ikuti contoh use case di setiap dokumentasi untuk testing flow lengkap.

---

## 🚀 QUICK START

### Scenario: Membuat Course Lengkap

```javascript
// 1. Create Course
POST /admin/courses
// Save: course_id

// 2. Create Unit
POST /admin/courses/{course_id}/units
// Save: unit_id

// 3. Create Lessons
POST /admin/units/{unit_id}/lessons
// Repeat for multiple lessons

// 4. Reorder Lessons
POST /admin/lessons/reorder

// 5. Publish Course
POST /admin/courses/{course_id}/publish

// 6. Check Statistics
GET /admin/courses/{course_id}/statistics
```

---

## 📞 SUPPORT

**Maintainer**: Backend Team  
**Contact**: backend@levl.id  
**Last Update**: 15 Maret 2026

---

**Dokumentasi ini adalah bagian dari Levl API Documentation Suite.**
