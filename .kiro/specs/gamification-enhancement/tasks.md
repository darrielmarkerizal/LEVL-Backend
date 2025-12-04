# Implementation Plan

## Phase 1: Database Migrations and Enums

- [x] 1. Create database migrations
  - [x] 1.1 Create migration to add criteria fields to challenges table
    - Add `criteria` JSON column after description
    - Add `target_count` integer column with default 1
    - _Requirements: 6.4_

  - [x] 1.2 Create migration to add progress fields to user_challenge_assignments table
    - Add `current_progress` integer column with default 0
    - Add `reward_claimed` boolean column with default false
    - _Requirements: 5.4, 8.5_

  - [x] 1.3 Update PointSourceType enum to include Challenge
    - Add `Challenge = 'challenge'` case to PointSourceType enum
    - _Requirements: 8.3_

- [x] 2. Create new enums
  - [x] 2.1 Create ChallengeCriteriaType enum
    - Add cases: LessonsCompleted, AssignmentsSubmitted, ExercisesCompleted, XpEarned, StreakDays, CoursesCompleted
    - _Requirements: 6.1, 6.2, 6.3_

  - [x] 2.2 Create ChallengeAssignmentStatus enum
    - Add cases: Pending, InProgress, Completed, Claimed, Expired
    - _Requirements: 5.4, 5.5_

- [x] 3. Update existing models
  - [x] 3.1 Update Challenge model
    - Add criteria and target_count to fillable
    - Add criteria cast as array
    - _Requirements: 6.4_

  - [x] 3.2 Update UserChallengeAssignment model
    - Add current_progress and reward_claimed to fillable
    - Add status cast to ChallengeAssignmentStatus enum
    - Add helper methods: isClaimable(), getProgressPercentage()
    - _Requirements: 5.4, 8.5_

- [x] 4. Checkpoint - Database and models ready
  - Ensure all tests pass, ask the user if questions arise.


## Phase 2: Challenge Service Implementation

- [x] 5. Create ChallengeService
  - [x] 5.1 Implement getUserChallenges method
    - Get all active challenges assigned to user
    - Include progress calculation
    - _Requirements: 3.3_

  - [x] 5.2 Implement assignDailyChallenges method
    - Get all active users with enrollments
    - Assign daily challenges that are not already assigned
    - Set expires_at to end of day
    - _Requirements: 4.1, 4.3, 4.4_

  - [x] 5.3 Implement assignWeeklyChallenges method
    - Get all active users with enrollments
    - Assign weekly challenges that are not already assigned
    - Set expires_at to end of week
    - _Requirements: 4.2, 4.3, 4.4_

  - [x] 5.4 Implement checkAndUpdateProgress method
    - Accept userId, criteriaType, and count
    - Find matching active challenges
    - Update current_progress
    - Check if challenge is completed
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [x] 5.5 Implement completeChallenge method
    - Mark assignment as completed
    - Set completed_at timestamp
    - _Requirements: 5.4_

  - [x] 5.6 Implement claimReward method
    - Verify challenge is completed and not claimed
    - Award XP using GamificationService
    - Award badge if challenge has badge_id
    - Mark reward_claimed as true
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [x] 5.7 Implement expireOverdueChallenges method
    - Find assignments past expires_at with status pending/in_progress
    - Mark as expired
    - _Requirements: 5.5_

- [ ]* 5.8 Write property test for Challenge Assignment Idempotence
  - **Property 2: Challenge Assignment Idempotence**
  - **Validates: Requirements 4.4**

- [ ]* 5.9 Write property test for Challenge Progress Tracking
  - **Property 3: Challenge Progress Tracking**
  - **Validates: Requirements 5.1, 5.2, 5.3**

- [ ]* 5.10 Write property test for Challenge Completion Detection
  - **Property 4: Challenge Completion Detection**
  - **Validates: Requirements 5.4**

- [ ]* 5.11 Write property test for Reward Claim Idempotence
  - **Property 5: Reward Claim Idempotence**
  - **Validates: Requirements 8.5**

- [x] 6. Checkpoint - ChallengeService complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 3: Challenge Progress Listener

- [x] 7. Create UpdateChallengeProgress listener
  - [x] 7.1 Create listener class
    - Inject ChallengeService
    - _Requirements: 5.1, 5.2, 5.3_

  - [x] 7.2 Handle LessonCompleted event
    - Call checkAndUpdateProgress with 'lessons_completed' type
    - _Requirements: 5.1_

  - [x] 7.3 Handle SubmissionCreated event
    - Call checkAndUpdateProgress with 'assignments_submitted' type
    - _Requirements: 5.2_

  - [x] 7.4 Handle AttemptCompleted event
    - Call checkAndUpdateProgress with 'exercises_completed' type
    - _Requirements: 5.3_

  - [x] 7.5 Register listener in EventServiceProvider
    - Add listener to LessonCompleted, SubmissionCreated, AttemptCompleted events
    - _Requirements: 5.1, 5.2, 5.3_

- [x] 8. Checkpoint - Challenge progress tracking complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 4: Leaderboard Service Implementation

- [x] 9. Create LeaderboardService
  - [x] 9.1 Implement getGlobalLeaderboard method
    - Query UserGamificationStat ordered by total_xp desc
    - Include user data (name, avatar)
    - Support pagination with configurable limit
    - _Requirements: 7.1, 7.3, 7.5_

  - [x] 9.2 Implement getUserRank method
    - Get user's global rank
    - Include surrounding users (above and below)
    - _Requirements: 7.2_

  - [x] 9.3 Implement updateRankings method
    - Recalculate all rankings based on total_xp
    - Update leaderboards table
    - _Requirements: 7.4_

