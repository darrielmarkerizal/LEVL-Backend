# Media Library Image Optimization Fix

## Problem

The `PerformConversionsJob` was failing when processing uploaded images (avatars, course thumbnails, banners) because:

1. Image optimization tools (jpegoptim, pngquant, optipng, etc.) are not installed on the server
2. Conversions were queued by default, causing jobs to fail in the background
3. Failed jobs were accumulating in the Redis queue

## Solution

Disabled queued image conversions by changing the default behavior in `config/media-library.php`:

```php
'queue_conversions_by_default' => env('QUEUE_CONVERSIONS_BY_DEFAULT', false),
```

Changed from `true` to `false`.

## Impact

### Before Fix
- Images uploaded successfully but conversions failed
- `PerformConversionsJob` errors in queue worker logs
- Thumbnail/optimized versions not generated
- Original images still accessible

### After Fix
- Images upload successfully
- Conversions run synchronously (no queue jobs)
- If optimization tools missing, conversions are skipped gracefully
- Original images always available
- No failed jobs in queue

## Image Conversions Defined

### User Model (Avatar)
- `thumb`: 150x150px with sharpening
- `small`: 64x64px
- `medium`: 256x256px

### Course Model (Thumbnail & Banner)
- `thumb`: 400x225px with sharpening
- `medium`: 800x450px
- `large`: 1920x1080px (banner only)
- `mobile`: 320x180px (thumbnail only)
- `tablet`: 600x338px (thumbnail only)

## Optional: Install Optimization Tools

If you want optimized images, install these tools on the server:

```bash
# Ubuntu/Debian
sudo apt-get install jpegoptim optipng pngquant gifsicle webp

# macOS
brew install jpegoptim optipng pngquant gifsicle webp
```

Then change back to queued conversions:

```bash
# In .env file
QUEUE_CONVERSIONS_BY_DEFAULT=true
```

## TrashBin Integration

The TrashBin integration is **already automatic** for all models using the `TracksTrashBin` trait:

### Models with TrashBin Integration
- `User` (Modules/Auth/app/Models/User.php)
- `Course` (Modules/Schemes/app/Models/Course.php)
- `Unit` (Modules/Schemes/app/Models/Unit.php)
- `Lesson` (Modules/Schemes/app/Models/Lesson.php)
- `Quiz` (Modules/Learning/app/Models/Quiz.php)
- `Assignment` (Modules/Learning/app/Models/Assignment.php)
- `Badge` (Modules/Gamification/app/Models/Badge.php)
- `Post` (Modules/Notifications/app/Models/Post.php) - ADDED
- `News` (Modules/Content/app/Models/News.php)
- `Announcement` (Modules/Content/app/Models/Announcement.php) - ADDED
- `Thread` (Modules/Forums/app/Models/Thread.php) - ADDED
- `Reply` (Modules/Forums/app/Models/Reply.php) - ADDED
- `Category` (Modules/Common/app/Models/Category.php) - ADDED

### How It Works
1. Model uses `TracksTrashBin` trait
2. Trait hooks into model's `deleted` event
3. When soft delete occurs, TrashBin entry is created automatically
4. No manual integration needed

### Example: Course Deletion
```php
// When you delete a course
$course->delete();

// TrashBin entry is created automatically via trait
// Entry includes: model type, model ID, deleted by, deleted at, etc.
```

## Files Modified

- `config/media-library.php` - Changed `queue_conversions_by_default` to `false`
- `Modules/Notifications/app/Models/Post.php` - Added `TracksTrashBin` trait
- `Modules/Content/app/Models/Announcement.php` - Added `TracksTrashBin` trait
- `Modules/Forums/app/Models/Thread.php` - Added `TracksTrashBin` trait
- `Modules/Forums/app/Models/Reply.php` - Added `TracksTrashBin` trait
- `Modules/Common/app/Models/Category.php` - Added `TracksTrashBin` trait

## Related Documentation

- `QUEUE_WORKER_GUIDE.md` - Queue worker setup and monitoring
- `Modules/Trash/TRASH_API_DOCUMENTATION.md` - TrashBin API documentation
- `app/Models/Concerns/TracksTrashBin.php` - TrashBin trait implementation

## Testing

1. Upload a user avatar - should work without errors
2. Upload course thumbnail/banner - should work without errors
3. Check queue worker logs - no `PerformConversionsJob` errors
4. Delete a course - should appear in trash automatically
5. Check `trash_bins` table - entry should exist

## Date

March 17, 2026
