# Gamification Data Seeder Documentation

## Overview

Seeder untuk populate data gamifikasi (XP, badges, levels) untuk students. Seeder ini dapat digunakan dalam dua skenario:

1. **Fresh Migration** - Saat menjalankan `php artisan migrate:fresh --seed`
2. **Post-Migration** - Untuk populate data pada database yang sudah ada

## Features

- ✅ Generate random XP untuk setiap student (0 - 50,000 XP)
- ✅ Calculate level berdasarkan XP yang digenerate
- ✅ Generate XP transaction history yang realistis
- ✅ Award random badges berdasarkan level
- ✅ Generate streak data (current & longest streak)
- ✅ Idempotent - tidak akan duplicate data jika dijalankan ulang
- ✅ Batch insert untuk performa optimal

## Usage

### 1. Fresh Migration (Recommended)

Untuk setup database dari awal dengan semua data gamification:

```bash
php artisan migrate:fresh --seed
```

Ini akan menjalankan semua seeder termasuk `GamificationDataSeeder`.

### 2. Seed Gamification Only

Untuk populate gamification data pada database yang sudah ada:

```bash
php artisan gamification:seed-data
```

### 3. Force Re-seed (Clear & Seed)

Untuk clear semua data gamification dan seed ulang:

```bash
php artisan gamification:seed-data --fresh
```

⚠️ **Warning**: Opsi `--fresh` akan menghapus semua data di:
- `user_gamification_stats`
- `user_badges`
- `points`

### 4. Manual Seeder Call

Jika ingin menjalankan seeder secara manual:

```bash
php artisan db:seed --class="Modules\\Gamification\\Database\\Seeders\\GamificationDataSeeder"
```

## Prerequisites

Sebelum menjalankan seeder, pastikan data berikut sudah ada:

1. **Users with Student role** - Minimal 1 student
2. **Level Configs** - Dijalankan oleh `LevelConfigSeeder`
3. **XP Sources** - Dijalankan oleh `XpSourceSeeder`
4. **Badges** - Dijalankan oleh `BadgeSeeder`

Jika menjalankan `GamificationDatabaseSeeder`, semua prerequisites akan otomatis dijalankan.

## Seeder Flow

```
GamificationDatabaseSeeder
├── LevelConfigSeeder         # Sync level configs
├── XpSourceSeeder            # Seed XP sources
├── MilestoneSeeder           # Seed milestones
├── BadgeSeeder               # Seed badges
├── LinkMilestoneBadgesSeeder # Link badges to levels
└── GamificationDataSeeder    # Populate student data ⭐
```

## What Gets Seeded

### For Each Student:

#### 1. User Gamification Stats
- `total_xp`: Random 0 - 50,000
- `global_level`: Calculated from XP
- `current_streak`: Random 0 - 30 days
- `longest_streak`: Random (>= current_streak, max 60)
- `last_activity_date`: Random within last 7 days

#### 2. XP Transaction History
- 5-20 random transactions per student
- Uses actual XP sources from database
- Includes level-up tracking
- Realistic timestamps (last 30 days)

#### 3. Badges
- 0-10 random badges per student
- More badges for higher level students
- Only active badges are awarded
- No duplicates

## Data Distribution

### XP Distribution
```
0 - 10,000 XP:   ~40% of students (Level 1-15)
10,000 - 30,000: ~40% of students (Level 15-35)
30,000 - 50,000: ~20% of students (Level 35-50)
```

### Badge Distribution
```
Level 1-10:  0-2 badges
Level 11-20: 2-5 badges
Level 21-30: 5-7 badges
Level 31+:   7-10 badges
```

## Examples

### Example 1: Fresh Setup
```bash
# Complete fresh setup
php artisan migrate:fresh --seed

# Verify data
php artisan tinker
>>> \Modules\Gamification\Models\UserGamificationStat::count()
>>> \Modules\Gamification\Models\UserBadge::count()
>>> \Modules\Gamification\Models\Point::count()
```

### Example 2: Add Gamification to Existing Database
```bash
# Seed only gamification data
php artisan gamification:seed-data

# Check leaderboard
curl -X GET http://localhost:8000/api/v1/leaderboards \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Example 3: Reset and Re-seed
```bash
# Clear and re-seed gamification data
php artisan gamification:seed-data --fresh

