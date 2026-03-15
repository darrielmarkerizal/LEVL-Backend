# Audit: Spatie Query Builder & PgSearchable Implementation
## Gamification Module API Endpoints

**Tanggal**: 15 Maret 2026  
**Module**: Gamification  
**Auditor**: Backend Team

---

## 📋 EXECUTIVE SUMMARY

Audit ini memeriksa semua endpoint API Gamifikasi Student untuk memastikan:
1. ✅ Menggunakan **Spatie Query Builder** untuk filtering, sorting, dan pagination
2. ✅ Menggunakan **PgSearchable trait** untuk search functionality
3. ✅ Konsistensi implementasi di seluruh endpoint

---

## 🎯 HASIL AUDIT

### ✅ SUDAH SESUAI STANDAR

#### 1. **GET /user/points-history** - Riwayat Transaksi XP
**Status**: ✅ PERFECT IMPLEMENTATION

**Location**: `PointManager::getPointsHistory()`

**Implementation**:
```php
return QueryBuilder::for(Point::class)
    ->where('user_id', $userId)
    ->allowedFilters([
        AllowedFilter::exact('source_type'),
        AllowedFilter::exact('reason'),
        AllowedFilter::callback('period', function ($query, $value) {
            // Period filtering logic
        }),
        AllowedFilter::callback('date_from', function ($query, $value) {
            $query->whereDate('points.created_at', '>=', $value);
        }),
        AllowedFilter::callback('date_to', function ($query, $value) {
            $query->whereDate('points.created_at', '<=', $value);
        }),
        AllowedFilter::callback('points_min', function ($query, $value) {
            $query->where('points.points', '>=', (int) $value);
        }),
        AllowedFilter::callback('points_max', function ($query, $value) {
            $query->where('points.points', '<=', (int) $value);
        }),
    ])
    ->defaultSort('-created_at')
    ->allowedSorts(['created_at', 'points', 'source_type', 'reason'])
    ->paginate($perPage);
```

**Features**:
- ✅ Spatie Query Builder
- ✅ Comprehensive filtering (source_type, reason, period, date range, points range)
- ✅ Multiple sort options
- ✅ Pagination with max limit (100)
- ✅ Caching implemented

---

#### 2. **GET /leaderboards** - Global Leaderboard
**Status**: ✅ GOOD IMPLEMENTATION

**Location**: `LeaderboardService::getGlobalLeaderboard()`

**Implementation**:
```php
// For all_time period
$query = QueryBuilder::for(UserGamificationStat::class)
    ->allowedFilters([
        AllowedFilter::callback('period', function ($query, $value) {
            // Period filtering
        }),
    ])
    ->with(['user:id,name', 'user.media']);

if ($search) {
    $query->whereHas('user', function ($q) use ($search) {
        $q->search($search); // Uses PgSearchable
    });
}
$query->orderByDesc('total_xp');

// For other periods (today, this_week, etc)
$query = QueryBuilder::for(\Modules\Gamification\Models\Point::class)
    ->select('user_id', DB::raw('SUM(points) as total_xp'))
    ->groupBy('user_id')
    ->allowedFilters([
        AllowedFilter::callback('period', function ($query, $value) {}),
    ])
    ->with(['user:id,name', 'user.media', 'user.gamificationStats']);

if ($search) {
    $query->whereHas('user', function ($q) use ($search) {
        $q->search($search); // Uses PgSearchable
    });
}
```

**Features**:
- ✅ Spatie Query Builder
- ✅ Period filtering (today, this_week, this_month, this_year, all_time)
- ✅ Search using PgSearchable on User model
- ✅ Pagination with max limit (100)
- ✅ Caching implemented

**User Model Search Configuration**:
```php
protected array $searchable_columns = [
    'name',
    'username',
    'email',
];
```

---

#### 3. **GET /badges** (Admin) - Daftar Semua Badge
**Status**: ✅ EXCELLENT IMPLEMENTATION

**Location**: `BadgeService::paginate()`

**Implementation**:
```php
$query = Badge::with('rules')->withCount('users');

if ($search && trim($search) !== '') {
    $query->search($search); // Uses PgSearchable
}

return QueryBuilder::for($query)
    ->allowedFilters([
        AllowedFilter::exact('id'),
        AllowedFilter::partial('code'),
        AllowedFilter::partial('name'),
        AllowedFilter::exact('type'),
        AllowedFilter::partial('category'),
        AllowedFilter::exact('rarity'),
        AllowedFilter::exact('active'),
        AllowedFilter::callback('search', fn ($q, $v) => $q->search($v)),
    ])
    ->allowedSorts(['id', 'code', 'name', 'type', 'rarity', 'xp_reward', 'threshold', 'created_at', 'updated_at'])
    ->allowedIncludes(['rules'])
    ->defaultSort('-created_at')
    ->paginate($perPage);
```

