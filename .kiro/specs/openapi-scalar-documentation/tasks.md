# Implementation Plan

## Phase 1: Update Feature Groups Keywords

- [x] 1. Update featureGroups untuk Content Module
  - [x] 1.1 Add keywords untuk announcements, news, content/statistics, content/search
    - Add 'announcements', 'news', 'content/statistics', 'content/search', 'content/pending', 'content/submit', 'content/approve', 'content/reject'
    - Add 'courses/{course}/announcements' pattern
    - _Requirements: 1.1-1.6_

- [x] 2. Update featureGroups untuk Profile Module
  - [x] 2.1 Add keywords untuk profile management endpoints
    - Add 'profile/privacy', 'profile/activities', 'profile/achievements', 'profile/statistics'
    - Add 'profile/password', 'profile/account', 'profile/avatar'
    - Add 'users/{user}/profile', 'badges/pin', 'badges/unpin'
    - _Requirements: 2.1-2.8_

- [x] 3. Update featureGroups untuk Admin Users Module
  - [x] 3.1 Add keywords untuk admin user management
    - Add 'admin/users', 'suspend', 'activate', 'audit-logs'
    - _Requirements: 3.1-3.5_

- [x] 4. Update featureGroups untuk Forum Module
  - [x] 5.1 Add keywords untuk forum statistics
    - Add 'forum/statistics', 'forum/statistics/me'
    - _Requirements: 5.1-5.2_

- [x] 6. Update featureGroups untuk Export Module
  - [x] 6.1 Add keywords untuk export endpoints
    - Add 'exports/enrollments-csv'
    - _Requirements: 6.1_

- [x] 7. Update featureGroups untuk Learning Module
  - [x] 7.1 Add keywords untuk nested routes
    - Add 'lessons/assignments', 'assignments/submissions'
    - _Requirements: 7.1, 28.1-28.4_

- [x] 8. Update featureGroups untuk Search Module
  - [x] 8.1 Add keywords untuk search endpoints
    - Add 'search/courses', 'search/autocomplete', 'search/history'
    - _Requirements: 13.1-13.4_

- [x] 9. Update featureGroups untuk Notifications Module
  - [x] 9.1 Add keywords untuk notification endpoints
    - Add 'notification-preferences'
    - _Requirements: 14.1-14.3_

- [x] 10. Checkpoint - Feature groups complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 2: Add Endpoint-Specific Examples Map

- [x] 11. Create endpointExamples property
  - [x] 11.1 Add Auth Module examples
    - Add examples for login, register, logout, refresh, profile
    - Add examples for email verification, password reset
    - Include actual field names and realistic values
    - _Requirements: 9.1-9.6_

- [x] 12. Add Schemes Module examples
  - [x] 12.1 Add Course examples
    - Add examples for courses list, show, store, update, delete
    - Add examples for publish, unpublish
    - Include actual course structure with title, slug, description, instructor, category, tags
    - _Requirements: 11.1-11.4, 9.3_

  - [x] 12.2 Add Unit examples
    - Add examples for units CRUD and reorder
    - Include actual unit structure
    - _Requirements: 17.1-17.2_

  - [x] 12.3 Add Lesson examples
    - Add examples for lessons CRUD
    - Include actual lesson structure
    - _Requirements: 17.3_

  - [x] 12.4 Add Block examples
    - Add examples for blocks CRUD
    - Include actual block structure (text, video, document types)
    - _Requirements: 17.4_

- [x] 13. Add Enrollments Module examples
  - [x] 13.1 Add enrollment examples
    - Add examples for enroll, cancel, withdraw
    - Add examples for approve, decline, remove
    - Include enrollment status values: pending, active, completed, cancelled
    - _Requirements: 10.1-10.4_

  - [x] 13.2 Add enrollment key examples
    - Add examples for generate, update, remove enrollment key
    - _Requirements: 19.1-19.3_

  - [x] 13.3 Add enrollment reports examples
    - Add examples for completion-rate, enrollment-funnel
    - _Requirements: 27.1-27.2_

