# Design Document

## Overview

Dokumen ini menjelaskan desain teknis untuk melengkapi dokumentasi API pada Scalar OpenAPI specification dengan fokus pada dokumentasi yang **actual dan spesifik** - bukan generic. Solusi utama adalah memperbarui `OpenApiGeneratorService` untuk:

1. Mengenali dan mendokumentasikan endpoint yang saat ini missing
2. Memastikan setiap endpoint memiliki summary, description, dan response examples yang spesifik
3. Menggunakan actual field names dan realistic values dari Resource/Transformer classes

## Architecture

### Current Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    OpenAPI Generation Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Laravel Routes ──► OpenApiGeneratorService ──► openapi.json    │
│                            │                                     │
│                            ▼                                     │
│                    ┌───────────────┐                            │
│                    │ featureGroups │ (keyword matching)          │
│                    └───────────────┘                            │
│                            │                                     │
│                            ▼                                     │
│                    Scalar UI (/scalar)                          │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Problem Analysis

`OpenApiGeneratorService` menggunakan keyword matching untuk mengkategorikan endpoint ke dalam feature groups. Masalah yang ditemukan:

1. **Missing Keywords**: Beberapa module seperti Content tidak memiliki keyword yang lengkap
2. **Generic Summaries**: Summary menggunakan pattern generic seperti "Membuat {resource}"
3. **Generic Examples**: Response examples menggunakan placeholder generic, bukan actual data structure
4. **Missing Endpoints**: Beberapa endpoint tidak ter-capture karena keyword tidak match


### Solution Design

```
┌─────────────────────────────────────────────────────────────────┐
│                 Enhanced OpenAPI Generation Flow                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Laravel Routes ──► OpenApiGeneratorService                     │
│                            │                                     │
│                            ▼                                     │
│                    ┌───────────────────┐                        │
│                    │ Updated Keywords  │ (Content, Profile, etc)│
│                    └───────────────────┘                        │
│                            │                                     │
│                            ▼                                     │
│                    ┌───────────────────┐                        │
│                    │ endpointExamples  │ (actual response data) │
│                    └───────────────────┘                        │
│                            │                                     │
│                            ▼                                     │
│                    ┌───────────────────┐                        │
│                    │ summaryOverrides  │ (specific summaries)   │
│                    └───────────────────┘                        │
│                            │                                     │
│                            ▼                                     │
│                    openapi.json ──► Scalar UI                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Enhanced featureGroups

Tambahkan keywords untuk endpoint yang missing:

```php
// Content Module
'berita' => [
    'keywords' => [
        'news', 'announcements', 'content/statistics', 
        'content/search', 'content/pending', 'content/submit',
        'content/approve', 'content/reject', 'courses/{course}/announcements'
    ],
],

// Profile Module  
'profil' => [
    'keywords' => [
        'profile', 'profile/privacy', 'profile/activities',
        'profile/statistics', 'profile/achievements', 'profile/password',
        'profile/account', 'profile/avatar', 'users/{user}/profile'
    ],
],