- [ ]* 9.4 Write property test for Leaderboard Ranking Consistency
  - **Property 6: Leaderboard Ranking Consistency**
  - **Validates: Requirements 7.1, 7.4**

- [x] 10. Checkpoint - LeaderboardService complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 5: API Controllers

- [x] 11. Create ChallengeController
  - [x] 11.1 Implement index method
    - Return paginated list of active challenges
    - Include user progress if authenticated
    - _Requirements: 3.1_

  - [x] 11.2 Implement show method
    - Return challenge details with user progress
    - _Requirements: 3.2_

  - [x] 11.3 Implement myChallenges method
    - Return challenges assigned to current user
    - Include progress and status
    - _Requirements: 3.3_

  - [x] 11.4 Implement completed method
    - Return user's completed challenges history
    - Support pagination
    - _Requirements: 3.4_

  - [x] 11.5 Implement claim method
    - Validate challenge is completed
    - Call ChallengeService.claimReward
    - Return reward details
    - _Requirements: 3.5_

- [x] 12. Create LeaderboardController
  - [x] 12.1 Implement index method
    - Return global leaderboard with pagination
    - _Requirements: 7.1_

  - [x] 12.2 Implement myRank method
    - Return current user's rank and surrounding users
    - _Requirements: 7.2_

- [x] 13. Enhance GamificationController
  - [x] 13.1 Implement summary method
    - Return user's total XP, level, badges count, streak, rank
    - _Requirements: 9.1_

  - [x] 13.2 Implement badges method
    - Return all badges earned by user
    - _Requirements: 9.2_

  - [x] 13.3 Implement pointsHistory method
    - Return paginated XP earning history
    - _Requirements: 9.3_

  - [x] 13.4 Implement achievements method
    - Return user's milestone achievements
    - _Requirements: 9.4, 9.5_

- [x] 14. Checkpoint - API Controllers complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 6: API Routes

- [x] 15. Update routes/api.php
  - [x] 15.1 Add Challenge routes
    - GET /v1/challenges
    - GET /v1/challenges/my
    - GET /v1/challenges/completed
    - GET /v1/challenges/{challenge}
    - POST /v1/challenges/{challenge}/claim
    - _Requirements: 3.1-3.5_

  - [x] 15.2 Add Leaderboard routes
    - GET /v1/leaderboards
    - GET /v1/leaderboards/my-rank
    - _Requirements: 7.1, 7.2_

  - [x] 15.3 Add Gamification dashboard routes
    - GET /v1/gamification/summary
    - GET /v1/gamification/badges
    - GET /v1/gamification/points-history
    - GET /v1/gamification/achievements
    - _Requirements: 9.1-9.5_

- [x] 16. Checkpoint - API Routes complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 7: Scheduled Commands

- [x] 17. Create Artisan commands
  - [x] 17.1 Create AssignDailyChallenges command
    - Call ChallengeService.assignDailyChallenges
    - Log assignment count
    - _Requirements: 4.1_

  - [x] 17.2 Create AssignWeeklyChallenges command
    - Call ChallengeService.assignWeeklyChallenges
    - Log assignment count
    - _Requirements: 4.2_

  - [x] 17.3 Create ExpireChallenges command
    - Call ChallengeService.expireOverdueChallenges
    - Log expired count
    - _Requirements: 5.5_

- [ ]* 17.4 Write property test for Challenge Expiration
  - **Property 7: Challenge Expiration**
  - **Validates: Requirements 5.5**

  - [x] 17.5 Create UpdateLeaderboard command
    - Call LeaderboardService.updateRankings
    - Log update status
    - _Requirements: 7.4_

- [x] 18. Register commands in Kernel
  - [x] 18.1 Schedule commands
    - challenges:assign-daily at 00:01 daily
    - challenges:assign-weekly at 00:01 on Monday
    - challenges:expire hourly
    - leaderboard:update every 5 minutes
    - _Requirements: 4.1, 4.2, 5.5, 7.4_

- [x] 19. Checkpoint - Scheduled commands complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 8: Badge Enhancement

- [x] 20. Enhance badge awarding for course completion
  - [x] 20.1 Update AwardBadgeForCourseCompleted listener
    - Create unique badge code per course (e.g., course_completion_{course_id})
    - Include course title in badge name
    - _Requirements: 1.1, 1.2, 1.4_

- [ ]* 20.2 Write property test for Badge Uniqueness
  - **Property 1: Badge Uniqueness per Course**
  - **Validates: Requirements 1.3**

- [x] 21. Checkpoint - Badge enhancement complete
  - Ensure all tests pass, ask the user if questions arise.


## Phase 9: Integration and Final Testing

- [ ]* 22. Write property test for XP Award Creates Point Record
  - **Property 8: XP Award Creates Point Record**
  - **Validates: Requirements 8.3**

- [x] 23. Create database seeder for sample challenges
  - [x] 23.1 Create ChallengeSeeder
    - Create sample daily challenges (complete 3 lessons, submit 1 assignment)
    - Create sample weekly challenges (earn 500 XP, complete 10 lessons)
    - _Requirements: 6.1, 6.2_

- [x] 24. Run migrations and seeders
  - [x] 24.1 Execute migrations
    - Run php artisan migrate
    - Verify tables are updated
    - _Requirements: All_

  - [x] 24.2 Execute seeders
    - Run php artisan db:seed --class=ChallengeSeeder
    - Verify sample data
    - _Requirements: 6.1, 6.2_

- [x] 25. Final Checkpoint - All tests passing
  - Ensure all tests pass, ask the user if questions arise.
