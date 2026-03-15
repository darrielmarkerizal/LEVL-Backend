# QUICK INTEGRATION GUIDE: Info & News dengan Notifikasi Otomatis

## 🎯 Konsep Utama

### Sistem yang Sudah Ada (JANGAN DIUBAH)
```
Event → Listener → NotificationService → Notification → User
```
**Contoh**: Assignment dinilai → Notifikasi otomatis ke student

### Sistem Baru (TAMBAHAN)
```
Admin → Create Post → PostService → NotificationSender → Notification → Users (by role)
```
**Contoh**: Admin buat pengumuman → Notifikasi manual ke semua student

---

## 📁 File Structure

```
Modules/Notifications/
├── app/
│   ├── Models/
│   │   ├── Notification.php          ✅ EXISTING - Keep
│   │   ├── Post.php                  ⭐ NEW
│   │   ├── PostAudience.php          ⭐ NEW
│   │   ├── PostNotification.php      ⭐ NEW
│   │   └── PostView.php              ⭐ NEW
│   │
│   ├── Services/
│   │   ├── NotificationService.php   ✅ EXISTING - Keep
│   │   ├── PostService.php           ⭐ NEW
│   │   └── Shared/
│   │       └── NotificationSender.php ⭐ NEW (Bridge)
│   │
│   └── Http/Controllers/
│       ├── NotificationsController.php ✅ EXISTING - Keep
│       └── PostController.php         ⭐ NEW
```

---

## 🔗 Integration Points

### 1. NotificationSender (Bridge Service)

**Lokasi**: `app/Services/Shared/NotificationSender.php`

**Fungsi**: Menjembatani PostService dengan NotificationService yang sudah ada

```php
class NotificationSender
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    // Digunakan oleh PostService untuk kirim notifikasi
    public function sendToRole(string $role, string $type, string $title, string $message, array $channels)
    {
        $users = User::where('role', $role)->get();
        
        foreach ($users as $user) {
            $this->notificationService->sendWithPreferences(
                user: $user,
                category: $type,
                channel: $channel,
                title: $title,
                message: $message
            );
        }
    }
}
```

### 2. PostService menggunakan NotificationSender

```php
class PostService
{
    public function __construct(
        private NotificationSender $notificationSender
    ) {}

    private function sendNotifications(Post $post): void
    {
        $channels = $post->notifications->pluck('channel')->toArray();
        $audiences = $post->audiences->pluck('role')->toArray();

        foreach ($audiences as $role) {
            $this->notificationSender->sendToRole(
                role: $role,
                type: 'post_' . $post->category,
                title: $post->title,
                message: strip_tags($post->content),
                channels: $channels
            );
        }
    }
}
```

---

## 🗄️ Database Tables

### Existing (JANGAN DIUBAH)
- `notifications` - Menyimpan semua notifikasi (otomatis & manual)
- `user_notifications` - Pivot table user-notification

### New (TAMBAHAN)
- `posts` - Menyimpan konten Info & News
- `post_audiences` - Target audience per post
- `post_notifications` - Channel settings per post
- `post_views` - Tracking views

---

## 🚀 Implementation Steps

### Step 1: Create Migrations
```bash
php artisan make:migration create_posts_table --path=Modules/Notifications/database/migrations
php artisan make:migration create_post_audiences_table --path=Modules/Notifications/database/migrations
php artisan make:migration create_post_notifications_table --path=Modules/Notifications/database/migrations
php artisan make:migration create_post_views_table --path=Modules/Notifications/database/migrations
```

### Step 2: Create Models
```bash
# Copy dari INFO_NEWS_IMPLEMENTATION_PLAN.md
- Post.php
- PostAudience.php
- PostNotification.php
- PostView.php
```

### Step 3: Create Shared Service
```bash
mkdir -p Modules/Notifications/app/Services/Shared
# Create NotificationSender.php
```

### Step 4: Create Post Components
```bash
# Services
- PostService.php
- PostRepository.php

# Controllers
- PostController.php

# Requests
- StorePostRequest.php
- UpdatePostRequest.php

# Resources
- PostResource.php
- PostListResource.php
```

