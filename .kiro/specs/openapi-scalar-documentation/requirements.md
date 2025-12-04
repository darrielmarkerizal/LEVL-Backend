# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk melengkapi dokumentasi API pada Scalar OpenAPI specification. Berdasarkan hasil audit, ditemukan beberapa endpoint yang belum terdokumentasi di OpenAPI spec yang di-generate oleh `OpenApiGeneratorService`. Perbaikan ini mencakup penambahan endpoint yang missing, kelengkapan parameter, response codes, request body, dan contoh response yang **actual dan spesifik** - bukan generic.

## Glossary

- **OpenAPI**: Spesifikasi standar untuk dokumentasi REST API (sebelumnya Swagger)
- **Scalar**: Library untuk menampilkan dokumentasi API dengan UI modern
- **OpenApiGeneratorService**: Service Laravel yang generate OpenAPI spec dari routes
- **Path Parameter**: Parameter yang ada di URL path (e.g., `{id}`, `{slug}`)
- **Query Parameter**: Parameter yang ada di query string (e.g., `?page=1&per_page=15`)
- **Request Body**: Data yang dikirim dalam body request (JSON/form-data)
- **Response Schema**: Struktur data yang dikembalikan oleh endpoint
- **Bearer Token**: JWT token untuk autentikasi API
- **Resource Class**: Laravel API Resource yang transform model ke JSON response
- **Transformer**: Class yang mengubah data model ke format response API

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

### Requirement 4: Forum Statistics Documentation

**User Story:** As an API consumer, I want complete documentation for forum statistics endpoints, so that I can integrate forum analytics features.

#### Acceptance Criteria

1. WHEN viewing forum statistics THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/statistics` endpoint
2. WHEN viewing user forum statistics THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/statistics/me` endpoint

### Requirement 5: Export and Reports Documentation

**User Story:** As an API consumer, I want complete documentation for export and reporting endpoints, so that I can integrate data export features.

#### Acceptance Criteria

1. WHEN exporting enrollments THEN the OpenAPI spec SHALL document `/v1/courses/{course}/exports/enrollments-csv` endpoint with CSV response type

### Requirement 6: Learning Module Nested Routes Documentation

**User Story:** As an API consumer, I want complete documentation for learning module nested routes, so that I can integrate assignment features within lessons.

#### Acceptance Criteria

1. WHEN accessing lesson assignments THEN the OpenAPI spec SHALL document `/v1/courses/{course}/units/{unit}/lessons/{lesson}/assignments` GET and POST endpoints

### Requirement 7: OpenAPI Spec Quality Standards

**User Story:** As an API consumer, I want consistent and complete API documentation, so that I can efficiently integrate with the system.

#### Acceptance Criteria

1. WHEN any endpoint is documented THEN the OpenAPI spec SHALL include all path parameters with type and description
2. WHEN any list endpoint is documented THEN the OpenAPI spec SHALL include pagination query parameters (page, per_page, sort, filter)
3. WHEN any authenticated endpoint is documented THEN the OpenAPI spec SHALL include bearerAuth security requirement
4. WHEN any endpoint is documented THEN the OpenAPI spec SHALL include all possible response codes (200, 201, 400, 401, 403, 404, 422, 500)
5. WHEN any POST/PUT endpoint is documented THEN the OpenAPI spec SHALL include request body schema with required fields

### Requirement 8: Specific and Actual Documentation Content

**User Story:** As an API consumer, I want documentation with specific and actual names, descriptions, and response examples, so that I can understand exactly what each endpoint does and what data it returns.

#### Acceptance Criteria

1. WHEN any endpoint summary is generated THEN the OpenAPI spec SHALL use specific action names relevant to the endpoint (e.g., "Mendaftarkan peserta ke kursus" instead of generic "Membuat Pendaftaran")
2. WHEN any endpoint description is generated THEN the OpenAPI spec SHALL include context-specific details about the endpoint's purpose and behavior
3. WHEN any success response example is generated THEN the OpenAPI spec SHALL include actual field names and realistic sample values matching the real API response structure from Resource/Transformer classes
4. WHEN any error response example is generated THEN the OpenAPI spec SHALL include specific error messages relevant to the endpoint's validation rules
5. WHEN any request body example is generated THEN the OpenAPI spec SHALL include field-specific example values (e.g., actual course titles, user names, dates) instead of generic placeholders
6. WHEN any parameter description is generated THEN the OpenAPI spec SHALL describe the parameter's purpose in the context of the specific endpoint

