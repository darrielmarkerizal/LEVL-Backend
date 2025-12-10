# Implementation Plan

## Phase 1: Foundation & Shared Components

- [x] 1. Enhance shared ManagesCourse trait
  - [x] 1.1 Update `app/Traits/ManagesCourse.php` to include `userCanManageCourse()` and `canModifyEnrollment()` methods
    - Consolidate duplicate logic from AssignmentController and EnrollmentsController
    - Add proper type hints and PHPDoc
    - _Requirements: 8.1, 8.2_
  - [ ]* 1.2 Write property test for authorization consistency
    - **Property 5: Authorization Enforcement**
    - **Validates: Requirements 6.1, 16.4**

- [x] 2. Create missing service interfaces
  - [x] 2.1 Create `ContentStatisticsServiceInterface`
    - Create file at `Modules/Content/app/Contracts/Services/ContentStatisticsServiceInterface.php`
    - Define all public methods from ContentStatisticsService
    - _Requirements: 2.1, 7.3_
  - [x] 2.2 Update `ContentStatisticsService` to implement interface
    - Add `implements ContentStatisticsServiceInterface`
    - _Requirements: 2.1_
  - [x] 2.3 Bind interface in `ContentServiceProvider`
    - Add binding in register() method
    - _Requirements: 5.3_

- [ ] 3. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 2: Content Module Refactoring

- [x] 4. Refactor ContentStatisticsController
  - [x] 4.1 Add ApiResponse trait and refactor response format
    - Replace `response()->json(['status' => 'success', ...])` with `$this->success()`
    - Inject `ContentStatisticsServiceInterface` instead of concrete class
    - _Requirements: 1.2, 13.1, 14.1, 14.2_
  - [ ]* 4.2 Write property test for response format
    - **Property 1: Success Response Format Consistency**
    - **Validates: Requirements 1.2, 3.1, 13.1, 13.2, 14.1**

- [x] 5. Refactor NewsController
  - [x] 5.1 Add ApiResponse trait and refactor response format
    - Replace all `response()->json()` calls with ApiResponse methods
    - Remove direct Model queries, use service methods
    - _Requirements: 1.2, 1.3, 13.1, 14.1, 14.2_
  - [x] 5.2 Move Model queries to service layer
    - Move `News::where('slug', $slug)->firstOrFail()` to service method
    - _Requirements: 1.3, 2.3_

- [x] 6. Refactor CourseAnnouncementController
  - [x] 6.1 Add ApiResponse trait and refactor response format
    - Replace `response()->json(['status' => 'success', ...])` with ApiResponse methods
    - _Requirements: 1.2, 13.1, 14.1_

- [x] 7. Refactor Content/SearchController
  - [x] 7.1 Add ApiResponse trait and refactor response format
    - Replace `response()->json(['status' => 'success', ...])` with ApiResponse methods
    - _Requirements: 1.2, 13.1, 14.1_

- [x] 8. Remove or implement ContentController placeholder
  - [x] 8.1 Evaluate ContentController usage
    - Check if routes reference this controller
    - Either implement properly or remove if unused
    - _Requirements: 9.1, 9.2_

- [ ] 9. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 3: Auth Module 

- [x] 10. Refactor Profile Controllers
  - [x] 10.1 Refactor ProfileController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 10.2 Refactor ProfileAccountController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 10.3 Refactor ProfileAchievementController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 10.4 Refactor ProfileActivityController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 10.5 Refactor ProfilePrivacyController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 10.6 Refactor ProfilePasswordController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 10.7 Refactor ProfileStatisticsController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 10.8 Refactor PublicProfileController to use ApiResponse trait
    - Add `use ApiResponse` trait
    - Replace `response()->json()` with trait methods
    - _Requirements: 1.2, 13.1, 14.1_

- [x] 11. Move audit logging from AuthApiController to service
  - [x] 11.1 Move Audit::create() calls to AuthService
    - Create audit logging methods in AuthService
    - Remove direct Audit::create() from controller
    - _Requirements: 11.2_
  - [ ]* 11.2 Write property test for audit trail
    - **Property 9: Audit Trail for Sensitive Operations**
    - **Validates: Requirements 11.1, 11.3**