// Admin Users
'users' => [
    'keywords' => [
        'admin/users', 'suspend', 'activate', 'audit-logs'
    ],
],
```

### 2. Endpoint-Specific Examples Map

```php
protected array $endpointExamples = [
    // Enrollments
    'v1/courses/{course}/enroll' => [
        'post' => [
            'summary' => 'Mendaftarkan peserta ke kursus',
            'description' => 'Mendaftarkan user yang sedang login ke kursus tertentu. Jika kursus memerlukan enrollment key, key harus disertakan dalam request body.',
            'successExample' => [
                'message' => 'Berhasil mendaftar ke kursus.',
                'data' => [
                    'enrollment' => [
                        'id' => 1,
                        'user_id' => 5,
                        'course_id' => 10,
                        'status' => 'pending',
                        'enrolled_at' => '2024-01-15T10:30:00.000000Z',
                        'completed_at' => null,
                        'progress_percentage' => 0,
                    ]
                ]
            ],
            'errorExample' => [
                'message' => 'Enrollment key tidak valid.',
                'errors' => [
                    'enrollment_key' => ['Enrollment key yang Anda masukkan salah.']
                ]
            ]
        ]
    ],
    
    // Profile Privacy
    'v1/profile/privacy' => [
        'get' => [
            'summary' => 'Mengambil pengaturan privasi profil',
            'description' => 'Mengambil pengaturan privasi profil user yang sedang login.',
            'successExample' => [
                'data' => [
                    'show_email' => false,
                    'show_phone' => false,
                    'show_activity' => true,
                    'show_achievements' => true,
                ]
            ]
        ],
        'put' => [
            'summary' => 'Memperbarui pengaturan privasi profil',
            'description' => 'Memperbarui pengaturan privasi profil user yang sedang login.',
            'successExample' => [
                'message' => 'Pengaturan privasi berhasil diperbarui.',
                'data' => [
                    'show_email' => true,
                    'show_phone' => false,
                    'show_activity' => true,
                    'show_achievements' => true,
                ]
            ]
        ]
    ],
    
    // ... more endpoints
];
```


### 3. Summary Overrides Map

```php
protected array $summaryOverrides = [
    // Enrollments
    'v1/courses/{course}/enroll' => [
        'post' => 'Mendaftarkan peserta ke kursus'
    ],
    'v1/courses/{course}/unenroll' => [
        'delete' => 'Membatalkan pendaftaran dari kursus'
    ],
    
    // Profile
    'v1/profile/privacy' => [
        'get' => 'Mengambil pengaturan privasi profil',
        'put' => 'Memperbarui pengaturan privasi profil'
    ],
    'v1/profile/activities' => [
        'get' => 'Mengambil riwayat aktivitas pengguna'
    ],
    'v1/profile/achievements' => [
        'get' => 'Mengambil daftar pencapaian dan badge pengguna'
    ],
    'v1/profile/statistics' => [
        'get' => 'Mengambil statistik pembelajaran pengguna'
    ],
    'v1/profile/password' => [
        'put' => 'Mengubah password akun'
    ],
    'v1/profile/avatar' => [
        'post' => 'Mengunggah foto profil baru',
        'delete' => 'Menghapus foto profil'
    ],
    'v1/profile/account' => [
        'delete' => 'Menonaktifkan akun pengguna',
        'post' => 'Memulihkan akun yang dinonaktifkan'
    ],
    
    // Admin Users
    'v1/admin/users/{user}/profile' => [
        'get' => 'Mengambil profil pengguna (Admin)',
        'put' => 'Memperbarui profil pengguna (Admin)'
    ],
    'v1/admin/users/{user}/suspend' => [
        'post' => 'Menangguhkan akun pengguna'
    ],
    'v1/admin/users/{user}/activate' => [
        'post' => 'Mengaktifkan kembali akun pengguna'
    ],
    'v1/admin/users/{user}/audit-logs' => [
        'get' => 'Mengambil log audit aktivitas pengguna'
    ],
    
    // Assessments
    'v1/assessments/{assessment}/register' => [
        'post' => 'Mendaftar ke jadwal asesmen'
    ],
    'v1/assessments/{assessment}/prerequisites' => [
        'get' => 'Mengecek prasyarat asesmen'
    ],
    'v1/assessments/{assessment}/slots' => [
        'get' => 'Mengambil slot waktu asesmen yang tersedia'
    ],
    'v1/assessment-registrations/{registration}' => [
        'delete' => 'Membatalkan pendaftaran asesmen'
    ],
    
    // Forum Statistics
    'v1/schemes/{scheme}/forum/statistics' => [
        'get' => 'Mengambil statistik forum skema'
    ],
    'v1/schemes/{scheme}/forum/statistics/me' => [
        'get' => 'Mengambil statistik forum pengguna di skema'
    ],
    
    // Exports
    'v1/courses/{course}/exports/enrollments-csv' => [
        'get' => 'Mengekspor data pendaftaran ke CSV'
    ],
    
    // Learning Assignments
    'v1/courses/{course}/units/{unit}/lessons/{lesson}/assignments' => [
        'get' => 'Mengambil daftar tugas dalam lesson',
        'post' => 'Membuat tugas baru dalam lesson'
    ],
    
    // Content
    'v1/announcements' => [
        'get' => 'Mengambil daftar pengumuman',
        'post' => 'Membuat pengumuman baru'
    ],
    'v1/announcements/{announcement}' => [
        'get' => 'Mengambil detail pengumuman',
        'put' => 'Memperbarui pengumuman',
        'delete' => 'Menghapus pengumuman'
    ],
    'v1/news' => [
        'get' => 'Mengambil daftar berita',
        'post' => 'Membuat berita baru'
    ],
    'v1/news/trending' => [
        'get' => 'Mengambil berita trending'
    ],
    'v1/content/statistics' => [
        'get' => 'Mengambil statistik konten'
    ],
    'v1/content/search' => [
        'get' => 'Mencari konten'
    ],
];
```

## Data Models

### Enrollment Response Structure

```php
[
    'id' => 1,
    'user_id' => 5,
    'course_id' => 10,
    'status' => 'pending|active|completed|cancelled',
    'enrolled_at' => '2024-01-15T10:30:00.000000Z',
    'completed_at' => null,
    'progress_percentage' => 0,
    'last_accessed_at' => null,
    'course' => [
        'id' => 10,
        'title' => 'Dasar-Dasar Pemrograman',
        'slug' => 'dasar-dasar-pemrograman',
        'thumbnail_url' => 'https://...',
    ],
]
```

### Profile Privacy Response Structure

```php
[
    'show_email' => false,
    'show_phone' => false,
    'show_activity' => true,
    'show_achievements' => true,
    'show_enrollments' => true,
]
```

### User Activity Response Structure

```php
[
    'id' => 1,
    'type' => 'lesson_completed|quiz_submitted|badge_earned|course_enrolled',
    'description' => 'Menyelesaikan lesson "Pengenalan PHP"',
    'metadata' => [
        'lesson_id' => 5,
        'lesson_title' => 'Pengenalan PHP',
    ],
    'created_at' => '2024-01-15T10:30:00.000000Z',
]
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Route Documentation Completeness
*For any* registered Laravel API route with a named route and controller, the generated OpenAPI spec SHALL include a path item for that route with the correct HTTP method.
**Validates: Requirements 1.1-1.6, 2.1-2.8, 3.1-3.5, 4.1-4.4, 5.1-5.2, 6.1, 7.1, 10.1-10.4, 11.1-11.4, 12.1-12.3**

