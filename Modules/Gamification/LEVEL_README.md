# Level Management System

Sistem manajemen level dengan formula `XP(level) = 100 × level^1.6` untuk progression yang smooth dan engaging.

---

## 🚀 Quick Start

```bash
# 1. Sync level configurations (1-100)
php artisan gamification:sync-levels --force

# 2. Verify
php artisan tinker
>>> \Modules\Common\Models\LevelConfig::count()
=> 100

# 3. Test API
curl http://localhost/api/v1/levels/progression?start=1&end=10
```

---

## 📊 Level Progression

| Level | XP Required | Total XP | Name |
|-------|-------------|----------|------|
| 1 | 100 | 100 | Beginner |
| 10 | 3,981 | 20,433 | Novice |
| 25 | 22,097 | 206,145 | Intermediate |
| 50 | 78,446 | 1,197,126 | Advanced |
| 100 | 264,575 | 6,985,922 | Legendary Master |

---

## 🔌 API Endpoints

### Public
- `GET /api/v1/levels` - List all levels
- `GET /api/v1/levels/progression` - Progression table
- `GET /api/v1/user/level` - User's current level (auth)
- `POST /api/v1/levels/calculate` - Calculate level from XP

### Admin (Superadmin only)
- `POST /api/v1/levels/sync` - Sync configurations
- `PUT /api/v1/levels/{id}` - Update level config
- `GET /api/v1/levels/statistics` - Level statistics

---

## 💻 Usage Examples

### PHP (Backend)
```php
use Modules\Gamification\Services\LevelService;

$levelService = app(LevelService::class);

// Calculate level from XP
$level = $levelService->calculateLevelFromXp(50000);
// => 14

// Get level progress
$progress = $levelService->getLevelProgress(50000);
/*
[
    'current_level' => 14,
    'total_xp' => 50000,
    'current_level_xp' => 1582,
    'xp_to_next_level' => 6903,
    'progress_percentage' => 18.64
]
*/
```

### TypeScript (Frontend)
```typescript
// Fetch user level
const response = await fetch('/api/v1/user/level', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { data } = await response.json();

console.log(`Level ${data.current_level}`);
console.log(`Progress: ${data.progress_percentage}%`);
```

---

## 📁 Files Structure

```
Modules/Gamification/
├── app/
│   ├── Services/
│   │   └── LevelService.php              # Core service
│   ├── Http/Controllers/
│   │   └── LevelController.php           # API controller
│   └── Console/Commands/
│       └── SyncLevelConfigs.php          # Sync command
├── database/seeders/
│   └── LevelConfigSeeder.php             # Seeder
├── tests/Unit/
│   └── LevelServiceTest.php              # Unit tests
├── LEVEL_MANAGEMENT_GUIDE.md             # Complete guide
├── LEVEL_QUICK_START.md                  # Quick start
├── LEVEL_IMPLEMENTATION_SUMMARY.md       # Summary
└── LEVEL_README.md                       # This file
```

---

## 🎯 Key Features

✅ Formula: `XP(level) = 100 × level^1.6`  
✅ Binary search algorithm (O(log n))  
✅ 7 API endpoints  
✅ Console command with progress bar  
✅ Auto-level calculation on XP award  
✅ Milestone rewards (every 10 levels)  
✅ Level tiers (Beginner → Legendary Master)  
✅ Complete documentation  
✅ Unit tests included  

---

## 📚 Documentation

- **Complete Guide**: [LEVEL_MANAGEMENT_GUIDE.md](./LEVEL_MANAGEMENT_GUIDE.md)
- **Quick Start**: [LEVEL_QUICK_START.md](./LEVEL_QUICK_START.md)
- **Implementation**: [LEVEL_IMPLEMENTATION_SUMMARY.md](./LEVEL_IMPLEMENTATION_SUMMARY.md)

---

## 🔧 Commands

```bash
# Sync all levels
php artisan gamification:sync-levels --force

# Sync specific range
php artisan gamification:sync-levels --start=1 --end=50

# Run seeder
php artisan db:seed --class=Modules\\Gamification\\Database\\Seeders\\LevelConfigSeeder

# Run tests
php artisan test --filter=LevelServiceTest
```

---

## 🐛 Troubleshooting

**Levels not syncing?**
```bash
php artisan cache:clear
php artisan gamification:sync-levels --force
```

**User level not updating?**
```php
// Recalculate manually
$service = app(\Modules\Gamification\Services\LevelService::class);
$newLevel = $service->calculateLevelFromXp($user->gamificationStats->total_xp);
$user->gamificationStats->update(['global_level' => $newLevel]);
```

---

## ✅ Status

**Status**: ✅ Production Ready  
**Version**: 1.0  
**Date**: March 14, 2026

---

**Need help?** Check the complete documentation in [LEVEL_MANAGEMENT_GUIDE.md](./LEVEL_MANAGEMENT_GUIDE.md)
