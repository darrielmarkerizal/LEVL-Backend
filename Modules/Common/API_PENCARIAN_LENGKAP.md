# DOKUMENTASI API PENCARIAN LENGKAP - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Common - Search  
**Platform**: Shared (Semua Platform)

---

## 📋 DAFTAR ISI

1. [Ringkasan](#ringkasan)
2. [Base URL & Headers](#base-url--headers)
3. [Endpoints Pencarian Global](#endpoints-pencarian-global)
   - [1. Pencarian Global](#1-get-shared-pencarian---pencarian-global)
   - [2. Autocomplete](#2-get-shared-pencarian---autocomplete)
   - [3. Cari Skema](#3-get-shared-pencarian---cari-skema)
   - [4. Cari Pengguna](#4-get-shared-pencarian---cari-pengguna)
   - [5. Cari Konten](#5-get-shared-pencarian---cari-konten)
4. [Endpoints Riwayat Pencarian](#endpoints-riwayat-pencarian)
   - [6. Lihat Riwayat](#6-get-shared-pencarian---lihat-riwayat)
   - [7. Hapus Riwayat](#7-delete-shared-pencarian---hapus-riwayat)
   - [8. Hapus Item Riwayat](#8-delete-shared-pencarian---hapus-item-riwayat)
5. [Response Format](#response-format)
6. [Search Types](#search-types)
7. [Error Codes](#error-codes)
8. [Contoh Use Case](#contoh-use-case)

---

## 🎯 RINGKASAN

API Pencarian Levl menyediakan endpoint untuk mencari berbagai resource dalam sistem termasuk skema (courses), pengguna, dan konten pembelajaran. API ini mendukung pencarian global, autocomplete, dan manajemen riwayat pencarian.

### Fitur Utama
- ✅ Pencarian global di semua resource
- ✅ Autocomplete untuk search suggestions
- ✅ Pencarian spesifik per resource type
- ✅ Filter dan sorting hasil pencarian
- ✅ Pagination untuk hasil banyak
- ✅ Riwayat pencarian pengguna
- ✅ Relevance scoring untuk hasil terbaik
- ✅ Support multiple search fields

---

## 🌐 BASE URL & HEADERS

### Base URL
```
Development:  http://localhost:8000/api/v1
Staging:      https://staging-api.levl.id/api/v1
Production:   https://api.levl.id/api/v1
```

### Headers Standar (Semua Endpoint)
```http
Content-Type: application/json
Accept: application/json
Accept-Language: id
Authorization: Bearer {{auth_token}}
```

**Note**: Beberapa endpoint pencarian dapat diakses tanpa autentikasi (public search), namun dengan autentikasi akan mendapat hasil yang lebih personal dan akses ke riwayat pencarian.

---

## 🔎 ENDPOINTS PENCARIAN GLOBAL

### 1. GET [Shared] Pencarian - Pencarian Global

Mencari di semua resource (skema, pengguna, forum) sekaligus dengan limit 5 per kategori.

#### Endpoint
```
GET /search/global
```

#### Authorization
```
Bearer Token Optional (hasil lebih baik jika authenticated)
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | ✅ Yes | - | Keyword pencarian (min: 1 karakter) |
| `type` | string | ❌ No | all | Tipe resource: `all`, `courses`, `units`, `lessons`, `users`, `forums` |

#### Valid Values

**q** (query):
- Minimal: 1 karakter
- Maksimal: 255 karakter
- Contoh: `"programming"`, `"web development"`, `"john doe"`

**type**:
- `all` - Cari di semua resource (courses, users, forums)
- `courses` - Hanya cari skema/kursus
- `units` - Hanya cari unit kompetensi
- `lessons` - Hanya cari elemen/pelajaran (requires authentication)
- `users` - Hanya cari pengguna (requires authentication)
- `forums` - Hanya cari forum (requires authentication)

**Note**: 
- Courses dan Units bersifat PUBLIC (dapat diakses tanpa autentikasi)
- Lessons, Users, dan Forums bersifat RESTRICTED (memerlukan autentikasi)
- Hasil dibatasi 5 item per kategori untuk performa optimal

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Search results retrieved successfully",
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "introduction-to-programming",
        "description": "Learn the basics of programming",
        "thumbnail": "https://api.levl.id/storage/courses/intro-prog.jpg",
        "instructor": {
          "id": 2,
          "name": "John Doe"
        },
        "enrollments_count": 150
      }
    ],
    "users": [
      {
        "id": 5,
        "name": "Jane Smith",
        "username": "jane_smith",
        "email": "jane@example.com",
        "avatar_url": "https://api.levl.id/storage/avatars/jane.jpg",
        "roles": [
          {
            "id": 3,
            "name": "Student"
          }
        ]
      }
    ],
    "forums": [
      {
        "id": 10,
        "title": "How to use variables?",
        "slug": "how-to-use-variables",
        "course_id": 1,
        "user_id": 5,
        "created_at": "2026-03-10T10:00:00.000000Z"
      }
    ]
  }
}
```

**Note**: 
- Jika tidak authenticated, hanya `courses` yang akan dikembalikan
- `users` dan `forums` hanya muncul jika user authenticated
- Setiap kategori dibatasi maksimal 5 hasil

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "q": [
      "The q field is required.",
      "The q must be at least 2 characters."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}} // Optional

// Query Params
q: programming
type: all

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Search results received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.be.an('object');
});

pm.test("Results grouped by type", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('courses');
    // users and forums only if authenticated
});

pm.test("Response time < 1000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(1000);
});
```

---

### 1.1 GET [Shared] Pencarian - Pencarian dengan Filter

Mencari data dengan filter, sorting, dan pagination menggunakan Spatie Query Builder.

#### Endpoint
```
GET /search
```

#### Authorization
```
Bearer Token Optional (required untuk lessons dan users)
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | ✅ Yes | - | Keyword pencarian (min: 1 karakter) |
| `type` | string | ❌ No | courses | Tipe resource: `courses`, `units`, `lessons`, `users` |
| `filter[status]` | string | ❌ No | - | Filter status (untuk courses) |
| `filter[category_id]` | integer | ❌ No | - | Filter kategori (untuk courses) |
| `filter[level_tag]` | string | ❌ No | - | Filter level (untuk courses) |
| `filter[instructor_id]` | integer | ❌ No | - | Filter instruktur (untuk courses) |
| `filter[course_id]` | integer | ❌ No | - | Filter course (untuk units/lessons) |
| `filter[unit_id]` | integer | ❌ No | - | Filter unit (untuk lessons) |
| `filter[role]` | string | ❌ No | - | Filter role (untuk users) |
| `sort` | string | ❌ No | -created_at | Field sorting (prefix `-` untuk desc) |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**type**:
- `courses` - Cari skema/kursus (PUBLIC)
- `units` - Cari unit kompetensi (PUBLIC)
- `lessons` - Cari elemen/pelajaran (RESTRICTED - requires auth)
- `users` - Cari pengguna (RESTRICTED - requires auth)

**filter[status]** (courses):
- `published` - Hanya kursus published
- `draft` - Hanya draft (admin/instructor only)

**filter[level_tag]** (courses):
- `beginner` - Level pemula
- `intermediate` - Level menengah
- `advanced` - Level lanjut

**filter[role]** (users):
- `Student` - Hanya student
- `Instructor` - Hanya instructor (admin only)
- `Admin` - Hanya admin (superadmin only)

**sort** (courses):
- `title` - Berdasarkan judul
- `created_at` - Berdasarkan tanggal dibuat
- `updated_at` - Berdasarkan tanggal update
- Prefix `-` untuk descending (contoh: `-created_at`)

**sort** (units):
- `title` - Berdasarkan judul
- `order` - Berdasarkan urutan
- `created_at` - Berdasarkan tanggal dibuat

**sort** (lessons):
- `title` - Berdasarkan judul
- `order` - Berdasarkan urutan
- `created_at` - Berdasarkan tanggal dibuat

**sort** (users):
- `name` - Berdasarkan nama
- `email` - Berdasarkan email
- `created_at` - Berdasarkan tanggal registrasi

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Search results retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "introduction-to-programming",
        "description": "Learn the basics of programming",
        "status": "published",
        "level_tag": "beginner",
        "instructor": {
          "id": 2,
          "name": "John Doe"
        },
        "enrollments_count": 150,
        "created_at": "2026-01-15T10:00:00.000000Z"
      }
    ],
    "links": {
      "first": "http://api.levl.id/api/v1/search?page=1",
      "last": "http://api.levl.id/api/v1/search?page=3",
      "prev": null,
      "next": "http://api.levl.id/api/v1/search?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 3,
      "path": "http://api.levl.id/api/v1/search",
      "per_page": 15,
      "to": 15,
      "total": 42,
      "query": "programming",
      "execution_time": 0.0234
    }
  }
}
```

#### Authorization Rules

**Courses & Units** (PUBLIC):
- Dapat diakses tanpa autentikasi
- Semua role dapat melihat semua courses dan units

**Lessons** (RESTRICTED):
- Requires authentication
- Student: Hanya lessons dari courses yang enrolled (status: active/completed)
- Instructor: Hanya lessons dari courses yang dikelola
- Admin/SuperAdmin: Semua lessons

**Users** (RESTRICTED):
- Requires authentication
- Student: Hanya dapat mencari student lain
- Instructor: Dapat mencari semua student
- Admin/SuperAdmin: Dapat mencari semua user

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}} // Required for lessons/users

// Query Params
q: programming
type: courses
filter[status]: published
filter[level_tag]: beginner
filter[category_id]: 1
sort: -created_at
per_page: 20
page: 1

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Paginated results received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.data).to.be.an('array');
    pm.expect(jsonData.data.meta).to.have.property('total');
});

pm.test("Filters applied correctly", function () {
    var jsonData = pm.response.json();
    if (jsonData.data.data.length > 0) {
        pm.expect(jsonData.data.data[0]).to.have.property('status', 'published');
        pm.expect(jsonData.data.data[0]).to.have.property('level_tag', 'beginner');
    }
});

pm.test("Response time < 1000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(1000);
});
```

---

### 2. GET [Shared] Pencarian - Autocomplete

Mendapatkan suggestions untuk autocomplete saat user mengetik.

#### Endpoint
```
GET /search/autocomplete
```

#### Authorization
```
Bearer Token Optional
```

#### Rate Limit
```
120 requests per minute (lebih tinggi untuk UX yang baik)
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | ✅ Yes | - | Keyword pencarian (min: 2 karakter) |
| `type` | string | ❌ No | all | Tipe resource untuk suggestions |
| `limit` | integer | ❌ No | 10 | Jumlah suggestions (max: 20) |

#### Valid Values

**q** (query):
- Minimal: 1 karakter
- Maksimal: 100 karakter
- Contoh: `"prog"`, `"web"`, `"john"`

**type**:
- `all` - Suggestions dari semua resource
- `courses` - Hanya suggestions skema
- `users` - Hanya suggestions pengguna (requires auth)
- `content` - Hanya suggestions konten (requires auth)

**limit**:
- Minimal: 1
- Maksimal: 20
- Default: 10

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Autocomplete suggestions retrieved successfully",
  "data": [
    "Introduction to Programming",
    "Programming Fundamentals",
    "Web Programming",
    "Advanced Programming Concepts",
    "Programming Best Practices"
  ]
}
```

**Note**: 
- Response berupa array sederhana dari string suggestions
- Diurutkan berdasarkan relevansi dengan query
- Hanya courses yang published yang muncul dalam suggestions

#### Response Error (422 Validation Error)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "q": [
      "The q must be at least 1 characters."
    ]
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}} // Optional

// Query Params
q: prog
limit: 10

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Suggestions received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.be.an('array');
});

pm.test("Response time < 300ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(300);
});
```

---

### 3. GET [Shared] Pencarian - Cari Skema

Mencari skema/kursus dengan filter lebih detail menggunakan Spatie Query Builder.

**Note**: Endpoint ini sama dengan `GET /search?type=courses`. Dokumentasi ini untuk referensi lengkap filter courses.

#### Endpoint
```
GET /search?type=courses
```

#### Authorization
```
Bearer Token Optional
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | ✅ Yes | - | Keyword pencarian |
| `type` | string | ✅ Yes | courses | Tipe resource (harus `courses`) |
| `filter[status]` | string | ❌ No | - | Filter status: `published`, `draft` |
| `filter[level_tag]` | string | ❌ No | - | Filter level: `beginner`, `intermediate`, `advanced` |
| `filter[type]` | string | ❌ No | - | Filter tipe course |
| `filter[category_id]` | integer | ❌ No | - | Filter berdasarkan kategori |
| `filter[instructor_id]` | integer | ❌ No | - | Filter berdasarkan instruktur |
| `sort` | string | ❌ No | -created_at | Sorting field (prefix `-` untuk desc) |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[level_tag]**:
- `beginner` - Kursus untuk pemula
- `intermediate` - Kursus tingkat menengah
- `advanced` - Kursus tingkat lanjut

**filter[status]**:
- `published` - Hanya kursus yang dipublikasikan
- `draft` - Hanya draft (admin/instruktur only)

**sort**:
- `title` - Berdasarkan judul
- `created_at` - Berdasarkan tanggal dibuat
- `updated_at` - Berdasarkan tanggal update
- Prefix `-` untuk descending (contoh: `-created_at`, `-title`)

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Search results retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "introduction-to-programming",
        "description": "Learn the basics of programming with hands-on examples",
        "status": "published",
        "level_tag": "beginner",
        "instructor": {
          "id": 2,
          "name": "John Doe"
        },
        "enrollments_count": 150,
        "created_at": "2026-01-15T10:00:00.000000Z"
      }
    ],
    "links": {
      "first": "http://api.levl.id/api/v1/search?page=1",
      "last": "http://api.levl.id/api/v1/search?page=3",
      "prev": null,
      "next": "http://api.levl.id/api/v1/search?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 3,
      "path": "http://api.levl.id/api/v1/search",
      "per_page": 15,
      "to": 15,
      "total": 42,
      "query": "programming",
      "execution_time": 0.0234
    }
  }
}
```

#### Postman Example
```javascript
// Query Params
q: programming
type: courses
filter[status]: published
filter[level_tag]: beginner
filter[category_id]: 1
sort: -created_at
per_page: 15
page: 1

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Courses array received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.data).to.be.an('array');
});

