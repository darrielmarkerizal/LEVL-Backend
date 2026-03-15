# DOKUMENTASI API MANAJEMEN UNIT KOMPETENSI ADMIN - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Schemes - Admin Unit Management  
**Platform**: Web Admin Dashboard

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [CRUD Unit Kompetensi](#crud-unit-kompetensi)
4. [Bulk Operations](#bulk-operations)
5. [Response Format](#response-format)
6. [Complete Use Case](#complete-use-case)

---

## 🎯 RINGKASAN

Dokumentasi ini menjelaskan manajemen unit kompetensi dalam skema:
1. **CRUD Unit** - Create, Read, Update, Delete unit
2. **Reorder** - Mengatur urutan unit
3. **Duplicate** - Menduplikasi unit beserta kontennya
4. **Statistics** - Melihat statistik unit

### Fitur Utama
- ✅ CRUD lengkap untuk unit kompetensi
- ✅ Drag & drop reordering
- ✅ Duplicate unit dengan semua lesson
- ✅ Soft delete & restore
- ✅ Statistics & analytics
- ✅ Bulk operations

---

## 🌐 BASE URL & HEADERS

### Base URL
```
Development:  http://localhost:8000/api/v1
Staging:      https://staging-api.levl.id/api/v1
Production:   https://api.levl.id/api/v1
```

### Headers Standar
```http
Content-Type: application/json
Accept: application/json
Accept-Language: id
Authorization: Bearer {{auth_token}}
```

---

## 📚 CRUD UNIT KOMPETENSI

### 2.1. GET [Admin] Unit - Daftar Unit dalam Skema

Melihat semua unit dalam skema dengan urutan yang benar.

#### Endpoint
```
GET /admin/courses/{course_id}/units
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_id` | integer | ✅ Yes | ID skema |

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `include` | string | ❌ No | - | Includes: `lessons`, `assignments`, `quizzes`, `statistics` |
| `sort` | string | ❌ No | order | Sorting: `order`, `title`, `created_at` |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar unit berhasil diambil",
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Getting Started",
      "slug": "getting-started",
      "description": "Introduction to the course",
      "order": 1,
      "status": "published",
      "stats": {
        "total_lessons": 5,
        "total_assignments": 1,
        "total_quizzes": 1,
        "duration_estimate": 120
      },
      "created_at": "2026-01-15T10:00:00.000000Z",
      "updated_at": "2026-03-15T10:00:00.000000Z"
    },
    {
      "id": 2,
      "course_id": 1,
      "title": "Basic Concepts",
      "slug": "basic-concepts",
      "description": "Learn fundamental concepts",
      "order": 2,
      "status": "published",
      "stats": {
        "total_lessons": 10,
        "total_assignments": 2,
        "total_quizzes": 1,
        "duration_estimate": 300
      },
      "created_at": "2026-01-15T10:30:00.000000Z",
      "updated_at": "2026-03-15T10:00:00.000000Z"
    }
  ]
}
```

#### Postman Example
```javascript
// URL
{{base_url}}/admin/courses/{{course_id}}/units

// Query Params - With Includes
include: lessons,statistics
sort: order

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has units", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Units ordered correctly", () => {
    const data = pm.response.json().data;
    if (data.length > 1) {
        pm.expect(data[0].order).to.be.below(data[1].order);
    }
});

