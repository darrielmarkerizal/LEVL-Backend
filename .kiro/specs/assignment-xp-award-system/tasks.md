# Implementation Plan: Assignment XP Award System

## Overview

This implementation plan converts the XP award system design into actionable coding tasks. The system awards flat 50 XP to students who achieve passing grades (≥70%) on assignments, with strict duplicate prevention using assignment.id as the unique source identifier. The implementation involves database migration, listener rewrite, listener removal, model updates, and comprehensive testing.

## Tasks

- [x] 1. Database migration for allow_multiple field
  - [x] 1.1 Create migration file to add allow_multiple column to assignments table
    - Create migration: `{timestamp}_add_allow_multiple_to_assignments_table.php`
    - Add boolean column with default value true
    - Include rollback method to drop column
    - _Requirements: 7.1, 7.2, 9.1, 9.2, 9.3, 9.4_

  - [ ]* 1.2 Write unit tests for migration
    - Test column exists after migration
    - Test default value is true
    - Test rollback removes column
    - _Requirements: 9.1, 9.2, 9.3_

- [x] 2. Update Assignment model
  - [x] 2.1 Add allow_multiple to Assignment model fillable and casts
    - Add 'allow_multiple' to $fillable array
    - Add 'allow_multiple' => 'boolean' to $casts array
    - _Requirements: 7.1, 7.2_

  - [ ]* 2.2 Write unit tests for Assignment model
    - Test allow_multiple field is fillable
    - Test allow_multiple defaults to true
    - Test allow_multiple can be set to false
    - Test allow_multiple is cast to boolean
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

- [x] 3. Update system settings seeder
  - [x] 3.1 Add gamification.points.assignment_completion setting to SystemSettingSeeder
    - Add setting with key 'gamification.points.assignment_completion'
    - Set default value to 50
    - Set type to 'integer'
    - Add description: 'Flat XP amount for completing any assignment with passing grade'
    - _Requirements: 3.1, 3.3, 10.5_

  - [x] 3.2 Remove deprecated tiered XP settings from SystemSettingSeeder
    - Remove 'gamification.points.grade.tier_s'
    - Remove 'gamification.points.grade.tier_a'
    - Remove 'gamification.points.grade.tier_b'
    - Remove 'gamification.points.grade.tier_c'
    - Remove 'gamification.points.grade.min'
    - _Requirements: 3.2_

- [ ] 4. Checkpoint - Verify database and model changes
  - Run migration and verify allow_multiple column exists
  - Run seeder and verify new setting exists
  - Ensure all tests pass, ask the user if questions arise

- [-] 5. Complete rewrite of AwardXpForGradeReleased listener
  - [x] 5.1 Rewrite AwardXpForGradeReleased listener with correct logic
    - Inject GamificationService via constructor
    - Iterate through event submissions collection
    - For each submission: extract grade and assignment
    - Check grade exists and is released
    - Retrieve passing threshold from 'grading.passing_score_percent' setting (default 70)
    - Compare grade.effective_score >= passing_score_percent
    - If passing: call gamification.awardXp() with userId, 50 XP, 'achievement', 'assignment', assignment.id
    - Use 'allow_multiple' => false option in awardXp call
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.3, 3.4, 8.1, 8.2, 8.3, 8.4, 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ] 5.2 Write property test for Property 1: One-time XP award per assignment
    - **Property 1: One-Time XP Award Per Assignment**
    - **Validates: Requirements 1.1, 1.2, 10.2**
    - Generate random user and assignment
    - Award XP for first passing grade
    - Attempt to award XP for second passing grade
    - Assert second attempt returns null
    - Assert only one XP record exists
    - Run 100 iterations

  - [ ]* 5.3 Write property test for Property 2: Correct source tracking
    - **Property 2: Correct Source Tracking**
    - **Validates: Requirements 1.3, 1.4, 8.1, 8.2, 8.3, 8.4, 10.4**
    - Generate random user, assignment, and passing score
    - Award XP
    - Assert source_type is 'assignment'
    - Assert source_id equals assignment.id
    - Assert reason is 'achievement'
    - Run 100 iterations

  - [ ]* 5.4 Write property test for Property 3: Passing grade requirement
    - **Property 3: Passing Grade Requirement**
    - **Validates: Requirements 2.2, 2.3, 2.4, 5.3, 10.3**
    - Generate random user, assignment, and score (0-100)
    - Award XP based on score
    - If score >= 70: assert XP awarded
    - If score < 70: assert XP not awarded
    - Run 100 iterations

  - [ ]* 5.5 Write property test for Property 4: Flat XP amount
    - **Property 4: Flat XP Amount**
    - **Validates: Requirements 3.1, 3.2, 3.4, 10.5**
    - Generate random user, assignment, and passing score (70-100)
    - Award XP
    - Assert XP amount is exactly 50
    - Run 100 iterations

  - [ ]* 5.6 Write unit tests for AwardXpForGradeReleased listener
    - Test passing grade (85%) awards XP
    - Test failing grade (65%) does not award XP
    - Test boundary: exactly 70% awards XP
    - Test boundary: 69.9% does not award XP
    - Test missing grade is skipped
    - Test unreleased grade is skipped
    - Test multiple submissions in single event
    - Test uses correct source_type 'assignment'
    - Test uses correct source_id (assignment.id)
    - Test uses correct reason 'achievement'
    - Test uses flat XP amount from settings
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.2, 2.3, 2.4, 3.1, 3.4, 8.1, 8.2, 8.3, 8.4, 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 6. Remove AwardXpForAssignmentSubmission listener
  - [x] 6.1 Delete AwardXpForAssignmentSubmission listener file
    - Delete file: `Modules/Gamification/app/Listeners/AwardXpForAssignmentSubmission.php`
    - _Requirements: 6.1, 6.3_

  - [x] 6.2 Remove listener registration from EventServiceProvider
    - Open `Modules/Gamification/app/Providers/EventServiceProvider.php`
    - Remove AwardXpForAssignmentSubmission from event mappings
    - Remove import statement for AwardXpForAssignmentSubmission
    - _Requirements: 6.4_

  - [ ]* 6.3 Write property test for Property 6: No participation XP on submission
    - **Property 6: No Participation XP on Submission**
    - **Validates: Requirements 6.1, 6.2**
    - Generate random user and assignment
    - Create submission
    - Assert no XP records exist for user
    - Run 100 iterations