### Requirement 9: Enrollments Module Specific Documentation

**User Story:** As an API consumer, I want complete and specific documentation for enrollment endpoints, so that I can integrate course enrollment features correctly.

#### Acceptance Criteria

1. WHEN enrolling to a course THEN the OpenAPI spec SHALL document `/v1/courses/{course}/enroll` POST endpoint with enrollment_key field if course requires key
2. WHEN viewing enrollment status THEN the OpenAPI spec SHALL document enrollment status values: pending, active, completed, cancelled
3. WHEN listing user enrollments THEN the OpenAPI spec SHALL document `/v1/enrollments` GET endpoint with actual enrollment response structure
4. WHEN unenrolling from course THEN the OpenAPI spec SHALL document `/v1/courses/{course}/unenroll` DELETE endpoint

### Requirement 10: Schemes Module Documentation

**User Story:** As an API consumer, I want complete documentation for certification schemes endpoints, so that I can integrate scheme management features.

#### Acceptance Criteria

1. WHEN listing schemes THEN the OpenAPI spec SHALL document `/v1/schemes` GET endpoint with pagination and filter parameters
2. WHEN viewing scheme detail THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}` GET endpoint with complete scheme structure
3. WHEN accessing scheme courses THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/courses` endpoint

### Requirement 11: Gamification Module Documentation

**User Story:** As an API consumer, I want complete documentation for gamification endpoints, so that I can integrate badges, points, and leaderboard features.

#### Acceptance Criteria

1. WHEN accessing gamification THEN the OpenAPI spec SHALL document `/v1/gamifications` CRUD endpoints

### Requirement 12: Search Module Documentation

**User Story:** As an API consumer, I want complete documentation for search endpoints, so that I can integrate course search and autocomplete features.

#### Acceptance Criteria

1. WHEN searching courses THEN the OpenAPI spec SHALL document `/v1/search/courses` endpoint with query parameters
2. WHEN using autocomplete THEN the OpenAPI spec SHALL document `/v1/search/autocomplete` endpoint
3. WHEN viewing search history THEN the OpenAPI spec SHALL document `/v1/search/history` GET endpoint
4. WHEN clearing search history THEN the OpenAPI spec SHALL document `/v1/search/history` DELETE endpoint

### Requirement 13: Notifications Module Documentation

**User Story:** As an API consumer, I want complete documentation for notification endpoints, so that I can integrate notification features.

#### Acceptance Criteria

1. WHEN accessing notifications THEN the OpenAPI spec SHALL document `/v1/notifications` CRUD endpoints
2. WHEN accessing notification preferences THEN the OpenAPI spec SHALL document `/v1/notification-preferences` GET and PUT endpoints
3. WHEN resetting notification preferences THEN the OpenAPI spec SHALL document `/v1/notification-preferences/reset` POST endpoint

### Requirement 14: Categories Module Documentation

**User Story:** As an API consumer, I want complete documentation for category endpoints, so that I can integrate category management features.

#### Acceptance Criteria

1. WHEN listing categories THEN the OpenAPI spec SHALL document `/v1/categories` GET endpoint
2. WHEN viewing category detail THEN the OpenAPI spec SHALL document `/v1/categories/{category}` GET endpoint
3. WHEN managing categories (admin) THEN the OpenAPI spec SHALL document POST, PUT, DELETE endpoints

### Requirement 15: Course Tags Documentation

**User Story:** As an API consumer, I want complete documentation for course tag endpoints, so that I can integrate tag management features.

#### Acceptance Criteria

1. WHEN listing course tags THEN the OpenAPI spec SHALL document `/v1/course-tags` GET endpoint
2. WHEN viewing tag detail THEN the OpenAPI spec SHALL document `/v1/course-tags/{tag}` GET endpoint
3. WHEN managing tags (admin) THEN the OpenAPI spec SHALL document POST, PUT, DELETE endpoints

### Requirement 16: Course Structure Documentation (Units, Lessons, Blocks)

**User Story:** As an API consumer, I want complete documentation for course structure endpoints, so that I can integrate course content navigation.

#### Acceptance Criteria