- [x] 14. Add Content Module examples
  - [x] 14.1 Add Announcement examples
    - Add examples for announcements CRUD
    - Add examples for publish, schedule, markAsRead
    - Include actual announcement structure
    - _Requirements: 1.1, 9.3_

  - [x] 14.2 Add News examples
    - Add examples for news CRUD
    - Add examples for trending
    - Include actual news structure with slug, author, category
    - _Requirements: 1.2, 9.3_

  - [x] 14.3 Add Content Statistics examples
    - Add examples for statistics endpoints
    - Include actual statistics structure
    - _Requirements: 1.4_

  - [x] 14.4 Add Content Approval examples
    - Add examples for submit, approve, reject, pending-review
    - _Requirements: 1.6_

- [x] 15. Add Forum Module examples
  - [x] 16.1 Add Thread examples
    - Add examples for threads CRUD, search, pin, close
    - Include actual thread structure with author, reactions, replies count
    - _Requirements: 24.1-24.5_

  - [x] 16.2 Add Reply examples
    - Add examples for replies CRUD, accept
    - _Requirements: 25.1-25.3_

  - [x] 16.3 Add Reaction examples
    - Add examples for thread and reply reactions
    - _Requirements: 26.1-26.2_

  - [x] 16.4 Add Forum Statistics examples
    - Add examples for forum statistics and user stats
    - _Requirements: 5.1-5.2_

- [x] 17. Add Profile Module examples
  - [x] 17.1 Add Privacy examples
    - Add examples for privacy settings GET and PUT
    - Include actual privacy fields
    - _Requirements: 2.1_

  - [x] 17.2 Add Activity examples
    - Add examples for activities list
    - Include actual activity structure with type, description, metadata
    - _Requirements: 2.2_

  - [x] 17.3 Add Achievement examples
    - Add examples for achievements list, badge pin/unpin
    - _Requirements: 2.3_

  - [x] 17.4 Add Statistics examples
    - Add examples for profile statistics
    - _Requirements: 2.4_

  - [x] 17.5 Add Account Management examples
    - Add examples for password update, account delete/restore, avatar upload/delete
    - _Requirements: 2.5-2.7_

  - [x] 17.6 Add Public Profile examples
    - Add examples for public profile view
    - _Requirements: 2.8_

- [x] 18. Add Admin Profile examples
  - [x] 18.1 Add admin user management examples
    - Add examples for profile show/update, suspend, activate, audit-logs
    - _Requirements: 3.1-3.5_

- [x] 19. Add Search Module examples
  - [x] 19.1 Add search examples
    - Add examples for courses search, autocomplete, history
    - _Requirements: 13.1-13.4_

- [x] 20. Add Notifications Module examples
  - [x] 20.1 Add notification examples
    - Add examples for notifications CRUD
    - Add examples for preferences GET/PUT, reset
    - _Requirements: 14.1-14.3_

- [x] 21. Add Categories Module examples
  - [x] 21.1 Add category examples
    - Add examples for categories CRUD
    - _Requirements: 15.1-15.3_

- [x] 22. Add Course Tags examples
  - [x] 22.1 Add tag examples
    - Add examples for course-tags CRUD
    - _Requirements: 16.1-16.3_

- [x] 23. Add Progress examples
  - [x] 23.1 Add progress examples
    - Add examples for course progress, lesson complete
    - _Requirements: 18.1-18.2_

- [x] 24. Add Learning Submissions examples
  - [x] 24.1 Add submission examples
    - Add examples for submissions CRUD
    - _Requirements: 28.1-28.4_

- [x] 25. Checkpoint - Endpoint examples complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 3: Add Summary and Description Overrides

- [x] 26. Create summaryOverrides property
  - [x] 26.1 Add summary overrides for all endpoints
    - Map URI patterns to specific Indonesian summaries
    - Ensure summaries are action-specific, not generic
    - _Requirements: 9.1_

- [x] 27. Create descriptionOverrides property
  - [x] 27.1 Add description overrides for all endpoints
    - Map URI patterns to context-specific descriptions
    - Include business logic context and validation requirements
    - _Requirements: 9.2_

## Phase 4: Enhance Generator Methods

- [x] 28. Update getSummary method
  - [x] 28.1 Check summaryOverrides first
    - Add lookup in summaryOverrides map before generating generic summary
    - Return specific summary if found in map
    - _Requirements: 9.1_