// Save first unit
if (pm.response.json().data.length > 0) {
    pm.environment.set("unit_id", pm.response.json().data[0].id);
    pm.environment.set("unit_slug", pm.response.json().data[0].slug);
}
```

---

### 2.2. GET [Admin] Unit - Detail Unit

Melihat detail lengkap unit termasuk semua lesson, assignment, dan quiz.

#### Endpoint
```
GET /admin/units/{unit_id}
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `unit_id` | integer | ✅ Yes | ID unit |

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `include` | string | ❌ No | Includes: `lessons`, `assignments`, `quizzes`, `course` |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Detail unit berhasil diambil",
  "data": {
    "id": 1,
    "course_id": 1,
    "title": "Getting Started",
    "slug": "getting-started",
    "description": "Introduction to the course",
    "order": 1,
    "status": "published",
    "course": {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming"
    },
    "lessons": [
      {
        "id": 1,
        "title": "Introduction",
        "slug": "introduction",
        "order": 1,
        "duration_minutes": 15
      }
    ],
    "stats": {
      "total_lessons": 5,
      "total_assignments": 1,
      "total_quizzes": 1,
      "total_students_completed": 45
    },
    "created_at": "2026-01-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z"
  }
}
```

---

### 2.3. POST [Admin] Unit - Tambah Unit Baru

Membuat unit kompetensi baru dalam skema.

#### Endpoint
```
POST /admin/courses/{course_id}/units
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_id` | integer | ✅ Yes | ID skema |

#### Request Body (JSON)
```json
{
  "title": "Getting Started",
  "description": "Introduction to the course",
  "order": 1,
  "status": "published"
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `title` | string | ✅ Yes | max:255 | Judul unit |
| `description` | text | ❌ No | - | Deskripsi unit |
| `order` | integer | ❌ No | min:1 | Urutan (auto if not provided) |
| `status` | string | ❌ No | in:draft,published | Status (default: draft) |

#### Response Success (201 Created)
```json
{
  "success": true,
  "message": "Unit berhasil dibuat",
  "data": {
    "id": 6,
    "course_id": 1,
    "title": "Getting Started",
    "slug": "getting-started",
    "order": 1,
    "status": "draft",
    "created_at": "2026-03-15T11:00:00.000000Z"
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/admin/courses/{{course_id}}/units

// Body
{
  "title": "Getting Started",
  "description": "Introduction to the course",
  "status": "published"
}

// Tests
pm.test("Status 201", () => pm.response.to.have.status(201));
pm.test("Unit created", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.have.property('id');
    pm.expect(data).to.have.property('slug');
});

// Save unit ID
if (pm.response.code === 201) {
    pm.environment.set("unit_id", pm.response.json().data.id);
}
```

---

### 2.4. PUT [Admin] Unit - Update Unit

Mengupdate data unit yang sudah ada.

#### Endpoint
```
PUT /admin/units/{unit_id}
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `unit_id` | integer | ✅ Yes | ID unit |

#### Request Body (JSON)
```json
{
  "title": "Getting Started - Updated",
  "description": "Updated description",
  "status": "published"
}
```

**Note**: Hanya field yang ingin diupdate yang perlu dikirim.

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Unit berhasil diupdate",
  "data": {
    "id": 1,
    "title": "Getting Started - Updated",
    "slug": "getting-started-updated",
    "updated_at": "2026-03-15T11:30:00.000000Z"
  }
}
```

---

### 2.5. DELETE [Admin] Unit - Hapus Unit

Menghapus unit (soft delete). Unit akan dipindahkan ke trash.

#### Endpoint
```
DELETE /admin/units/{unit_id}
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `unit_id` | integer | ✅ Yes | ID unit |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Unit berhasil dihapus",
  "data": {
    "id": 1,
    "title": "Getting Started",
    "deleted_at": "2026-03-15T11:30:00.000000Z"
  }
}
```

**Note**: 
- Semua lesson, assignment, dan quiz dalam unit juga akan dihapus (cascade soft delete)
- Bisa di-restore melalui trash management

---

### 2.6. POST [Admin] Unit - Urutkan Ulang Unit

Mengatur ulang urutan unit dalam skema (drag & drop).

#### Endpoint
```
POST /admin/units/reorder
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Request Body (JSON)
```json
{
  "course_id": 1,
  "units": [
    {"id": 2, "order": 1},
    {"id": 1, "order": 2},
    {"id": 3, "order": 3}
  ]
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `course_id` | integer | ✅ Yes | exists:courses,id | ID skema |
| `units` | array | ✅ Yes | required | Array unit dengan order baru |
| `units.*.id` | integer | ✅ Yes | exists:units,id | ID unit |
| `units.*.order` | integer | ✅ Yes | min:1 | Order baru |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Urutan unit berhasil diupdate",
  "data": {
    "course_id": 1,
    "updated_units": 3
  }
}
```

#### Postman Example
```javascript
// Body
{
  "course_id": {{course_id}},
  "units": [
    {"id": 2, "order": 1},
    {"id": 1, "order": 2},
    {"id": 3, "order": 3}
  ]
}

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Units reordered", () => {
    const data = pm.response.json().data;
    pm.expect(data.updated_units).to.be.above(0);
});
```

---

### 2.7. POST [Admin] Unit - Duplikasi Unit

Menduplikasi unit beserta semua lesson, assignment, dan quiz-nya.

#### Endpoint
```
POST /admin/units/{unit_id}/duplicate
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `unit_id` | integer | ✅ Yes | ID unit |

#### Request Body (JSON)
```json
{
  "title": "Getting Started - Copy",
  "course_id": 1
}
```

