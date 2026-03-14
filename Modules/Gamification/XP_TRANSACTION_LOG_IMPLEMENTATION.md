# XP Transaction Log & Global Daily Cap Implementation

## Overview

Implementasi sistem XP Transaction Log yang lengkap untuk audit, analytics, dan rollback, plus Global Daily XP Cap untuk mencegah user naik level terlalu cepat.

---

## 1. XP Transaction Log

### Enhanced Points Table

Tabel `points` telah di-enhance dengan field tambahan untuk transaction logging:

**New Fields:**
- `xp_source_code` - Code dari xp_sources table
- `old_level` - Level user sebelum earn XP
- `new_level` - Level user setelah earn XP
- `triggered_level_up` - Boolean, true jika transaction ini trigger level up
- `metadata` - JSON untuk additional data
- `ip_address` - IP address user saat earn XP
- `user_agent` - User agent browser/app

### Transaction Log Structure

```sql
SELECT 
    id,
    user_id,
    points as xp,
    source_type,
    source_id,
    reason,
    xp_source_code,
    old_level,
    new_level,
    triggered_level_up,
    ip_address,
    created_at
FROM points
WHERE user_id = 123
ORDER BY created_at DESC;
```

### Example Transaction Log

| ID | User | XP | Source | Reason | Old Level | New Level | Level Up | Created At |
|----|------|----|----|--------|-----------|-----------|----------|------------|
| 1001 | 123 | 50 | lesson | lesson_completed | 14 | 14 | false | 2026-03-14 10:00:00 |
| 1002 | 123 | 100 | assignment | assignment_submitted | 14 | 15 | true | 2026-03-14 10:30:00 |
| 1003 | 123 | 200 | system | level_up_bonus | 15 | 15 | false | 2026-03-14 10:30:01 |

---

## 2. Global Daily XP Cap

### xp_daily_caps Table

Tabel baru untuk tracking daily XP per user:

**Fields:**
- `user_id` - Foreign key ke users
- `date` - Tanggal (unique per user)
- `total_xp_earned` - Total XP earned hari ini
- `global_daily_cap` - Cap limit (default 10,000)
- `cap_reached` - Boolean, true jika sudah reach cap
- `cap_reached_at` - Timestamp saat reach cap
- `xp_by_source` - JSON breakdown XP per source

### Daily Cap Logic

```
1. User earns XP
   ↓
2. Check current daily total
   ↓
3. If (current + new) > cap:
   - Reject XP award
   - Log attempt
   ↓
4. If within cap:
   - Award XP
   - Update daily total
   - Track by source
```

### Configuration

Set global daily cap di `.env`:

```env
GAMIFICATION_GLOBAL_DAILY_XP_CAP=10000
```

Atau di config file:

```php
// config/gamification.php
'global_daily_xp_cap' => 10000,
```

---

## 3. API Endpoints

### Get Daily XP Stats

**Endpoint:**
```
GET /api/v1/user/daily-xp-stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_xp_earned": 3500,
    "global_daily_cap": 10000,
    "remaining_xp": 6500,
    "cap_reached": false,
    "cap_reached_at": null,
    "xp_by_source": {
      "lesson_completed": 1500,
      "assignment_submitted": 1000,
      "quiz_passed": 800,
      "forum_post_created": 200
    }
  }
}
```

---

## 4. Use Cases

### Audit Trail

```sql
-- Get all XP transactions for a user
SELECT 
    created_at,
    points as xp,
    reason,
    source_type,
    source_id,
    old_level,
    new_level,
    triggered_level_up
FROM points
WHERE user_id = 123
ORDER BY created_at DESC;
```

### Analytics

```sql
-- XP earned by source (last 30 days)
SELECT 
    xp_source_code,
    COUNT(*) as transactions,
    SUM(points) as total_xp,
    AVG(points) as avg_xp
FROM points
WHERE created_at >= NOW() - INTERVAL '30 days'
GROUP BY xp_source_code
ORDER BY total_xp DESC;

-- Level up frequency
SELECT 
    DATE(created_at) as date,
    COUNT(*) as level_ups
FROM points
WHERE triggered_level_up = true
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Daily XP distribution
SELECT 
    date,
    AVG(total_xp_earned) as avg_xp,
    MAX(total_xp_earned) as max_xp,
    COUNT(CASE WHEN cap_reached THEN 1 END) as users_reached_cap
FROM xp_daily_caps
WHERE date >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY date
ORDER BY date DESC;
```

### Rollback (if needed)

```sql
-- Rollback specific transaction
BEGIN;

-- Get transaction details
SELECT * FROM points WHERE id = 1002;

-- Reverse XP
UPDATE user_gamification_stats
SET total_xp = total_xp - 100,
    global_level = 14  -- Restore old level
WHERE user_id = 123;

-- Mark transaction as reversed
UPDATE points
SET metadata = jsonb_set(
    COALESCE(metadata, '{}'::jsonb),
    '{reversed}',
    'true'::jsonb
)
WHERE id = 1002;

COMMIT;
```

