# Badge Media Filename Fix

## Problem

Badge icons were showing "Bad Request" errors in the frontend even though the URLs were accessible when opened directly in the browser.

### Root Cause

When using `addMediaFromUrl()` with SVG files from DiceBear API (URLs without `.svg` extension), Spatie Media Library couldn't properly detect the file extension, resulting in incorrect filenames like:

```
svg.svg+xml
```

This caused issues with:
- Image loading in Next.js Image component
- Content-Type detection
- URL generation

### Example of Bad URL

```
https://levl-assets.sgp1.digitaloceanspaces.com/badges/111/icon/3467/svg.svg+xml?v=1773635010
```

## Solutions Implemented

### 1. Frontend Fix (Immediate)

**File:** `Levl-FE/components/dashboard/lencana/badges-table.tsx`

Added error handling to show fallback icon when image fails to load:

```typescript
const [imageError, setImageError] = useState(false);

{badge.icon_thumb_url && !imageError ? (
  <Image
    src={badge.icon_thumb_url}
    alt={badge.name || "Badge"}
    fill
    onError={() => setImageError(true)}
  />
) : (
  <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-yellow-400">
    <Icon name="badge" size={24} className="text-white" />
  </div>
)}
```

### 2. Seeder Fix (Permanent)

**File:** `Levl-BE/Modules/Gamification/database/seeders/BadgeSeeder.php`

Added explicit filename and name when adding media from URL:

```php
$badge->addMediaFromUrl($url)
    ->usingFileName($badge->code . '.svg')  // ✅ Explicit filename
    ->usingName($badge->name)                // ✅ Explicit name
    ->withCustomProperties(['seeded' => true])
    ->toMediaCollection('icon');
```

### 3. Database Fix Script

**File:** `Levl-BE/fix_badge_media_filenames.php`

Created a script to fix existing media records in the database:

```bash
php fix_badge_media_filenames.php
```

This script:
- Finds all badges with incorrect filenames
- Updates the `file_name` field to use correct format (`{code}.svg`)
- Updates the `mime_type` to `image/svg+xml`
- Preserves the actual files in storage (they remain accessible)

## How to Apply Fixes

### Step 1: Fix Existing Data

Run the fix script:

```bash
cd Levl-BE
php fix_badge_media_filenames.php
```

### Step 2: Re-seed (Optional)

If you want to completely regenerate all badge media:

```bash
php artisan db:seed --class=Modules\\Gamification\\Database\\Seeders\\BadgeSeeder
```

The seeder will now use the correct filename format.

## Verification

After applying fixes, verify:

1. **Check Database:**
   ```sql
   SELECT id, file_name, mime_type FROM media WHERE model_type = 'Modules\\Gamification\\Models\\Badge' LIMIT 10;
   ```
   
   Should show filenames like: `night_owl.svg`, `bug_hunter.svg`, etc.

2. **Check API Response:**
   ```bash
   curl http://localhost:8000/api/v1/badges | jq '.data[0].icon_url'
   ```
   
   Should return URLs like:
   ```
   https://levl-assets.sgp1.digitaloceanspaces.com/badges/111/icon/night_owl.svg
   ```

3. **Check Frontend:**
   - Navigate to `/lencana`
   - Badge icons should load correctly
   - No "Bad Request" errors in console

## Prevention

To prevent this issue in the future:

1. **Always specify filename** when using `addMediaFromUrl()` with URLs that don't have clear extensions
2. **Use `usingFileName()`** method to set explicit filename
3. **Test media uploads** with different file types and sources

## Related Files

- `Levl-BE/Modules/Gamification/app/Models/Badge.php` - Badge model with media handling
- `Levl-BE/Modules/Gamification/database/seeders/BadgeSeeder.php` - Badge seeder (fixed)
- `Levl-BE/fix_badge_media_filenames.php` - Database fix script
- `Levl-FE/components/dashboard/lencana/badges-table.tsx` - Frontend with error handling

## Notes

- The actual files in DigitalOcean Spaces remain unchanged
- Only the database records are updated
- URLs will work correctly after the fix
- Frontend fallback ensures graceful degradation if any issues persist
