# Requirements Document

## Introduction

This document specifies the requirements for implementing a correct XP (Experience Points) award system for assignments and quizzes in the Learning Management System. The system awards XP to students who achieve passing grades on assignments and quizzes, with strict rules to ensure fair, one-time awards per assignment regardless of multiple attempts.

## Glossary

- **Assignment**: A learning activity that can be either an assignment or quiz, differentiated by a 'type' field
- **Quiz**: An Assignment with type='quiz'
- **XP_System**: The gamification module responsible for tracking and awarding experience points
- **Grade**: The final score record for a student's assignment submission
- **Submission**: A student's attempt at completing an assignment
- **Passing_Grade**: The minimum score percentage required to pass (default: 70%)
- **Auto_Graded_Question**: A question type that can be graded automatically (MultipleChoice, Checkbox)
- **Manual_Graded_Question**: A question type requiring instructor grading (Essay)
- **XP_Transaction**: A record in the xp_transactions table tracking XP awards
- **Allow_Multiple**: A boolean field on assignments controlling whether students can retake after failing

## Requirements

### Requirement 1: One-Time XP Award Per Assignment

**User Story:** As a student, I want to receive XP only once per assignment, so that I cannot farm XP by repeatedly submitting the same assignment.

#### Acceptance Criteria

1. WHEN the XP_System checks if XP should be awarded, THE XP_System SHALL verify no existing XP_Transaction exists with (user_id, source_type='assignment', source_id=assignment.id, reason='achievement')
2. IF an XP_Transaction already exists for the assignment, THEN THE XP_System SHALL NOT award additional XP
3. THE XP_System SHALL use assignment.id as the source_id for all XP_Transactions (not grade.id or submission.id)
4. THE XP_System SHALL use 'assignment' as the source_type for all assignment-related XP_Transactions

### Requirement 2: Passing Grade Requirement

**User Story:** As an instructor, I want XP awarded only when students achieve passing grades, so that XP reflects actual learning achievement.

#### Acceptance Criteria

1. WHEN a Grade is finalized, THE XP_System SHALL retrieve the passing_score_percent from system settings
2. THE XP_System SHALL compare the Grade score against the passing_score_percent threshold
3. IF the Grade score is greater than or equal to passing_score_percent, THEN THE XP_System SHALL proceed with XP award evaluation
4. IF the Grade score is less than passing_score_percent, THEN THE XP_System SHALL NOT award XP
5. THE XP_System SHALL use the setting key 'grading.passing_score_percent' with a default value of 70

### Requirement 3: Flat XP Award Amount

**User Story:** As a student, I want to receive consistent XP for passing any assignment, so that all learning achievements are valued equally.

#### Acceptance Criteria

1. WHEN the XP_System awards XP for a passing Grade, THE XP_System SHALL award exactly 50 XP
2. THE XP_System SHALL NOT use tiered XP amounts based on score percentages
3. THE XP_System SHALL retrieve the XP amount from the setting 'gamification.points.assignment_completion' with a default value of 50
4. FOR ALL passing Grades, the XP amount SHALL be identical regardless of the exact score achieved

### Requirement 4: Auto-Graded Immediate XP Award

**User Story:** As a student, I want to receive XP immediately after submitting auto-graded assignments, so that I get instant feedback on my achievement.

#### Acceptance Criteria

1. WHEN a Submission contains only Auto_Graded_Questions, THE XP_System SHALL calculate the Grade immediately upon submission
2. WHEN the calculated Grade meets the passing threshold, THE XP_System SHALL award XP immediately
3. WHEN the calculated Grade is below the passing threshold AND allow_multiple is true, THE XP_System SHALL NOT award XP but allow retake
4. WHEN the calculated Grade is below the passing threshold AND allow_multiple is false, THE XP_System SHALL NOT award XP and mark as final

### Requirement 5: Manual-Graded Deferred XP Award

**User Story:** As a student, I want to receive XP after my instructor grades essay questions, so that my complete work is evaluated before awarding points.

