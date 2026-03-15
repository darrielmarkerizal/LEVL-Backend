# QUICK REFERENCE - API ADMIN MANAJEMEN SKEMA & KONTEN
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026

---

## 📚 MANAJEMEN SKEMA (COURSE)

### Daftar & Detail
```
GET    /admin/courses                    - Daftar semua skema
GET    /admin/courses/{id}               - Detail skema
```

### CRUD Operations
```
POST   /admin/courses                    - Tambah skema baru
PUT    /admin/courses/{id}               - Update skema
DELETE /admin/courses/{id}               - Hapus skema (soft delete)
```

### Status Management
```
POST   /admin/courses/{id}/publish       - Publikasikan skema
POST   /admin/courses/{id}/unpublish     - Batalkan publikasi
```

### Advanced Operations
```
POST   /admin/courses/{id}/duplicate     - Duplikasi skema
GET    /admin/courses/{id}/statistics    - Statistik skema
PUT    /admin/courses/{id}/settings      - Update pengaturan
PUT    /admin/courses/{id}/instructor    - Assign instructor
POST   /admin/courses/bulk-delete        - Bulk delete
```

---

## 📑 MANAJEMEN UNIT KOMPETENSI

### Daftar & Detail
```
GET    /admin/courses/{course_id}/units  - Daftar unit dalam skema
GET    /admin/units/{id}                 - Detail unit
```

### CRUD Operations
```
POST   /admin/courses/{course_id}/units  - Tambah unit baru
PUT    /admin/units/{id}                 - Update unit
DELETE /admin/units/{id}                 - Hapus unit (soft delete)
```

### Advanced Operations
```
POST   /admin/units/reorder              - Urutkan ulang unit
POST   /admin/units/{id}/duplicate       - Duplikasi unit
GET    /admin/units/{id}/statistics      - Statistik unit
POST   /admin/units/bulk-delete          - Bulk delete
```

---

## 📝 MANAJEMEN ELEMEN KOMPETENSI (LESSON)

### Daftar & Detail
```
GET    /admin/units/{unit_id}/lessons    - Daftar elemen dalam unit
GET    /admin/lessons/{id}               - Detail elemen
```

### CRUD Operations
```
POST   /admin/units/{unit_id}/lessons    - Tambah elemen baru
PUT    /admin/lessons/{id}               - Update elemen
DELETE /admin/lessons/{id}               - Hapus elemen (soft delete)
```

### Content Management
```
POST   /admin/lessons/{id}/upload-content - Upload konten
PUT    /admin/lessons/{id}/content        - Update konten
```

### Advanced Operations
```
POST   /admin/lessons/reorder            - Urutkan ulang elemen
POST   /admin/lessons/{id}/duplicate     - Duplikasi elemen
GET    /admin/lessons/{id}/statistics    - Statistik elemen
POST   /admin/lessons/bulk-delete        - Bulk delete
```

---

## 📁 MANAJEMEN MEDIA

### Upload Operations
```
POST   /admin/media/upload-image         - Upload gambar
POST   /admin/media/upload-document      - Upload dokumen
POST   /admin/media/upload-video         - Upload video
POST   /admin/media/bulk-upload          - Bulk upload
```

### Management
```
GET    /admin/media                      - Daftar media
DELETE /admin/media/{id}                 - Hapus media
```

---

## 🔑 QUERY PARAMETERS UMUM

### Pagination
```
?page=1&per_page=15
```

### Sorting
```
?sort=title           - Ascending
?sort=-title          - Descending
```

### Filtering
```
?filter[status]=published
?filter[level_tag]=beginner
?filter[category_id]=1
```

### Search
```
?search=programming
```

### Includes
```
?include=units,instructor,category
```

---

## 📊 RESPONSE FORMAT

### Success
```json
{
  "success": true,
  "message": "Success message",
  "data": { },
  "meta": {
    "pagination": { }
  }
}
```

### Error
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error detail"]
  }
}
```

---

## 🎯 COMMON USE CASES

### 1. Create Complete Course
```javascript
// 1. Create course
POST /admin/courses
// 2. Create units
POST /admin/courses/{id}/units
// 3. Create lessons
POST /admin/units/{id}/lessons
// 4. Upload content
POST /admin/lessons/{id}/upload-content
// 5. Publish
POST /admin/courses/{id}/publish
```

### 2. Reorder Content
```javascript
// Reorder units
POST /admin/units/reorder
// Reorder lessons
POST /admin/lessons/reorder
```

### 3. Duplicate Course
```javascript
// Duplicate entire course
POST /admin/courses/{id}/duplicate
// Or duplicate unit only
POST /admin/units/{id}/duplicate
```

---

## ⚠️ IMPORTANT NOTES

### Authorization
- All endpoints require Bearer Token
- Role: Admin or Superadmin only

### Soft Delete
- DELETE operations are soft deletes
- Can be restored via trash management
- Permanent delete via force-delete

### Validation
- Title and code must be unique
- Status: draft or published
- Published courses require complete content

---

**Quick Reference untuk Admin API**  
**Maintainer**: Backend Team
