# Design Document

## Overview

Dokumen ini menjelaskan desain teknis untuk melengkapi sistem gamifikasi pada platform LSP. Berdasarkan analisis codebase, sistem gamifikasi sudah memiliki fondasi yang baik dengan Points, Badges, Levels, dan struktur Challenge/Leaderboard. Implementasi ini akan menambahkan:

1. **Badge untuk Course Completion** - Memperbaiki badge awarding dengan badge unik per course
2. **Challenges System** - API endpoints, auto-assignment, dan completion tracking
3. **Global Leaderboard** - API endpoints untuk ranking global
4. **Gamification Dashboard** - API untuk summary dan history

**Catatan:** Berdasarkan analisis codebase, "scheme" dalam konteks ini merujuk ke Course (tidak ada model Scheme terpisah). Badge completion sudah ada untuk course, akan diperbaiki untuk lebih spesifik.

## Architecture

### Current Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Gamification Module                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Events (LessonCompleted, SubmissionCreated, AttemptCompleted)  │
│                            │                                     │
│                            ▼                                     │
│                    ┌───────────────┐                            │
│                    │   Listeners   │ (Award XP/Badge)           │
│                    └───────────────┘                            │
│                            │                                     │
│                            ▼                                     │
│                    ┌───────────────┐                            │
│                    │ GamificationService │                      │
│                    └───────────────┘                            │
│                            │                                     │
│                            ▼                                     │
│              Points, Badges, Levels, Leaderboards               │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Enhanced Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                 Enhanced Gamification Module                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Events ──────────────────────────────────────────────────────► │
│  │ LessonCompleted                                              │
│  │ SubmissionCreated                                            │
│  │ AttemptCompleted                                             │
│  │ CourseCompleted ──► CheckChallengeProgress                   │
│  │                                                              │
│  ▼                                                              │
│  ┌─────────────────┐    ┌─────────────────┐                    │
│  │ XP Listeners    │    │ Challenge       │                    │
│  │ Badge Listeners │    │ Progress        │                    │
│  └─────────────────┘    │ Listener        │                    │
│           │             └─────────────────┘                    │
│           ▼                      │                              │
│  ┌─────────────────────────────────────────┐                   │
│  │         GamificationService             │                   │
│  │  - awardXp()                            │                   │
│  │  - awardBadge()                         │                   │
│  │  - updateGlobalLeaderboard()            │                   │
│  └─────────────────────────────────────────┘                   │
│           │                                                     │
│           ▼                                                     │
│  ┌─────────────────────────────────────────┐                   │
│  │         ChallengeService                │                   │
│  │  - assignDailyChallenges()              │                   │
│  │  - assignWeeklyChallenges()             │                   │
│  │  - checkProgress()                      │                   │
│  │  - completeChallenge()                  │                   │
│  │  - claimReward()                        │                   │
│  └─────────────────────────────────────────┘                   │
│           │                                                     │
│           ▼                                                     │
│  ┌─────────────────────────────────────────┐                   │
│  │              API Controllers            │                   │
│  │  - ChallengeController                  │                   │
│  │  - LeaderboardController                │                   │
│  │  - GamificationController (enhanced)    │                   │
│  └─────────────────────────────────────────┘                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Enhanced Challenge Model

Tambahkan field `criteria` untuk menyimpan kriteria challenge:

```php
// Challenge model - add criteria field
protected $fillable = [
    'title',
    'description', 
    'type',
    'points_reward',
    'badge_id',
    'criteria',      // NEW: JSON criteria
    'target_count',  // NEW: target untuk completion
    'start_at',
    'end_at',
];

protected $casts = [
    'criteria' => 'array',
    'target_count' => 'integer',
];
```

### 2. Challenge Criteria Types

```php
// Enums/ChallengeCriteriaType.php
enum ChallengeCriteriaType: string
{
    case LessonsCompleted = 'lessons_completed';
    case AssignmentsSubmitted = 'assignments_submitted';
    case ExercisesCompleted = 'exercises_completed';
    case XpEarned = 'xp_earned';
    case StreakDays = 'streak_days';
    case CoursesCompleted = 'courses_completed';
}
```

### 3. ChallengeService

```php
class ChallengeService
{
    public function assignDailyChallenges(): void;
    public function assignWeeklyChallenges(): void;
    public function getUserChallenges(int $userId): Collection;
    public function getActiveChallenge(int $challengeId): ?Challenge;
    public function checkAndUpdateProgress(int $userId, string $criteriaType, int $count = 1): void;
    public function completeChallenge(UserChallengeAssignment $assignment): void;
    public function claimReward(int $userId, int $challengeId): array;
    public function expireOverdueChallenges(): int;
}
```

### 4. LeaderboardService