### Property 2: Path Parameters Documentation Completeness
*For any* endpoint with path parameters (e.g., `{id}`, `{slug}`, `{course}`), the generated OpenAPI spec SHALL include parameter definitions with name, type (string/integer), and description for each path parameter.
**Validates: Requirements 8.1**

### Property 3: List Endpoint Pagination Parameters
*For any* GET endpoint that returns a list (identified by method name `index` or `list`, or URI pattern without path parameters), the generated OpenAPI spec SHALL include pagination query parameters: page, per_page, search, sort.
**Validates: Requirements 8.2**

### Property 4: Authenticated Endpoint Security
*For any* endpoint with `auth:api` or `auth:sanctum` middleware, the generated OpenAPI spec SHALL include `security: [{"bearerAuth": []}]` in the path item.
**Validates: Requirements 8.3**

### Property 5: Response Codes Coverage
*For any* documented endpoint, the generated OpenAPI spec SHALL include response definitions for at least: success code (200 or 201), 401 (unauthorized), 422 (validation error), and 404 (not found).
**Validates: Requirements 8.4**

### Property 6: Request Body Schema for Mutations
*For any* POST, PUT, or PATCH endpoint, the generated OpenAPI spec SHALL include a requestBody definition with content type (application/json or multipart/form-data) and schema.
**Validates: Requirements 8.5**

### Property 7: Specific Summary Content
*For any* endpoint in the `endpointExamples` or `summaryOverrides` map, the generated OpenAPI spec SHALL use the specific summary text defined in the map, not a generic pattern.
**Validates: Requirements 9.1**

### Property 8: Actual Response Examples
*For any* endpoint with success response, the example SHALL contain actual field names and realistic values matching the real API response structure from the corresponding Resource/Transformer class or `endpointExamples` map.
**Validates: Requirements 9.3**

## Error Handling

### Missing Route Detection
- Service akan log warning jika route tidak memiliki controller
- Route tanpa nama akan di-skip dengan info log

### Fallback Category
- Endpoint yang tidak match keyword akan masuk ke "Endpoint Lainnya" category
- Ini memastikan tidak ada endpoint yang hilang dari dokumentasi