pm.test("Each course has required fields", function () {
    var jsonData = pm.response.json();
    if (jsonData.data.data.length > 0) {
        pm.expect(jsonData.data.data[0]).to.have.property('id');
        pm.expect(jsonData.data.data[0]).to.have.property('title');
        pm.expect(jsonData.data.data[0]).to.have.property('instructor');
    }
});
```

---

### 4. GET [Shared] Pencarian - Cari Pengguna

Mencari pengguna (student, instruktur, admin) menggunakan Spatie Query Builder.

**Note**: Endpoint ini sama dengan `GET /search?type=users`. Dokumentasi ini untuk referensi lengkap filter users.

#### Endpoint
```
GET /search?type=users
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | ✅ Yes | - | Keyword pencarian |
| `type` | string | ✅ Yes | users | Tipe resource (harus `users`) |
| `filter[status]` | string | ❌ No | - | Filter status: `active`, `inactive` |
| `filter[role]` | string | ❌ No | - | Filter role: `Student`, `Instructor`, `Admin` |
| `sort` | string | ❌ No | name | Sorting field (prefix `-` untuk desc) |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**filter[role]**:
- `Student` - Hanya student/partisipan
- `Instructor` - Hanya instruktur (admin only)
- `Admin` - Hanya admin (superadmin only)