```php
class LeaderboardService
{
    public function getGlobalLeaderboard(int $limit = 10, int $page = 1): LengthAwarePaginator;
    public function getUserRank(int $userId): array;
    public function updateRankings(): void;
}
```

### 5. API Controllers

#### ChallengeController
```php
class ChallengeController extends Controller
{
    public function index(Request $request);           // GET /v1/challenges
    public function show(int $challengeId);            // GET /v1/challenges/{challenge}
    public function myChallenges(Request $request);    // GET /v1/challenges/my
    public function completed(Request $request);       // GET /v1/challenges/completed
    public function claim(int $challengeId);           // POST /v1/challenges/{challenge}/claim
}
```

#### LeaderboardController
```php
class LeaderboardController extends Controller
{
    public function index(Request $request);    // GET /v1/leaderboards
    public function myRank(Request $request);   // GET /v1/leaderboards/my-rank
}
```

#### Enhanced GamificationController
```php
class GamificationController extends Controller
{
    public function summary(Request $request);       // GET /v1/gamification/summary
    public function badges(Request $request);        // GET /v1/gamification/badges
    public function pointsHistory(Request $request); // GET /v1/gamification/points-history
    public function achievements(Request $request);  // GET /v1/gamification/achievements
}
```

## Data Models

### Challenge Criteria Structure

```php
// Daily Challenge Example
[
    'type' => 'lessons_completed',
    'target' => 3,
    'description' => 'Selesaikan 3 lesson hari ini'
]

// Weekly Challenge Example
[
    'type' => 'xp_earned',
    'target' => 500,
    'description' => 'Kumpulkan 500 XP minggu ini'
]
```

### UserChallengeAssignment Status Values

```php
enum ChallengeAssignmentStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Claimed = 'claimed';
    case Expired = 'expired';
}
```

### API Response Structures

#### Challenge List Response
```php
[
    'data' => [
        [
            'id' => 1,
            'title' => 'Pejuang Harian',
            'description' => 'Selesaikan 3 lesson hari ini',
            'type' => 'daily',
            'points_reward' => 50,
            'badge' => null,
            'criteria' => [
                'type' => 'lessons_completed',
                'target' => 3,
            ],
            'progress' => [
                'current' => 1,
                'target' => 3,
                'percentage' => 33.33,
            ],
            'status' => 'in_progress',
            'expires_at' => '2024-01-16T00:00:00.000000Z',
        ]
    ],
    'meta' => [
        'current_page' => 1,
        'total' => 5,
    ]
]
```

#### Leaderboard Response
```php
[
    'data' => [
        [
            'rank' => 1,
            'user' => [
                'id' => 5,
                'name' => 'Budi Santoso',
                'avatar_url' => 'https://...',
            ],
            'total_xp' => 15000,
            'level' => 25,
        ]
    ],
    'meta' => [
        'current_page' => 1,
        'per_page' => 10,
        'total' => 100,
    ]
]
```

#### Gamification Summary Response
```php
[
    'data' => [
        'total_xp' => 5000,
        'level' => 15,
        'xp_to_next_level' => 150,
        'progress_to_next_level' => 65.5,
        'badges_count' => 8,
        'current_streak' => 5,
        'longest_streak' => 12,
        'rank' => 42,
        'active_challenges' => 3,
    ]
]
```

## Database Migrations

### 1. Add criteria to challenges table

```php
Schema::table('challenges', function (Blueprint $table) {
    $table->json('criteria')->nullable()->after('description');
    $table->integer('target_count')->default(1)->after('criteria');
});
```

### 2. Add progress to user_challenge_assignments

```php
Schema::table('user_challenge_assignments', function (Blueprint $table) {
    $table->integer('current_progress')->default(0)->after('status');
    $table->boolean('reward_claimed')->default(false)->after('completed_at');
});
```

### 3. Add source_type 'challenge' to points enum

