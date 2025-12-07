# Implementation Plan

## Phase 1: Create Base Infrastructure

- [x] 1. Create base DTO class
  - [x] 1.1 Create BaseDTO abstract class
    - Create `app/Support/BaseDTO.php`
    - Implement abstract methods: fromRequest(), toArray()
    - Add helper methods: fromModel(), toArrayWithoutNull()
    - _Requirements: 1.1, 1.4, 1.5, 1.6_

  - [ ]* 1.2 Write property test for DTO Round-Trip
    - **Property 1: DTO Round-Trip Consistency**
    - **Validates: Requirements 1.4, 1.6**

- [x] 2. Create base repository infrastructure
  - [x] 2.1 Create BaseRepositoryInterface
    - Create `app/Contracts/BaseRepositoryInterface.php`
    - Define methods: query(), findById(), findByIdOrFail(), create(), update(), delete(), paginate(), list()
    - _Requirements: 2.2, 2.6_

  - [x] 2.2 Create BaseRepository abstract class
    - Create `app/Repositories/BaseRepository.php`
    - Use FilterableRepository trait
    - Implement all interface methods
    - Add configurable properties: allowedFilters, allowedSorts, defaultSort, with
    - _Requirements: 2.1, 2.3, 2.4, 2.5, 2.6_

  - [ ]* 2.3 Write property test for Repository CRUD
    - **Property 6: Repository CRUD Operations**
    - **Validates: Requirements 2.6**

  - [ ]* 2.4 Write property test for Repository Not Found
    - **Property 8: Repository Not Found Exception**
    - **Validates: Requirements 5.2**

- [x] 3. Create base service infrastructure
  - [x] 3.1 Create BaseService abstract class
    - Create `app/Services/BaseService.php`
    - Inject BaseRepositoryInterface via constructor
    - Implement common methods: paginate(), list(), find(), findOrFail(), create(), update(), delete()
    - _Requirements: 3.2, 3.5_

- [x] 4. Create custom exceptions
  - [x] 4.1 Create BusinessException class
    - Create `app/Exceptions/BusinessException.php`
    - Add errors property and getErrors() method
    - Set default HTTP code to 422
    - _Requirements: 5.3_

  - [x] 4.2 Update exception handler
    - Update `app/Exceptions/Handler.php` to handle BusinessException
    - Return consistent JSON structure for all custom exceptions
    - _Requirements: 5.4_

  - [ ]* 4.3 Write property test for Exception Response
    - **Property 9: Exception Response Consistency**
    - **Validates: Requirements 5.4**

- [x] 5. Checkpoint - Base infrastructure complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 2: Refactor Auth Module (Pilot)

- [x] 6. Create Auth DTOs
  - [x] 6.1 Create RegisterDTO
    - Create `Modules/Auth/app/DTOs/RegisterDTO.php`
    - Properties: name, username, email, password
    - Implement fromRequest() and toArray()
    - _Requirements: 1.1, 1.2, 1.4, 1.6_

  - [x] 6.2 Create LoginDTO
    - Create `Modules/Auth/app/DTOs/LoginDTO.php`
    - Properties: login, password
    - _Requirements: 1.1, 1.2_

  - [x] 6.3 Create UpdateProfileDTO
    - Create `Modules/Auth/app/DTOs/UpdateProfileDTO.php`
    - Properties: name, username, avatar (optional)
    - _Requirements: 1.1, 1.2_

  - [x] 6.4 Create ChangePasswordDTO
    - Create `Modules/Auth/app/DTOs/ChangePasswordDTO.php`
    - Properties: currentPassword, newPassword
    - _Requirements: 1.1, 1.2_

- [x] 7. Refactor Auth Service
  - [x] 7.1 Update AuthService to use DTOs
    - Update register() to accept RegisterDTO
    - Update login() to accept LoginDTO
    - Ensure all methods return Model or DTO, not arrays
    - _Requirements: 3.1, 3.5_

  - [x] 7.2 Add business validation to AuthService
    - Throw BusinessException for business rule violations
    - Example: duplicate email, invalid credentials
    - _Requirements: 3.3, 5.3_

  - [ ]* 7.3 Write property test for Service Business Exception
    - **Property 7: Service Business Exception**
    - **Validates: Requirements 5.3**

- [x] 8. Refactor Auth Controller
  - [x] 8.1 Simplify AuthApiController
    - Remove any direct database queries
    - Convert request to DTO before passing to service
    - Use Resource for response transformation
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 8.2 Simplify ProfileController
    - Remove any business logic
    - Delegate all operations to service
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 9. Checkpoint - Auth module refactored
  - Ensure all tests pass, ask the user if questions arise.


## Phase 3: Refactor Schemes Module