**filter[status]**:
- `active` - Hanya user aktif
- `inactive` - Hanya user tidak aktif

**sort**:
- `name` - Berdasarkan nama
- `email` - Berdasarkan email
- `created_at` - Berdasarkan tanggal registrasi
- Prefix `-` untuk descending (contoh: `-name`, `-created_at`)

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Search results retrieved successfully",
  "data": {
    "data": [
      {
        "id": 5,
        "name": "Jane Smith",
        "username": "jane_smith",
        "email": "jane@example.com",
        "status": "active",
        "roles": [
          {
            "id": 3,
            "name": "Student"
          }
        ],
        "created_at": "2026-02-10T10:00:00.000000Z"
      }
    ],
    "links": {
      "first": "http://api.levl.id/api/v1/search?page=1",
      "last": "http://api.levl.id/api/v1/search?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://api.levl.id/api/v1/search",
      "per_page": 15,
      "to": 8,
      "total": 8,
      "query": "jane",
      "execution_time": 0.0156
    }
  }
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Query Params
q: jane
type: users
filter[role]: Student
filter[status]: active
sort: name
per_page: 15
page: 1

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Users array received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.data).to.be.an('array');
});
```

---

### 5. GET [Shared] Pencarian - Cari Konten

Mencari konten pembelajaran (unit, elemen/lessons) menggunakan Spatie Query Builder.

**Note**: Endpoint ini menggunakan `GET /search?type=units` atau `GET /search?type=lessons`.

#### Endpoint
```
GET /search?type=units
GET /search?type=lessons
```

#### Authorization
```
Bearer Token Optional (units), Required (lessons)
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | ✅ Yes | - | Keyword pencarian |
| `type` | string | ✅ Yes | - | Tipe resource: `units` atau `lessons` |
| `filter[course_id]` | integer | ❌ No | - | Filter berdasarkan kursus |
| `filter[unit_id]` | integer | ❌ No | - | Filter berdasarkan unit (hanya untuk lessons) |
| `sort` | string | ❌ No | order | Sorting field (prefix `-` untuk desc) |
| `per_page` | integer | ❌ No | 15 | Item per halaman (max: 100) |
| `page` | integer | ❌ No | 1 | Nomor halaman |