- [ ] 12. Remove backup file
  - [ ] 12.1 Delete `AuthApiController.php.bak`
    - Remove backup file from repository
    - _Requirements: 9.3_

- [ ] 13. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 4: Notifications Module Refactoring

- [x] 14. Refactor NotificationPreferenceController
  - [x] 14.1 Add ApiResponse trait and refactor response format
    - Replace `response()->json()` with ApiResponse methods
    - _Requirements: 1.2, 13.1, 14.1_
  - [x] 14.2 Create NotificationPreferenceServiceInterface if not exists
    - Ensure service has corresponding interface
    - _Requirements: 2.1, 5.1_

- [ ] 15. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 5: Search Module Refactoring

- [x] 16. Refactor Search/SearchController
  - [x] 16.1 Add ApiResponse trait and refactor response format
    - Replace `response()->json()` with ApiResponse methods
    - _Requirements: 1.2, 13.1, 14.1_

- [ ] 17. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 6: Other Modules Refactoring

- [x] 18. Refactor Operations Module
  - [x] 18.1 Add ApiResponse trait to OperationsController
    - Add `use ApiResponse` trait
    - Refactor response format if needed
    - _Requirements: 1.2, 13.1_

- [x] 19. Refactor Grading Module
  - [x] 19.1 Add ApiResponse trait to GradingController
    - Add `use ApiResponse` trait
    - Refactor response format if needed
    - _Requirements: 1.2, 13.1_

- [x] 20. Refactor Forums Module
  - [x] 20.1 Evaluate ForumsController
    - Check if placeholder or implemented
    - Either implement or remove
    - _Requirements: 9.1_

- [x] 21. Refactor Learning Module
  - [x] 21.1 Update AssignmentController to use ManagesCourse trait
    - Replace local `userCanManageCourse()` with trait method
    - _Requirements: 8.1, 8.2_

- [x] 22. Refactor Enrollments Module
  - [x] 22.1 Update EnrollmentsController to use ManagesCourse trait
    - Replace local `userCanManageCourse()` and `canModifyEnrollment()` with trait methods
    - _Requirements: 8.1, 8.2_

- [ ] 23. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 7: Property-Based Tests

- [ ]* 24. Write property tests for response format
  - [ ]* 24.1 Create ResponseFormatTest.php
    - **Property 1: Success Response Format Consistency**
    - **Property 2: Error Response Format Consistency**
    - **Property 3: Paginated Response Structure**
    - **Property 4: Created Resource Status Code**
    - **Validates: Requirements 1.2, 1.4, 3.1, 3.2, 3.3, 3.4, 13.1-13.4, 14.1-14.3**

- [ ]* 25. Write property tests for validation
  - [ ]* 25.1 Create ValidationTest.php
    - **Property 6: Validation Error Format**
    - **Validates: Requirements 10.4, 16.2**

- [ ]* 26. Write property tests for security
  - [ ]* 26.1 Create SecurityTest.php
    - **Property 7: Rate Limiting Enforcement**
    - **Property 8: Sensitive Data Exclusion**
    - **Validates: Requirements 16.1, 16.3**

- [ ]* 27. Write property tests for transactions
  - [ ]* 27.1 Create TransactionIntegrityTest.php
    - **Property 10: Transaction Integrity**
    - **Validates: Requirements 2.4**

- [ ] 28. Final Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 8: Documentation Update

- [ ] 29. Update API documentation
  - [ ] 29.1 Review and fix response examples in controller docblocks
    - Ensure examples match actual ApiResponse format
    - Update 'status' to 'success' in all examples
    - _Requirements: 12.1, 12.2_
  - [ ] 29.2 Verify @authenticated and @role annotations
    - Ensure all protected endpoints have proper annotations
    - _Requirements: 12.3, 12.4_

- [ ] 30. Final Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