---

## 5. Monitoring & Alerts

### Daily Cap Monitoring

```sql
-- Users who reached daily cap today
SELECT 
    u.id,
    u.name,
    xdc.total_xp_earned,
    xdc.cap_reached_at
FROM xp_daily_caps xdc
JOIN users u ON u.id = xdc.user_id
WHERE xdc.date = CURRENT_DATE
  AND xdc.cap_reached = true
ORDER BY xdc.cap_reached_at;
```

### Suspicious Activity

```sql
-- Users with unusual XP patterns
SELECT 
    user_id,
    COUNT(*) as transactions,
    SUM(points) as total_xp,
    COUNT(DISTINCT ip_address) as unique_ips
FROM points
WHERE created_at >= CURRENT_DATE
GROUP BY user_id
HAVING COUNT(*) > 100  -- More than 100 transactions per day
   OR COUNT(DISTINCT ip_address) > 5  -- Multiple IPs
ORDER BY transactions DESC;
```

---

## 6. Frontend Integration

### Display Daily XP Progress

```typescript
interface DailyXpStats {
  total_xp_earned: number;
  global_daily_cap: number;
  remaining_xp: number;
  cap_reached: boolean;
  xp_by_source: Record<string, number>;
}

// Fetch daily stats
const { data: dailyStats } = useQuery<DailyXpStats>({
  queryKey: ['daily-xp-stats'],
  queryFn: () => fetch('/api/v1/user/daily-xp-stats').then(r => r.json()),
  refetchInterval: 60000, // Refetch every minute
});

// Display progress bar
<ProgressBar 
  value={dailyStats.total_xp_earned}
  max={dailyStats.global_daily_cap}
  label={`${dailyStats.total_xp_earned} / ${dailyStats.global_daily_cap} XP`}
/>

// Show warning when approaching cap
{dailyStats.remaining_xp < 1000 && (
  <Alert variant="warning">
    You're approaching your daily XP cap! 
    Only {dailyStats.remaining_xp} XP remaining today.
  </Alert>
)}

// Show cap reached message
{dailyStats.cap_reached && (
  <Alert variant="info">
    You've reached your daily XP cap of {dailyStats.global_daily_cap} XP.
    Come back tomorrow to earn more!
  </Alert>
)}
```

### XP Breakdown Chart

```typescript
// Display XP by source
<PieChart
  data={Object.entries(dailyStats.xp_by_source).map(([source, xp]) => ({
    name: source,
    value: xp
  }))}
/>
```

---

## 7. Configuration Examples

### Adjust Global Daily Cap

```sql
-- Increase cap for specific user (VIP, etc)
UPDATE xp_daily_caps
SET global_daily_cap = 20000
WHERE user_id = 123
  AND date = CURRENT_DATE;

-- Set different cap for all users tomorrow
INSERT INTO xp_daily_caps (user_id, date, global_daily_cap, total_xp_earned)
SELECT id, CURRENT_DATE + INTERVAL '1 day', 15000, 0
FROM users
ON CONFLICT (user_id, date) DO UPDATE
SET global_daily_cap = 15000;
```

### Disable Daily Cap (for testing)

```env
GAMIFICATION_GLOBAL_DAILY_XP_CAP=999999
```

---

## 8. Benefits

### Audit & Compliance
- Complete transaction history
- IP address tracking
- User agent logging
- Rollback capability

### Analytics
- XP source distribution
- Level up patterns
- User engagement metrics
- Abuse detection

### Anti-Abuse
- Global daily cap prevents grinding
- Per-source caps prevent exploitation
- Cooldown prevents spam
- Transaction logging deters cheating

### User Experience
- Transparent XP tracking
- Daily progress visibility
- Fair progression system
- Predictable rewards

---

## 9. Testing

### Test Global Daily Cap

```php
// Award XP until cap reached
$pointManager = app(PointManager::class);

for ($i = 0; $i < 250; $i++) {
    $result = $pointManager->awardXp(
        userId: 1,
        points: 0,
        reason: 'lesson_completed',
        sourceType: 'lesson',
        sourceId: $i
    );
    
    if (!$result) {
        echo "Cap reached after {$i} lessons\n";
        break;
    }
}

// Check daily stats
$stats = $pointManager->getDailyXpStats(1);
echo "Total XP: {$stats['total_xp_earned']}\n";
echo "Cap: {$stats['global_daily_cap']}\n";
echo "Remaining: {$stats['remaining_xp']}\n";
```

### Test Transaction Logging

```php
// Award XP and check transaction log
$point = $pointManager->awardXp(
    userId: 1,
    points: 0,
    reason: 'lesson_completed',
    sourceType: 'lesson',
    sourceId: 123
);

// Verify transaction fields
assert($point->xp_source_code === 'lesson_completed');
assert($point->old_level !== null);
assert($point->new_level !== null);
assert($point->ip_address !== null);
```

---

**Implementation Date**: 14 Maret 2026  
**Status**: ✅ Complete  
**Version**: 1.0