**Badge Model Search Configuration**:
```php
protected array $searchable_columns = [
    'code',
    'name',
    'description',
];
```

**Features**:
- ✅ Spatie Query Builder
- ✅ PgSearchable trait
- ✅ Comprehensive filtering (id, code, name, type, category, rarity, active)
- ✅ Multiple sort options
- ✅ Includes support (rules)
- ✅ Pagination with max limit (100)
- ✅ Caching implemented

---

### ⚠️ PERLU PERBAIKAN

#### 4. **GET /user/level** - Informasi Level Saya
**Status**: ⚠️ NO FILTERING/SORTING NEEDED

**Location**: `LevelController::userLevel()`

**Current Implementation**:
```php
public function userLevel(Request $request): JsonResponse
{
    $user = auth('api')->user();
    $stats = $user->gamificationStats;
    $totalXp = $stats?->total_xp ?? 0;
    $levelInfo = $this->levelService->getLevelProgress($totalXp);

    return $this->success($levelInfo, 'messages.level_info_retrieved');
}
```

**Analysis**: 
- ✅ Endpoint ini hanya mengembalikan data single user
- ✅ Tidak memerlukan filtering, sorting, atau pagination
- ✅ Implementation sudah benar

---

#### 5. **GET /levels** - Daftar Semua Level
**Status**: ⚠️ NEEDS IMPROVEMENT

**Location**: `LevelController::index()`

**Current Implementation**:
```php
public function index(Request $request): JsonResponse
{
    $perPage = min((int) $request->get('per_page', 20), 100);
    
    $levels = LevelConfig::with('milestoneBadge')
        ->orderBy('level')
        ->paginate($perPage);

    $levels->getCollection()->transform(fn($level) => new LevelConfigResource($level));

    return $this->paginateResponse($levels, 'messages.levels_retrieved');
}
```

**Recommendations**:
```php
public function index(Request $request): JsonResponse
{
    $perPage = min((int) $request->get('per_page', 20), 100);
    
    $levels = QueryBuilder::for(LevelConfig::class)
        ->with('milestoneBadge')
        ->allowedFilters([
            AllowedFilter::exact('level'),
            AllowedFilter::callback('level_min', function ($query, $value) {
                $query->where('level', '>=', (int) $value);
            }),
            AllowedFilter::callback('level_max', function ($query, $value) {
                $query->where('level', '<=', (int) $value);
            }),
            AllowedFilter::callback('xp_min', function ($query, $value) {
                $query->where('xp_required', '>=', (int) $value);
            }),
            AllowedFilter::callback('xp_max', function ($query, $value) {
                $query->where('xp_required', '<=', (int) $value);
            }),
        ])
        ->allowedSorts(['level', 'xp_required', 'bonus_xp'])
        ->defaultSort('level')
        ->paginate($perPage);

    $levels->getCollection()->transform(fn($level) => new LevelConfigResource($level));

    return $this->paginateResponse($levels, 'messages.levels_retrieved');
}
```

**Benefits**:
- ✅ Filter by level range
- ✅ Filter by XP range
- ✅ Sort by level, xp_required, bonus_xp
- ✅ Consistent with other endpoints

---

#### 6. **GET /user/badges** - Lencana Saya
**Status**: ⚠️ NEEDS IMPLEMENTATION

**Location**: `GamificationController::badges()`

**Current Implementation**:
```php
public function badges(Request $request): JsonResponse
{
    $userId = $request->user()->id;
    $badges = $this->gamificationService->getUserBadges($userId);

    return $this->success(
        UserBadgeResource::collection($badges), 
        __('gamification.badges_retrieved')
    );
}
```

**Issue**: Mengembalikan Collection, bukan paginated result

**Recommendations**:
```php
// In GamificationService
public function getUserBadges(int $userId, int $perPage = 15, ?Request $request = null): LengthAwarePaginator
{
    $perPage = max(1, min($perPage, 100));
    
    return QueryBuilder::for(UserBadge::class)
        ->where('user_id', $userId)
        ->with(['badge', 'badge.media'])
        ->allowedFilters([
            AllowedFilter::callback('category', function ($query, $value) {
                $query->whereHas('badge', function ($q) use ($value) {
                    $q->where('category', $value);
                });
            }),
            AllowedFilter::callback('rarity', function ($query, $value) {
                $query->whereHas('badge', function ($q) use ($value) {
                    $q->where('rarity', $value);
                });
            }),
            AllowedFilter::callback('type', function ($query, $value) {
                $query->whereHas('badge', function ($q) use ($value) {
                    $q->where('type', $value);
                });
            }),
        ])
        ->allowedSorts(['earned_at', 'progress'])
        ->defaultSort('-earned_at')
        ->paginate($perPage);
}

// In Controller
public function badges(Request $request): JsonResponse
{
    $userId = $request->user()->id;
    $perPage = (int) ($request->input('per_page') ?? 15);
    
    $badges = $this->gamificationService->getUserBadges($userId, $perPage, $request);
    $badges->appends($request->query());
    
    $badges->getCollection()->transform(fn ($item) => new UserBadgeResource($item));

    return $this->paginateResponse($badges, __('gamification.badges_retrieved'));
}
```

