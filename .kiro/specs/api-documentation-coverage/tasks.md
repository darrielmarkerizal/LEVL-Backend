# Implementation Plan

## Phase 1: Update OpenApiGeneratorService Feature Groups

- [x] 1. Add Content Module to Feature Groups
  - [x] 1.1 Update featureGroups array for Content module
    - Add 'Content' to modules array in '05-info' group
    - Add keywords: 'announcements', 'news', 'content/statistics', 'content/search', 'content/pending'
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

- [x] 2. Add Profile Management Keywords
  - [x] 2.1 Update '07-profil' feature group keywords
    - Add keywords for privacy: 'profile/privacy'
    - Add keywords for activities: 'profile/activities'
    - Add keywords for achievements: 'profile/achievements', 'badges/pin', 'badges/unpin'
    - Add keywords for statistics: 'profile/statistics'
    - Add keywords for password: 'profile/password'
    - Add keywords for account: 'profile/account'
    - Add keywords for avatar: 'profile/avatar'
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7_

- [x] 3. Add Admin Profile Management Keywords
  - [x] 3.1 Update '10-sistem' feature group for admin endpoints
    - Add keywords: 'admin/users', 'suspend', 'activate', 'audit-logs'
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 4. Add Assessment Registration Keywords
  - [x] 4.1 Update '01-asesmen' feature group
    - Add keywords for registration: 'assessments/{assessment}/register', 'assessments/{assessment}/prerequisites', 'assessments/{assessment}/slots'
    - Add keywords for cancellation: 'assessment-registrations'
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 5. Add Forum Statistics Keywords
  - [x] 5.1 Update '03-forum' feature group
    - Add keywords: 'forum/statistics'
    - _Requirements: 5.1, 5.2_

- [x] 6. Add Export Keywords
  - [x] 6.1 Update '09-kelas' feature group
    - Add keywords: 'exports', 'enrollments-csv'
    - _Requirements: 6.1_

- [x] 7. Add Learning Module Nested Routes Keywords
  - [x] 7.1 Update '11-tugas' feature group
    - Add keywords for lesson assignments: 'lessons/{lesson}/assignments'
    - _Requirements: 7.1_

- [x] 8. Checkpoint - Feature groups updated
  - Ensure all tests pass, ask the user if questions arise.

## Phase 2: Enhance Path Item Generation

- [-] 9. Improve Path Parameter Extraction
  - [x] 9.1 Update extractParameters method
    - Ensure all path parameters have type and description
    - Add support for slug-based parameters
    - _Requirements: 8.1_
  - [ ] 9.2 Write property test for path parameters
    - **Property 1: Path Parameters Documentation Completeness**
    - **Validates: Requirements 8.1**

- [-] 10. Enhance List Endpoint Detection
  - [x] 10.1 Update isListEndpoint logic
    - Detect list endpoints by method name (index, list, search)
    - Add pagination parameters automatically
    - _Requirements: 8.2_
  - [ ] 10.2 Write property test for pagination parameters
    - **Property 2: List Endpoint Pagination Parameters**
    - **Validates: Requirements 8.2**

- [-] 11. Ensure Security Definition
  - [x] 11.1 Verify auth middleware detection
    - Check for 'auth:api', 'auth:sanctum', 'auth' middleware
    - Add bearerAuth security requirement
    - _Requirements: 8.3_
  - [ ] 11.2 Write property test for security definition
    - **Property 3: Authenticated Endpoint Security**
    - **Validates: Requirements 8.3**

- [x] 12. Checkpoint - Path item generation enhanced
  - Ensure all tests pass, ask the user if questions arise.

## Phase 3: Response and Request Body Enhancement

- [-] 13. Add Complete Response Codes
  - [x] 13.1 Update buildResponses method
    - Add 200/201 for success responses
    - Add 400 for bad request
    - Add 401 for unauthorized
    - Add 403 for forbidden
    - Add 404 for not found
    - Add 422 for validation error
    - Add 500 for server error
    - _Requirements: 8.4_
  - [ ] 13.2 Write property test for response codes
    - **Property 4: Response Codes Coverage**
    - **Validates: Requirements 8.4**

- [-] 14. Enhance Request Body Generation
  - [x] 14.1 Update buildRequestBody method
    - Detect POST/PUT methods
    - Generate schema from FormRequest if available
    - Add required fields
    - _Requirements: 8.5_
  - [ ] 14.2 Write property test for request body
    - **Property 5: Request Body Schema for Mutations**
    - **Validates: Requirements 8.5**

- [-] 15. Add Response Examples
  - [x] 15.1 Add example responses
    - Add success example with data structure
    - Add error example with error format
    - _Requirements: 8.6_

- [x] 16. Checkpoint - Response and request body enhanced
  - Ensure all tests pass, ask the user if questions arise.

## Phase 4: Content Module Specific Documentation

- [x] 17. Document Announcements Endpoints
  - [x] 17.1 Add announcements endpoint documentation
    - Document GET /v1/announcements with filters (course_id, priority, unread)
    - Document POST /v1/announcements with request body
    - Document GET/PUT/DELETE /v1/announcements/{announcement}
    - Document POST /v1/announcements/{announcement}/publish
    - Document POST /v1/announcements/{announcement}/schedule
    - Document POST /v1/announcements/{announcement}/read
    - _Requirements: 1.1_