- [x] 29. Update getDescription method
  - [x] 29.1 Check descriptionOverrides first
    - Add lookup in descriptionOverrides map before generating generic description
    - Return specific description if found in map
    - _Requirements: 9.2_

- [x] 30. Update buildSuccessExample method
  - [x] 30.1 Check endpointExamples first
    - Add lookup in endpointExamples map before generating generic example
    - Return specific example if found in map
    - Include actual field names and realistic values
    - _Requirements: 9.3_

- [x] 31. Update buildResponses method
  - [x] 31.1 Use endpoint-specific error examples
    - Add lookup for error examples in endpointExamples map
    - Include specific validation error messages
    - _Requirements: 9.4_

- [x] 32. Update generateExample method
  - [x] 32.1 Add field-specific example values
    - Use realistic Indonesian content for text fields
    - Include actual enum values from validation rules
    - _Requirements: 9.5_

- [x] 33. Update getParameterDescription method
  - [x] 33.1 Add context-specific parameter descriptions
    - Describe parameter's purpose in specific endpoint context
    - Include valid value ranges or formats
    - _Requirements: 9.6_

- [x] 34. Checkpoint - Generator methods enhanced
  - Ensure all tests pass, ask the user if questions arise.

## Phase 5: Property-Based Tests

- [ ]* 35. Write property test for Route Documentation Completeness
  - **Property 1: Route Documentation Completeness**
  - **Validates: Requirements 1.1-1.6, 2.1-2.8, 3.1-3.5, 4.1-4.4, 5.1-5.2, 6.1, 7.1, 10-28**
  - Test that all registered Laravel API routes appear in generated spec

- [ ]* 36. Write property test for Path Parameters
  - **Property 2: Path Parameters Documentation Completeness**
  - **Validates: Requirements 8.1**
  - Test that all endpoints with path params have parameter definitions

- [ ]* 37. Write property test for Pagination Parameters
  - **Property 3: List Endpoint Pagination Parameters**
  - **Validates: Requirements 8.2**
  - Test that all list endpoints have pagination query parameters

- [ ]* 38. Write property test for Security
  - **Property 4: Authenticated Endpoint Security**
  - **Validates: Requirements 8.3**
  - Test that all auth:api endpoints have bearerAuth security

- [ ]* 39. Write property test for Response Codes
  - **Property 5: Response Codes Coverage**
  - **Validates: Requirements 8.4**
  - Test that all endpoints have required response codes

- [ ]* 40. Write property test for Request Body
  - **Property 6: Request Body Schema for Mutations**
  - **Validates: Requirements 8.5**
  - Test that all POST/PUT/PATCH endpoints have requestBody

- [ ]* 41. Write property test for Specific Summaries
  - **Property 7: Specific Summary Content**
  - **Validates: Requirements 9.1**
  - Test that endpoints in summaryOverrides use specific summaries

- [ ]* 42. Write property test for Actual Examples
  - **Property 8: Actual Response Examples**
  - **Validates: Requirements 9.3**
  - Test that endpoints in endpointExamples use actual response structures

- [x] 43. Checkpoint - Property tests complete
  - Ensure all tests pass, ask the user if questions arise.

## Phase 6: Regenerate and Verify

- [x] 44. Regenerate OpenAPI spec
  - [x] 44.1 Run generation command
    - Execute `php artisan openapi:generate`
    - Verify no errors during generation
    - _Requirements: All_

- [x] 45. Verify documentation completeness
  - [x] 45.1 Check all endpoints in Scalar UI
    - Access `/scalar` and verify all endpoints appear in sidebar
    - Verify endpoints are grouped correctly by feature
    - _Requirements: 1-7, 10-28_

  - [x] 45.2 Verify specific documentation
    - Check summaries are specific, not generic
    - Check descriptions include context-specific details
    - Check response examples use actual field names
    - _Requirements: 9.1-9.6_

  - [x] 45.3 Verify quality standards
    - Check path parameters have descriptions
    - Check list endpoints have pagination parameters
    - Check authenticated endpoints have security
    - Check response codes are complete
    - _Requirements: 8.1-8.5_

- [x] 46. Final Checkpoint - All tests passing
  - Ensure all tests pass, ask the user if questions arise.