### Missing Example Fallback
- Jika endpoint tidak ada di `endpointExamples` map, gunakan generic example dengan warning log
- Log akan membantu identify endpoint yang perlu ditambahkan ke map

## Testing Strategy

### Unit Testing
- Test `OpenApiGeneratorService::generate()` menghasilkan spec valid
- Test setiap module memiliki endpoint yang terdokumentasi
- Test path parameters ter-extract dengan benar
- Test `endpointExamples` map menghasilkan response yang benar

### Property-Based Testing
- Menggunakan PHPUnit dengan data providers untuk test properties
- Test bahwa semua authenticated routes memiliki security definition
- Test bahwa semua list endpoints memiliki pagination parameters
- Test bahwa semua endpoints dengan path params memiliki parameter definitions
- Test bahwa semua POST/PUT endpoints memiliki request body

### Integration Testing
- Generate spec dan validate dengan OpenAPI validator
- Verify spec dapat di-render oleh Scalar tanpa error
- Verify semua endpoint muncul di sidebar Scalar

## Implementation Notes

### Files to Modify
1. `app/Services/OpenApiGeneratorService.php` - Update featureGroups, add endpointExamples, summaryOverrides
2. `storage/api-docs/openapi.json` - Re-generate setelah update

### New Properties to Add

```php
// Di OpenApiGeneratorService

protected array $endpointExamples = [
    // Map URI pattern ke response examples
];

protected array $summaryOverrides = [
    // Map URI pattern ke specific summaries
];

protected array $descriptionOverrides = [
    // Map URI pattern ke specific descriptions
];
```

### Method Enhancements

1. `getSummary()` - Check `summaryOverrides` first before generating generic summary
2. `getDescription()` - Check `descriptionOverrides` first before generating generic description
3. `buildSuccessExample()` - Check `endpointExamples` first before generating generic example
4. `buildResponses()` - Use endpoint-specific error examples from `endpointExamples`

### Regeneration Command
```bash
php artisan openapi:generate
```

### Verification
- Access `/scalar` untuk verify dokumentasi
- Check semua endpoint muncul di sidebar
- Verify request/response examples menggunakan actual data
- Verify summaries dan descriptions spesifik, bukan generic


## Complete Endpoint Examples Map

Berikut adalah mapping lengkap untuk semua endpoint dengan summary, description, dan response examples yang spesifik:

### Auth Module Endpoints

```php
// Login
'v1/auth/login' => [
    'post' => [
        'summary' => 'Login ke sistem',
        'description' => 'Melakukan autentikasi pengguna dengan email/username dan password. Mengembalikan access token dan refresh token.',
    ]
],

// Register
'v1/auth/register' => [
    'post' => [
        'summary' => 'Registrasi akun baru',
        'description' => 'Mendaftarkan pengguna baru sebagai Student. Email verifikasi akan dikirim setelah registrasi.',
    ]
],

// Profile
'v1/profile' => [
    'get' => [
        'summary' => 'Mengambil profil pengguna',
        'description' => 'Mengambil data profil lengkap pengguna yang sedang login.',
    ],
    'put' => [
        'summary' => 'Memperbarui profil pengguna',
        'description' => 'Memperbarui data profil pengguna yang sedang login.',
    ]
],
```

### Schemes Module Endpoints

