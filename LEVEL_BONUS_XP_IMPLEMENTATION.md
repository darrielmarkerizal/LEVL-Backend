# Level Bonus XP Implementation

## Overview
Implementasi sistem bonus XP dinamis untuk setiap level up yang mengikuti pola progresif yang sama dengan XP required.

## Formula Bonus XP

```
bonus_xp = round(10 × level^1.3)
```

### Perbandingan dengan XP Required
- **XP Required Formula**: `100 × level^1.6`
- **Bonus XP Formula**: `10 × level^1.3`

Bonus XP menggunakan eksponen yang lebih kecil (1.3 vs 1.6) dan multiplier yang lebih kecil (10 vs 100), sehingga:
- Tetap progresif dan meningkat seiring level
- Tidak terlalu besar sehingga tidak mengganggu balance
- Memberikan reward yang meaningful untuk achievement

## Contoh Bonus XP per Level

| Level | XP Required | Bonus XP | Persentase |
|-------|-------------|----------|------------|
| 1     | 100         | 10       | 10%        |
| 5     | 1,313       | 81       | 6.2%       |
| 10    | 3,981       | 200      | 5.0%       |
| 20    | 12,068      | 491      | 4.1%       |
| 30    | 23,088      | 832      | 3.6%       |
| 50    | 52,282      | 1,617    | 3.1%       |
| 75    | 100,023     | 2,739    | 2.7%       |
| 100   | 158,489     | 3,981    | 2.5%       |

## Database Changes

### Migration
File: `2026_03_15_060715_add_milestone_badge_id_to_level_configs_table.php`

```php
Schema::table('level_configs', function (Blueprint $table) {
    $table->foreignId('milestone_badge_id')
        ->nullable()
        ->after('rewards')
        ->constrained('badges')
        ->nullOnDelete();
    
    $table->integer('bonus_xp')
        ->default(0)
        ->after('milestone_badge_id')
        ->comment('Bonus XP awarded when reaching this level');
});
```

### Model Update
File: `Modules/Common/app/Models/LevelConfig.php`

```php
protected $fillable = [
    'level',
    'name',
    'xp_required',
    'rewards',
    'milestone_badge_id',
    'bonus_xp',
];

public function milestoneBadge(): BelongsTo
{
    return $this->belongsTo(Badge::class, 'milestone_badge_id');
}
```

## Automatic Bonus XP Award

### PointManager Update
File: `Modules/Gamification/app/Services/Support/PointManager.php`

Ketika user naik level, sistem otomatis:
1. Mengambil `bonus_xp` dari `level_configs`
2. Membuat point transaction dengan reason `'bonus'`
3. Menambahkan bonus XP ke total user
4. Dispatch event `UserLeveledUp` dengan informasi bonus

```php
private function handleLevelUp(int $userId, int $oldLevel, int $newLevel, int $totalXp): void
{
    $levelConfig = $this->levelService->getLevelConfig($newLevel);
    $bonusXp = $levelConfig?->bonus_xp ?? 0;
    
    if ($bonusXp > 0) {
        // Create bonus point transaction
        $this->repository->createPoint([
            'user_id' => $userId,
            'points' => $bonusXp,
            'reason' => 'bonus',
            'source_type' => 'system',
            'description' => sprintf('Bonus XP untuk mencapai level %d', $newLevel),
            // ...
        ]);
        
        // Update total XP
        $stats->total_xp += $bonusXp;
    }
    
    // Dispatch event
    event(new UserLeveledUp(...));
}
```

## API Response Structure

### GET /api/v1/levels

```json
{
  "success": true,
  "message": "Daftar level berhasil diambil.",
  "data": [
    {
      "id": 1,
      "level": 1,
      "name": "Beginner",
      "xp_required": 100,
      "bonus_xp": 10,
      "milestone_badge": null,
      "rewards": [],
      "created_at": "2026-03-14T15:22:55.000000Z",
      "updated_at": "2026-03-15T06:15:00.000000Z"
    },
    {
      "id": 10,
      "level": 10,
      "name": "Novice",
      "xp_required": 3981,
      "bonus_xp": 200,
      "milestone_badge": {
        "id": 1,
        "name": "Langkah Pertama",
        "slug": null,
        "description": "Bagian dari permulaan perjalanan LMS Anda.",
        "icon_url": "https://...",
        "rarity": "common"
      },
      "rewards": [],
      "created_at": "2026-03-14T15:22:55.000000Z",
      "updated_at": "2026-03-15T06:15:00.000000Z"
    }
  ],
  "meta": {
    "pagination": {...}
  }
}
```

## Points History Entry

Ketika user naik level, akan ada entry baru di points history:

```json
{
  "id": 123,
  "points": 200,
  "source_type": "system",
  "source_type_label": "Sistem",
  "reason": "bonus",
  "reason_label": "Bonus",
  "description": "Bonus XP untuk mencapai level 10",
  "context": {
    "level_up": true,
    "old_level": 9,
    "new_level": 10
  },
  "created_at": "2026-03-15T10:30:00.000000Z"
}
```

## Benefits

1. **Progressive Rewards**: Bonus XP meningkat seiring kesulitan level
2. **Balanced**: Tidak terlalu besar sehingga tidak mengganggu game balance
3. **Motivating**: Memberikan reward tambahan untuk achievement
4. **Transparent**: User bisa melihat bonus XP di level list
5. **Trackable**: Bonus XP tercatat di points history

## Testing

### Manual Test
```bash
# Update all levels with bonus XP
php update_level_bonus_xp.php

# Check API response
curl -X GET "http://localhost:8000/api/v1/levels?per_page=20" \
  -H "Authorization: Bearer {token}"
```

### Expected Behavior
1. Semua level memiliki `bonus_xp` > 0
2. Bonus XP meningkat progresif seiring level
3. Milestone levels (10, 20, 30, dst) memiliki badge
4. Saat user naik level, otomatis dapat bonus XP
5. Bonus XP tercatat di points history

## Migration Steps

1. ✅ Run migration untuk add kolom `bonus_xp` dan `milestone_badge_id`
2. ✅ Update model `LevelConfig` dengan fillable dan relation
3. ✅ Update `LevelConfigResource` untuk include bonus_xp dan badge
4. ✅ Run script `update_level_bonus_xp.php` untuk populate data
5. ✅ Update `PointManager::handleLevelUp()` untuk award bonus XP
6. ✅ Update controller untuk use translations
7. ✅ Test API endpoint

## Future Enhancements

1. **Dynamic Multiplier**: Admin bisa adjust multiplier bonus XP
2. **Special Events**: Double bonus XP untuk event tertentu
3. **Streak Bonus**: Extra bonus untuk consecutive level ups
4. **Badge Rewards**: Otomatis award badge untuk milestone levels
5. **Notification**: Push notification saat dapat bonus XP

---

**Status**: ✅ Implemented and Tested
**Date**: 15 Maret 2026
**Version**: 1.0