#### Valid Values

**type**:
- `units` - Cari unit kompetensi (PUBLIC)
- `lessons` - Cari elemen kompetensi/pelajaran (RESTRICTED)

**sort** (units):
- `title` - Berdasarkan judul
- `order` - Berdasarkan urutan (default)
- `created_at` - Berdasarkan tanggal dibuat
- Prefix `-` untuk descending

**sort** (lessons):
- `title` - Berdasarkan judul
- `order` - Berdasarkan urutan (default)
- `created_at` - Berdasarkan tanggal dibuat
- Prefix `-` untuk descending

#### Response Success (200 OK) - Units
```json
{
  "success": true,
  "message": "Search results retrieved successfully",
  "data": {
    "data": [
      {
        "id": 3,
        "title": "Basic Concepts",
        "description": "Learn the fundamental concepts",
        "order": 1,
        "course": {
          "id": 1,
          "title": "Introduction to Programming",
          "slug": "introduction-to-programming"
        },
        "created_at": "2026-01-18T10:00:00.000000Z"
      }
    ],
    "links": {
      "first": "http://api.levl.id/api/v1/search?page=1",
      "last": "http://api.levl.id/api/v1/search?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://api.levl.id/api/v1/search",
      "per_page": 15,
      "to": 5,
      "total": 5,
      "query": "basic",
      "execution_time": 0.0123
    }
  }
}
```

#### Response Success (200 OK) - Lessons
```json
{
  "success": true,
  "message": "Search results retrieved successfully",
  "data": {
    "data": [
      {
        "id": 20,
        "title": "Variables and Data Types",
        "description": "Learn about different data types in programming",
        "order": 2,
        "unit": {
          "id": 3,
          "title": "Basic Concepts",
          "course": {
            "id": 1,
            "title": "Introduction to Programming"
          }
        },
        "created_at": "2026-01-20T10:00:00.000000Z"
      }
    ],
    "links": {
      "first": "http://api.levl.id/api/v1/search?page=1",
      "last": "http://api.levl.id/api/v1/search?page=2",
      "prev": null,
      "next": "http://api.levl.id/api/v1/search?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 2,
      "path": "http://api.levl.id/api/v1/search",
      "per_page": 15,
      "to": 15,
      "total": 28,
      "query": "variables",
      "execution_time": 0.0189
    }
  }
}
```

#### Postman Example - Search Units
```javascript
// Query Params
q: basic
type: units
filter[course_id]: 1
sort: order
per_page: 15
page: 1

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Units array received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.data).to.be.an('array');
});

pm.test("Each unit has course", function () {
    var jsonData = pm.response.json();
    if (jsonData.data.data.length > 0) {
        pm.expect(jsonData.data.data[0]).to.have.property('course');
    }
});
```

#### Postman Example - Search Lessons
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Query Params
q: variables
type: lessons
filter[unit_id]: 3
sort: order
per_page: 15
page: 1

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Lessons array received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.data).to.be.an('array');
});

pm.test("Each lesson has unit", function () {
    var jsonData = pm.response.json();
    if (jsonData.data.data.length > 0) {
        pm.expect(jsonData.data.data[0]).to.have.property('unit');
    }
});
```

---

## 📜 ENDPOINTS RIWAYAT PENCARIAN

### 6. GET [Shared] Pencarian - Lihat Riwayat

Mendapatkan riwayat pencarian pengguna.

#### Endpoint
```
GET /search/history
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | ❌ No | 20 | Jumlah riwayat (max: 100) |

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Search history retrieved successfully",
  "data": [
    {
      "id": 1,
      "query": "programming basics",
      "type": "all",
      "results_count": 25,
      "searched_at": "2026-03-15T10:00:00.000000Z"
    },
    {
      "id": 2,
      "query": "web development",
      "type": "courses",
      "results_count": 12,
      "searched_at": "2026-03-14T15:30:00.000000Z"
    },
    {
      "id": 3,
      "query": "javascript",
      "type": "content",
      "results_count": 45,
      "searched_at": "2026-03-14T14:20:00.000000Z"
    }
  ],
  "meta": {
    "total": 15,
    "limit": 20
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// Query Params
limit: 20

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("History array received", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.be.an('array');
});

pm.test("Each history has query", function () {
    var jsonData = pm.response.json();
    if (jsonData.data.length > 0) {
        pm.expect(jsonData.data[0]).to.have.property('query');
        pm.expect(jsonData.data[0]).to.have.property('searched_at');
    }
});

// Save first history ID for deletion test
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.data.length > 0) {
        pm.environment.set("history_id", jsonData.data[0].id);
    }
}
```

---

### 7. DELETE [Shared] Pencarian - Hapus Riwayat

Menghapus semua riwayat pencarian pengguna.

#### Endpoint
```
DELETE /search/history
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Request Body
```
No body required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Search history deleted successfully",
  "data": {
    "deleted_count": 15
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// No body required

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("History deleted", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data).to.have.property('deleted_count');
});
```