```php
// Update points table enum
DB::statement("ALTER TABLE points MODIFY COLUMN source_type ENUM('lesson', 'assignment', 'attempt', 'challenge', 'system') DEFAULT 'system'");
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Badge Uniqueness per Course
*For any* course and user combination, awarding a completion badge twice SHALL result in only one UserBadge record.
**Validates: Requirements 1.3**

### Property 2: Challenge Assignment Idempotence
*For any* user with an active challenge of a specific type, attempting to assign the same challenge type SHALL NOT create duplicate assignments.
**Validates: Requirements 4.4**

### Property 3: Challenge Progress Tracking
*For any* user with an assigned challenge, completing a relevant activity (lesson/assignment/exercise) SHALL increment the challenge progress by the correct amount.
**Validates: Requirements 5.1, 5.2, 5.3**

### Property 4: Challenge Completion Detection
*For any* challenge assignment where current_progress >= target_count, the system SHALL mark the challenge as completed.
**Validates: Requirements 5.4**

### Property 5: Reward Claim Idempotence
*For any* completed challenge, claiming rewards twice SHALL NOT award double XP or badges.
**Validates: Requirements 8.5**

### Property 6: Leaderboard Ranking Consistency
*For any* set of users with XP, the leaderboard ranking SHALL be in descending order of total_xp.
**Validates: Requirements 7.1, 7.4**

### Property 7: Challenge Expiration
*For any* challenge assignment past its expires_at timestamp with status not 'completed' or 'claimed', the system SHALL mark it as 'expired'.
**Validates: Requirements 5.5**

### Property 8: XP Award Creates Point Record
*For any* challenge reward claim, the system SHALL create a Point record with source_type 'challenge' and correct points value.
**Validates: Requirements 8.3**

## Error Handling

### Challenge Not Found
- Return 404 with message "Challenge tidak ditemukan"

### Challenge Not Assigned
- Return 403 with message "Challenge belum di-assign ke Anda"

### Challenge Not Completed
- Return 400 with message "Challenge belum selesai, tidak dapat claim reward"

### Reward Already Claimed
- Return 400 with message "Reward sudah di-claim sebelumnya"

### Challenge Expired
- Return 400 with message "Challenge sudah expired"

## Testing Strategy

### Unit Testing
- Test ChallengeService methods individually
- Test LeaderboardService ranking calculation
- Test challenge criteria evaluation logic
- Test progress tracking for each criteria type

### Property-Based Testing
Menggunakan PHPUnit dengan data providers:
- Test badge uniqueness with random user/course combinations
- Test challenge assignment idempotence
- Test progress tracking with various activity counts
- Test leaderboard ordering with random XP values
- Test reward claim idempotence

### Integration Testing
- Test full challenge flow: assign → progress → complete → claim
- Test leaderboard update after XP changes
- Test API endpoints with authentication
- Test scheduled commands for auto-assignment

## Implementation Notes

### Files to Create

1. `app/Enums/ChallengeCriteriaType.php`
2. `app/Enums/ChallengeAssignmentStatus.php`
3. `app/Services/ChallengeService.php`
4. `app/Services/LeaderboardService.php`
5. `app/Http/Controllers/ChallengeController.php`
6. `app/Http/Controllers/LeaderboardController.php`
7. `app/Listeners/UpdateChallengeProgress.php`
8. `app/Console/Commands/AssignDailyChallenges.php`
9. `app/Console/Commands/AssignWeeklyChallenges.php`
10. `app/Console/Commands/ExpireChallenges.php`
11. `database/migrations/xxxx_add_criteria_to_challenges_table.php`
12. `database/migrations/xxxx_add_progress_to_user_challenge_assignments.php`
13. `database/migrations/xxxx_add_challenge_source_type_to_points.php`

### Files to Modify

1. `app/Models/Challenge.php` - Add criteria cast
2. `app/Models/UserChallengeAssignment.php` - Add progress fields
3. `app/Enums/PointSourceType.php` - Add Challenge case
4. `app/Services/GamificationService.php` - Add summary methods
5. `app/Http/Controllers/GamificationController.php` - Add API methods
6. `app/Providers/EventServiceProvider.php` - Register new listeners
7. `routes/api.php` - Add new routes

### Scheduled Commands

```php
// app/Console/Kernel.php
$schedule->command('challenges:assign-daily')->dailyAt('00:01');
$schedule->command('challenges:assign-weekly')->weeklyOn(1, '00:01');
$schedule->command('challenges:expire')->hourly();
$schedule->command('leaderboard:update')->everyFiveMinutes();
```

### API Routes

```php
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Challenges
    Route::get('challenges', [ChallengeController::class, 'index']);
    Route::get('challenges/my', [ChallengeController::class, 'myChallenges']);
    Route::get('challenges/completed', [ChallengeController::class, 'completed']);
    Route::get('challenges/{challenge}', [ChallengeController::class, 'show']);
    Route::post('challenges/{challenge}/claim', [ChallengeController::class, 'claim']);
    
    // Leaderboards
    Route::get('leaderboards', [LeaderboardController::class, 'index']);
    Route::get('leaderboards/my-rank', [LeaderboardController::class, 'myRank']);
    
    // Gamification Dashboard
    Route::get('gamification/summary', [GamificationController::class, 'summary']);
    Route::get('gamification/badges', [GamificationController::class, 'badges']);
    Route::get('gamification/points-history', [GamificationController::class, 'pointsHistory']);
    Route::get('gamification/achievements', [GamificationController::class, 'achievements']);
});
```
