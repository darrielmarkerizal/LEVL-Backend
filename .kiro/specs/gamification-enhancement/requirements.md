# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk melengkapi sistem gamifikasi pada platform LSP. Berdasarkan analisis gap, beberapa fitur perlu dikembangkan: Badge untuk scheme completion, Challenges system dengan auto-assign dan completion tracking, serta Global Leaderboard. Implementasi ini akan meningkatkan engagement user melalui mekanisme reward yang lebih komprehensif.

## Glossary

- **Scheme**: Unit kompetensi/skema sertifikasi yang berisi beberapa course
- **Badge**: Penghargaan visual yang diberikan kepada user atas pencapaian tertentu
- **Challenge**: Tantangan dengan batas waktu yang memberikan reward points/badge
- **Leaderboard**: Papan peringkat global yang menampilkan ranking user berdasarkan XP
- **XP (Experience Points)**: Poin yang dikumpulkan user dari berbagai aktivitas
- **Auto-assign**: Proses otomatis memberikan challenge ke user yang eligible
- **Challenge Completion**: Penyelesaian challenge berdasarkan kriteria tertentu

## Requirements

### Requirement 1: Badge untuk Scheme Completion

**User Story:** As a learner, I want to receive a badge when I complete all courses in a scheme, so that I can showcase my certification achievement.

#### Acceptance Criteria

1. WHEN a user completes all courses within a scheme THEN the system SHALL award a scheme completion badge to that user
2. WHEN awarding a scheme completion badge THEN the system SHALL create a unique badge code based on scheme identifier
3. WHEN a user already has a scheme completion badge THEN the system SHALL NOT award duplicate badges
4. WHEN a scheme completion badge is awarded THEN the system SHALL record the award timestamp and scheme details
5. WHEN checking scheme completion THEN the system SHALL verify all courses in the scheme have enrollment status 'completed'

### Requirement 2: SchemeCompleted Event and Listener

**User Story:** As a system administrator, I want the system to automatically detect scheme completion, so that badges are awarded without manual intervention.

#### Acceptance Criteria

1. WHEN a course is marked as completed THEN the system SHALL check if all courses in the parent scheme are completed
2. WHEN all courses in a scheme are completed THEN the system SHALL dispatch a SchemeCompleted event
3. WHEN SchemeCompleted event is dispatched THEN the AwardBadgeForSchemeCompleted listener SHALL process the event
4. WHEN processing SchemeCompleted event THEN the listener SHALL award both badge and bonus XP (configurable)
5. WHEN SchemeCompleted event fails THEN the system SHALL log the error and NOT affect other processes

### Requirement 3: Challenges API Endpoints

**User Story:** As a learner, I want to view and track my challenges through API, so that I can see available challenges and my progress.

#### Acceptance Criteria

1. WHEN requesting GET /v1/challenges THEN the system SHALL return list of active challenges with pagination
2. WHEN requesting GET /v1/challenges/{challenge} THEN the system SHALL return challenge details including user progress
3. WHEN requesting GET /v1/challenges/my THEN the system SHALL return challenges assigned to current user
4. WHEN requesting GET /v1/challenges/completed THEN the system SHALL return user's completed challenges history
5. WHEN requesting POST /v1/challenges/{challenge}/claim THEN the system SHALL award challenge rewards if completed

### Requirement 4: Challenge Auto-Assignment

**User Story:** As a learner, I want to automatically receive daily and weekly challenges, so that I have continuous goals to achieve.

#### Acceptance Criteria

1. WHEN a new day starts THEN the system SHALL assign daily challenges to all active users
2. WHEN a new week starts THEN the system SHALL assign weekly challenges to all active users
3. WHEN assigning challenges THEN the system SHALL check user eligibility based on enrollment status
4. WHEN a user already has an active challenge of same type THEN the system SHALL NOT assign duplicate
5. WHEN auto-assignment runs THEN the system SHALL create UserChallengeAssignment records with deadline

### Requirement 5: Challenge Completion Logic

**User Story:** As a learner, I want my challenge progress to be tracked automatically, so that I receive rewards when I complete challenges.

#### Acceptance Criteria

1. WHEN a user completes a lesson THEN the system SHALL check and update related challenge progress
2. WHEN a user submits an assignment THEN the system SHALL check and update related challenge progress
3. WHEN a user completes an exercise attempt THEN the system SHALL check and update related challenge progress
4. WHEN challenge criteria is met THEN the system SHALL mark challenge as completed and award rewards
5. WHEN challenge deadline passes without completion THEN the system SHALL mark challenge as expired

### Requirement 6: Challenge Types and Criteria

**User Story:** As a system administrator, I want to define various challenge types with specific criteria, so that challenges are diverse and engaging.

#### Acceptance Criteria

1. WHEN creating a daily challenge THEN the system SHALL support criteria: complete X lessons, submit X assignments, complete X exercises
2. WHEN creating a weekly challenge THEN the system SHALL support criteria: complete X lessons, maintain X-day streak, earn X XP
3. WHEN creating a special challenge THEN the system SHALL support custom criteria with flexible parameters
4. WHEN defining challenge criteria THEN the system SHALL store criteria as JSON with type and target values
5. WHEN evaluating challenge completion THEN the system SHALL compare user progress against criteria

### Requirement 7: Global Leaderboard API Endpoints

**User Story:** As a learner, I want to view global leaderboard to compare my progress with others, so that I stay motivated through competition.

#### Acceptance Criteria

1. WHEN requesting GET /v1/leaderboards THEN the system SHALL return top users ranked by total XP with pagination
2. WHEN requesting GET /v1/leaderboards/my-rank THEN the system SHALL return current user's global rank and surrounding users
3. WHEN returning leaderboard data THEN the system SHALL include user avatar, name, total XP, level, and rank position
4. WHEN a user earns XP THEN the system SHALL update global leaderboard ranking
5. WHEN leaderboard is requested THEN the system SHALL return configurable limit (default 10, max 100)

### Requirement 8: Challenge Rewards Integration

**User Story:** As a learner, I want to receive XP and badges when completing challenges, so that my efforts are rewarded.

#### Acceptance Criteria

1. WHEN a challenge is completed THEN the system SHALL award configured XP points to user
2. WHEN a challenge has associated badge THEN the system SHALL award the badge upon completion
3. WHEN awarding challenge rewards THEN the system SHALL create Point record with source_type 'challenge'
4. WHEN awarding challenge rewards THEN the system SHALL update user's total XP and level
5. WHEN challenge reward is claimed THEN the system SHALL prevent duplicate reward claims

### Requirement 9: Gamification Dashboard Data

**User Story:** As a learner, I want to see my gamification summary, so that I can track my overall progress and achievements.

#### Acceptance Criteria

1. WHEN requesting GET /v1/gamification/summary THEN the system SHALL return user's total XP, level, badges count, and current streak
2. WHEN requesting GET /v1/gamification/badges THEN the system SHALL return all badges earned by user
3. WHEN requesting GET /v1/gamification/points-history THEN the system SHALL return paginated XP earning history
4. WHEN requesting GET /v1/gamification/achievements THEN the system SHALL return user's milestone achievements
5. WHEN returning gamification data THEN the system SHALL include progress percentages and next milestone info