---

### 8. DELETE [Shared] Pencarian - Hapus Item Riwayat

Menghapus satu item riwayat pencarian berdasarkan ID.

#### Endpoint
```
DELETE /search/history/{id}
```

#### Authorization
```
Bearer Token Required
```

#### Rate Limit
```
60 requests per minute
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | ✅ Yes | ID riwayat pencarian |

#### Request Body
```
No body required
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Search history item deleted successfully",
  "data": null
}
```

#### Response Error (404 Not Found)
```json
{
  "success": false,
  "message": "Search history item not found",
  "errors": {}
}
```

#### Postman Example
```javascript
// Headers
Authorization: Bearer {{auth_token}}

// URL
{{base_url}}/search/history/{{history_id}}

// Tests
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("History item deleted", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

// Clear history ID
if (pm.response.code === 200) {
    pm.environment.unset("history_id");
}
```

---

## 📊 RESPONSE FORMAT

### Success Response Structure
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // Response data
  },
  "meta": {
    // Metadata (pagination, filters, etc)
  }
}
```

### Error Response Structure
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

### Course Search Result Structure
```json
{
  "id": 1,
  "title": "Introduction to Programming",
  "slug": "introduction-to-programming",
  "description": "Learn the basics of programming",
  "thumbnail": "https://api.levl.id/storage/courses/intro-prog.jpg",
  "level": "beginner",
  "status": "published",
  "category": {
    "id": 1,
    "name": "Programming"
  },
  "instructor": {
    "id": 2,
    "name": "John Doe",
    "avatar_url": "https://api.levl.id/storage/avatars/john.jpg"
  },
  "stats": {
    "total_students": 150,
    "total_units": 10,
    "total_lessons": 45,
    "average_rating": 4.5
  },
  "relevance_score": 0.95,
  "created_at": "2026-01-15T10:00:00.000000Z"
}
```

### User Search Result Structure
```json
{
  "id": 5,
  "name": "Jane Smith",
  "username": "jane_smith",
  "email": "jane@example.com",
  "avatar_url": "https://api.levl.id/storage/avatars/jane.jpg",
  "role": "Student",
  "status": "active",
  "bio": "Passionate learner",
  "stats": {
    "total_courses": 5,
    "completed_courses": 2,
    "total_xp": 1250,
    "current_level": 5
  },
  "relevance_score": 0.85,
  "created_at": "2026-02-10T10:00:00.000000Z"
}
```

### Content Search Result Structure
```json
{
  "id": 20,
  "type": "lesson",
  "title": "Variables and Data Types",
  "description": "Learn about different data types",
  "course": {
    "id": 1,
    "title": "Introduction to Programming",
    "slug": "introduction-to-programming"
  },
  "unit": {
    "id": 3,
    "title": "Basic Concepts"
  },
  "order": 2,
  "duration_minutes": 30,
  "relevance_score": 0.90,
  "created_at": "2026-01-20T10:00:00.000000Z"
}
```

### Search History Structure
```json
{
  "id": 1,
  "query": "programming basics",
  "type": "all",
  "results_count": 25,
  "searched_at": "2026-03-15T10:00:00.000000Z"
}
```

---

## 🔍 SEARCH TYPES

### Search Algorithm

API pencarian Levl menggunakan algoritma full-text search dengan relevance scoring:

1. **Exact Match** (Score: 1.0)
   - Query exact match dengan title/name
   - Contoh: Query "Programming" → Title "Programming"

2. **Starts With** (Score: 0.9)
   - Title/name dimulai dengan query
   - Contoh: Query "Prog" → Title "Programming Basics"

3. **Contains** (Score: 0.7)
   - Title/name mengandung query
   - Contoh: Query "gram" → Title "Introduction to Programming"

4. **Word Match** (Score: 0.6)
   - Salah satu kata dalam title/name match dengan query
   - Contoh: Query "basics" → Title "Programming Basics Tutorial"

5. **Partial Match** (Score: 0.4)
   - Partial match di description atau content
   - Contoh: Query "variable" → Description contains "variables"

### Search Fields by Type

#### Courses
- `title` (weight: 1.0)
- `description` (weight: 0.7)
- `instructor.name` (weight: 0.5)
- `category.name` (weight: 0.6)
- `tags` (weight: 0.5)

#### Users
- `name` (weight: 1.0)
- `username` (weight: 0.9)
- `email` (weight: 0.8)
- `bio` (weight: 0.5)

#### Content
- `title` (weight: 1.0)
- `description` (weight: 0.7)
- `course.title` (weight: 0.6)
- `unit.title` (weight: 0.5)

---

## ⚠️ ERROR CODES

### HTTP Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request berhasil |
| 400 | Bad Request | Request tidak valid |
| 401 | Unauthorized | Authentication gagal atau token invalid |
| 403 | Forbidden | User tidak memiliki akses |
| 404 | Not Found | Resource tidak ditemukan |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Common Error Messages

#### Validation Errors
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "q": [
      "The q field is required.",
      "The q must be at least 2 characters.",
      "The q must not be greater than 255 characters."
    ],
    "per_page": [
      "The per page must not be greater than 50."
    ]
  }
}
```

#### Authentication Errors
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": {}
}
```

#### Not Found Errors
```json
{
  "success": false,
  "message": "Search history item not found",
  "errors": {}
}
```

---

## 📖 CONTOH USE CASE

### Use Case 1: Global Search Flow

```javascript
// Step 1: User types in search box (autocomplete)
GET /search/autocomplete?q=prog&limit=10
// Response: Suggestions list
// Display suggestions dropdown

// Step 2: User selects suggestion or presses enter
GET /search?q=programming&type=all&page=1
// Response: Global search results
// Display results grouped by type

// Step 3: User clicks on a course result
// Navigate to course detail page

// Step 4: Search is saved to history automatically
GET /search/history?limit=20
// Response: Updated history including latest search
```

