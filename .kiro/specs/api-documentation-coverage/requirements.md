# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk melengkapi dokumentasi API pada Scalar OpenAPI specification. Berdasarkan hasil audit, ditemukan beberapa endpoint yang belum terdokumentasi di OpenAPI spec yang di-generate oleh `OpenApiGeneratorService`. Perbaikan ini mencakup penambahan endpoint yang missing, kelengkapan parameter, response codes, request body, dan contoh response.

## Glossary

- **OpenAPI**: Spesifikasi standar untuk dokumentasi REST API (sebelumnya Swagger)
- **Scalar**: Library untuk menampilkan dokumentasi API dengan UI modern
- **OpenApiGeneratorService**: Service Laravel yang generate OpenAPI spec dari routes
- **Path Parameter**: Parameter yang ada di URL path (e.g., `{id}`, `{slug}`)
- **Query Parameter**: Parameter yang ada di query string (e.g., `?page=1&per_page=15`)
- **Request Body**: Data yang dikirim dalam body request (JSON/form-data)
- **Response Schema**: Struktur data yang dikembalikan oleh endpoint
- **Bearer Token**: JWT token untuk autentikasi API

## Requirements

### Requirement 1: Content Module Documentation

**User Story:** As an API consumer, I want complete documentation for Content module endpoints, so that I can integrate announcements, news, and content management features.

#### Acceptance Criteria

1. WHEN accessing announcements endpoints THEN the OpenAPI spec SHALL document all CRUD operations for `/v1/announcements`
2. WHEN accessing news endpoints THEN the OpenAPI spec SHALL document all CRUD operations for `/v1/news` including trending endpoint
3. WHEN accessing course announcements THEN the OpenAPI spec SHALL document `/v1/courses/{course}/announcements` endpoints
4. WHEN accessing content statistics THEN the OpenAPI spec SHALL document all `/v1/content/statistics` endpoints
5. WHEN accessing content search THEN the OpenAPI spec SHALL document `/v1/content/search` with query parameters
6. WHEN accessing content approval workflow THEN the OpenAPI spec SHALL document submit, approve, reject, and pending-review endpoints

### Requirement 2: Profile Management Documentation

**User Story:** As an API consumer, I want complete documentation for profile management endpoints, so that I can integrate user profile features.

#### Acceptance Criteria

1. WHEN accessing privacy settings THEN the OpenAPI spec SHALL document `/v1/profile/privacy` GET and PUT endpoints
2. WHEN accessing activity history THEN the OpenAPI spec SHALL document `/v1/profile/activities` endpoint with pagination
3. WHEN accessing achievements THEN the OpenAPI spec SHALL document `/v1/profile/achievements` and badge pin/unpin endpoints
4. WHEN accessing profile statistics THEN the OpenAPI spec SHALL document `/v1/profile/statistics` endpoint
5. WHEN managing password THEN the OpenAPI spec SHALL document `/v1/profile/password` PUT endpoint
6. WHEN managing account THEN the OpenAPI spec SHALL document `/v1/profile/account` delete and restore endpoints
7. WHEN managing avatar THEN the OpenAPI spec SHALL document `/v1/profile/avatar` upload and delete endpoints
8. WHEN accessing public profile THEN the OpenAPI spec SHALL document `/v1/users/{user}/profile` endpoint

### Requirement 3: Admin Profile Management Documentation

**User Story:** As an API consumer, I want complete documentation for admin profile management endpoints, so that I can integrate admin user management features.

#### Acceptance Criteria

1. WHEN admin views user profile THEN the OpenAPI spec SHALL document `/v1/admin/users/{user}/profile` GET endpoint
2. WHEN admin updates user profile THEN the OpenAPI spec SHALL document `/v1/admin/users/{user}/profile` PUT endpoint
3. WHEN admin suspends user THEN the OpenAPI spec SHALL document `/v1/admin/users/{user}/suspend` endpoint
4. WHEN admin activates user THEN the OpenAPI spec SHALL document `/v1/admin/users/{user}/activate` endpoint
5. WHEN admin views audit logs THEN the OpenAPI spec SHALL document `/v1/admin/users/{user}/audit-logs` endpoint

### Requirement 4: Assessment Registration Documentation

**User Story:** As an API consumer, I want complete documentation for assessment registration endpoints, so that I can integrate assessment scheduling features.

#### Acceptance Criteria

1. WHEN registering for assessment THEN the OpenAPI spec SHALL document `/v1/assessments/{assessment}/register` endpoint
2. WHEN checking prerequisites THEN the OpenAPI spec SHALL document `/v1/assessments/{assessment}/prerequisites` endpoint
3. WHEN viewing available slots THEN the OpenAPI spec SHALL document `/v1/assessments/{assessment}/slots` endpoint
4. WHEN cancelling registration THEN the OpenAPI spec SHALL document `/v1/assessment-registrations/{registration}` DELETE endpoint

### Requirement 5: Forum Statistics Documentation

**User Story:** As an API consumer, I want complete documentation for forum statistics endpoints, so that I can integrate forum analytics features.

#### Acceptance Criteria

1. WHEN viewing forum statistics THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/statistics` endpoint
2. WHEN viewing user forum statistics THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/statistics/me` endpoint

### Requirement 6: Export and Reports Documentation

**User Story:** As an API consumer, I want complete documentation for export and reporting endpoints, so that I can integrate data export features.

#### Acceptance Criteria

1. WHEN exporting enrollments THEN the OpenAPI spec SHALL document `/v1/courses/{course}/exports/enrollments-csv` endpoint with CSV response type

### Requirement 7: Learning Module Nested Routes Documentation

**User Story:** As an API consumer, I want complete documentation for learning module nested routes, so that I can integrate assignment features within lessons.

#### Acceptance Criteria

1. WHEN accessing lesson assignments THEN the OpenAPI spec SHALL document `/v1/courses/{course}/units/{unit}/lessons/{lesson}/assignments` GET and POST endpoints

### Requirement 8: OpenAPI Spec Quality Standards

**User Story:** As an API consumer, I want consistent and complete API documentation, so that I can efficiently integrate with the system.

#### Acceptance Criteria

1. WHEN any endpoint is documented THEN the OpenAPI spec SHALL include all path parameters with type and description
2. WHEN any list endpoint is documented THEN the OpenAPI spec SHALL include pagination query parameters (page, per_page, sort, filter)
3. WHEN any authenticated endpoint is documented THEN the OpenAPI spec SHALL include bearerAuth security requirement
4. WHEN any endpoint is documented THEN the OpenAPI spec SHALL include all possible response codes (200, 201, 400, 401, 403, 404, 422, 500)
5. WHEN any POST/PUT endpoint is documented THEN the OpenAPI spec SHALL include request body schema with required fields
6. WHEN any endpoint is documented THEN the OpenAPI spec SHALL include example responses for success and error cases
