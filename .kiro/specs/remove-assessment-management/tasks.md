# Implementation Plan

## Phase 1: Cleanup OpenApiGeneratorService

- [x] 1. Remove assessment entries from OpenApiGeneratorService
  - [x] 1.1 Remove assessment entries from endpointExamples property
    - Remove all entries with keys containing 'assessment'
    - Remove entries: v1/assessments/*, v1/assessment-registrations/*
    - _Requirements: 2.1_

  - [x] 1.2 Remove assessment entries from summaryOverrides property
    - Remove all entries with keys containing 'assessment'
    - _Requirements: 2.2_

  - [x] 1.3 Remove assessment entries from descriptionOverrides property
    - Remove all entries with keys containing 'assessment'
    - _Requirements: 2.3_

- [x] 2. Checkpoint - OpenApiGeneratorService cleanup complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 2: Cleanup Spec Documents

- [x] 3. Update openapi-scalar-documentation requirements.md
  - [x] 3.1 Remove Assessment Registration Documentation requirement
    - Remove Requirement 4 (Assessment Registration Documentation)
    - _Requirements: 3.1_

  - [x] 3.2 Remove Assessment Exercises, Questions, Attempts, Grading requirements
    - Remove Requirements 20-23
    - _Requirements: 3.2_

  - [x] 3.3 Renumber remaining requirements
    - Update requirement numbers to maintain sequence
    - _Requirements: 3.1, 3.2_

- [x] 4. Update openapi-scalar-documentation design.md
  - [x] 4.1 Remove Assessment Module Endpoints section
    - Remove entire "Assessment Module Endpoints" section
    - Remove all v1/assessments/* entries from endpoint examples
    - _Requirements: 3.3_

- [x] 5. Update openapi-scalar-documentation tasks.md
  - [x] 5.1 Remove assessment-related tasks
    - Remove Task 15 (Add Assessment Module examples) and subtasks
    - Update task numbering if needed
    - _Requirements: 3.4_

- [x] 6. Checkpoint - Spec documents cleanup complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 3: Regenerate and Verify

- [x] 7. Regenerate OpenAPI spec
  - [x] 7.1 Run generation command
    - Execute `php artisan openapi:generate`
    - Verify no errors during generation
    - _Requirements: 1.1-1.6_

- [x] 8. Verify documentation completeness
  - [x] 8.1 Check Scalar UI
    - Access `/scalar` and verify no assessment endpoints appear
    - Verify other endpoints still work correctly
    - _Requirements: 1.1-1.6_

- [x] 9. Final Checkpoint - All cleanup complete
  - Ensure all tests pass, ask the user if questions arise.