#### Acceptance Criteria

1. WHEN a Submission contains at least one Manual_Graded_Question, THE XP_System SHALL defer XP evaluation until the instructor submits the final Grade
2. WHEN the instructor submits a Grade that meets the passing threshold, THE XP_System SHALL award XP at that time
3. WHEN the instructor submits a Grade below the passing threshold, THE XP_System SHALL NOT award XP
4. THE XP_System SHALL NOT award partial XP before all questions are graded

### Requirement 6: Remove Participation XP

**User Story:** As an instructor, I want XP awarded only for achievement, so that students are incentivized to learn rather than just participate.

#### Acceptance Criteria

1. THE XP_System SHALL NOT award XP upon Submission creation
2. THE XP_System SHALL NOT award participation-based XP for any assignment activity
3. IF the AwardXpForAssignmentSubmission listener exists, THE XP_System SHALL disable or remove it
4. THE XP_System SHALL remove the AwardXpForAssignmentSubmission listener from the EventServiceProvider

### Requirement 7: Allow Multiple Attempts Configuration

**User Story:** As an instructor, I want to control whether students can retake failed assignments, so that I can set appropriate learning policies per assignment.

#### Acceptance Criteria

1. THE Assignment SHALL have an allow_multiple boolean field
2. THE Assignment SHALL default allow_multiple to true when created
3. WHEN allow_multiple is true AND a student fails, THE Assignment SHALL permit additional Submissions
4. WHEN allow_multiple is false AND a student fails, THE Assignment SHALL NOT permit additional Submissions
5. THE allow_multiple field controls whether a failed student can retake and potentially earn XP on a future passing attempt. XP is still awarded only once per assignment regardless of the number of attempts.

### Requirement 8: XP Transaction Source Tracking

**User Story:** As a system administrator, I want XP transactions to correctly reference assignments, so that I can audit and debug XP awards accurately.

#### Acceptance Criteria

1. WHEN creating an XP_Transaction for assignment completion, THE XP_System SHALL set source_type to 'assignment'
2. WHEN creating an XP_Transaction for assignment completion, THE XP_System SHALL set source_id to the Assignment primary key
3. THE XP_System SHALL set reason to 'achievement' for all assignment completion XP awards
4. THE XP_System SHALL NOT use grade.id or submission.id as the source_id for assignment XP awards

### Requirement 9: Database Schema Migration

**User Story:** As a developer, I want the database schema to support the allow_multiple field, so that the system can store retake permissions per assignment.

#### Acceptance Criteria

1. THE Migration SHALL add an allow_multiple column to the assignments table
2. THE allow_multiple column SHALL be of type boolean
3. THE allow_multiple column SHALL default to true
4. THE Migration SHALL be timestamped and follow Laravel naming conventions

### Requirement 10: Grade Released Event Handling

**User Story:** As a student, I want to receive XP when my grade is released, so that I am rewarded for my achievement at the appropriate time.

#### Acceptance Criteria

1. WHEN a GradeReleased event is fired, THE XP_System SHALL handle it via the AwardXpForGradeReleased listener
2. THE AwardXpForGradeReleased listener SHALL verify the Grade has not already received XP
3. THE AwardXpForGradeReleased listener SHALL verify the Grade meets the passing threshold
4. THE AwardXpForGradeReleased listener SHALL create an XP_Transaction with correct source tracking
5. THE AwardXpForGradeReleased listener SHALL award the flat XP amount from system settings

### Requirement 11: No Maximum XP Per Course

**User Story:** As a student, I want to earn XP for all assignments I complete, so that my total learning effort is recognized without artificial limits.

#### Acceptance Criteria

1. THE XP_System SHALL NOT enforce a maximum XP limit per course
2. THE XP_System SHALL NOT cap total XP earnings at the course level
3. FOR ALL passing Grades in a course, THE XP_System SHALL award XP according to the standard rules
4. THE XP_System SHALL allow unlimited XP accumulation across all courses