### Use Case 2: Course Search with Filters

```javascript
// Step 1: User searches for courses
GET /search/courses?q=web development&page=1
// Response: All web development courses

// Step 2: User applies filters
GET /search/courses?q=web development&level=beginner&category_id=2&page=1
// Response: Filtered results

// Step 3: User changes sorting
GET /search/courses?q=web development&level=beginner&sort=students_count&direction=desc
// Response: Sorted by most popular

// Step 4: User views more results
GET /search/courses?q=web development&level=beginner&page=2
// Response: Page 2 results
```

### Use Case 3: Search History Management

```javascript
// Step 1: View search history
GET /search/history?limit=20
// Response: List of recent searches

// Step 2: Delete specific history item
DELETE /search/history/5
// Response: Item deleted

// Step 3: Clear all history
DELETE /search/history
// Response: All history deleted

// Step 4: Verify history cleared
GET /search/history?limit=20
// Response: Empty list
```

### Use Case 4: Mobile App Search Flow

```javascript
// Step 1: App opens search screen
// Show popular searches and recent history
GET /search/history?limit=5
// Display recent searches

// Step 2: User starts typing
GET /search/autocomplete?q=ja&limit=10
// Response: Suggestions starting with "ja"
// Update suggestions in real-time

// Step 3: User continues typing
GET /search/autocomplete?q=java&limit=10
// Response: More specific suggestions
// Update suggestions

// Step 4: User selects "JavaScript Tutorial"
GET /search/courses?q=JavaScript Tutorial
// Response: Matching courses
// Display results

// Step 5: User taps on a course
// Navigate to course detail
// Search saved to history automatically
```

### Use Case 5: Content Search within Course

```javascript
// Step 1: User is viewing a course
// Opens search within course

// Step 2: Search for specific topic
GET /search/content?q=loops&course_id=1&content_type=lesson
// Response: Lessons about loops in this course

// Step 3: User wants to see assignments too
GET /search/content?q=loops&course_id=1&content_type=all
// Response: All content types about loops

// Step 4: User clicks on a lesson
// Navigate to lesson page
```

---

## 🔒 SECURITY BEST PRACTICES

### For Frontend/Mobile Developers

1. **Search Input**
   - Debounce search input (300-500ms)
   - Validate minimum 2 characters before search
   - Sanitize user input before sending
   - Limit search query length (max 255 chars)

2. **Autocomplete**
   - Implement debouncing for autocomplete
   - Cancel previous requests when new input
   - Cache autocomplete results locally
   - Show loading state during search

3. **Search Results**
   - Implement pagination or infinite scroll
   - Cache search results locally
   - Show empty state when no results
   - Provide filter/sort options

4. **Search History**
   - Store history locally for offline access
   - Sync with server when online
   - Provide option to clear history
   - Limit history display (e.g., last 20)

5. **Performance**
   - Lazy load search results
   - Optimize images in results
   - Implement virtual scrolling for long lists
   - Use skeleton loaders

### For Backend Developers

1. **Search Performance**
   - Use full-text search indexes
   - Implement search result caching
   - Optimize database queries
   - Use search engine (Elasticsearch, Algolia)

2. **Security**
   - Sanitize search queries
   - Prevent SQL injection
   - Rate limit search endpoints
   - Log suspicious search patterns

3. **Relevance**
   - Implement relevance scoring
   - Track search result clicks
   - Use machine learning for better results
   - A/B test search algorithms

4. **History Management**
   - Store search history efficiently
   - Implement automatic cleanup (>90 days)
   - Index history for fast retrieval
   - Respect user privacy settings

5. **Monitoring**
   - Track popular searches
   - Monitor search performance
   - Log failed searches
   - Alert on unusual patterns

---

## 📝 POSTMAN COLLECTION SETUP

### Environment Variables

```json
{
  "base_url": "http://localhost:8000/api/v1",
  "auth_token": "",
  "user_id": "",
  "last_search_query": "",
  "history_id": "",
  "course_id": "",
  "category_id": ""
}
```

### Pre-request Script (Collection Level)

```javascript
// Set base URL
pm.variables.set("base_url", pm.environment.get("base_url"));

// Add timestamp for debugging
pm.variables.set("timestamp", new Date().toISOString());

// Log search query
if (pm.request.url.query.has("q")) {
    console.log("Search query:", pm.request.url.query.get("q"));
}
```

### Tests Script (Collection Level)

```javascript
// Log response time
console.log("Response time:", pm.response.responseTime + "ms");

// Check if response is JSON
pm.test("Response is JSON", function () {
    pm.response.to.be.json;
});

// Check response structure
pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
});

// Log errors if any
if (pm.response.code !== 200 && pm.response.code !== 201) {
    var jsonData = pm.response.json();
    console.error("Error:", jsonData.message);
    if (jsonData.errors) {
        console.error("Validation errors:", JSON.stringify(jsonData.errors, null, 2));
    }
}

// Log search results count
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.meta && jsonData.meta.total_results !== undefined) {
        console.log("Total results:", jsonData.meta.total_results);
    }
}
```

---

## 🎯 QUICK REFERENCE

### Search Endpoints
```
GET    /search                      - Pencarian dengan filter (Spatie Query Builder)
GET    /search/global               - Pencarian global (limit 5 per kategori)
GET    /search/autocomplete         - Autocomplete suggestions
```

### History Endpoints
```
GET    /search/history              - Lihat riwayat
DELETE /search/history              - Hapus semua riwayat
DELETE /search/history/{id}         - Hapus item riwayat
```