- [x] 10. Create Course DTOs
  - [x] 10.1 Create CreateCourseDTO
    - Create `Modules/Schemes/app/DTOs/CreateCourseDTO.php`
    - Properties: title, description, categoryId, levelTag, type, tags
    - _Requirements: 1.1, 1.2_

  - [x] 10.2 Create UpdateCourseDTO
    - Create `Modules/Schemes/app/DTOs/UpdateCourseDTO.php`
    - All properties optional for partial updates
    - _Requirements: 1.1, 1.2_

  - [x] 10.3 Create CourseFilterDTO
    - Create `Modules/Schemes/app/DTOs/CourseFilterDTO.php`
    - Properties: status, levelTag, type, categoryId, tag, search, sort, perPage, page
    - _Requirements: 1.1, 1.2_

- [x] 11. Refactor CourseRepository
  - [x] 11.1 Extend BaseRepository
    - Update CourseRepository to extend BaseRepository
    - Keep custom methods (findBySlug, applyTagFilters)
    - _Requirements: 2.1, 2.2_

  - [ ]* 11.2 Write property test for Repository Filter
    - **Property 3: Repository Filter Application**
    - **Validates: Requirements 2.3**

  - [ ]* 11.3 Write property test for Repository Sort
    - **Property 4: Repository Sort Application**
    - **Validates: Requirements 2.4**

  - [ ]* 11.4 Write property test for Repository Pagination
    - **Property 5: Repository Pagination Structure**
    - **Validates: Requirements 2.5**

- [x] 12. Create CourseService
  - [x] 12.1 Create CourseService class
    - Create `Modules/Schemes/app/Services/CourseService.php`
    - Inject CourseRepositoryInterface
    - Implement CRUD methods accepting DTOs
    - _Requirements: 3.1, 3.2_

  - [x] 12.2 Add business logic to CourseService
    - Validate course can be published (has units/lessons)
    - Handle enrollment key generation
    - _Requirements: 3.3_

- [x] 13. Refactor CourseController
  - [x] 13.1 Simplify CourseController
    - Remove direct repository calls
    - Convert request to DTO
    - Delegate to CourseService
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 14. Create Unit DTOs and refactor
  - [x] 14.1 Create Unit DTOs
    - CreateUnitDTO, UpdateUnitDTO
    - _Requirements: 1.1, 1.2_

  - [x] 14.2 Create UnitService
    - Inject UnitRepositoryInterface
    - Handle unit reordering logic
    - _Requirements: 3.1, 3.2_

  - [x] 14.3 Simplify UnitController
    - Delegate to UnitService
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 15. Create Lesson DTOs and refactor
  - [x] 15.1 Create Lesson DTOs
    - CreateLessonDTO, UpdateLessonDTO
    - _Requirements: 1.1, 1.2_

  - [x] 15.2 Create LessonService
    - Inject LessonRepositoryInterface
    - Handle lesson content management
    - _Requirements: 3.1, 3.2_

  - [x] 15.3 Simplify LessonController
    - Delegate to LessonService
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 16. Register Schemes bindings
  - [x] 16.1 Update SchemesServiceProvider
    - Bind CourseService
    - Bind UnitService
    - Bind LessonService
    - _Requirements: 6.5_

- [x] 17. Checkpoint - Schemes module refactored
  - Ensure all tests pass, ask the user if questions arise.


## Phase 4: Refactor Enrollments Module

- [x] 18. Create Enrollment DTOs
  - [x] 18.1 Create EnrollmentDTO classes
    - CreateEnrollmentDTO (courseId, enrollmentKey optional)
    - EnrollmentFilterDTO
    - _Requirements: 1.1, 1.2_

- [x] 19. Refactor EnrollmentRepository
  - [x] 19.1 Extend BaseRepository
    - Update EnrollmentRepository to extend BaseRepository
    - Keep custom query methods
    - _Requirements: 2.1, 2.2_

- [x] 20. Create EnrollmentService
  - [x] 20.1 Create EnrollmentService class
    - Handle enrollment business logic
    - Validate enrollment prerequisites
    - Handle approval/decline workflow
    - _Requirements: 3.1, 3.2, 3.3_

- [x] 21. Refactor EnrollmentsController
  - [x] 21.1 Simplify EnrollmentsController
    - Delegate to EnrollmentService
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 22. Checkpoint - Enrollments module refactored
  - Ensure all tests pass, ask the user if questions arise.


## Phase 5: Refactor Content Module

- [x] 23. Create Content DTOs
  - [x] 23.1 Create Announcement DTOs
    - CreateAnnouncementDTO, UpdateAnnouncementDTO
    - _Requirements: 1.1, 1.2_

  - [x] 23.2 Create News DTOs
    - CreateNewsDTO, UpdateNewsDTO
    - _Requirements: 1.1, 1.2_

- [x] 24. Refactor Content Repositories
  - [x] 24.1 Extend BaseRepository for AnnouncementRepository
    - _Requirements: 2.1, 2.2_

  - [x] 24.2 Extend BaseRepository for NewsRepository
    - _Requirements: 2.1, 2.2_