#### Field Validation

| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `title` | string | ✅ Yes | max:255 | Judul unit baru |
| `course_id` | integer | ❌ No | exists:courses,id | ID skema tujuan (default: same course) |

#### Response Success (201 Created)
```json
{
  "success": true,
  "message": "Unit berhasil diduplikasi",
  "data": {
    "id": 7,
    "course_id": 1,
    "title": "Getting Started - Copy",
    "slug": "getting-started-copy",
    "order": 6,
    "status": "draft",
    "original_id": 1,
    "stats": {
      "lessons_copied": 5,
      "assignments_copied": 1,
      "quizzes_copied": 1
    },
    "created_at": "2026-03-15T12:00:00.000000Z"
  }
}
```

**Note**: 
- Duplikasi akan menyalin semua lessons, assignments, quizzes
- Status selalu draft
- Order akan otomatis di akhir
- Media files akan di-copy

---

### 2.8. GET [Admin] Unit - Statistik Unit

Melihat statistik lengkap unit.

#### Endpoint
```
GET /admin/units/{unit_id}/statistics
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `unit_id` | integer | ✅ Yes | ID unit |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Statistik unit berhasil diambil",
  "data": {
    "unit": {
      "id": 1,
      "title": "Getting Started"
    },
    "content": {
      "total_lessons": 5,
      "total_assignments": 1,
      "total_quizzes": 1,
      "total_duration_minutes": 120
    },
    "completion": {
      "total_students": 150,
      "completed_students": 120,
      "completion_rate": 80,
      "average_time_spent": 95
    },
    "engagement": {
      "average_lesson_completion": 85,
      "assignment_submission_rate": 90,
      "quiz_completion_rate": 88
    }
  }
}
```

---

## 🔄 BULK OPERATIONS

### 2.9. POST [Admin] Unit - Bulk Delete

Menghapus multiple unit sekaligus.

#### Endpoint
```
POST /admin/units/bulk-delete
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Request Body (JSON)
```json
{
  "unit_ids": [1, 2, 3]
}
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "3 unit berhasil dihapus",
  "data": {
    "deleted_count": 3,
    "deleted_ids": [1, 2, 3]
  }
}
```

---

## 📖 COMPLETE USE CASE

### Scenario: Admin mengelola unit dalam skema

```javascript
// ============================================
// STEP 1: Lihat semua unit dalam skema
// ============================================
GET /admin/courses/1/units?include=statistics
// Response: 5 units dengan statistik

// ============================================
// STEP 2: Tambah unit baru
// ============================================
POST /admin/courses/1/units
Body: {
  "title": "Advanced Topics",
  "description": "Learn advanced concepts",
  "status": "draft"
}
// Response: Unit created, id = 6, order = 6

// ============================================
// STEP 3: Update unit
// ============================================
PUT /admin/units/6
Body: {
  "title": "Advanced Topics - Updated",
  "status": "published"
}
// Response: Unit updated

// ============================================
// STEP 4: Reorder units (drag & drop)
// ============================================
POST /admin/units/reorder
Body: {
  "course_id": 1,
  "units": [
    {"id": 6, "order": 1},
    {"id": 1, "order": 2},
    {"id": 2, "order": 3}
  ]
}
// Response: 3 units reordered

// ============================================
// STEP 5: Duplicate unit
// ============================================
POST /admin/units/1/duplicate
Body: {
  "title": "Getting Started - Copy"
}
// Response: Unit duplicated with all lessons

// ============================================
// STEP 6: View statistics
// ============================================
GET /admin/units/1/statistics
// Response: Detailed statistics

// ============================================
// STEP 7: Delete unit
// ============================================
DELETE /admin/units/7
// Response: Unit soft deleted

// ============================================
// STEP 8: Bulk delete
// ============================================
POST /admin/units/bulk-delete
Body: {
  "unit_ids": [8, 9, 10]
}
// Response: 3 units deleted
```

---

## 📊 RESPONSE FORMAT

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { }
}
```

### Error Response
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

## ⚠️ ERROR CODES

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request berhasil |
| 201 | Created | Resource berhasil dibuat |
| 401 | Unauthorized | Token invalid/expired |
| 403 | Forbidden | Tidak memiliki akses |
| 404 | Not Found | Resource tidak ditemukan |
| 422 | Validation Error | Input tidak valid |

---

**Dokumentasi ini mencakup complete unit management untuk admin.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
