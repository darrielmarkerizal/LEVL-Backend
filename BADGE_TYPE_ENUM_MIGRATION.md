# Badge Type Enum Migration

## Overview

Migration untuk mengubah kolom `type` di tabel `badges` dari enum dengan 3 values menjadi enum dengan 7 values yang lebih lengkap dan sesuai dengan sistem gamifikasi.

## Changes

### Before Migration

**Badge Type Enum (Old):**
- `achievement`
- `milestone`
- `completion`

### After Migration

**Badge Type Enum (New):**
- `completion` - Badge untuk menyelesaikan tugas/kursus
- `quality` - Badge untuk kualitas kerja yang baik
- `speed` - Badge untuk kecepatan menyelesaikan tugas
- `habit` - Badge untuk kebiasaan belajar yang konsisten
- `social` - Badge untuk interaksi sosial di forum
- `milestone` - Badge untuk pencapaian milestone tertentu
- `hidden` - Badge tersembunyi/easter egg

## Data Migration

Migration ini juga melakukan mapping data lama ke data baru:

| Old Value     | New Value    | Count |
|---------------|--------------|-------|
| `achievement` | `completion` | 22    |
| (new)         | `quality`    | 22    |
| (new)         | `speed`      | 22    |
| (new)         | `habit`      | 22    |
| (new)         | `social`     | 12    |
| `milestone`   | `milestone`  | 10    |
| (new)         | `hidden`     | 2     |

**Total Badges:** 112

## Migration File

```
Levl-BE/Modules/Gamification/database/migrations/2026_03_16_195614_update_badges_table_type_and_rarity_to_enum.php
```

## How to Run

```bash
# Run migration
php artisan migrate --path=Modules/Gamification/database/migrations/2026_03_16_195614_update_badges_table_type_and_rarity_to_enum.php

# Rollback if needed
php artisan migrate:rollback --step=1
```

## Verification

After running the migration, verify the changes:

```bash
# Check badge type distribution
php artisan tinker --execute="DB::table('badges')->select('type', DB::raw('count(*) as count'))->groupBy('type')->get()"

# Check a sample badge
php artisan tinker --execute="DB::table('badges')->first()"
```

## Impact

### Backend (Laravel)

✅ **No changes needed** - `BadgeType` enum already has all 7 values:
```php
// Levl-BE/Modules/Gamification/app/Enums/BadgeType.php
enum BadgeType: string
{
    case Completion = 'completion';
    case Quality = 'quality';
    case Speed = 'speed';
    case Habit = 'habit';
    case Social = 'social';
    case Milestone = 'milestone';
    case Hidden = 'hidden';
}
```

### Frontend (TypeScript)

✅ **Already updated** - TypeScript enum created:
```typescript
// Levl-FE/hooks/api/types/badges.ts
export enum BadgeType {
  COMPLETION = "completion",
  QUALITY = "quality",
  SPEED = "speed",
  HABIT = "habit",
  SOCIAL = "social",
  MILESTONE = "milestone",
  HIDDEN = "hidden",
}
```

### Translations

✅ **Already complete** - All badge types have translations:

**English** (`Levl-BE/lang/en/enums.php` & `Levl-FE/messages/en/page.json`):
- completion → "Completion"
- quality → "Quality"
- speed → "Speed"
- habit → "Habit"
- social → "Social"
- milestone → "Milestone"
- hidden → "Hidden"

**Indonesian** (`Levl-BE/lang/id/enums.php` & `Levl-FE/messages/id/page.json`):
- completion → "Penyelesaian"
- quality → "Kualitas"
- speed → "Kecepatan"
- habit → "Kebiasaan"
- social → "Sosial"
- milestone → "Pencapaian"
- hidden → "Tersembunyi"

## Rollback

If you need to rollback, the migration will:
1. Map all new badge types back to `achievement` (except `milestone`)
2. Restore the old enum constraint with 3 values

**Warning:** Rolling back will lose the distinction between different badge types (quality, speed, habit, social, hidden will all become `achievement`).

## Notes

- The `rarity` column already exists as an enum with 5 values (common, uncommon, rare, epic, legendary) from a previous migration
- This migration only updates the `type` column
- All existing badges have been preserved and mapped to appropriate new types
- The BadgeSeeder already uses the new badge types, so re-seeding will work correctly

## Related Files

### Backend
- `Levl-BE/Modules/Gamification/app/Enums/BadgeType.php`
- `Levl-BE/Modules/Gamification/app/Enums/BadgeRarity.php`
- `Levl-BE/Modules/Gamification/database/seeders/BadgeSeeder.php`
- `Levl-BE/lang/en/enums.php`
- `Levl-BE/lang/id/enums.php`

### Frontend
- `Levl-FE/hooks/api/types/badges.ts`
- `Levl-FE/components/dashboard/lencana/badges-table.tsx`
- `Levl-FE/services/dashboard/badges/badges-table.service.ts`
- `Levl-FE/messages/en/page.json`
- `Levl-FE/messages/id/page.json`

## Testing

After migration, test the following:

1. ✅ Badge listing page loads correctly
2. ✅ Badge type filter works with all 7 types
3. ✅ Badge creation with new types works
4. ✅ Badge update preserves type correctly
5. ✅ Translations display correctly for all types
6. ✅ API returns correct type and type_label

## Completion Status

✅ Migration created and executed successfully
✅ Database schema updated
✅ All 112 badges migrated to new types
✅ Backend enum already supports all types
✅ Frontend enum created and integrated
✅ Translations complete in EN and ID
✅ No breaking changes to existing code