- [x] 25. Create Content Services
  - [x] 25.1 Create AnnouncementService
    - Handle announcement publishing logic
    - _Requirements: 3.1, 3.2_

  - [x] 25.2 Create NewsService
    - Handle news publishing and trending logic
    - _Requirements: 3.1, 3.2_

- [x] 26. Refactor Content Controllers
  - [x] 26.1 Simplify AnnouncementController
    - _Requirements: 4.1, 4.2, 4.3_

  - [x] 26.2 Simplify NewsController
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 27. Checkpoint - Content module refactored
  - Ensure all tests pass, ask the user if questions arise.


## Phase 6: Refactor Forums Module

- [x] 28. Create Forums DTOs
  - [x] 28.1 Create Thread DTOs
    - CreateThreadDTO, UpdateThreadDTO
    - _Requirements: 1.1, 1.2_

  - [x] 28.2 Create Reply DTOs
    - CreateReplyDTO, UpdateReplyDTO
    - _Requirements: 1.1, 1.2_

- [x] 29. Refactor Forums Repositories
  - [x] 29.1 Extend BaseRepository for ThreadRepository
    - _Requirements: 2.1, 2.2_

  - [x] 29.2 Extend BaseRepository for ReplyRepository
    - _Requirements: 2.1, 2.2_

- [x] 30. Create Forums Services
  - [x] 30.1 Create ThreadService
    - Handle thread creation and moderation
    - _Requirements: 3.1, 3.2_

  - [x] 30.2 Create ReplyService
    - Handle reply creation and reactions
    - _Requirements: 3.1, 3.2_

- [x] 31. Refactor Forums Controllers
  - [x] 31.1 Simplify ThreadController
    - _Requirements: 4.1, 4.2, 4.3_

  - [x] 31.2 Simplify ReplyController
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 32. Checkpoint - Forums module refactored
  - Ensure all tests pass, ask the user if questions arise.


## Phase 7: Refactor Gamification Module

- [x] 33. Create Gamification DTOs
  - [x] 33.1 Create Challenge DTOs
    - CreateChallengeDTO, ChallengeFilterDTO
    - _Requirements: 1.1, 1.2_

  - [x] 33.2 Create Badge DTOs
    - CreateBadgeDTO
    - _Requirements: 1.1, 1.2_

- [x] 34. Refactor Gamification Repository
  - [x] 34.1 Extend BaseRepository for GamificationRepository
    - _Requirements: 2.1, 2.2_

- [x] 35. Update Gamification Services
  - [x] 35.1 Update ChallengeService to use DTOs
    - _Requirements: 3.1, 3.5_

  - [x] 35.2 Update LeaderboardService
    - Ensure returns proper types
    - _Requirements: 3.1, 3.5_

- [x] 36. Refactor Gamification Controllers
  - [x] 36.1 Simplify ChallengeController
    - _Requirements: 4.1, 4.2, 4.3_

  - [x] 36.2 Simplify LeaderboardController
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 37. Checkpoint - Gamification module refactored
  - Ensure all tests pass, ask the user if questions arise.


## Phase 8: Refactor Remaining Modules

- [x] 38. Refactor Learning Module
  - [x] 38.1 Create Learning DTOs
    - AssignmentDTO, SubmissionDTO
    - _Requirements: 1.1, 1.2_

  - [x] 38.2 Create Learning Services
    - AssignmentService, SubmissionService
    - _Requirements: 3.1, 3.2_

  - [x] 38.3 Simplify Learning Controllers
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 39. Refactor Notifications Module
  - [x] 39.1 Create Notification DTOs
    - _Requirements: 1.1, 1.2_

  - [x] 39.2 Update NotificationService
    - _Requirements: 3.1, 3.5_

  - [x] 39.3 Simplify NotificationsController
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 40. Refactor Common Module
  - [x] 40.1 Create Category DTOs
    - CreateCategoryDTO, UpdateCategoryDTO
    - _Requirements: 1.1, 1.2_

  - [x] 40.2 Update CategoryService to use DTOs
    - _Requirements: 3.1, 3.5_

  - [x] 40.3 Simplify CategoriesController
    - _Requirements: 4.1, 4.2, 4.3_

- [x] 41. Checkpoint - All modules refactored
  - Ensure all tests pass, ask the user if questions arise.


## Phase 9: Documentation and Final Verification

- [x] 42. Update documentation
  - [x] 42.1 Create architecture documentation
    - Document DTO, Repository, Service patterns
    - Add examples for each pattern
    - _Requirements: All_

  - [x] 42.2 Update module README files
    - Document new structure per module
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [x] 43. Final verification
  - [x] 43.1 Run all tests
    - Ensure all unit tests pass
    - Ensure all property tests pass
    - _Requirements: All_

  - [x] 43.2 Code review checklist
    - Verify no direct queries in controllers
    - Verify no business logic in controllers
    - Verify all services use repositories
    - _Requirements: 4.4, 4.5, 3.2_

- [x] 44. Final Checkpoint - Refactoring complete
  - Ensure all tests pass, ask the user if questions arise.