---

#### 7. **GET /badges** (Student) - Semua Lencana Available
**Status**: ⚠️ NEEDS STUDENT ENDPOINT

**Current Situation**: 
- Badge endpoint saat ini hanya untuk Admin
- Perlu endpoint terpisah untuk Student yang menampilkan badge dengan status earned/not earned

**Recommendations**:

**Create New Controller Method**:
```php
// In BadgesController or create new StudentBadgesController
public function available(Request $request): JsonResponse
{
    $userId = auth('api')->user()->id;
    $perPage = min((int) $request->get('per_page', 15), 100);
    
    // Get user's earned badges
    $earnedBadgeIds = UserBadge::where('user_id', $userId)
        ->pluck('badge_id')
        ->toArray();
    
    $query = Badge::with(['rules', 'media'])
        ->where('active', true);
    
    // Add earned status and progress for each badge
    $query->selectRaw('badges.*, 
        CASE WHEN badges.id IN (' . implode(',', $earnedBadgeIds ?: [0]) . ') 
        THEN true ELSE false END as is_earned');
    
    $search = $request->query('search');
    if ($search && trim($search) !== '') {
        $query->search($search);
    }
    
    $badges = QueryBuilder::for($query)
        ->allowedFilters([
            AllowedFilter::exact('category'),
            AllowedFilter::exact('rarity'),
            AllowedFilter::exact('type'),
            AllowedFilter::callback('earned', function ($query, $value) use ($earnedBadgeIds) {
                if ($value === 'true' || $value === true) {
                    $query->whereIn('badges.id', $earnedBadgeIds);
                } elseif ($value === 'false' || $value === false) {
                    $query->whereNotIn('badges.id', $earnedBadgeIds);
                }
            }),
            AllowedFilter::callback('search', fn ($q, $v) => $q->search($v)),
        ])
        ->allowedSorts(['name', 'rarity', 'xp_reward', 'created_at'])
        ->defaultSort('name')
        ->paginate($perPage);
    
    // Transform to include progress
    $badges->getCollection()->transform(function ($badge) use ($userId) {
        $userBadge = UserBadge::where('user_id', $userId)
            ->where('badge_id', $badge->id)
            ->first();
        
        $badge->earned_at = $userBadge?->earned_at;
        $badge->progress = $userBadge ? [
            'current' => $userBadge->progress ?? $badge->threshold,
            'target' => $badge->threshold,
            'percentage' => $badge->threshold > 0 
                ? round(($userBadge->progress ?? $badge->threshold) / $badge->threshold * 100) 
                : 100
        ] : [
            'current' => 0,
            'target' => $badge->threshold,
            'percentage' => 0
        ];
        
        return new BadgeResource($badge);
    });
    
    return $this->paginateResponse($badges, __('gamification.badges_retrieved'));
}
```

**Add Route**:
```php
// In routes/api.php
Route::middleware(['auth:api'])->group(function () {
    Route::get('/badges/available', [BadgesController::class, 'available']);
});
```

---

#### 8. **GET /user/rank** - Ranking Saya
**Status**: ✅ NO FILTERING/SORTING NEEDED

**Location**: `LeaderboardController::myRank()`

**Current Implementation**:
```php
public function myRank(Request $request): JsonResponse
{
    $period = $request->input('filter.period', 'all_time');
    $rankData = $this->leaderboardService->getUserRank($request->user()->id, $period);

    return $this->success($rankData, __('gamification.rank_retrieved'));
}
```

**Analysis**: 
- ✅ Endpoint ini hanya mengembalikan data single user
- ✅ Period filtering sudah ada
- ✅ Tidak memerlukan pagination atau sorting
- ✅ Implementation sudah benar

---

#### 9. **GET /user/gamification-summary** - Dashboard Gamifikasi
**Status**: ✅ NO FILTERING/SORTING NEEDED

**Location**: `GamificationController::summary()`

**Analysis**: 
- ✅ Endpoint ini mengembalikan summary data
- ✅ Tidak memerlukan filtering, sorting, atau pagination
- ✅ Implementation sudah benar

---

#### 10. **GET /user/daily-xp-stats** - Progress Harian
**Status**: ✅ SIMPLE FILTERING SUFFICIENT