- [x] 18. Document News Endpoints
  - [x] 18.1 Add news endpoint documentation
    - Document GET /v1/news with filters (category_id, tag_id, featured, date_from, date_to)
    - Document POST /v1/news with request body
    - Document GET /v1/news/trending
    - Document GET/PUT/DELETE /v1/news/{news}
    - Document POST /v1/news/{news}/publish
    - Document POST /v1/news/{news}/schedule
    - _Requirements: 1.2_

- [x] 19. Document Course Announcements
  - [x] 19.1 Add course announcements documentation
    - Document GET /v1/courses/{course}/announcements
    - Document POST /v1/courses/{course}/announcements
    - _Requirements: 1.3_

- [x] 20. Document Content Statistics
  - [x] 20.1 Add content statistics documentation
    - Document GET /v1/content/statistics with filters
    - Document GET /v1/content/statistics/announcements/{announcement}
    - Document GET /v1/content/statistics/news/{news}
    - Document GET /v1/content/statistics/trending
    - Document GET /v1/content/statistics/most-viewed
    - _Requirements: 1.4_

- [x] 21. Document Content Search
  - [x] 21.1 Add content search documentation
    - Document GET /v1/content/search with query parameters (q, type, category_id, date_from, date_to)
    - _Requirements: 1.5_

- [x] 22. Document Content Approval Workflow
  - [x] 22.1 Add content approval documentation
    - Document POST /v1/content/{type}/{id}/submit
    - Document POST /v1/content/{type}/{id}/approve
    - Document POST /v1/content/{type}/{id}/reject
    - Document GET /v1/content/pending-review
    - _Requirements: 1.6_

- [x] 23. Checkpoint - Content module documented
  - Ensure all tests pass, ask the user if questions arise.

## Phase 5: Profile and Admin Documentation

- [x] 24. Document Profile Management Endpoints
  - [x] 24.1 Add profile management documentation
    - Document GET/PUT /v1/profile/privacy
    - Document GET /v1/profile/activities with pagination
    - Document GET /v1/profile/achievements
    - Document POST /v1/profile/badges/{badge}/pin
    - Document DELETE /v1/profile/badges/{badge}/unpin
    - Document GET /v1/profile/statistics
    - Document PUT /v1/profile/password
    - Document DELETE /v1/profile/account
    - Document POST /v1/profile/account/restore
    - Document POST /v1/profile/avatar (multipart/form-data)
    - Document DELETE /v1/profile/avatar
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7_

- [x] 25. Document Public Profile
  - [x] 25.1 Add public profile documentation
    - Document GET /v1/users/{user}/profile
    - _Requirements: 2.8_

- [x] 26. Document Admin Profile Management
  - [x] 26.1 Add admin profile management documentation
    - Document GET /v1/admin/users/{user}/profile
    - Document PUT /v1/admin/users/{user}/profile
    - Document POST /v1/admin/users/{user}/suspend
    - Document POST /v1/admin/users/{user}/activate
    - Document GET /v1/admin/users/{user}/audit-logs
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 27. Checkpoint - Profile and admin documented
  - Ensure all tests pass, ask the user if questions arise.

## Phase 6: Assessment, Forum, and Export Documentation

- [x] 28. Document Assessment Registration
  - [x] 28.1 Add assessment registration documentation
    - Document POST /v1/assessments/{assessment}/register
    - Document GET /v1/assessments/{assessment}/prerequisites
    - Document GET /v1/assessments/{assessment}/slots
    - Document DELETE /v1/assessment-registrations/{registration}
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 29. Document Forum Statistics
  - [x] 29.1 Add forum statistics documentation
    - Document GET /v1/schemes/{scheme}/forum/statistics
    - Document GET /v1/schemes/{scheme}/forum/statistics/me
    - _Requirements: 5.1, 5.2_

- [x] 30. Document Export Endpoints
  - [x] 30.1 Add export documentation
    - Document GET /v1/courses/{course}/exports/enrollments-csv with CSV response type
    - _Requirements: 6.1_

- [x] 31. Document Learning Module Nested Routes
  - [x] 31.1 Add lesson assignments documentation
    - Document GET /v1/courses/{course}/units/{unit}/lessons/{lesson}/assignments
    - Document POST /v1/courses/{course}/units/{unit}/lessons/{lesson}/assignments
    - _Requirements: 7.1_

- [x] 32. Checkpoint - All modules documented
  - Ensure all tests pass, ask the user if questions arise.

## Phase 7: Regenerate and Verify

- [x] 33. Regenerate OpenAPI Specification
  - [x] 33.1 Run generation command
    - Execute `php artisan openapi:generate`
    - Verify output file at `storage/api-docs/openapi.json`
    - Check total paths count increased

- [x] 34. Verify Documentation in Scalar
  - [x] 34.1 Manual verification
    - Access /scalar endpoint
    - Verify all new endpoints appear in sidebar
    - Check request/response examples are correct
    - Verify authentication requirements shown

- [x] 35. Final Checkpoint - All documentation complete
  - Ensure all tests pass, ask the user if questions arise.
