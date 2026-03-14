# Level System Migration Guide

**From**: `XP = 100 × 1.1^(level-1)`  
**To**: `XP(level) = 100 × level^1.6`

---

## Pre-Migration Checklist

- [ ] Backup database
- [ ] Test in staging environment
- [ ] Notify users about changes
- [ ] Schedule maintenance window
- [ ] Prepare rollback plan

---

## Migration Steps

### Step 1: Backup Current Data
```bash
# Backup level configs
mysqldump -u user -p database level_configs > level_configs_backup.sql

# Backup user stats
mysqldump -u user -p database user_gamification_stats > user_stats_backup.sql
```

### Step 2: Deploy New Code
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan cache:clear
```

### Step 3: Sync New Level Configurations
```bash
php artisan gamification:sync-levels --force
```

### Step 4: Recalculate User Levels
```php
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Services\LevelService;

$levelService = app(LevelService::class);

UserGamificationStat::chunk(100, function ($stats) use ($levelService) {
    foreach ($stats as $stat) {
        $newLevel = $levelService->calculateLevelFromXp($stat->total_xp);
        $stat->update(['global_level' => $newLevel]);
    }
});
```

### Step 5: Verify Migration
```bash
php artisan tinker
>>> $service = app(\Modules\Gamification\Services\LevelService::class);
>>> $service->calculateXpForLevel(10)
=> 3981 # Should match new formula
```

---

## Impact Analysis

| XP Amount | Old Level | New Level | Change |
|-----------|-----------|-----------|--------|
| 1,000 | 22 | 3 | -19 levels |
| 10,000 | 47 | 9 | -38 levels |
| 50,000 | 64 | 14 | -50 levels |
| 100,000 | 71 | 17 | -54 levels |

**Note**: Most users will see level decrease initially, but progression will be more balanced.

---

## Rollback Plan

If issues occur:

```bash
# Restore backups
mysql -u user -p database < level_configs_backup.sql
mysql -u user -p database < user_stats_backup.sql

# Revert code
git revert HEAD
composer install
php artisan cache:clear
```

---

## Post-Migration

- [ ] Monitor user feedback
- [ ] Check level distribution
- [ ] Adjust rewards if needed
- [ ] Update frontend displays

---

**Estimated Time**: 30-60 minutes  
**Downtime**: 5-10 minutes
