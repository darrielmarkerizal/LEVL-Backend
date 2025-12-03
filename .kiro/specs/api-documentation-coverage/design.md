# Design Document

## Overview

Dokumen ini menjelaskan desain teknis untuk melengkapi dokumentasi API pada Scalar OpenAPI specification. Solusi utama adalah memperbarui `OpenApiGeneratorService` untuk mengenali dan mendokumentasikan endpoint yang saat ini missing, serta memastikan setiap endpoint memiliki dokumentasi yang lengkap.

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

`OpenApiGeneratorService` menggunakan keyword matching untuk mengkategorikan endpoint ke dalam feature groups. Endpoint yang tidak match dengan keyword akan di-skip atau masuk ke fallback category. Beberapa module seperti Content tidak memiliki keyword yang sesuai.

### Solution Design

1. **Update featureGroups** - Tambahkan keyword untuk Content module dan endpoint lain yang missing
2. **Enhance buildPathItem** - Pastikan semua endpoint memiliki dokumentasi lengkap
3. **Add module-specific handlers** - Untuk endpoint dengan struktur khusus

## Components and Interfaces

### OpenApiGeneratorService Updates

```php
// Tambahan di featureGroups array
'05-info' => [
    'features' => [
        'berita' => [
            'keywords' => ['news', 'announcements', 'content/statistics', 'content/search', 'content/pending'],
            'modules' => ['Content', 'Common', 'Operations'],
        ],
    ],
],

'07-profil' => [
    'features' => [
        'profil' => [
            'keywords' => ['profile', 'me', 'privacy', 'activities', 'achievements', 'statistics'],
        ],
        'avatar' => [
            'keywords' => ['avatar', 'biodata', 'badges'],
        ],
        'password' => [
            'keywords' => ['password/update', 'profile/password'],
        ],
        'account' => [
            'keywords' => ['account', 'profile/account'],
        ],
    ],
],

'10-sistem' => [
    'features' => [
        'users' => [
            'keywords' => ['users', 'admin/users', 'suspend', 'activate', 'audit-logs'],
        ],
    ],
],
```

### New Feature Group for Content

```php
// Content module perlu ditambahkan ke featureGroups
'05-info' => [
    'label' => '05 - Informasi & Notifikasi',
    'features' => [
        'berita' => [
            'label' => 'Berita & Pengumuman',
            'modules' => ['Content'],
            'keywords' => ['announcements', 'news', 'content'],
        ],
    ],
],
```

## Data Models

### OpenAPI Path Item Structure

```json
{
  "/v1/announcements": {
    "get": {
      "tags": ["Berita & Pengumuman"],
      "summary": "Get all announcements",
      "description": "Mendapatkan daftar pengumuman",
      "operationId": "announcements.index",
      "security": [{"bearerAuth": []}],
      "parameters": [
        {"name": "page", "in": "query", "schema": {"type": "integer"}},
        {"name": "per_page", "in": "query", "schema": {"type": "integer"}},
        {"name": "course_id", "in": "query", "schema": {"type": "integer"}},
        {"name": "priority", "in": "query", "schema": {"type": "string", "enum": ["low", "normal", "high"]}}
      ],
      "responses": {
        "200": {"description": "Success", "content": {...}},
        "401": {"description": "Unauthorized"},
        "403": {"description": "Forbidden"}
      }
    },
    "post": {
      "tags": ["Berita & Pengumuman"],
      "summary": "Create announcement",
      "requestBody": {
        "required": true,
        "content": {
          "application/json": {
            "schema": {
              "type": "object",
              "required": ["title", "content", "target_type"],
              "properties": {...}
            }
          }
        }
      }
    }
  }
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*



### Property 1: Path Parameters Documentation Completeness
*For any* endpoint with path parameters (e.g., `{id}`, `{slug}`), the generated OpenAPI spec SHALL include parameter definitions with name, type, and description for each path parameter.
**Validates: Requirements 8.1**

### Property 2: List Endpoint Pagination Parameters
*For any* GET endpoint that returns a list (identified by method name `index` or `list`), the generated OpenAPI spec SHALL include pagination query parameters: page, per_page.
**Validates: Requirements 8.2**

### Property 3: Authenticated Endpoint Security
*For any* endpoint with `auth:api` middleware, the generated OpenAPI spec SHALL include `security: [{"bearerAuth": []}]` in the path item.
**Validates: Requirements 8.3**

### Property 4: Response Codes Coverage
*For any* documented endpoint, the generated OpenAPI spec SHALL include response definitions for at least: 200/201 (success), 401 (unauthorized), and 422 (validation error).
**Validates: Requirements 8.4**

### Property 5: Request Body Schema for Mutations
*For any* POST or PUT endpoint, the generated OpenAPI spec SHALL include a requestBody definition with content type and schema.
**Validates: Requirements 8.5**

## Error Handling

### Missing Route Detection
- Service akan log warning jika route tidak memiliki controller
- Route tanpa nama akan di-skip dengan info log

### Fallback Category
- Endpoint yang tidak match keyword akan masuk ke "Endpoint Lainnya" category
- Ini memastikan tidak ada endpoint yang hilang dari dokumentasi

## Testing Strategy

### Unit Testing
- Test `OpenApiGeneratorService::generate()` menghasilkan spec valid
- Test setiap module memiliki endpoint yang terdokumentasi
- Test path parameters ter-extract dengan benar

### Property-Based Testing
- Menggunakan PHPUnit dengan data providers untuk test properties
- Test bahwa semua authenticated routes memiliki security definition
- Test bahwa semua list endpoints memiliki pagination parameters

### Integration Testing
- Generate spec dan validate dengan OpenAPI validator
- Verify spec dapat di-render oleh Scalar tanpa error

## Implementation Notes

### Files to Modify
1. `app/Services/OpenApiGeneratorService.php` - Update featureGroups dan keyword matching
2. `storage/api-docs/openapi.json` - Re-generate setelah update

### Regeneration Command
```bash
php artisan openapi:generate
```

### Verification
- Access `/scalar` untuk verify dokumentasi
- Check semua endpoint muncul di sidebar
- Verify request/response examples