**Location**: `LevelController::dailyXpStats()`

**Current Implementation**:
```php
public function dailyXpStats(Request $request): JsonResponse
{
    $user = auth('api')->user();
    $stats = $this->pointManager->getDailyXpStats($user->id);

    return $this->success($stats, 'messages.daily_xp_stats_retrieved');
}
```

**Analysis**: 
- ✅ Endpoint ini mengembalikan aggregated data
- ✅ Query parameter `days` sudah cukup untuk filtering
- ✅ Tidak memerlukan Spatie Query Builder (data sudah di-aggregate)
- ✅ Implementation sudah benar

---

## 📊 SUMMARY TABLE

| Endpoint | Spatie QB | PgSearchable | Filtering | Sorting | Pagination | Status |
|----------|-----------|--------------|-----------|---------|------------|--------|
| GET /user/points-history | ✅ | N/A | ✅ | ✅ | ✅ | ✅ Perfect |
| GET /leaderboards | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Good |
| GET /badges (Admin) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Excellent |
| GET /user/level | N/A | N/A | N/A | N/A | N/A | ✅ Correct |
| GET /levels | ⚠️ | N/A | ⚠️ | ⚠️ | ✅ | ⚠️ Needs Improvement |
| GET /user/badges | ⚠️ | N/A | ⚠️ | ⚠️ | ⚠️ | ⚠️ Needs Implementation |
| GET /badges (Student) | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ Missing Endpoint |
| GET /user/rank | N/A | N/A | ✅ | N/A | N/A | ✅ Correct |
| GET /user/gamification-summary | N/A | N/A | N/A | N/A | N/A | ✅ Correct |
| GET /user/daily-xp-stats | N/A | N/A | ✅ | N/A | N/A | ✅ Correct |

---

## 🔧 ACTION ITEMS

### Priority 1: Critical
1. ❌ **Create Student Badge Endpoint** (`GET /badges/available`)
   - Implement Spatie Query Builder
   - Add filtering by category, rarity, type, earned status
   - Add search using PgSearchable
   - Include progress tracking

### Priority 2: High
2. ⚠️ **Improve Levels Endpoint** (`GET /levels`)
   - Add Spatie Query Builder
   - Add filtering by level range and XP range
   - Add sorting options

3. ⚠️ **Improve User Badges Endpoint** (`GET /user/badges`)
   - Convert to paginated result
   - Add Spatie Query Builder
   - Add filtering by category, rarity, type
   - Add sorting options

### Priority 3: Enhancement
4. 📝 **Add Search to Leaderboard**
   - Already implemented ✅
   - Document in API documentation

---

## 📝 IMPLEMENTATION CHECKLIST

### For Each Endpoint That Needs Improvement:

- [ ] Add Spatie Query Builder import
- [ ] Define allowedFilters
- [ ] Define allowedSorts
- [ ] Set defaultSort
- [ ] Add pagination with max limit (100)
- [ ] Add caching (optional but recommended)
- [ ] Update API documentation
- [ ] Add Postman examples
- [ ] Write tests

---

## 🎯 BEST PRACTICES

### 1. Spatie Query Builder Pattern
```php
return QueryBuilder::for(Model::class)
    ->allowedFilters([
        AllowedFilter::exact('field'),
        AllowedFilter::partial('field'),
        AllowedFilter::callback('custom', function ($query, $value) {
            // Custom logic
        }),
    ])
    ->allowedSorts(['field1', 'field2'])
    ->defaultSort('-created_at')
    ->paginate($perPage);
```

### 2. PgSearchable Pattern
```php
// In Model
use PgSearchable;

protected array $searchable_columns = [
    'field1',
    'field2',
    'field3',
];

// In Query
if ($search) {
    $query->search($search);
}
```

### 3. Pagination Pattern
```php
$perPage = max(1, min($perPage, 100)); // Limit max to 100
$result = $query->paginate($perPage);
$result->appends($request->query()); // Preserve query params
```

### 4. Caching Pattern
```php
return cache()->tags(['module', 'resource'])->remember(
    $cacheKey,
    300, // 5 minutes
    function () use ($query) {
        return $query->paginate($perPage);
    }
);
```

---

## 📚 REFERENCES

- [Spatie Query Builder Documentation](https://spatie.be/docs/laravel-query-builder)
- [PgSearchable Trait Implementation](Levl-BE/Modules/Common/app/Traits/PgSearchable.php)
- [API Documentation](Levl-BE/Modules/Gamification/API_GAMIFIKASI_STUDENT_LENGKAP.md)

---

**Audit Complete**  
**Next Steps**: Implement Priority 1 and 2 action items

**Maintainer**: Backend Team  
**Contact**: backend@levl.id
