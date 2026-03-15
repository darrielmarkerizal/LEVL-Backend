# Gamification Seeder - Quick Reference

## 🚀 Quick Start

### Fresh Database Setup
```bash
php artisan migrate:fresh --seed
```

### Add Gamification to Existing Database
```bash
php artisan gamification:seed-data
```

### Reset & Re-seed Gamification
```bash
php artisan gamification:seed-data --fresh
```

## 📊 What Gets Seeded

| Data | Range | Description |
|------|-------|-------------|
| **XP** | 0 - 50,000 | Random XP per student |
| **Level** | 1 - 50+ | Calculated from XP |
| **Badges** | 0 - 10 | Based on level |
| **Transactions** | 5 - 20 | XP history entries |
| **Streak** | 0 - 30 days | Current streak |

## 🎯 Common Commands

```bash
# Seed only gamification module
php artisan db:seed --class="Modules\\Gamification\\Database\\Seeders\\GamificationDatabaseSeeder"

# Seed only student data (skip configs)
php artisan db:seed --class="Modules\\Gamification\\Database\\Seeders\\GamificationDataSeeder"

# Check seeded data
php artisan tinker
>>> \Modules\Gamification\Models\UserGamificationStat::count()
>>> \Modules\Gamification\Models\UserBadge::count()
```

## ✅ Prerequisites

Before seeding, ensure you have:
- ✅ Students in database (User with Student role)
- ✅ Level configs (auto-seeded)
- ✅ XP sources (auto-seeded)
- ✅ Badges (auto-seeded)

## 🔧 Customization

Edit `GamificationDataSeeder.php`:

```php
// Line 90: Change XP range
$totalXp = rand(0, 100000); // Default: 50000

// Line 226: Change badge count
$maxBadges = min(20, ...); // Default: 10

// Line 177: Change transaction count
$transactionCount = rand(10, 50); // Default: 5-20
```

## 🐛 Troubleshooting

| Error | Solution |
|-------|----------|
| No students found | Run `UserSeeder` first |
| No badges found | Run `BadgeSeeder` first |
| Data already exists | Use `--fresh` flag |

## 📈 Verify Results

```bash
# API test
curl http://localhost:8000/api/v1/leaderboards \
  -H "Authorization: Bearer TOKEN"

# Database check
php artisan tinker
>>> \Modules\Gamification\Models\UserGamificationStat::orderByDesc('total_xp')->limit(10)->get()
```

## 📚 Full Documentation

See [SEEDER_DOCUMENTATION.md](./SEEDER_DOCUMENTATION.md) for complete details.
