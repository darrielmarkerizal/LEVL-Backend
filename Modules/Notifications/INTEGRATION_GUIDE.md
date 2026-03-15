# PANDUAN INTEGRASI: SISTEM NOTIFIKASI OTOMATIS & INFO/NEWS MANUAL
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Notifications  

---

## 📋 DAFTAR ISI

1. [Ringkasan Sistem](#ringkasan-sistem)
2. [Perbedaan Kedua Sistem](#perbedaan-kedua-sistem)
3. [Arsitektur Integrasi](#arsitektur-integrasi)
4. [Struktur Database](#struktur-database)
5. [Namespace & Folder Structure](#namespace--folder-structure)
6. [Shared Components](#shared-components)
7. [Implementation Steps](#implementation-steps)
8. [API Endpoints](#api-endpoints)
9. [Best Practices](#best-practices)

---

## 🎯 RINGKASAN SISTEM

### Sistem yang Sudah Ada: Notifikasi Otomatis
**Tujuan**: Sistem notifikasi otomatis yang dipicu oleh events dalam aplikasi

**Karakteristik**:
- Triggered by events (assignment graded, badge earned, etc.)
- Automatic creation via listeners
- User preferences untuk channel (email, push, in-app)
- Stored in `notifications` table
- Relationship dengan users via `user_notifications` pivot table

**Use Cases**:
- Assignment graded notification
- Badge earned notification
- Course enrollment notification
- Forum reply notification
- System alerts

### Sistem Baru: Info & News (Manual Posts)
**Tujuan**: Sistem konten manual yang dibuat oleh admin untuk broadcast informasi

**Karakteristik**:
- Manually created by admin
- Rich content dengan editor
- Scheduled publishing
- Target audience selection
- Stored in `posts` table
- Can trigger notifications when published

**Use Cases**:
- Announcements
- News updates
- System maintenance notices
- Award announcements
- Gamification events

---

## 🔄 PERBEDAAN KEDUA SISTEM

| Aspek | Notifikasi Otomatis | Info & News Manual |
|-------|---------------------|-------------------|
| **Pembuatan** | Otomatis via events | Manual via admin |
| **Konten** | Template-based | Rich text editor |
| **Scheduling** | Immediate | Support scheduled |
| **Target** | Individual users | Audience groups |
| **Persistence** | Notification only | Post + Notification |
| **Management** | Read/unread only | Full CRUD |
| **Table** | `notifications` | `posts` |
| **Soft Delete** | No | Yes (trash) |

---

## 🏗️ ARSITEKTUR INTEGRASI

### Konsep Integrasi

```
┌─────────────────────────────────────────────────────────────┐
│                    NOTIFICATIONS MODULE                      │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────┐      ┌──────────────────────┐    │
│  │  AUTOMATIC SYSTEM    │      │   MANUAL SYSTEM      │    │
│  │  (Existing)          │      │   (New: Info & News) │    │
│  ├──────────────────────┤      ├──────────────────────┤    │
│  │ - Event-driven       │      │ - Admin-created      │    │
│  │ - Notifications      │      │ - Posts              │    │
│  │ - User preferences   │      │ - Rich content       │    │
│  │ - Immediate          │      │ - Scheduled          │    │
│  └──────────┬───────────┘      └──────────┬───────────┘    │
│             │                              │                 │
│             └──────────┬───────────────────┘                 │
│                        │                                     │
│              ┌─────────▼─────────┐                          │
│              │  SHARED SERVICES  │                          │
│              ├───────────────────┤                          │
│              │ - NotificationSender                         │
│              │ - UserPreferences │                          │
│              │ - ChannelManager  │                          │
│              └───────────────────┘                          │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Flow Diagram

```
AUTOMATIC NOTIFICATIONS:
Event → Listener → NotificationService → Create Notification → Send via Channels

MANUAL POSTS:
Admin → Create Post → Publish → PostService → NotificationService → Send to Audience
```

---

## 🗄️ STRUKTUR DATABASE

### Tabel yang Sudah Ada

#### 1. `notifications` (Existing)
```sql
- id
- type (enum: assignment_graded, badge_earned, etc.)
- title
- message
- data (json)
- action_url
- channel (enum: email, push, in_app)
- priority
- is_broadcast
- scheduled_at
- sent_at
- created_at
- updated_at
```

#### 2. `user_notifications` (Existing - Pivot)
```sql
- id
- user_id
- notification_id
- status
- read_at
- created_at
- updated_at
```

### Tabel Baru untuk Info & News

#### 3. `posts` (New)
```sql
- id
- uuid
- title
- slug
- content (rich text)
- category (enum: announcement, information, warning, system, award, gamification)
- status (enum: draft, scheduled, published)
- is_pinned
- author_id
- scheduled_at
- published_at
- deleted_at (soft delete)
- created_at
- updated_at
```

#### 4. `post_audiences` (New)
```sql
- id
- post_id
- role (enum: student, instructor, admin)
- created_at
```

#### 5. `post_notifications` (New)
```sql
- id
- post_id
- channel (enum: email, in_app, push)
- sent_at
- created_at
```

#### 6. `post_views` (New)
```sql
- id
- post_id
- user_id
- viewed_at
```

### Relationship Diagram

```
posts (1) ──────< (N) post_audiences
posts (1) ──────< (N) post_notifications
posts (1) ──────< (N) post_views
posts (N) >────── (1) users (author)

notifications (N) >────< (N) users (via user_notifications)
```

---

## 📁 NAMESPACE & FOLDER STRUCTURE

### Struktur Folder yang Direkomendasikan

```
Modules/Notifications/
├── app/
│   ├── Models/
│   │   ├── Notification.php              # Existing
│   │   ├── NotificationPreference.php    # Existing
│   │   ├── UserNotification.php          # Existing
│   │   ├── Post.php                      # NEW
│   │   ├── PostAudience.php              # NEW
│   │   ├── PostNotification.php          # NEW
│   │   └── PostView.php                  # NEW
│   │
│   ├── Services/
│   │   ├── NotificationService.php       # Existing - Keep as is
│   │   ├── NotificationPreferenceService.php  # Existing
│   │   ├── GradingNotificationService.php     # Existing
│   │   ├── PostService.php               # NEW
│   │   └── Shared/
│   │       ├── NotificationSender.php    # NEW - Shared logic
│   │       └── ChannelManager.php        # NEW - Channel handling
│   │
│   ├── Repositories/
│   │   ├── NotificationsRepository.php   # Existing
│   │   └── PostRepository.php            # NEW
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── NotificationsController.php      # Existing
│   │   │   ├── NotificationPreferenceController.php  # Existing
│   │   │   └── PostController.php        # NEW
│   │   │
│   │   ├── Requests/
│   │   │   ├── StorePostRequest.php      # NEW
│   │   │   └── UpdatePostRequest.php     # NEW
│   │   │
│   │   └── Resources/
│   │       ├── PostResource.php          # NEW
│   │       └── PostListResource.php      # NEW
│   │
│   ├── Jobs/
│   │   ├── SendNotificationJob.php       # Existing
│   │   └── SendPostNotificationJob.php   # NEW
│   │
│   ├── Notifications/
│   │   ├── GradeRecalculatedNotification.php    # Existing
│   │   ├── GradesReleasedNotification.php       # Existing
│   │   └── PostPublishedNotification.php # NEW
│   │
│   ├── Enums/
│   │   ├── NotificationType.php          # Existing - UPDATE
│   │   ├── NotificationChannel.php       # Existing
│   │   ├── PostCategory.php              # NEW
│   │   ├── PostStatus.php                # NEW
│   │   └── PostAudienceRole.php          # NEW
│   │
│   ├── DTOs/
│   │   ├── CreateNotificationDTO.php     # Existing
│   │   ├── SendNotificationDTO.php       # Existing
│   │   ├── CreatePostDTO.php             # NEW
│   │   └── UpdatePostDTO.php             # NEW
│   │
│   ├── Console/
│   │   └── Commands/
│   │       └── PublishScheduledPostsCommand.php  # NEW
│   │
│   └── Listeners/
│       ├── NotifyOnGradeRecalculated.php # Existing
│       ├── NotifyOnGradesReleased.php    # Existing
│       └── NotifyOnSubmissionStateChanged.php  # Existing
│
├── database/
│   ├── migrations/
│   │   ├── 2025_11_03_062435_create_notifications_table.php  # Existing
│   │   ├── 2025_11_03_062446_create_user_notifications_table.php  # Existing
│   │   ├── 2026_03_15_100000_create_posts_table.php          # NEW
│   │   ├── 2026_03_15_100001_create_post_audiences_table.php # NEW
│   │   ├── 2026_03_15_100002_create_post_notifications_table.php  # NEW
│   │   ├── 2026_03_15_100003_create_post_views_table.php     # NEW
│   │   └── 2026_03_15_100004_update_notifications_type_enum.php   # NEW
│   │
│   └── seeders/
│       └── PostSeeder.php                # NEW
│
└── routes/
    └── api.php                           # UPDATE - Add post routes
```

---

## 🔗 SHARED COMPONENTS

### 1. NotificationSender Service (NEW)

Shared service untuk mengirim notifikasi dari kedua sistem:

```php
<?php

namespace Modules\Notifications\app\Services\Shared;

use Modules\Auth\app\Models\User;
use Modules\Notifications\app\Services\NotificationService;
use Modules\Notifications\app\Services\NotificationPreferenceService;

class NotificationSender
{
    public function __construct(
        private NotificationService $notificationService,
        private NotificationPreferenceService $preferenceService
    ) {}

    /**
     * Send notification to single user
     */
    public function sendToUser(
        User $user,
        string $type,
        string $title,
        string $message,
        array $channels = ['in_app'],
        ?array $data = null,
        bool $respectPreferences = true
    ): void {
        foreach ($channels as $channel) {
            if ($respectPreferences) {
                $this->notificationService->sendWithPreferences(
                    user: $user,
                    category: $type,
                    channel: $channel,
                    title: $title,
                    message: $message,
                    data: $data
                );
            } else {
                // Force send (for critical notifications)
                $this->notificationService->send(
                    userId: $user->id,
                    type: $type,
                    title: $title,
                    message: $message,
                    data: $data
                );
            }
        }
    }

    /**
     * Send notification to multiple users (broadcast)
     */
    public function sendToUsers(
        array $users,
        string $type,
        string $title,
        string $message,
        array $channels = ['in_app'],
        ?array $data = null,
        bool $respectPreferences = true
    ): void {
        foreach ($users as $user) {
            $this->sendToUser(
                user: $user,
                type: $type,
                title: $title,
                message: $message,
                channels: $channels,
                data: $data,
                respectPreferences: $respectPreferences
            );
        }
    }

    /**
     * Send notification to users by role
     */
    public function sendToRole(
        string $role,
        string $type,
        string $title,
        string $message,
        array $channels = ['in_app'],
        ?array $data = null,
        bool $respectPreferences = true
    ): void {
        $users = User::where('role', $role)->get();
        
        $this->sendToUsers(
            users: $users->toArray(),
            type: $type,
            title: $title,
            message: $message,
            channels: $channels,
            data: $data,
            respectPreferences: $respectPreferences
        );
    }
}
```

### 2. Update NotificationType Enum

Tambahkan tipe baru untuk posts:

```php
<?php

namespace Modules\Notifications\app\Enums;

enum NotificationType: string
{
    // Existing types
    case ASSIGNMENT_GRADED = 'assignment_graded';
    case BADGE_EARNED = 'badge_earned';
    case LEVEL_UP = 'level_up';
    case FORUM_REPLY = 'forum_reply';
    case COURSE_ENROLLMENT = 'course_enrollment';
    
    // NEW: Post-related types
    case POST_PUBLISHED = 'post_published';
    case POST_ANNOUNCEMENT = 'post_announcement';
    case POST_INFORMATION = 'post_information';
    case POST_WARNING = 'post_warning';
    case POST_SYSTEM = 'post_system';
    case POST_AWARD = 'post_award';
    case POST_GAMIFICATION = 'post_gamification';
}
```

---

## 🔨 IMPLEMENTATION STEPS

### Step 1: Create Database Migrations

```bash
# Create migrations for new tables
php artisan make:migration create_posts_table --path=Modules/Notifications/database/migrations
php artisan make:migration create_post_audiences_table --path=Modules/Notifications/database/migrations
php artisan make:migration create_post_notifications_table --path=Modules/Notifications/database/migrations
php artisan make:migration create_post_views_table --path=Modules/Notifications/database/migrations
php artisan make:migration update_notifications_type_enum --path=Modules/Notifications/database/migrations

# Run migrations
php artisan migrate
```

### Step 2: Create Models

Create all new models as specified in the implementation plan:
- `Post.php`
- `PostAudience.php`
- `PostNotification.php`
- `PostView.php`

### Step 3: Create Shared Services

```bash
mkdir -p Modules/Notifications/app/Services/Shared
# Create NotificationSender.php
```

### Step 4: Create Post Service

Implement `PostService.php` yang menggunakan `NotificationSender` untuk mengirim notifikasi.

### Step 5: Update Routes

```php
// Modules/Notifications/routes/api.php

// Existing notification routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Automatic notifications (existing)
    Route::apiResource('notifications', NotificationsController::class);
    Route::get('notification-preferences', [NotificationPreferenceController::class, 'index']);
    Route::put('notification-preferences', [NotificationPreferenceController::class, 'update']);
    
    // NEW: Manual posts (Info & News)
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{uuid}', [PostController::class, 'show']);
        Route::post('/{uuid}/view', [PostController::class, 'markViewed']);
        Route::get('/pinned', [PostController::class, 'pinned']);
    });
});

// Admin-only post management
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/admin')->group(function () {
    Route::apiResource('posts', PostController::class)->except(['index', 'show']);
    Route::post('posts/{uuid}/publish', [PostController::class, 'publish']);
    Route::post('posts/{uuid}/unpublish', [PostController::class, 'unpublish']);
    Route::post('posts/{uuid}/schedule', [PostController::class, 'schedule']);
    Route::post('posts/{uuid}/cancel-schedule', [PostController::class, 'cancelSchedule']);
    Route::post('posts/{uuid}/toggle-pin', [PostController::class, 'togglePin']);
    Route::get('posts/drafts', [PostController::class, 'drafts']);
    Route::get('posts/scheduled', [PostController::class, 'scheduled']);
});
```

### Step 6: Integrate PostService with NotificationSender

```php
// In PostService.php

private function sendNotifications(Post $post): void
{
    $channels = $post->notifications->pluck('channel')->toArray();
    $audiences = $post->audiences->pluck('role')->toArray();

    if (empty($channels) || empty($audiences)) {
        return;
    }

    // Get notification type based on post category
    $notificationType = $this->getNotificationTypeFromCategory($post->category);

    // Send to each audience role
    foreach ($audiences as $role) {
        $this->notificationSender->sendToRole(
            role: $role,
            type: $notificationType,
            title: $post->title,
            message: strip_tags($post->content),
            channels: $channels,
            data: [
                'post_id' => $post->uuid,
                'post_slug' => $post->slug,
                'category' => $post->category,
                'author' => $post->author->name,
            ],
            respectPreferences: true
        );
    }
}

private function getNotificationTypeFromCategory(string $category): string
{
    return match($category) {
        'announcement' => NotificationType::POST_ANNOUNCEMENT->value,
        'information' => NotificationType::POST_INFORMATION->value,
        'warning' => NotificationType::POST_WARNING->value,
        'system' => NotificationType::POST_SYSTEM->value,
        'award' => NotificationType::POST_AWARD->value,
        'gamification' => NotificationType::POST_GAMIFICATION->value,
        default => NotificationType::POST_PUBLISHED->value,
    };
}
```

---

## 🌐 API ENDPOINTS

### Automatic Notifications (Existing)
```
GET    /api/v1/notifications                    - List user notifications
GET    /api/v1/notifications/{id}               - Get notification detail
PUT    /api/v1/notifications/{id}/read          - Mark as read
DELETE /api/v1/notifications/{id}               - Delete notification
GET    /api/v1/notification-preferences         - Get preferences
PUT    /api/v1/notification-preferences         - Update preferences
```

### Manual Posts (New)
```
# Public/Shared endpoints
GET    /api/v1/posts                            - List published posts
GET    /api/v1/posts/{uuid}                     - Get post detail
POST   /api/v1/posts/{uuid}/view                - Mark as viewed
GET    /api/v1/posts/pinned                     - Get pinned posts

# Admin endpoints
POST   /api/v1/admin/posts                      - Create post
PUT    /api/v1/admin/posts/{uuid}               - Update post
DELETE /api/v1/admin/posts/{uuid}               - Delete post
POST   /api/v1/admin/posts/{uuid}/publish       - Publish post
POST   /api/v1/admin/posts/{uuid}/unpublish     - Unpublish post
POST   /api/v1/admin/posts/{uuid}/schedule      - Schedule post
POST   /api/v1/admin/posts/{uuid}/cancel-schedule - Cancel schedule
POST   /api/v1/admin/posts/{uuid}/toggle-pin    - Toggle pin
GET    /api/v1/admin/posts/drafts               - List drafts
GET    /api/v1/admin/posts/scheduled            - List scheduled
```

---

## ✅ BEST PRACTICES

### 1. Separation of Concerns

- **Automatic Notifications**: Tetap menggunakan `NotificationService` yang sudah ada
- **Manual Posts**: Gunakan `PostService` baru
- **Shared Logic**: Gunakan `NotificationSender` untuk logic yang sama

### 2. Naming Conventions

- Automatic: `NotificationService`, `NotificationsController`
- Manual: `PostService`, `PostController`
- Shared: `NotificationSender`, `ChannelManager`

### 3. Database Design

- Pisahkan tabel `posts` dari `notifications`
- Gunakan pivot tables untuk relationships
- Soft delete untuk posts, hard delete untuk notifications

### 4. User Preferences

- Respect user preferences untuk kedua sistem
- Critical notifications dapat bypass preferences
- Admin dapat force send notifications

### 5. Testing

```php
// Test automatic notifications
tests/Feature/NotificationServiceTest.php

// Test manual posts
tests/Feature/PostServiceTest.php

// Test shared components
tests/Unit/NotificationSenderTest.php
```

### 6. Documentation

- Update API documentation untuk endpoints baru
- Document integration points
- Provide examples untuk kedua sistem

---

## 🚀 MIGRATION CHECKLIST

- [ ] Create database migrations
- [ ] Create new models (Post, PostAudience, PostNotification, PostView)
- [ ] Create shared services (NotificationSender)
- [ ] Update NotificationType enum
- [ ] Create PostService
- [ ] Create PostController
- [ ] Create Form Requests
- [ ] Create Resources
- [ ] Update routes
- [ ] Create console command for scheduled posts
- [ ] Add task scheduler configuration
- [ ] Create seeders
- [ ] Write tests
- [ ] Update API documentation
- [ ] Deploy migrations

---

**Dokumen ini menjelaskan bagaimana mengintegrasikan sistem notifikasi otomatis yang sudah ada dengan fitur Info & News manual yang baru tanpa mengganggu fungsionalitas yang sudah berjalan.**