### Step 5: Update Routes
```php
// Add to routes/api.php
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Existing routes tetap
    Route::apiResource('notifications', NotificationsController::class);
    
    // NEW: Posts routes
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{uuid}', [PostController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/admin')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{uuid}/publish', [PostController::class, 'publish']);
});
```

### Step 6: Update NotificationType Enum
```php
enum NotificationType: string
{
    // Existing
    case ASSIGNMENT_GRADED = 'assignment_graded';
    case BADGE_EARNED = 'badge_earned';
    
    // NEW for posts
    case POST_ANNOUNCEMENT = 'post_announcement';
    case POST_INFORMATION = 'post_information';
    case POST_WARNING = 'post_warning';
    case POST_SYSTEM = 'post_system';
}
```

---

## 🔄 Flow Comparison

### Automatic Notification Flow (EXISTING)
```
1. User submits assignment
2. AssignmentSubmitted event fired
3. Listener catches event
4. NotificationService creates notification
5. Notification sent to user
```

### Manual Post Flow (NEW)
```
1. Admin creates post via PostController
2. PostService handles creation
3. When published, PostService calls NotificationSender
4. NotificationSender uses NotificationService
5. Notification sent to target audience
```

---

## ✅ Testing Checklist

### Test Existing System (Harus tetap berfungsi)
- [ ] Assignment graded notification works
- [ ] Badge earned notification works
- [ ] User preferences respected
- [ ] Email notifications sent

### Test New System
- [ ] Admin can create post
- [ ] Post can be published
- [ ] Notifications sent to correct audience
- [ ] User preferences respected
- [ ] Scheduled posts work

### Test Integration
- [ ] Both systems work independently
- [ ] No conflicts in notification table
- [ ] User receives both types of notifications
- [ ] Notification preferences apply to both

---

## 🎨 Frontend Integration

### Notification List (Existing + New)
```typescript
// User sees both automatic and manual notifications
GET /api/v1/notifications
// Returns:
[
  { type: 'assignment_graded', ... },  // Automatic
  { type: 'post_announcement', ... },  // Manual
  { type: 'badge_earned', ... },       // Automatic
]
```

### Posts List (New)
```typescript
// User sees published posts
GET /api/v1/posts
// Returns:
[
  { title: 'System Maintenance', category: 'system', ... },
  { title: 'New Feature', category: 'announcement', ... },
]
```

---

## 🚨 Important Notes

### DO NOT MODIFY
- `NotificationService.php` - Keep existing logic
- `NotificationsController.php` - Keep existing endpoints
- `notifications` table structure
- Existing listeners and events

### SAFE TO ADD
- New models (Post, PostAudience, etc.)
- New services (PostService, NotificationSender)
- New controllers (PostController)
- New routes (posts endpoints)
- New tables (posts, post_audiences, etc.)

### MODIFY CAREFULLY
- `NotificationType` enum - Only ADD new values
- `routes/api.php` - Only ADD new routes
- Module service provider - Register new services

---

## 📞 Quick Reference

### Send Automatic Notification (Existing)
```php
// In Listener
$this->notificationService->send(
    userId: $user->id,
    type: 'assignment_graded',
    title: 'Assignment Graded',
    message: 'Your assignment has been graded'
);
```

### Send Manual Notification (New)
```php
// In PostService
$this->notificationSender->sendToRole(
    role: 'student',
    type: 'post_announcement',
    title: $post->title,
    message: strip_tags($post->content),
    channels: ['email', 'in_app']
);
```

---

## 🎯 Summary

**Sistem Lama**: Event-driven, otomatis, individual users
**Sistem Baru**: Admin-driven, manual, audience groups
**Bridge**: NotificationSender menghubungkan keduanya
**Result**: Dua sistem bekerja independen tapi menggunakan infrastruktur notifikasi yang sama

**Key Point**: Sistem baru TIDAK mengubah sistem lama, hanya menambahkan layer baru di atasnya.