# Confirm with 'yes' when prompted
```

## Customization

### Adjust XP Range

Edit `GamificationDataSeeder.php`:

```php
// Change from 0-50,000 to 0-100,000
$totalXp = rand(0, 100000);
```

### Adjust Badge Count

Edit `GamificationDataSeeder.php`:

```php
// Change max badges from 10 to 20
$maxBadges = min(20, (int)($level / 2) + rand(0, 5));
```

### Adjust Transaction Count

Edit `GamificationDataSeeder.php`:

```php
// Change from 5-20 to 10-50 transactions
$transactionCount = rand(10, min(50, (int)($totalXp / 10)));
```

## Troubleshooting

### No students found
```
⚠️  No students found. Please seed users first.
```

**Solution**: Run user seeder first
```bash
php artisan db:seed --class="Modules\\Auth\\Database\\Seeders\\UserSeeder"
```

### No badges found
```
⚠️  No badges found. Please seed badges first.
```

**Solution**: Run badge seeder first
```bash
php artisan db:seed --class="Modules\\Gamification\\Database\\Seeders\\BadgeSeeder"
```

### No XP sources found
```
⚠️  No XP sources found. Please seed XP sources first.
```

**Solution**: Run XP source seeder first
```bash
php artisan db:seed --class="Modules\\Gamification\\Database\\Seeders\\XpSourceSeeder"
```

### Unique constraint violation
```
SQLSTATE[23505]: Unique violation: duplicate key value violates unique constraint "points_unique_transaction"
```

**Cause**: The `points` table has a unique constraint on `(user_id, source_type, source_id, reason)` to prevent duplicate transactions.

**Solution**: The seeder now uses timestamp-based unique `source_id` values to avoid collisions. If you still encounter this error:

1. Clear existing points data:
```bash
php artisan gamification:seed-data --fresh
```

2. Or manually clear points for specific user:
```bash
php artisan tinker
>>> \Modules\Gamification\Models\Point::where('user_id', USER_ID)->delete();
```

### Data already exists

By default, seeder akan skip students yang sudah memiliki gamification data. Untuk re-seed:

```bash
php artisan gamification:seed-data --fresh
```

## Performance

- Uses batch inserts for optimal performance
- Progress bar shows seeding progress
- Typical performance: ~100 students/second

### Large Database Tips

For databases with 1000+ students:

```bash
# Run in background
nohup php artisan gamification:seed-data > seeder.log 2>&1 &

# Monitor progress
tail -f seeder.log
```

## Testing

### Verify Seeded Data

```bash
php artisan tinker
```

```php
// Check stats
$stats = \Modules\Gamification\Models\UserGamificationStat::with('user')->get();
$stats->each(fn($s) => dump([
    'user' => $s->user->name,
    'xp' => $s->total_xp,
    'level' => $s->global_level,
    'badges' => $s->user->badges()->count()
]));

// Check leaderboard
$leaderboard = \Modules\Gamification\Models\UserGamificationStat::orderByDesc('total_xp')
    ->limit(10)
    ->with('user')
    ->get();

// Check XP history
$points = \Modules\Gamification\Models\Point::with('user')
    ->latest()
    ->limit(20)
    ->get();
```

### API Testing

```bash
# Get leaderboard
curl -X GET http://localhost:8000/api/v1/leaderboards \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get user profile with gamification data
curl -X GET http://localhost:8000/api/v1/profile \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Integration with DatabaseSeeder

Add to main `DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        // ... other seeders
        \Modules\Gamification\Database\Seeders\GamificationDatabaseSeeder::class,
    ]);
}
```

## Notes

- Seeder is idempotent - safe to run multiple times
- Uses realistic data distribution
- Respects badge rules and constraints
- Generates valid XP transaction history
- Compatible with existing gamification system

## Related Documentation

- [Gamification System Overview](./EXECUTIVE_SUMMARY.md)
- [Level Management Guide](./LEVEL_MANAGEMENT_GUIDE.md)
- [Badge Management Documentation](./BADGE_MANAGEMENT_DOCUMENTATION.md)
- [XP System Documentation](./XP_SYSTEM_COMPLETE_SUMMARY.md)
