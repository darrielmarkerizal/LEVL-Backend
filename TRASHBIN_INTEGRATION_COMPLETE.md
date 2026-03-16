# TrashBin Integration - Complete Summary

## Overview

All models with soft deletes now automatically integrate with the TrashBin system. When any of these models are deleted, an entry is automatically created in the `trash_bins` table, allowing users to view and restore deleted items from a centralized trash interface.

## How It Works

### Automatic Integration via Trait

The `TracksTrashBin` trait (located at `app/Models/Concerns/TracksTrashBin.php`) provides automatic TrashBin integration:

1. Trait is added to model's `use` statement
2. Trait hooks into model's `deleted` event
3. When soft delete occurs, TrashBin entry is created automatically
4. Entry includes: model type, model ID, deleted by, deleted at, original data

### No Manual Code Required

Once the trait is added to a model, everything happens automatically. No need to:
- Manually create TrashBin entries
- Add event listeners
- Modify delete methods
- Write custom logic

## Models with TrashBin Integration

All models below now have automatic TrashBin integration:

### Authentication & Users
- `User` (Modules/Auth/app/Models/User.php)

### Courses & Learning
- `Course` (Modules/Schemes/app/Models/Course.php)
- `Unit` (Modules/Schemes/app/Models/Unit.php)
- `Lesson` (Modules/Schemes/app/Models/Lesson.php)
- `Quiz` (Modules/Learning/app/Models/Quiz.php)
- `Assignment` (Modules/Learning/app/Models/Assignment.php)

### Gamification
- `Badge` (Modules/Gamification/app/Models/Badge.php)

### Content & Notifications
- `Post` (Modules/Notifications/app/Models/Post.php)
- `News` (Modules/Content/app/Models/News.php)
- `Announcement` (Modules/Content/app/Models/Announcement.php)

### Forums
- `Thread` (Modules/Forums/app/Models/Thread.php)
- `Reply` (Modules/Forums/app/Models/Reply.php)

### Common
- `Category` (Modules/Common/app/Models/Category.php)

## Usage Examples

### Deleting a Model
```php
// Delete a course
$course = Course::find(1);
$course->delete();

// TrashBin entry is created automatically
// No additional code needed!
```

### Viewing Trash
```php
// Get all trash items
GET /api/trash

// Filter by model type
GET /api/trash?model_type=Course

// Filter by deleted user
GET /api/trash?deleted_by=5
```

### Restoring from Trash
```php
// Restore a single item
POST /api/trash/{id}/restore

// Bulk restore
POST /api/trash/bulk-restore
{
  "ids": [1, 2, 3]
}
```

### Permanent Delete
```php
// Permanently delete a single item
DELETE /api/trash/{id}

// Bulk permanent delete
POST /api/trash/bulk-delete
{
  "ids": [1, 2, 3]
}
```

## TrashBin Table Structure

The `trash_bins` table stores:
- `id` - Primary key
- `model_type` - Fully qualified class name (e.g., "Modules\Schemes\Models\Course")
- `model_id` - ID of the deleted model
- `deleted_by` - User ID who deleted the item
- `deleted_at` - Timestamp of deletion
- `data` - JSON snapshot of the deleted model
- `created_at` / `updated_at` - Standard timestamps

## Adding TrashBin to New Models

To add TrashBin integration to a new model:

1. Ensure model uses `SoftDeletes` trait
2. Add `TracksTrashBin` trait to model

```php
use App\Models\Concerns\TracksTrashBin;
use Illuminate\Database\Eloquent\SoftDeletes;

class YourModel extends Model
{
    use SoftDeletes, TracksTrashBin;
    
    // ... rest of your model
}
```

That's it! The model now automatically integrates with TrashBin.

## API Endpoints

All TrashBin endpoints are available at `/api/trash`:

- `GET /api/trash` - List all trash items (paginated, filterable)
- `GET /api/trash/{id}` - Get single trash item details
- `POST /api/trash/{id}/restore` - Restore single item
- `DELETE /api/trash/{id}` - Permanently delete single item
- `POST /api/trash/bulk-restore` - Restore multiple items
- `POST /api/trash/bulk-delete` - Permanently delete multiple items

## Permissions

TrashBin operations require appropriate permissions:
- `trash.view` - View trash items
- `trash.restore` - Restore items from trash
- `trash.delete` - Permanently delete items

Typically granted to:
- Superadmin (all permissions)
- Admin (all permissions)
- Instructor (view and restore only)

## Related Documentation

- `Modules/Trash/TRASH_API_DOCUMENTATION.md` - Complete API documentation
- `app/Models/Concerns/TracksTrashBin.php` - Trait implementation
- `Modules/Trash/app/Services/TrashBinService.php` - Service layer
- `QUEUE_WORKER_GUIDE.md` - Queue worker setup (for bulk operations)

## Testing

To verify TrashBin integration:

1. Delete an item from any integrated model
2. Check the `trash_bins` table - entry should exist
3. Use trash API to view the deleted item
4. Restore the item - it should be undeleted
5. Delete again and permanently delete - item should be gone

## Implementation Date

March 17, 2026

## Changes Made

### Phase 1 (Previous)
- Created `TracksTrashBin` trait
- Integrated with Course, User, Unit, Lesson, Quiz, Assignment, Badge, News models

### Phase 2 (Current)
- Added TrashBin integration to:
  - Post model
  - Announcement model
  - Thread model
  - Reply model
  - Category model
- All models with SoftDeletes now have TrashBin integration

## Notes

- TrashBin entries are created only on soft delete, not hard delete
- If a model is force deleted (`forceDelete()`), no TrashBin entry is created
- Restoring from trash calls the model's `restore()` method
- Permanent delete from trash calls the model's `forceDelete()` method
- Bulk operations are queued for performance
