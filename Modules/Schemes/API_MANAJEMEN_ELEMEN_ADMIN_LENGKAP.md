# DOKUMENTASI API MANAJEMEN ELEMEN KOMPETENSI ADMIN - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Schemes - Admin Lesson Management  
**Platform**: Web Admin Dashboard

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [CRUD Elemen Kompetensi](#crud-elemen-kompetensi)
4. [Content Management](#content-management)
5. [Bulk Operations](#bulk-operations)
6. [Complete Use Case](#complete-use-case)

---

## 🎯 RINGKASAN

Dokumentasi ini menjelaskan manajemen elemen kompetensi (lesson) dalam unit:
1. **CRUD Lesson** - Create, Read, Update, Delete lesson
2. **Content Upload** - Upload konten (markdown, video, dokumen)
3. **Reorder** - Mengatur urutan lesson
4. **Duplicate** - Menduplikasi lesson
5. **Statistics** - Melihat statistik lesson

### Fitur Utama
- ✅ CRUD lengkap untuk elemen kompetensi
- ✅ Rich content editor (markdown)
- ✅ Media upload (video, dokumen, gambar)
- ✅ Drag & drop reordering
- ✅ Duplicate functionality
- ✅ Soft delete & restore
- ✅ Statistics & analytics

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

## 📚 CRUD ELEMEN KOMPETENSI

### 3.1. GET [Admin] Elemen - Daftar Elemen dalam Unit

Melihat semua elemen/lesson dalam unit dengan urutan yang benar.

#### Endpoint
```
GET /admin/units/{unit_id}/lessons
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

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `include` | string | ❌ No | - | Includes: `unit`, `statistics` |
| `sort` | string | ❌ No | order | Sorting: `order`, `title`, `created_at` |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Daftar elemen berhasil diambil",
  "data": [
    {
      "id": 1,
      "unit_id": 1,
      "title": "Introduction",
      "slug": "introduction",
      "description": "Course introduction",
      "order": 1,
      "content_type": "markdown",
      "duration_minutes": 15,
      "status": "published",
      "has_content": true,
      "created_at": "2026-01-15T10:00:00.000000Z",
      "updated_at": "2026-03-15T10:00:00.000000Z"
    },
    {
      "id": 2,
      "unit_id": 1,
      "title": "Setup Environment",
      "slug": "setup-environment",
      "description": "How to setup your environment",
      "order": 2,
      "content_type": "video",
      "duration_minutes": 30,
      "status": "published",
      "has_content": true,
      "video_url": "https://api.levl.id/storage/videos/setup.mp4",
      "created_at": "2026-01-15T10:30:00.000000Z",
      "updated_at": "2026-03-15T10:00:00.000000Z"
    }
  ]
}
```

#### Postman Example
```javascript
// URL
{{base_url}}/admin/units/{{unit_id}}/lessons

// Query Params
include: statistics
sort: order

// Tests
pm.test("Status 200", () => pm.response.to.have.status(200));
pm.test("Has lessons", () => {
    const data = pm.response.json().data;
    pm.expect(data).to.be.an('array');
});
pm.test("Lessons ordered correctly", () => {
    const data = pm.response.json().data;
    if (data.length > 1) {
        pm.expect(data[0].order).to.be.below(data[1].order);
    }
});

// Save first lesson
if (pm.response.json().data.length > 0) {
    pm.environment.set("lesson_id", pm.response.json().data[0].id);
    pm.environment.set("lesson_slug", pm.response.json().data[0].slug);
}
```

---

### 3.2. GET [Admin] Elemen - Detail Elemen

Melihat detail lengkap elemen termasuk konten.

#### Endpoint
```
GET /admin/lessons/{lesson_id}
```

#### Authorization
```
Bearer Token Required (Admin/Superadmin only)
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `lesson_id` | integer | ✅ Yes | ID lesson |

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `include` | string | ❌ No | Includes: `unit`, `unit.course` |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Detail elemen berhasil diambil",
  "data": {
    "id": 1,
    "unit_id": 1,
    "title": "Introduction",
    "slug": "introduction",
    "description": "Course introduction",
    "markdown_content": "# Introduction\n\nWelcome to the course...",
    "content_type": "markdown",
    "content_url": null,
    "order": 1,
    "duration_minutes": 15,
    "status": "published",
    "unit": {
      "id": 1,
      "title": "Getting Started",
      "slug": "getting-started",
      "course": {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "introduction-to-programming"
      }
    },
    "stats": {
      "total_completions": 120,
      "completion_rate": 80,
      "average_time_spent": 18
    },
    "created_at": "2026-01-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z"
  }
}
```
