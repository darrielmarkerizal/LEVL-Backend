# Level Management - Quick Start Guide

**Formula**: `XP(level) = 100 × level^1.6`

---

## 🚀 Quick Setup (5 minutes)

### Step 1: Sync Level Configurations

```bash
# Sync all 100 levels
php artisan gamification:sync-levels --force
```

**Output**:
```
✓ Successfully synced 100 level configurations

Examples:
  Level 1: 100 XP (Total: 100 XP)
  Level 10: 3,981 XP (Total: 20,433 XP)
  Level 50: 78,446 XP (Total: 1,197,126 XP)
  Level 100: 264,575 XP (Total: 6,985,922 XP)
```

### Step 2: Verify Installation

```bash
php artisan tinker
```

```php
// Check level configs
>>> \Modules\Common\Models\LevelConfig::count()
=> 100

// Test level calculation
>>> $service = app(\Modules\Gamification\Services\LevelService::class);
>>> $service->calculateLevelFromXp(50000)
=> 14

// Test level progress
>>> $service->getLevelProgress(50000)
=> [
     "current_level" => 14,
     "total_xp" => 50000,
     "current_level_xp" => 1582,
     "xp_to_next_level" => 6903,
     "progress_percentage" => 18.64
   ]
```

### Step 3: Test API Endpoints

```bash
# Get user level (requires authentication)
curl -X GET "http://localhost/api/v1/user/level" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get level progression table
curl -X GET "http://localhost/api/v1/levels/progression?start=1&end=20"

# Calculate level from XP
curl -X POST "http://localhost/api/v1/levels/calculate" \
  -H "Content-Type: application/json" \
  -d '{"xp": 50000}'
```

---

## 📊 Level Progression Examples

| Level | XP Required | Total XP | Time Estimate* |
|-------|-------------|----------|----------------|
| 5 | 1,148 | 3,524 | 1 week |
| 10 | 3,981 | 20,433 | 1 month |
| 20 | 14,568 | 117,486 | 3 months |
| 30 | 31,006 | 327,151 | 6 months |
| 50 | 78,446 | 1,197,126 | 1 year |
| 100 | 264,575 | 6,985,922 | 2+ years |

*Assuming 100 XP per day average

---

## 🎯 Common Use Cases

### 1. Display User Level in UI

**Backend (Laravel)**:
```php
// In your controller
public function getUserProfile(Request $request)
{
    $user = auth()->user();
    $stats = $user->gamificationStats;
    
    $levelService = app(\Modules\Gamification\Services\LevelService::class);
    $levelInfo = $levelService->getLevelProgress($stats->total_xp);
    
    return response()->json([
        'user' => $user,
        'level' => $levelInfo
    ]);
}
```

**Frontend (React/TypeScript)**:
```typescript
interface LevelInfo {
  current_level: number;
  total_xp: number;
  current_level_xp: number;
  xp_to_next_level: number;
  xp_required_for_next_level: number;
  progress_percentage: number;
}

function LevelBadge({ levelInfo }: { levelInfo: LevelInfo }) {
  return (
    <div className="level-badge">
      <div className="level-number">
        Level {levelInfo.current_level}
      </div>
      <div className="progress-bar">
        <div 
          className="progress-fill" 
          style={{ width: `${levelInfo.progress_percentage}%` }}
        />
      </div>
      <div className="xp-text">
        {levelInfo.current_level_xp} / {levelInfo.xp_required_for_next_level} XP
      </div>
    </div>
  );
}
```

### 2. Award XP and Auto-Level Up

```php
use Modules\Gamification\Services\Support\PointManager;

// Award XP (level automatically calculated)
$pointManager = app(PointManager::class);

$pointManager->awardXp(
    userId: $user->id,
    points: 500,
    reason: 'lesson_completed',
    sourceType: 'lesson',
    sourceId: $lesson->id
);

// User level is automatically updated in user_gamification_stats table
```

### 3. Check User Level for Features

```php
// Unlock feature based on level
$user = auth()->user();
$stats = $user->gamificationStats;

if ($stats->global_level >= 10) {
    // Unlock advanced features
    $user->givePermissionTo('access_advanced_lessons');
}

if ($stats->global_level >= 25) {
    // Unlock expert features
    $user->givePermissionTo('create_discussions');
}
```

### 4. Display Level Leaderboard

```php
use Modules\Gamification\Models\UserGamificationStat;

$topUsers = UserGamificationStat::with('user')
    ->orderBy('global_level', 'desc')
    ->orderBy('total_xp', 'desc')
    ->limit(10)
    ->get()
    ->map(function ($stat) {
        return [
            'user' => $stat->user->name,
            'level' => $stat->global_level,
            'xp' => $stat->total_xp,
        ];
    });
```

---

## 🔧 Admin Operations

### View Level Statistics