- [ ] 7. Checkpoint - Verify listener changes
  - Run all unit tests and property tests
  - Verify AwardXpForAssignmentSubmission is removed
  - Verify AwardXpForGradeReleased works correctly
  - Ensure all tests pass, ask the user if questions arise

- [ ] 8. Integration testing for complete workflows
  - [ ]* 8.1 Write property test for Property 5: XP awarded on GradesReleased event
    - **Property 5: XP Awarded on GradesReleased Event**
    - **Validates: Requirements 4.2, 5.2**
    - Generate random user, assignment, submission, and passing grade
    - Fire GradesReleased event
    - Assert XP record exists for user and assignment
    - Run 100 iterations

  - [ ]* 8.2 Write property test for Property 7: Default allow_multiple value
    - **Property 7: Default allow_multiple Value**
    - **Validates: Requirements 7.2, 9.3**
    - Create assignment without specifying allow_multiple
    - Assert allow_multiple is true
    - Run 100 iterations

  - [ ]* 8.3 Write property test for Property 8: No XP cap
    - **Property 8: No XP Cap**
    - **Validates: Requirements 11.1, 11.2, 11.4**
    - Generate random user and course
    - Create random number of assignments (5-20)
    - Award XP for all assignments
    - Assert total XP equals (assignment_count * 50)
    - Run 100 iterations

  - [ ]* 8.4 Write integration tests for auto-graded workflow
    - Test auto-graded assignment awards XP immediately on submission
    - Test auto-graded assignment with failing grade does not award XP
    - Test auto-graded assignment with allow_multiple=false prevents retake
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

  - [ ]* 8.5 Write integration tests for manual-graded workflow
    - Test manual-graded assignment defers XP until instructor grades
    - Test manual-graded assignment awards XP after instructor releases passing grade
    - Test manual-graded assignment does not award XP for failing grade
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [ ]* 8.6 Write integration tests for multiple attempts
    - Test second passing attempt does not award duplicate XP
    - Test XP awarded for multiple different assignments in same course
    - Test XP awarded across multiple courses without cap
    - _Requirements: 1.1, 1.2, 7.3, 7.4, 7.5, 11.1, 11.2, 11.3, 11.4_

- [ ] 9. Final checkpoint and code quality
  - [ ] 9.1 Run Laravel Pint to fix code style
    - Execute: `vendor/bin/pint`
    - Verify all files pass PSR-12 standards
    - _Requirements: All_

  - [ ] 9.2 Run PHPStan for static analysis
    - Execute: `composer phpstan`
    - Fix any type errors or issues
    - _Requirements: All_

  - [ ] 9.3 Run full test suite
    - Execute: `composer test`
    - Ensure all unit tests pass
    - Ensure all property tests pass (100 iterations each)
    - Ensure all integration tests pass
    - _Requirements: All_

  - [ ] 9.4 Final verification
    - Verify migration can be run and rolled back
    - Verify all deprecated settings removed
    - Verify AwardXpForAssignmentSubmission completely removed
    - Verify AwardXpForGradeReleased uses correct source tracking
    - Ensure all tests pass, ask the user if questions arise

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests run 100 iterations each to verify universal correctness
- Unit tests validate specific examples and edge cases
- Integration tests verify end-to-end workflows
- Checkpoints ensure incremental validation at key milestones
- All code must follow Laravel 11 and PSR-12 standards
- All code must be Octane-safe (stateless, no mutable static properties)
- Controllers must remain thin (max 10 lines per method)
- Business logic belongs in services, not controllers