### Rate Limits
```
Search endpoints:       60 requests/minute
Autocomplete endpoint:  120 requests/minute
```

### Query Parameters (Spatie Query Builder Format)
```
q                       - Keyword pencarian (required, min: 1 char)
type                    - Tipe resource (courses, units, lessons, users, forums, all)
filter[status]          - Filter status
filter[level_tag]       - Filter level (courses)
filter[category_id]     - Filter kategori (courses)
filter[instructor_id]   - Filter instruktur (courses)
filter[course_id]       - Filter course (units, lessons)
filter[unit_id]         - Filter unit (lessons)
filter[role]            - Filter role (users)
sort                    - Field sorting (prefix `-` untuk desc)
per_page                - Item per halaman (default: 15, max: 100)
page                    - Nomor halaman (default: 1)
```

### Course Filters (Spatie Format)
```
filter[status]          - published, draft
filter[level_tag]       - beginner, intermediate, advanced
filter[type]            - Tipe course
filter[category_id]     - ID kategori
filter[instructor_id]   - ID instruktur
```

### User Filters (Spatie Format)
```
filter[status]          - active, inactive
filter[role]            - Student, Instructor, Admin
```

### Content Filters (Spatie Format)
```
filter[course_id]       - ID kursus (units, lessons)
filter[unit_id]         - ID unit (lessons only)
```

### Sorting (Spatie Format)
```
Courses:  title, created_at, updated_at
Units:    title, order, created_at
Lessons:  title, order, created_at
Users:    name, email, created_at

Prefix `-` untuk descending:
  sort=-created_at  (newest first)
  sort=title        (A-Z)
  sort=-title       (Z-A)
```

---

## 📞 SUPPORT & TROUBLESHOOTING

### Common Issues

**Issue: No Search Results**
- Solution: Check if query is at least 2 characters
- Solution: Try broader search terms
- Solution: Remove filters and try again
- Solution: Check if resources exist in database

**Issue: Slow Search Response**
- Solution: Reduce per_page value
- Solution: Use more specific search terms
- Solution: Apply filters to narrow results
- Solution: Check server performance

**Issue: Autocomplete Not Working**
- Solution: Ensure query is at least 2 characters
- Solution: Check debounce implementation
- Solution: Verify API endpoint is correct
- Solution: Check network connection

**Issue: Search History Not Saving**
- Solution: Verify user is authenticated
- Solution: Check if search was successful
- Solution: Verify API is saving history

**Issue: Irrelevant Search Results**
- Solution: Use more specific keywords
- Solution: Apply appropriate filters
- Solution: Use quotes for exact phrases
- Solution: Report to improve search algorithm

### Debug Tips

1. Check request query parameters
2. Verify authentication token (if required)
3. Review response error messages
4. Check API logs for detailed errors
5. Test with different search terms
6. Verify filters are valid
7. Check pagination parameters

---

## 🚀 IMPLEMENTATION TIPS

### React Search Component Example

```javascript
import { useState, useEffect, useCallback } from 'react';
import { debounce } from 'lodash';

export function SearchComponent() {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [suggestions, setSuggestions] = useState([]);
  const [loading, setLoading] = useState(false);

  // Debounced autocomplete
  const fetchSuggestions = useCallback(
    debounce(async (searchQuery) => {
      if (searchQuery.length < 2) {
        setSuggestions([]);
        return;
      }

      try {
        const response = await fetch(
          `/search/autocomplete?q=${encodeURIComponent(searchQuery)}&limit=10`
        );
        const data = await response.json();
        setSuggestions(data.data.suggestions);
      } catch (error) {
        console.error('Autocomplete error:', error);
      }
    }, 300),
    []
  );

  // Handle input change
  const handleInputChange = (e) => {
    const value = e.target.value;
    setQuery(value);
    fetchSuggestions(value);
  };

  // Perform search
  const handleSearch = async () => {
    if (query.length < 2) return;

    setLoading(true);
    try {
      const response = await fetch(
        `/search?q=${encodeURIComponent(query)}&type=all&page=1`
      );
      const data = await response.json();
      setResults(data.data);
    } catch (error) {
      console.error('Search error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="search-component">
      <input
        type="text"
        value={query}
        onChange={handleInputChange}
        onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
        placeholder="Cari skema, pengguna, atau konten..."
      />
      
      {suggestions.length > 0 && (
        <div className="suggestions">
          {suggestions.map((suggestion) => (
            <div
              key={suggestion.id}
              onClick={() => {
                setQuery(suggestion.text);
                setSuggestions([]);
                handleSearch();
              }}
              dangerouslySetInnerHTML={{ __html: suggestion.highlight }}
            />
          ))}
        </div>
      )}

      {loading && <div>Loading...</div>}

      {results && (
        <div className="results">
          {/* Display results */}
        </div>
      )}
    </div>
  );
}
```

### Mobile Search Implementation Example