```bash
# Via API (Superadmin only)
curl -X GET "http://localhost/api/v1/levels/statistics" \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

**Response**:
```json
{
  "success": true,
  "data": {
    "total_levels": 100,
    "max_level": 100,
    "total_xp_to_max": 6985922,
    "users_by_level": [
      {"global_level": 1, "count": 150},
      {"global_level": 2, "count": 120},
      {"global_level": 5, "count": 80}
    ]
  }
}
```

### Update Specific Level

```bash
# Update level 50 rewards
curl -X PUT "http://localhost/api/v1/levels/50" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Elite Master",
    "rewards": {
      "badge": "elite_50",
      "bonus_xp": 2000,
      "title": "Elite Master",
      "unlock_features": ["premium_content"]
    }
  }'
```

### Re-sync Specific Range

```bash
# Re-sync levels 1-50 only
php artisan gamification:sync-levels --start=1 --end=50 --force
```

---

## 📈 Monitoring & Analytics

### Check User Distribution

```sql
-- Users by level range
SELECT 
    CASE 
        WHEN global_level BETWEEN 1 AND 9 THEN '1-9 (Beginner)'
        WHEN global_level BETWEEN 10 AND 19 THEN '10-19 (Novice)'
        WHEN global_level BETWEEN 20 AND 29 THEN '20-29 (Competent)'
        WHEN global_level BETWEEN 30 AND 49 THEN '30-49 (Intermediate)'
        WHEN global_level >= 50 THEN '50+ (Advanced)'
    END as level_range,
    COUNT(*) as user_count,
    ROUND(AVG(total_xp)) as avg_xp
FROM user_gamification_stats
GROUP BY level_range
ORDER BY MIN(global_level);
```

### Track Level Progression

```sql
-- Users who leveled up today
SELECT 
    u.name,
    ugs.global_level,
    ugs.total_xp,
    ugs.stats_updated_at
FROM user_gamification_stats ugs
JOIN users u ON u.id = ugs.user_id
WHERE DATE(ugs.stats_updated_at) = CURDATE()
ORDER BY ugs.stats_updated_at DESC;
```

---

## 🎨 UI Components Examples

### Level Progress Card

```tsx
import { useQuery } from '@tanstack/react-query';

export function LevelProgressCard() {
  const { data } = useQuery({
    queryKey: ['user-level'],
    queryFn: () => fetch('/api/v1/user/level').then(r => r.json())
  });

  if (!data?.success) return null;

  const { current_level, progress_percentage, xp_to_next_level } = data.data;

  return (
    <div className="bg-white rounded-lg shadow p-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-semibold">Your Level</h3>
        <span className="text-3xl font-bold text-blue-600">
          {current_level}
        </span>
      </div>
      
      <div className="relative pt-1">
        <div className="flex mb-2 items-center justify-between">
          <div>
            <span className="text-xs font-semibold inline-block text-blue-600">
              Progress to Level {current_level + 1}
            </span>
          </div>
          <div className="text-right">
            <span className="text-xs font-semibold inline-block text-blue-600">
              {Math.round(progress_percentage)}%
            </span>
          </div>
        </div>
        <div className="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
          <div 
            style={{ width: `${progress_percentage}%` }}
            className="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500"
          />
        </div>
        <p className="text-sm text-gray-600">
          {xp_to_next_level.toLocaleString()} XP to next level
        </p>
      </div>
    </div>
  );
}
```

### Level Progression Table

```tsx
export function LevelProgressionTable() {
  const { data } = useQuery({
    queryKey: ['level-progression'],
    queryFn: () => 
      fetch('/api/v1/levels/progression?start=1&end=20')
        .then(r => r.json())
  });

  if (!data?.success) return null;

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200">
        <thead className="bg-gray-50">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Level
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              XP Required
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Total XP
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Name
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {data.data.map((level: any) => (
            <tr key={level.level}>
              <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {level.level}
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {level.xp_required.toLocaleString()}
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {level.total_xp.toLocaleString()}
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {level.name}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

## 🐛 Troubleshooting

### Problem: Levels not syncing

```bash
# Solution 1: Clear cache
php artisan cache:clear

# Solution 2: Force re-sync
php artisan gamification:sync-levels --force

# Solution 3: Check database
php artisan tinker
>>> \Modules\Common\Models\LevelConfig::count()
```

### Problem: User level not updating

```bash
# Check if PointManager is using LevelService
php artisan tinker
>>> $pm = app(\Modules\Gamification\Services\Support\PointManager::class);
>>> $pm->calculateLevelFromXp(50000)
=> 14 # Should return correct level
```

### Problem: API returns 401 Unauthorized

```bash
# Make sure you're authenticated
curl -X GET "http://localhost/api/v1/user/level" \
  -H "Authorization: Bearer YOUR_VALID_TOKEN"
```

---

## 📚 Additional Resources

- **Full Documentation**: `LEVEL_MANAGEMENT_GUIDE.md`
- **API Reference**: See routes in `routes/api.php`
- **Service Code**: `app/Services/LevelService.php`
- **Controller**: `app/Http/Controllers/LevelController.php`

---

## ✅ Checklist

- [ ] Run `php artisan gamification:sync-levels --force`
- [ ] Verify 100 levels created in database
- [ ] Test API endpoint `/api/v1/user/level`
- [ ] Test level calculation with sample XP
- [ ] Update frontend to display user level
- [ ] Configure level-based rewards
- [ ] Monitor user distribution across levels

---

**Status**: ✅ Ready to use  
**Last Updated**: March 14, 2026