1. WHEN accessing units THEN the OpenAPI spec SHALL document `/v1/courses/{course}/units` CRUD endpoints
2. WHEN reordering units THEN the OpenAPI spec SHALL document `/v1/courses/{course}/units/reorder` PUT endpoint
3. WHEN accessing lessons THEN the OpenAPI spec SHALL document `/v1/courses/{course}/units/{unit}/lessons` CRUD endpoints
4. WHEN accessing lesson blocks THEN the OpenAPI spec SHALL document `/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks` CRUD endpoints
5. WHEN publishing/unpublishing units THEN the OpenAPI spec SHALL document publish/unpublish endpoints
6. WHEN publishing/unpublishing lessons THEN the OpenAPI spec SHALL document publish/unpublish endpoints

### Requirement 17: Course Progress Documentation

**User Story:** As an API consumer, I want complete documentation for course progress endpoints, so that I can integrate progress tracking features.

#### Acceptance Criteria

1. WHEN viewing course progress THEN the OpenAPI spec SHALL document `/v1/courses/{course}/progress` GET endpoint
2. WHEN completing a lesson THEN the OpenAPI spec SHALL document `/v1/courses/{course}/units/{unit}/lessons/{lesson}/complete` POST endpoint

### Requirement 18: Enrollment Key Management Documentation

**User Story:** As an API consumer, I want complete documentation for enrollment key management endpoints, so that I can integrate key-based enrollment features.

#### Acceptance Criteria

1. WHEN generating enrollment key THEN the OpenAPI spec SHALL document `/v1/courses/{course}/enrollment-key/generate` POST endpoint
2. WHEN updating enrollment key THEN the OpenAPI spec SHALL document `/v1/courses/{course}/enrollment-key` PUT endpoint
3. WHEN removing enrollment key THEN the OpenAPI spec SHALL document `/v1/courses/{course}/enrollment-key` DELETE endpoint

### Requirement 19: Forum Threads Documentation

**User Story:** As an API consumer, I want complete documentation for forum thread endpoints, so that I can integrate forum discussion features.

#### Acceptance Criteria

1. WHEN listing threads THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/threads` GET endpoint
2. WHEN searching threads THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/threads/search` GET endpoint
3. WHEN managing threads THEN the OpenAPI spec SHALL document POST, GET, PUT, DELETE endpoints for threads
4. WHEN pinning thread THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/threads/{thread}/pin` POST endpoint
5. WHEN closing thread THEN the OpenAPI spec SHALL document `/v1/schemes/{scheme}/forum/threads/{thread}/close` POST endpoint

### Requirement 20: Forum Replies Documentation

**User Story:** As an API consumer, I want complete documentation for forum reply endpoints, so that I can integrate reply features.

#### Acceptance Criteria

1. WHEN creating reply THEN the OpenAPI spec SHALL document `/v1/forum/threads/{thread}/replies` POST endpoint
2. WHEN managing reply THEN the OpenAPI spec SHALL document `/v1/forum/replies/{reply}` PUT, DELETE endpoints
3. WHEN accepting reply THEN the OpenAPI spec SHALL document `/v1/forum/replies/{reply}/accept` POST endpoint

### Requirement 21: Forum Reactions Documentation

**User Story:** As an API consumer, I want complete documentation for forum reaction endpoints, so that I can integrate reaction features.

#### Acceptance Criteria

1. WHEN reacting to thread THEN the OpenAPI spec SHALL document `/v1/forum/threads/{thread}/reactions` POST endpoint
2. WHEN reacting to reply THEN the OpenAPI spec SHALL document `/v1/forum/replies/{reply}/reactions` POST endpoint

### Requirement 22: Enrollment Reports Documentation

**User Story:** As an API consumer, I want complete documentation for enrollment report endpoints, so that I can integrate analytics features.

#### Acceptance Criteria

1. WHEN viewing completion rate THEN the OpenAPI spec SHALL document `/v1/courses/{course}/reports/completion-rate` GET endpoint
2. WHEN viewing enrollment funnel THEN the OpenAPI spec SHALL document `/v1/reports/enrollment-funnel` GET endpoint

### Requirement 23: Learning Submissions Documentation

**User Story:** As an API consumer, I want complete documentation for assignment submission endpoints, so that I can integrate submission features.

#### Acceptance Criteria

1. WHEN listing submissions THEN the OpenAPI spec SHALL document `/v1/assignments/{assignment}/submissions` GET endpoint
2. WHEN creating submission THEN the OpenAPI spec SHALL document `/v1/assignments/{assignment}/submissions` POST endpoint
3. WHEN viewing submission THEN the OpenAPI spec SHALL document `/v1/submissions/{submission}` GET endpoint
4. WHEN updating submission THEN the OpenAPI spec SHALL document `/v1/submissions/{submission}` PUT endpoint