```javascript
// React Native Search Screen
import React, { useState, useEffect } from 'react';
import { View, TextInput, FlatList, TouchableOpacity, Text } from 'react-native';

export function SearchScreen() {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(false);

  // Load search history on mount
  useEffect(() => {
    loadSearchHistory();
  }, []);

  const loadSearchHistory = async () => {
    try {
      const response = await fetch('/search/history?limit=5');
      const data = await response.json();
      setHistory(data.data);
    } catch (error) {
      console.error('Failed to load history:', error);
    }
  };

  const performSearch = async (searchQuery) => {
    if (searchQuery.length < 2) return;

    setLoading(true);
    try {
      const response = await fetch(
        `/search?q=${encodeURIComponent(searchQuery)}&type=all`
      );
      const data = await response.json();
      setResults(data.data);
      
      // Reload history after search
      loadSearchHistory();
    } catch (error) {
      console.error('Search failed:', error);
    } finally {
      setLoading(false);
    }
  };

  const deleteHistoryItem = async (id) => {
    try {
      await fetch(`/search/history/${id}`, { method: 'DELETE' });
      loadSearchHistory();
    } catch (error) {
      console.error('Failed to delete history:', error);
    }
  };

  return (
    <View style={{ flex: 1 }}>
      <TextInput
        value={query}
        onChangeText={setQuery}
        onSubmitEditing={() => performSearch(query)}
        placeholder="Cari..."
        style={{ padding: 10, borderWidth: 1 }}
      />

      {query.length === 0 && history.length > 0 && (
        <View>
          <Text>Pencarian Terakhir:</Text>
          <FlatList
            data={history}
            keyExtractor={(item) => item.id.toString()}
            renderItem={({ item }) => (
              <TouchableOpacity
                onPress={() => {
                  setQuery(item.query);
                  performSearch(item.query);
                }}
              >
                <Text>{item.query}</Text>
              </TouchableOpacity>
            )}
          />
        </View>
      )}

      {loading && <Text>Loading...</Text>}

      {results && (
        <FlatList
          data={[
            ...results.courses,
            ...results.users,
            ...results.content
          ]}
          keyExtractor={(item) => `${item.type}-${item.id}`}
          renderItem={({ item }) => (
            <TouchableOpacity>
              <Text>{item.title || item.name}</Text>
            </TouchableOpacity>
          )}
        />
      )}
    </View>
  );
}
```

### Backend Search Service Example

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    /**
     * Perform global search
     */
    public function globalSearch(string $query, array $filters = [])
    {
        // Cache key
        $cacheKey = "search:" . md5($query . json_encode($filters));
        
        // Try to get from cache
        return Cache::remember($cacheKey, 300, function () use ($query, $filters) {
            return [
                'courses' => $this->searchCourses($query, $filters),
                'users' => $this->searchUsers($query, $filters),
                'content' => $this->searchContent($query, $filters),
            ];
        });
    }

    /**
     * Search courses with relevance scoring
     */
    protected function searchCourses(string $query, array $filters = [])
    {
        return DB::table('courses')
            ->select([
                'courses.*',
                DB::raw("
                    CASE
                        WHEN LOWER(title) = LOWER(?) THEN 1.0
                        WHEN LOWER(title) LIKE LOWER(?) THEN 0.9
                        WHEN LOWER(title) LIKE LOWER(?) THEN 0.7
                        WHEN LOWER(description) LIKE LOWER(?) THEN 0.4
                        ELSE 0.0
                    END as relevance_score
                ")
            ])
            ->setBindings([
                $query,
                $query . '%',
                '%' . $query . '%',
                '%' . $query . '%'
            ])
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', '%' . $query . '%')
                  ->orWhere('description', 'LIKE', '%' . $query . '%');
            })
            ->orderBy('relevance_score', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Save search to history
     */
    public function saveToHistory(int $userId, string $query, string $type, int $resultsCount)
    {
        DB::table('search_history')->insert([
            'user_id' => $userId,
            'query' => $query,
            'type' => $type,
            'results_count' => $resultsCount,
            'searched_at' => now(),
        ]);
    }

    /**
     * Get autocomplete suggestions
     */
    public function getAutocompleteSuggestions(string $query, int $limit = 10)
    {
        $cacheKey = "autocomplete:" . md5($query);
        
        return Cache::remember($cacheKey, 600, function () use ($query, $limit) {
            // Get from courses
            $courseSuggestions = DB::table('courses')
                ->select('id', 'title as text', DB::raw("'course' as type"))
                ->where('title', 'LIKE', '%' . $query . '%')
                ->where('status', 'published')
                ->limit($limit)
                ->get();

            // Add highlighting
            return $courseSuggestions->map(function ($item) use ($query) {
                $item->highlight = $this->highlightMatch($item->text, $query);
                return $item;
            });
        });
    }

    /**
     * Highlight matching text
     */
    protected function highlightMatch(string $text, string $query): string
    {
        return preg_replace(
            '/(' . preg_quote($query, '/') . ')/i',
            '<mark>$1</mark>',
            $text
        );
    }
}
```

---

## 📊 PERFORMANCE OPTIMIZATION

### Caching Strategy

```javascript
// Client-side caching for search results
class SearchCache {
  constructor(ttl = 300000) { // 5 minutes
    this.cache = new Map();
    this.ttl = ttl;
  }

  set(key, value) {
    this.cache.set(key, {
      value,
      timestamp: Date.now()
    });
  }

  get(key) {
    const item = this.cache.get(key);
    if (!item) return null;

    // Check if expired
    if (Date.now() - item.timestamp > this.ttl) {
      this.cache.delete(key);
      return null;
    }

    return item.value;
  }

  clear() {
    this.cache.clear();
  }
}

// Usage
const searchCache = new SearchCache();

async function search(query) {
  const cacheKey = `search:${query}`;
  
  // Try cache first
  const cached = searchCache.get(cacheKey);
  if (cached) {
    return cached;
  }

  // Fetch from API
  const response = await fetch(`/search?q=${query}`);
  const data = await response.json();

  // Cache result
  searchCache.set(cacheKey, data);

  return data;
}
```

### Debouncing Implementation

```javascript
// Debounce utility
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Usage for autocomplete
const debouncedAutocomplete = debounce(async (query) => {
  const response = await fetch(`/search/autocomplete?q=${query}`);
  const data = await response.json();
  updateSuggestions(data.data.suggestions);
}, 300);

// Call on input change
inputElement.addEventListener('input', (e) => {
  debouncedAutocomplete(e.target.value);
});
```

---

**Dokumentasi ini mencakup semua endpoint pencarian yang tersedia di Levl API.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team  
**Contact**: backend@levl.id
```