```php
// Courses
'v1/courses' => [
    'get' => [
        'summary' => 'Mengambil daftar kursus',
        'description' => 'Mengambil daftar kursus yang tersedia dengan filter status, kategori, dan tag.',
        'successExample' => [
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Dasar-Dasar Pemrograman Web',
                    'slug' => 'dasar-dasar-pemrograman-web',
                    'description' => 'Kursus pengenalan pemrograman web untuk pemula',
                    'thumbnail_url' => 'https://storage.example.com/courses/web-dev.jpg',
                    'status' => 'published',
                    'enrollment_mode' => 'open',
                    'instructor' => [
                        'id' => 5,
                        'name' => 'Budi Santoso',
                    ],
                    'category' => [
                        'id' => 2,
                        'name' => 'Web Development',
                    ],
                    'tags' => [
                        ['id' => 1, 'name' => 'HTML'],
                        ['id' => 2, 'name' => 'CSS'],
                    ],
                    'enrolled_count' => 150,
                    'created_at' => '2024-01-01T00:00:00.000000Z',
                ]
            ]
        ]
    ],
    'post' => [
        'summary' => 'Membuat kursus baru',
        'description' => 'Membuat kursus baru. Hanya dapat dilakukan oleh Admin atau Superadmin.',
    ]
],

// Units
'v1/courses/{course}/units' => [
    'get' => [
        'summary' => 'Mengambil daftar unit dalam kursus',
        'description' => 'Mengambil daftar unit pembelajaran dalam kursus tertentu.',
    ],
    'post' => [
        'summary' => 'Membuat unit baru dalam kursus',
        'description' => 'Membuat unit pembelajaran baru dalam kursus. Hanya Admin/Superadmin.',
    ]
],

// Lessons
'v1/courses/{course}/units/{unit}/lessons' => [
    'get' => [
        'summary' => 'Mengambil daftar lesson dalam unit',
        'description' => 'Mengambil daftar lesson dalam unit tertentu.',
    ],
    'post' => [
        'summary' => 'Membuat lesson baru dalam unit',
        'description' => 'Membuat lesson baru dalam unit. Hanya Admin/Superadmin.',
    ]
],

// Blocks
'v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks' => [
    'get' => [
        'summary' => 'Mengambil daftar block dalam lesson',
        'description' => 'Mengambil daftar block konten (teks, video, dokumen) dalam lesson.',
    ],
    'post' => [
        'summary' => 'Membuat block baru dalam lesson',
        'description' => 'Membuat block konten baru dalam lesson. Hanya Admin/Superadmin.',
    ]
],
```

### Enrollments Module Endpoints

```php
'v1/courses/{course}/enrollments' => [
    'post' => [
        'summary' => 'Mendaftarkan peserta ke kursus',
        'description' => 'Mendaftarkan user yang sedang login ke kursus. Jika kursus memerlukan enrollment key, key harus disertakan.',
        'successExample' => [
            'message' => 'Berhasil mendaftar ke kursus.',
            'data' => [
                'enrollment' => [
                    'id' => 1,
                    'user_id' => 5,
                    'course_id' => 10,
                    'status' => 'pending',
                    'enrolled_at' => '2024-01-15T10:30:00.000000Z',
                ]
            ]
        ],
        'errorExample' => [
            'message' => 'Enrollment key tidak valid.',
            'errors' => [
                'enrollment_key' => ['Enrollment key yang Anda masukkan salah.']
            ]
        ]
    ],
    'get' => [
        'summary' => 'Mengambil daftar pendaftaran kursus',
        'description' => 'Mengambil daftar peserta yang terdaftar di kursus. Hanya Instructor/Admin.',
    ]
],

'v1/courses/{course}/enrollment-status' => [
    'get' => [
        'summary' => 'Mengecek status pendaftaran',
        'description' => 'Mengecek status pendaftaran user yang sedang login di kursus tertentu.',
    ]
],

'v1/enrollments/{enrollment}/approve' => [
    'post' => [
        'summary' => 'Menyetujui pendaftaran',
        'description' => 'Menyetujui pendaftaran peserta ke kursus. Hanya Instructor/Admin.',
    ]
],

'v1/enrollments/{enrollment}/decline' => [
    'post' => [
        'summary' => 'Menolak pendaftaran',
        'description' => 'Menolak pendaftaran peserta ke kursus. Hanya Instructor/Admin.',
    ]
],
```

### Content Module Endpoints

```php
'v1/announcements' => [
    'get' => [
        'summary' => 'Mengambil daftar pengumuman',
        'description' => 'Mengambil daftar pengumuman dengan filter status dan target audience.',
        'successExample' => [
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Jadwal Asesmen Semester Genap 2024',
                    'content' => 'Berikut adalah jadwal asesmen untuk semester genap...',
                    'status' => 'published',
                    'target_audience' => 'all',
                    'published_at' => '2024-01-15T08:00:00.000000Z',
                    'author' => [
                        'id' => 1,
                        'name' => 'Admin LSP',
                    ],
                ]
            ]
        ]
    ],
    'post' => [
        'summary' => 'Membuat pengumuman baru',
        'description' => 'Membuat pengumuman baru. Hanya Admin/Superadmin.',
    ]
],

'v1/news' => [
    'get' => [
        'summary' => 'Mengambil daftar berita',
        'description' => 'Mengambil daftar berita dengan filter kategori dan status.',
    ],
    'post' => [
        'summary' => 'Membuat berita baru',
        'description' => 'Membuat berita baru. Hanya Admin/Superadmin.',
    ]
],

'v1/news/trending' => [
    'get' => [
        'summary' => 'Mengambil berita trending',
        'description' => 'Mengambil daftar berita yang sedang trending berdasarkan view count.',
    ]
],

'v1/content/search' => [
    'get' => [
        'summary' => 'Mencari konten',
        'description' => 'Mencari konten (pengumuman dan berita) berdasarkan kata kunci.',
    ]
],

'v1/content/statistics' => [
    'get' => [
        'summary' => 'Mengambil statistik konten',
        'description' => 'Mengambil statistik keseluruhan konten (total pengumuman, berita, views).',
    ]
],
```

### Forum Module Endpoints

```php
'v1/schemes/{scheme}/forum/threads' => [
    'get' => [
        'summary' => 'Mengambil daftar thread forum',
        'description' => 'Mengambil daftar thread diskusi dalam forum skema.',
        'successExample' => [
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Bagaimana cara mengerjakan tugas unit 3?',
                    'content' => 'Saya kesulitan memahami materi tentang...',
                    'author' => [
                        'id' => 5,
                        'name' => 'Andi Pratama',
                        'avatar_url' => 'https://...',
                    ],
                    'is_pinned' => false,
                    'is_closed' => false,
                    'replies_count' => 5,
                    'reactions_count' => 10,
                    'created_at' => '2024-01-15T10:30:00.000000Z',
                ]
            ]
        ]
    ],
    'post' => [
        'summary' => 'Membuat thread baru',
        'description' => 'Membuat thread diskusi baru dalam forum skema.',
    ]
],

'v1/schemes/{scheme}/forum/statistics' => [
    'get' => [
        'summary' => 'Mengambil statistik forum skema',
        'description' => 'Mengambil statistik forum (total threads, replies, active users).',
    ]
],

'v1/schemes/{scheme}/forum/statistics/me' => [
    'get' => [
        'summary' => 'Mengambil statistik forum pengguna',
        'description' => 'Mengambil statistik partisipasi user di forum skema.',
    ]
],

'v1/forum/threads/{thread}/replies' => [
    'post' => [
        'summary' => 'Membuat balasan thread',
        'description' => 'Membuat balasan/reply pada thread diskusi.',
    ]
],

'v1/forum/replies/{reply}/accept' => [
    'post' => [
        'summary' => 'Menerima balasan sebagai jawaban',
        'description' => 'Menandai balasan sebagai jawaban yang diterima. Hanya pemilik thread.',
    ]
],
```

### Search Module Endpoints

```php
'v1/search/courses' => [
    'get' => [
        'summary' => 'Mencari kursus',
        'description' => 'Mencari kursus berdasarkan kata kunci, kategori, dan tag.',
    ]
],

'v1/search/autocomplete' => [
    'get' => [
        'summary' => 'Autocomplete pencarian',
        'description' => 'Mendapatkan saran pencarian berdasarkan input partial.',
    ]
],

'v1/search/history' => [
    'get' => [
        'summary' => 'Mengambil riwayat pencarian',
        'description' => 'Mengambil riwayat pencarian user yang sedang login.',
    ],
    'delete' => [
        'summary' => 'Menghapus riwayat pencarian',
        'description' => 'Menghapus semua riwayat pencarian user.',
    ]
],
```

### Notifications Module Endpoints

```php
'v1/notifications' => [
    'get' => [
        'summary' => 'Mengambil daftar notifikasi',
        'description' => 'Mengambil daftar notifikasi user dengan filter read/unread.',
    ]
],

'v1/notification-preferences' => [
    'get' => [
        'summary' => 'Mengambil preferensi notifikasi',
        'description' => 'Mengambil pengaturan preferensi notifikasi user.',
    ],
    'put' => [
        'summary' => 'Memperbarui preferensi notifikasi',
        'description' => 'Memperbarui pengaturan preferensi notifikasi user.',
    ]
],

'v1/notification-preferences/reset' => [
    'post' => [
        'summary' => 'Reset preferensi notifikasi',
        'description' => 'Mengembalikan preferensi notifikasi ke pengaturan default.',
    ]
],
```
