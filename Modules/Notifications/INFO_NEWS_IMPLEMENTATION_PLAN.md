# RENCANA IMPLEMENTASI FITUR INFO & NEWS
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Notifications  
**Platform**: Backend (Laravel) + Frontend (Next.js)

---

## 📋 DAFTAR ISI

1. [Ringkasan Fitur](#ringkasan-fitur)
2. [Analisis Desain UI](#analisis-desain-ui)
3. [Arsitektur Database](#arsitektur-database)
4. [Backend Implementation](#backend-implementation)
5. [Scheduled Posts Feature](#scheduled-posts-feature)
6. [API Endpoints](#api-endpoints)
7. [Timeline & Prioritas](#timeline--prioritas)
8. [Testing Strategy](#testing-strategy)

---

## 🎯 RINGKASAN FITUR

### Tujuan
Membuat sistem manajemen konten Info & News yang memungkinkan admin untuk:
- Membuat, mengedit, dan menghapus post (info/news)
- Mengatur kategori post (Announcement, Information, Warning, System, Award, Gamification)
- Menentukan target audience (Student, Instructor, Admin)
- Mengatur notifikasi (Email, In-App, Push)
- Mengelola draft dan published posts
- Pin/unpin posts untuk highlight
- Soft delete dengan trash management
- **Scheduled publishing** - Menjadwalkan post untuk dipublikasikan di waktu tertentu
- **Media management** - Upload dan manage gambar menggunakan Spatie Media Library
- **Audit trail** - Track last editor untuk setiap perubahan
- **Bulk operations** - Bulk delete dan bulk publish dengan queue support
- **Performance optimization** - Redis caching untuk read-heavy endpoints

### User Roles
- **Admin**: Full CRUD access untuk semua posts
- **Instructor**: Read access untuk posts yang ditargetkan ke instructor
- **Student**: Read access untuk posts yang ditargetkan ke student

### Key Features
1. ✅ Rich text editor dengan image upload (Spatie Media Library)
2. ✅ Scheduled publishing dengan auto-publish
3. ✅ Trash bin management (restore/force delete)
4. ✅ Audit trail (last editor tracking)
5. ✅ Selective notification resend by channel (array-based)
6. ✅ Bulk operations dengan queue support
7. ✅ Redis caching untuk performance optimization
8. ✅ Media management dengan Spatie Media Library
9. ✅ Image upload endpoint untuk rich text editor
10. ✅ Trash management endpoints (list/restore/force delete)

---

## 🎨 ANALISIS DESAIN UI

### 1. Info & News Management (List View)
**Komponen**:
- Search bar dengan filter kategori
- Bulk actions (Delete selected)
- Action buttons (Draft, Create Post)
- Data table dengan kolom:
  - Checkbox untuk bulk selection
  - Post Title
  - Category (dengan icon)
  - Author
  - Date
  - Actions (View, Edit, Delete)
- Pagination


**Fitur Khusus**:
- Filter by category dropdown
- Search functionality
- Bulk delete
- Draft management
- Pagination

### 2. Add New Post Form
**Komponen**:
- Post Title (text input)
- Category (dropdown select)
- Message Content (rich text editor dengan toolbar):
  - Bold, Italic, Underline
  - List (bullet/numbered)
  - Image upload
  - Link
  - Code block
- Publishing Options:
  - Target Audience (multi-checkbox: Student, Instructor, Admin)
  - Notification Type (multi-checkbox: Email, In-App Notification, Push Notification)
- Action buttons:
  - Cancel
  - Save as Draft
  - Publish Post

### 3. Edit Published Post
**Komponen**:
- Status indicator (Published badge)
- Posted By info
- Same form fields as Add New Post
- Post Visibility (Target Audience)
- Notification Update options:
  - Resend Notification via Email
  - Resend In-App Notification
- Action buttons:
  - Cancel
  - Unpublish (Move to Draft)
  - Save Changes

### 4. View Post (Detail)
**Komponen**:
- Status badge (Published, Pinned)
- Category icon & label
- Title
- Author
- Date
- Audience info
- Full content display
- Action buttons:
  - Edit Post
  - Unpublish
  - Delete

### 5. Manage Drafts
**Komponen**:
- Similar to main list view
- Shows draft posts only
- Bulk actions (Publish Selected, Discard Selected)
- Edit and publish individual drafts

---

## 🗄️ ARSITEKTUR DATABASE

### 1. Tabel Baru: `posts`
```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    category ENUM('announcement', 'information', 'warning', 'system', 'award', 'gamification') NOT NULL,
    status ENUM('draft', 'scheduled', 'published') DEFAULT 'draft',
    is_pinned BOOLEAN DEFAULT FALSE,
    author_id BIGINT UNSIGNED NOT NULL,
    last_editor_id BIGINT UNSIGNED NULL,
    scheduled_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (last_editor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_published_at (published_at),
    INDEX idx_is_pinned (is_pinned),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_last_editor_id (last_editor_id)
);
```

### 2. Tabel Baru: `post_audiences`
```sql
CREATE TABLE post_audiences (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT UNSIGNED NOT NULL,
    role ENUM('student', 'instructor', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_role (post_id, role),
    INDEX idx_post_id (post_id),
    INDEX idx_role (role)
);
```

### 3. Tabel Baru: `post_notifications`
```sql
CREATE TABLE post_notifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT UNSIGNED NOT NULL,
    channel ENUM('email', 'in_app', 'push') NOT NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_channel (post_id, channel),
    INDEX idx_post_id (post_id),
    INDEX idx_sent_at (sent_at)
);
```

### 4. Tabel Baru: `post_views`
```sql
CREATE TABLE post_views (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_user (post_id, user_id),
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id)
);
```

### 5. Update Tabel: `notifications`
Tambahkan tipe notifikasi baru untuk posts:
```sql
ALTER TABLE notifications 
MODIFY COLUMN type ENUM(
    'course_enrollment',
    'assignment_graded',
    'badge_earned',
    'level_up',
    'forum_reply',
    'announcement',
    'system',
    'post_published',  -- NEW
    'post_updated'     -- NEW
) NOT NULL;
```

### 6. Spatie Media Library Tables
Spatie Media Library akan otomatis membuat tabel `media` saat menjalankan migration:
```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
php artisan migrate
```

Tabel `media` akan menyimpan:
- File uploads (images untuk rich text editor)
- Metadata (file size, mime type, dimensions)
- Collections (untuk grouping media)
- Conversions (untuk image optimization)

---

## 🔧 BACKEND IMPLEMENTATION

### 1. Models

#### Post Model (`app/Models/Post.php`)
```php
<?php

namespace Modules\Notifications\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Modules\Auth\app\Models\User;

class Post extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'content',
        'category',
        'status',
        'is_pinned',
        'author_id',
        'last_editor_id',
        'scheduled_at',
        'published_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_editor_id');
    }

    public function audiences()
    {
        return $this->hasMany(PostAudience::class);
    }

    public function notifications()
    {
        return $this->hasMany(PostNotification::class);
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    // Spatie Media Library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->whereNotNull('scheduled_at');
    }

    public function scopePendingPublish($query)
    {
        return $query->where('status', 'scheduled')
                    ->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<=', now());
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeForRole($query, string $role)
    {
        return $query->whereHas('audiences', function ($q) use ($role) {
            $q->where('role', $role);
        });
    }

    // Accessors & Mutators
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
```


#### PostAudience Model (`app/Models/PostAudience.php`)
```php
<?php

namespace Modules\Notifications\app\Models;

use Illuminate\Database\Eloquent\Model;

class PostAudience extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'role',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

#### PostNotification Model (`app/Models/PostNotification.php`)
```php
<?php

namespace Modules\Notifications\app\Models;

use Illuminate\Database\Eloquent\Model;

class PostNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'channel',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

#### PostView Model (`app/Models/PostView.php`)
```php
<?php

namespace Modules\Notifications\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\app\Models\User;

class PostView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 2. Enums

#### PostCategory Enum (`app/Enums/PostCategory.php`)
```php
<?php

namespace Modules\Notifications\app\Enums;

enum PostCategory: string
{
    case ANNOUNCEMENT = 'announcement';
    case INFORMATION = 'information';
    case WARNING = 'warning';
    case SYSTEM = 'system';
    case AWARD = 'award';
    case GAMIFICATION = 'gamification';

    public function label(): string
    {
        return match($this) {
            self::ANNOUNCEMENT => 'Announcement',
            self::INFORMATION => 'Information',
            self::WARNING => 'Warning',
            self::SYSTEM => 'System',
            self::AWARD => 'Award',
            self::GAMIFICATION => 'Gamification',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::ANNOUNCEMENT => '📢',
            self::INFORMATION => 'ℹ️',
            self::WARNING => '⚠️',
            self::SYSTEM => '⚙️',
            self::AWARD => '🏆',
            self::GAMIFICATION => '🎮',
        };
    }
}
```

#### PostStatus Enum (`app/Enums/PostStatus.php`)
```php
<?php

namespace Modules\Notifications\app\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::PUBLISHED => 'Published',
        };
    }
}
```

#### PostAudienceRole Enum (`app/Enums/PostAudienceRole.php`)
```php
<?php

namespace Modules\Notifications\app\Enums;

enum PostAudienceRole: string
{
    case STUDENT = 'student';
    case INSTRUCTOR = 'instructor';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match($this) {
            self::STUDENT => 'Student',
            self::INSTRUCTOR => 'Instructor',
            self::ADMIN => 'Admin',
        };
    }
}
```

### 3. DTOs

#### CreatePostDTO (`app/DTOs/CreatePostDTO.php`)
```php
<?php

namespace Modules\Notifications\app\DTOs;

class CreatePostDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string $category,
        public readonly string $status,
        public readonly array $audiences,
        public readonly array $notificationChannels,
        public readonly bool $isPinned = false,
        public readonly ?string $scheduledAt = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            category: $data['category'],
            status: $data['status'] ?? 'draft',
            audiences: $data['audiences'] ?? [],
            notificationChannels: $data['notification_channels'] ?? [],
            isPinned: $data['is_pinned'] ?? false,
            scheduledAt: $data['scheduled_at'] ?? null,
        );
    }
}
```

#### UpdatePostDTO (`app/DTOs/UpdatePostDTO.php`)
```php
<?php

namespace Modules\Notifications\app\DTOs;

class UpdatePostDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $content = null,
        public readonly ?string $category = null,
        public readonly ?string $status = null,
        public readonly ?array $audiences = null,
        public readonly ?array $notificationChannels = null,
        public readonly ?bool $isPinned = null,
        public readonly array $resendNotificationChannels = [],
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            category: $data['category'] ?? null,
            status: $data['status'] ?? null,
            audiences: $data['audiences'] ?? null,
            notificationChannels: $data['notification_channels'] ?? null,
            isPinned: $data['is_pinned'] ?? null,
            resendNotificationChannels: $data['resend_notification_channels'] ?? [],
        );
    }
}
```

### 4. Repositories

#### PostRepository (`app/Repositories/PostRepository.php`)
```php
<?php

namespace Modules\Notifications\app\Repositories;

use Modules\Notifications\app\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PostRepository
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'posts:';

    public function __construct(
        private Post $model
    ) {}

    public function paginate(
        int $perPage = 15,
        ?string $status = null,
        ?string $category = null,
        ?string $search = null,
        ?string $role = null
    ): LengthAwarePaginator {
        // Cache key untuk published posts saja
        $cacheKey = null;
        if ($status === 'published' && !$search) {
            $cacheKey = self::CACHE_PREFIX . "list:{$status}:{$category}:{$role}:page:" . request('page', 1);
            
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = $this->model->with(['author', 'lastEditor', 'audiences'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->forRole($role);
        }

        $result = $query->paginate($perPage);

        // Cache hasil jika published posts
        if ($cacheKey) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    public function findByUuid(string $uuid): ?Post
    {
        return $this->model->with(['author', 'lastEditor', 'audiences', 'notifications'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function create(array $data): Post
    {
        $post = $this->model->create($data);
        $this->clearCache();
        return $post;
    }

    public function update(Post $post, array $data): bool
    {
        $result = $post->update($data);
        $this->clearCache();
        return $result;
    }

    public function delete(Post $post): bool
    {
        $result = $post->delete();
        $this->clearCache();
        return $result;
    }

    public function restore(Post $post): bool
    {
        $result = $post->restore();
        $this->clearCache();
        return $result;
    }

    public function forceDelete(Post $post): bool
    {
        $result = $post->forceDelete();
        $this->clearCache();
        return $result;
    }

    public function getPinnedPosts(?string $role = null): Collection
    {
        $cacheKey = self::CACHE_PREFIX . "pinned:{$role}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($role) {
            $query = $this->model->published()
                ->pinned()
                ->with(['author', 'audiences']);

            if ($role) {
                $query->forRole($role);
            }

            return $query->get();
        });
    }

    public function getTrashedPosts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->onlyTrashed()
            ->with(['author', 'lastEditor', 'audiences'])
            ->orderByDesc('deleted_at')
            ->paginate($perPage);
    }

    public function getPendingScheduledPosts(): Collection
    {
        return $this->model->pendingPublish()
            ->with(['author', 'audiences', 'notifications'])
            ->get();
    }

    public function getScheduledPosts(?string $role = null): LengthAwarePaginator
    {
        $query = $this->model->scheduled()
            ->with(['author', 'audiences'])
            ->orderBy('scheduled_at', 'asc');

        if ($role) {
            $query->forRole($role);
        }

        return $query->paginate(15);
    }

    private function clearCache(): void
    {
        // Clear all posts cache
        Cache::tags(['posts'])->flush();
        
        // Alternative: Clear specific patterns
        $patterns = [
            self::CACHE_PREFIX . 'list:*',
            self::CACHE_PREFIX . 'pinned:*',
        ];
        
        foreach ($patterns as $pattern) {
            $keys = Cache::get($pattern);
            if ($keys) {
                Cache::forget($keys);
            }
        }
    }
}
```


### 5. Services

#### PostService (`app/Services/PostService.php`)
```php
<?php

namespace Modules\Notifications\app\Services;

use Modules\Notifications\app\Models\Post;
use Modules\Notifications\app\Repositories\PostRepository;
use Modules\Notifications\app\DTOs\CreatePostDTO;
use Modules\Notifications\app\DTOs\UpdatePostDTO;
use Modules\Notifications\app\Jobs\SendPostNotificationJob;
use Modules\Notifications\app\Jobs\BulkDeletePostsJob;
use Modules\Notifications\app\Jobs\BulkPublishPostsJob;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class PostService
{
    public function __construct(
        private PostRepository $repository
    ) {}

    public function createPost(CreatePostDTO $dto, int $authorId): Post
    {
        return DB::transaction(function () use ($dto, $authorId) {
            // Validate scheduled_at if status is scheduled
            if ($dto->status === 'scheduled' && !$dto->scheduledAt) {
                throw new \InvalidArgumentException('Scheduled date is required for scheduled posts');
            }

            // Create post
            $post = $this->repository->create([
                'uuid' => Str::uuid(),
                'title' => $dto->title,
                'slug' => Str::slug($dto->title),
                'content' => $dto->content,
                'category' => $dto->category,
                'status' => $dto->status,
                'is_pinned' => $dto->isPinned,
                'author_id' => $authorId,
                'last_editor_id' => $authorId,
                'scheduled_at' => $dto->status === 'scheduled' ? $dto->scheduledAt : null,
                'published_at' => $dto->status === 'published' ? now() : null,
            ]);

            // Attach audiences
            if (!empty($dto->audiences)) {
                foreach ($dto->audiences as $role) {
                    $post->audiences()->create(['role' => $role]);
                }
            }

            // Store notification channels
            if (!empty($dto->notificationChannels)) {
                foreach ($dto->notificationChannels as $channel) {
                    $post->notifications()->create(['channel' => $channel]);
                }
            }

            // Send notifications if published immediately
            if ($dto->status === 'published') {
                $this->sendNotifications($post);
            }

            return $post->load(['author', 'lastEditor', 'audiences', 'notifications']);
        });
    }

    public function updatePost(Post $post, UpdatePostDTO $dto, int $editorId): Post
    {
        return DB::transaction(function () use ($post, $dto, $editorId) {
            $updateData = ['last_editor_id' => $editorId];

            if ($dto->title !== null) {
                $updateData['title'] = $dto->title;
                $updateData['slug'] = Str::slug($dto->title);
            }

            if ($dto->content !== null) {
                $updateData['content'] = $dto->content;
            }

            if ($dto->category !== null) {
                $updateData['category'] = $dto->category;
            }

            if ($dto->status !== null) {
                $updateData['status'] = $dto->status;
                
                // Set published_at when publishing
                if ($dto->status === 'published' && $post->status === 'draft') {
                    $updateData['published_at'] = now();
                }
                
                // Clear published_at when unpublishing
                if ($dto->status === 'draft' && $post->status === 'published') {
                    $updateData['published_at'] = null;
                }
            }

            if ($dto->isPinned !== null) {
                $updateData['is_pinned'] = $dto->isPinned;
            }

            // Update post
            $this->repository->update($post, $updateData);

            // Update audiences
            if ($dto->audiences !== null) {
                $post->audiences()->delete();
                foreach ($dto->audiences as $role) {
                    $post->audiences()->create(['role' => $role]);
                }
            }

            // Update notification channels
            if ($dto->notificationChannels !== null) {
                $post->notifications()->delete();
                foreach ($dto->notificationChannels as $channel) {
                    $post->notifications()->create(['channel' => $channel]);
                }
            }

            // Resend notifications for specific channels if requested
            if (!empty($dto->resendNotificationChannels) && $post->status === 'published') {
                $this->sendNotifications($post, $dto->resendNotificationChannels);
            }

            return $post->fresh(['author', 'lastEditor', 'audiences', 'notifications']);
        });
    }

    public function deletePost(Post $post): bool
    {
        return $this->repository->delete($post);
    }

    public function restorePost(Post $post): bool
    {
        return $this->repository->restore($post);
    }

    public function forceDeletePost(Post $post): bool
    {
        // Delete associated media
        $post->clearMediaCollection('images');
        
        return $this->repository->forceDelete($post);
    }

    public function publishPost(Post $post): Post
    {
        $this->repository->update($post, [
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->sendNotifications($post);

        return $post->fresh();
    }

    public function unpublishPost(Post $post): Post
    {
        $this->repository->update($post, [
            'status' => 'draft',
            'published_at' => null,
        ]);

        return $post->fresh();
    }

    public function schedulePost(Post $post, string $scheduledAt): Post
    {
        $this->repository->update($post, [
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'published_at' => null,
        ]);

        return $post->fresh();
    }

    public function publishScheduledPost(Post $post): Post
    {
        if ($post->status !== 'scheduled') {
            throw new \Exception('Post is not scheduled');
        }

        if (!$post->scheduled_at || $post->scheduled_at->isFuture()) {
            throw new \Exception('Post is not ready to be published');
        }

        $this->repository->update($post, [
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->sendNotifications($post);

        return $post->fresh();
    }

    public function cancelSchedule(Post $post): Post
    {
        $this->repository->update($post, [
            'status' => 'draft',
            'scheduled_at' => null,
        ]);

        return $post->fresh();
    }

    public function togglePin(Post $post): Post
    {
        $this->repository->update($post, [
            'is_pinned' => !$post->is_pinned,
        ]);

        return $post->fresh();
    }

    public function markAsViewed(Post $post, int $userId): void
    {
        $post->views()->firstOrCreate([
            'user_id' => $userId,
        ], [
            'viewed_at' => now(),
        ]);
    }

    public function uploadImage(Post $post, UploadedFile $file): string
    {
        $media = $post->addMedia($file)
            ->toMediaCollection('images');

        return $media->getUrl();
    }

    public function bulkDelete(array $postUuids): void
    {
        // Dispatch job untuk bulk delete
        BulkDeletePostsJob::dispatch($postUuids);
    }

    public function bulkPublish(array $postUuids): void
    {
        // Dispatch job untuk bulk publish
        BulkPublishPostsJob::dispatch($postUuids);
    }

    private function sendNotifications(Post $post, ?array $specificChannels = null): void
    {
        $channels = $specificChannels ?? $post->notifications->pluck('channel')->toArray();
        $audiences = $post->audiences->pluck('role')->toArray();

        if (!empty($channels) && !empty($audiences)) {
            SendPostNotificationJob::dispatch($post, $channels, $audiences);
        }
    }
}
```

### 6. Controllers

#### PostController (`app/Http/Controllers/PostController.php`)
```php
<?php

namespace Modules\Notifications\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Notifications\app\Services\PostService;
use Modules\Notifications\app\Repositories\PostRepository;
use Modules\Notifications\app\Http\Requests\StorePostRequest;
use Modules\Notifications\app\Http\Requests\UpdatePostRequest;
use Modules\Notifications\app\Http\Resources\PostResource;
use Modules\Notifications\app\Http\Resources\PostListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostService $service,
        private PostRepository $repository
    ) {}

    /**
     * Display a listing of posts
     */
    public function index(Request $request): JsonResponse
    {
        $posts = $this->repository->paginate(
            perPage: $request->input('per_page', 15),
            status: $request->input('status'),
            category: $request->input('category'),
            search: $request->input('search'),
            role: $request->user()->role
        );

        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'data' => PostListResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'from' => $posts->firstItem(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'to' => $posts->lastItem(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Store a newly created post
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $dto = CreatePostDTO::fromRequest($request->validated());
        $post = $this->service->createPost($dto, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => new PostResource($post),
        ], 201);
    }

    /**
     * Display the specified post
     */
    public function show(string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Post retrieved successfully',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Update the specified post
     */
    public function update(UpdatePostRequest $request, string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $dto = UpdatePostDTO::fromRequest($request->validated());
        $post = $this->service->updatePost($post, $dto, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Remove the specified post (soft delete)
     */
    public function destroy(string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $this->service->deletePost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Publish a draft post
     */
    public function publish(string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $post = $this->service->publishPost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post published successfully',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Unpublish a published post
     */
    public function unpublish(string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $post = $this->service->unpublishPost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post unpublished successfully',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Toggle pin status
     */
    public function togglePin(string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $post = $this->service->togglePin($post);

        return response()->json([
            'success' => true,
            'message' => 'Post pin status updated',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Mark post as viewed
     */
    public function markViewed(Request $request, string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $this->service->markAsViewed($post, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Post marked as viewed',
        ]);
    }

    /**
     * Upload image for rich text editor
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB max
            'post_uuid' => ['nullable', 'string', 'exists:posts,uuid'],
        ]);

        // If post_uuid provided, attach to existing post
        if ($request->has('post_uuid')) {
            $post = $this->repository->findByUuid($request->input('post_uuid'));
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found',
                ], 404);
            }
        } else {
            // Create temporary post for image upload
            // Images will be orphaned if post is not created
            $post = $this->repository->create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'title' => 'Temporary',
                'slug' => 'temporary-' . time(),
                'content' => '',
                'category' => 'information',
                'status' => 'draft',
                'author_id' => $request->user()->id,
            ]);
        }

        $url = $this->service->uploadImage($post, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => [
                'url' => $url,
                'post_uuid' => $post->uuid,
            ],
        ]);
    }

    /**
     * Get trashed posts
     */
    public function trash(Request $request): JsonResponse
    {
        $posts = $this->repository->getTrashedPosts(
            perPage: $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'message' => 'Trashed posts retrieved successfully',
            'data' => PostListResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'from' => $posts->firstItem(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'to' => $posts->lastItem(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Restore trashed post
     */
    public function restore(string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $this->service->restorePost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post restored successfully',
        ]);
    }

    /**
     * Permanently delete post
     */
    public function forceDelete(string $uuid): JsonResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $this->service->forceDeletePost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post permanently deleted',
        ]);
    }

    /**
     * Bulk delete posts
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'post_uuids' => ['required', 'array', 'min:1'],
            'post_uuids.*' => ['required', 'string', 'exists:posts,uuid'],
        ]);

        $this->service->bulkDelete($request->input('post_uuids'));

        return response()->json([
            'success' => true,
            'message' => 'Bulk delete job dispatched successfully',
        ]);
    }

    /**
     * Bulk publish posts
     */
    public function bulkPublish(Request $request): JsonResponse
    {
        $request->validate([
            'post_uuids' => ['required', 'array', 'min:1'],
            'post_uuids.*' => ['required', 'string', 'exists:posts,uuid'],
        ]);

        $this->service->bulkPublish($request->input('post_uuids'));

        return response()->json([
            'success' => true,
            'message' => 'Bulk publish job dispatched successfully',
        ]);
    }
}
```


### 7. Form Requests

#### StorePostRequest (`app/Http/Requests/StorePostRequest.php`)
```php
<?php

namespace Modules\Notifications\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', 'in:announcement,information,warning,system,award,gamification'],
            'status' => ['required', 'string', 'in:draft,published'],
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['required', 'string', 'in:student,instructor,admin'],
            'notification_channels' => ['nullable', 'array'],
            'notification_channels.*' => ['required', 'string', 'in:email,in_app,push'],
            'is_pinned' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Post title is required',
            'content.required' => 'Post content is required',
            'category.required' => 'Category is required',
            'category.in' => 'Invalid category selected',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected',
            'audiences.required' => 'At least one target audience is required',
            'audiences.min' => 'At least one target audience is required',
            'audiences.*.in' => 'Invalid audience role selected',
            'notification_channels.*.in' => 'Invalid notification channel selected',
        ];
    }
}
```

#### UpdatePostRequest (`app/Http/Requests/UpdatePostRequest.php`)
```php
<?php

namespace Modules\Notifications\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'in:announcement,information,warning,system,award,gamification'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'audiences' => ['nullable', 'array', 'min:1'],
            'audiences.*' => ['required', 'string', 'in:student,instructor,admin'],
            'notification_channels' => ['nullable', 'array'],
            'notification_channels.*' => ['required', 'string', 'in:email,in_app,push'],
            'is_pinned' => ['nullable', 'boolean'],
            'resend_notification_channels' => ['nullable', 'array'],
            'resend_notification_channels.*' => ['required', 'string', 'in:email,in_app,push'],
        ];
    }
}
```

### 8. Resources

#### PostResource (`app/Http/Resources/PostResource.php`)
```php
<?php

namespace Modules\Notifications\app\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'category' => [
                'value' => $this->category,
                'label' => ucfirst($this->category),
            ],
            'status' => [
                'value' => $this->status,
                'label' => ucfirst($this->status),
            ],
            'is_pinned' => $this->is_pinned,
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'username' => $this->author->username,
            ],
            'last_editor' => $this->lastEditor ? [
                'id' => $this->lastEditor->id,
                'name' => $this->lastEditor->name,
                'username' => $this->lastEditor->username,
            ] : null,
            'audiences' => $this->audiences->pluck('role')->toArray(),
            'notification_channels' => $this->notifications->pluck('channel')->toArray(),
            'view_count' => $this->views->count(),
            'images' => $this->getMedia('images')->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->name,
                'size' => $media->size,
            ]),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

#### PostListResource (`app/Http/Resources/PostListResource.php`)
```php
<?php

namespace Modules\Notifications\app\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'category' => [
                'value' => $this->category,
                'label' => ucfirst($this->category),
            ],
            'status' => [
                'value' => $this->status,
                'label' => ucfirst($this->status),
            ],
            'is_pinned' => $this->is_pinned,
            'author' => [
                'name' => $this->author->name,
                'username' => $this->author->username,
            ],
            'audiences' => $this->audiences->pluck('role')->toArray(),
            'published_at' => $this->published_at?->format('M d, Y'),
            'created_at' => $this->created_at->format('M d, Y'),
        ];
    }
}
```

### 9. Jobs

#### SendPostNotificationJob (`app/Jobs/SendPostNotificationJob.php`)
```php
<?php

namespace Modules\Notifications\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Notifications\app\Models\Post;
use Modules\Auth\app\Models\User;
use Modules\Notifications\app\Notifications\PostPublishedNotification;

class SendPostNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Post $post,
        private array $channels,
        private array $audiences
    ) {}

    public function handle(): void
    {
        // Get users based on audiences
        $users = User::whereIn('role', $this->audiences)->get();

        // Send notifications to each user
        foreach ($users as $user) {
            $user->notify(new PostPublishedNotification($this->post, $this->channels));
        }

        // Mark notifications as sent
        foreach ($this->channels as $channel) {
            $this->post->notifications()
                ->where('channel', $channel)
                ->update(['sent_at' => now()]);
        }
    }
}
```

#### BulkDeletePostsJob (`app/Jobs/BulkDeletePostsJob.php`)
```php
<?php

namespace Modules\Notifications\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Notifications\app\Repositories\PostRepository;
use Illuminate\Support\Facades\Log;

class BulkDeletePostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $postUuids
    ) {}

    public function handle(PostRepository $repository): void
    {
        Log::info('Starting bulk delete job', ['count' => count($this->postUuids)]);

        $successCount = 0;
        $failCount = 0;

        foreach ($this->postUuids as $uuid) {
            try {
                $post = $repository->findByUuid($uuid);
                if ($post) {
                    $repository->delete($post);
                    $successCount++;
                }
            } catch (\Exception $e) {
                $failCount++;
                Log::error('Failed to delete post in bulk operation', [
                    'uuid' => $uuid,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk delete job completed', [
            'success' => $successCount,
            'failed' => $failCount,
        ]);
    }
}
```

#### BulkPublishPostsJob (`app/Jobs/BulkPublishPostsJob.php`)
```php
<?php

namespace Modules\Notifications\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Notifications\app\Services\PostService;
use Modules\Notifications\app\Repositories\PostRepository;
use Illuminate\Support\Facades\Log;

class BulkPublishPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $postUuids
    ) {}

    public function handle(PostRepository $repository, PostService $service): void
    {
        Log::info('Starting bulk publish job', ['count' => count($this->postUuids)]);

        $successCount = 0;
        $failCount = 0;

        foreach ($this->postUuids as $uuid) {
            try {
                $post = $repository->findByUuid($uuid);
                if ($post && $post->status === 'draft') {
                    $service->publishPost($post);
                    $successCount++;
                }
            } catch (\Exception $e) {
                $failCount++;
                Log::error('Failed to publish post in bulk operation', [
                    'uuid' => $uuid,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk publish job completed', [
            'success' => $successCount,
            'failed' => $failCount,
        ]);
    }
}
```

### 10. Notifications

#### PostPublishedNotification (`app/Notifications/PostPublishedNotification.php`)
```php
<?php

namespace Modules\Notifications\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Notifications\app\Models\Post;

class PostPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Post $post,
        private array $channels
    ) {}

    public function via($notifiable): array
    {
        $via = [];
        
        if (in_array('email', $this->channels)) {
            $via[] = 'mail';
        }
        
        if (in_array('in_app', $this->channels)) {
            $via[] = 'database';
        }
        
        // Add push notification channel if needed
        // if (in_array('push', $this->channels)) {
        //     $via[] = 'fcm';
        // }
        
        return $via;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->post->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->post->title)
            ->line(strip_tags($this->post->content))
            ->action('View Post', url('/posts/' . $this->post->uuid))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable): array
    {
        return [
            'post_id' => $this->post->uuid,
            'title' => $this->post->title,
            'category' => $this->post->category,
            'content' => strip_tags($this->post->content),
        ];
    }
}
```

---

## 🌐 API ENDPOINTS

### Admin Endpoints

```
POST   /api/v1/admin/posts                    - Create new post
GET    /api/v1/admin/posts                    - List all posts (with filters)
GET    /api/v1/admin/posts/{uuid}             - Get post detail
PUT    /api/v1/admin/posts/{uuid}             - Update post
DELETE /api/v1/admin/posts/{uuid}             - Delete post (soft delete)
POST   /api/v1/admin/posts/{uuid}/publish     - Publish draft post
POST   /api/v1/admin/posts/{uuid}/unpublish   - Unpublish post
POST   /api/v1/admin/posts/{uuid}/toggle-pin  - Toggle pin status
GET    /api/v1/admin/posts/drafts             - List draft posts
POST   /api/v1/admin/posts/bulk-delete        - Bulk delete posts
POST   /api/v1/admin/posts/bulk-publish       - Bulk publish drafts
```

### Shared Endpoints (All Users)

```
GET    /api/v1/posts                          - List published posts (filtered by role)
GET    /api/v1/posts/{uuid}                   - Get post detail
POST   /api/v1/posts/{uuid}/view              - Mark post as viewed
GET    /api/v1/posts/pinned                   - Get pinned posts
```

### Routes File (`routes/api.php`)
```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\app\Http\Controllers\PostController;

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{uuid}/publish', [PostController::class, 'publish']);
    Route::post('posts/{uuid}/unpublish', [PostController::class, 'unpublish']);
    Route::post('posts/{uuid}/toggle-pin', [PostController::class, 'togglePin']);
    Route::get('posts/drafts', [PostController::class, 'drafts']);
    Route::post('posts/bulk-delete', [PostController::class, 'bulkDelete']);
    Route::post('posts/bulk-publish', [PostController::class, 'bulkPublish']);
});

// Shared routes (all authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{uuid}', [PostController::class, 'show']);
    Route::post('posts/{uuid}/view', [PostController::class, 'markViewed']);
    Route::get('posts/pinned', [PostController::class, 'pinned']);
});
```

---

## 🕐 SCHEDULED POSTS FEATURE

### 1. Console Command untuk Auto-Publish

#### PublishScheduledPostsCommand (`app/Console/Commands/PublishScheduledPostsCommand.php`)
```php
<?php

namespace Modules\Notifications\app\Console\Commands;

use Illuminate\Console\Command;
use Modules\Notifications\app\Services\PostService;
use Modules\Notifications\app\Repositories\PostRepository;

class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'posts:publish-scheduled';
    protected $description = 'Publish scheduled posts that are due';

    public function __construct(
        private PostRepository $repository,
        private PostService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking for scheduled posts to publish...');

        $posts = $this->repository->getPendingScheduledPosts();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts to publish.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($posts as $post) {
            try {
                $this->service->publishScheduledPost($post);
                $this->info("Published: {$post->title}");
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to publish {$post->title}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully published {$count} post(s).");
        return self::SUCCESS;
    }
}
```

### 2. Update PostRepository

Tambahkan method untuk mendapatkan scheduled posts:

```php
public function getPendingScheduledPosts(): Collection
{
    return $this->model->pendingPublish()
        ->with(['author', 'audiences', 'notifications'])
        ->get();
}

public function getScheduledPosts(?string $role = null): LengthAwarePaginator
{
    $query = $this->model->scheduled()
        ->with(['author', 'audiences'])
        ->orderBy('scheduled_at', 'asc');

    if ($role) {
        $query->forRole($role);
    }

    return $query->paginate(15);
}
```

### 3. Update PostService

Tambahkan method untuk scheduled posts:

```php
public function schedulePost(Post $post, string $scheduledAt): Post
{
    $this->repository->update($post, [
        'status' => 'scheduled',
        'scheduled_at' => $scheduledAt,
        'published_at' => null,
    ]);

    return $post->fresh();
}

public function publishScheduledPost(Post $post): Post
{
    if ($post->status !== 'scheduled') {
        throw new \Exception('Post is not scheduled');
    }

    if (!$post->scheduled_at || $post->scheduled_at->isFuture()) {
        throw new \Exception('Post is not ready to be published');
    }

    $this->repository->update($post, [
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->sendNotifications($post);

    return $post->fresh();
}

public function cancelSchedule(Post $post): Post
{
    $this->repository->update($post, [
        'status' => 'draft',
        'scheduled_at' => null,
    ]);

    return $post->fresh();
}
```

### 4. Update CreatePost Method

```php
public function createPost(CreatePostDTO $dto, int $authorId): Post
{
    return DB::transaction(function () use ($dto, $authorId) {
        // Validate scheduled_at if status is scheduled
        if ($dto->status === 'scheduled' && !$dto->scheduledAt) {
            throw new \InvalidArgumentException('Scheduled date is required for scheduled posts');
        }

        // Create post
        $post = $this->repository->create([
            'uuid' => Str::uuid(),
            'title' => $dto->title,
            'slug' => Str::slug($dto->title),
            'content' => $dto->content,
            'category' => $dto->category,
            'status' => $dto->status,
            'is_pinned' => $dto->isPinned,
            'author_id' => $authorId,
            'scheduled_at' => $dto->status === 'scheduled' ? $dto->scheduledAt : null,
            'published_at' => $dto->status === 'published' ? now() : null,
        ]);

        // Attach audiences
        if (!empty($dto->audiences)) {
            foreach ($dto->audiences as $role) {
                $post->audiences()->create(['role' => $role]);
            }
        }

        // Store notification channels
        if (!empty($dto->notificationChannels)) {
            foreach ($dto->notificationChannels as $channel) {
                $post->notifications()->create(['channel' => $channel]);
            }
        }

        // Send notifications if published immediately
        if ($dto->status === 'published') {
            $this->sendNotifications($post);
        }

        return $post->load(['author', 'audiences', 'notifications']);
    });
}
```

### 5. Update PostController

Tambahkan endpoints untuk scheduled posts:

```php
/**
 * Schedule a draft post
 */
public function schedule(Request $request, string $uuid): JsonResponse
{
    $request->validate([
        'scheduled_at' => ['required', 'date', 'after:now'],
    ]);

    $post = $this->repository->findByUuid($uuid);

    if (!$post) {
        return response()->json([
            'success' => false,
            'message' => 'Post not found',
        ], 404);
    }

    $post = $this->service->schedulePost($post, $request->input('scheduled_at'));

    return response()->json([
        'success' => true,
        'message' => 'Post scheduled successfully',
        'data' => new PostResource($post),
    ]);
}

/**
 * Cancel scheduled post
 */
public function cancelSchedule(string $uuid): JsonResponse
{
    $post = $this->repository->findByUuid($uuid);

    if (!$post) {
        return response()->json([
            'success' => false,
            'message' => 'Post not found',
        ], 404);
    }

    $post = $this->service->cancelSchedule($post);

    return response()->json([
        'success' => true,
        'message' => 'Schedule cancelled successfully',
        'data' => new PostResource($post),
    ]);
}

/**
 * Get scheduled posts
 */
public function scheduled(Request $request): JsonResponse
{
    $posts = $this->repository->getScheduledPosts($request->user()->role);

    return response()->json([
        'success' => true,
        'message' => 'Scheduled posts retrieved successfully',
        'data' => PostListResource::collection($posts),
        'meta' => [
            'current_page' => $posts->currentPage(),
            'from' => $posts->firstItem(),
            'last_page' => $posts->lastPage(),
            'per_page' => $posts->perPage(),
            'to' => $posts->lastItem(),
            'total' => $posts->total(),
        ],
    ]);
}
```

### 6. Update Form Requests

```php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'content' => ['required', 'string'],
        'category' => ['required', 'string', 'in:announcement,information,warning,system,award,gamification'],
        'status' => ['required', 'string', 'in:draft,scheduled,published'],
        'scheduled_at' => ['required_if:status,scheduled', 'nullable', 'date', 'after:now'],
        'audiences' => ['required', 'array', 'min:1'],
        'audiences.*' => ['required', 'string', 'in:student,instructor,admin'],
        'notification_channels' => ['nullable', 'array'],
        'notification_channels.*' => ['required', 'string', 'in:email,in_app,push'],
        'is_pinned' => ['nullable', 'boolean'],
    ];
}
```

### 7. Task Scheduling

Tambahkan di `app/Console/Kernel.php` atau `app/Providers/ConsoleServiceProvider.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Run every minute to check for scheduled posts
    $schedule->command('posts:publish-scheduled')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();
}
```

### 8. Update PostResource

```php
public function toArray($request): array
{
    return [
        'id' => $this->uuid,
        'title' => $this->title,
        'slug' => $this->slug,
        'content' => $this->content,
        'category' => [
            'value' => $this->category,
            'label' => ucfirst($this->category),
        ],
        'status' => [
            'value' => $this->status,
            'label' => ucfirst($this->status),
        ],
        'is_pinned' => $this->is_pinned,
        'author' => [
            'id' => $this->author->id,
            'name' => $this->author->name,
            'username' => $this->author->username,
        ],
        'audiences' => $this->audiences->pluck('role')->toArray(),
        'notification_channels' => $this->notifications->pluck('channel')->toArray(),
        'view_count' => $this->views->count(),
        'scheduled_at' => $this->scheduled_at?->toIso8601String(),
        'published_at' => $this->published_at?->toIso8601String(),
        'created_at' => $this->created_at->toIso8601String(),
        'updated_at' => $this->updated_at->toIso8601String(),
    ];
}
```

### 9. Event untuk Scheduled Posts

#### PostScheduledEvent (`app/Events/PostScheduledEvent.php`)
```php
<?php

namespace Modules\Notifications\app\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Notifications\app\Models\Post;

class PostScheduledEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Post $post
    ) {}
}
```

#### PostPublishedFromScheduleEvent (`app/Events/PostPublishedFromScheduleEvent.php`)
```php
<?php

namespace Modules\Notifications\app\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Notifications\app\Models\Post;

class PostPublishedFromScheduleEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Post $post
    ) {}
}
```

### 10. Logging untuk Scheduled Posts

Tambahkan logging di `PublishScheduledPostsCommand`:

```php
use Illuminate\Support\Facades\Log;

public function handle(): int
{
    Log::info('Starting scheduled posts publishing job');

    $posts = $this->repository->getPendingScheduledPosts();

    if ($posts->isEmpty()) {
        Log::info('No scheduled posts to publish');
        return self::SUCCESS;
    }

    Log::info("Found {$posts->count()} scheduled post(s) to publish");

    $count = 0;
    foreach ($posts as $post) {
        try {
            $this->service->publishScheduledPost($post);
            Log::info("Published scheduled post: {$post->title} (ID: {$post->uuid})");
            $count++;
        } catch (\Exception $e) {
            Log::error("Failed to publish scheduled post: {$post->title} (ID: {$post->uuid})", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    Log::info("Completed scheduled posts publishing: {$count} published");
    return self::SUCCESS;
}
```

---

## 🌐 API ENDPOINTS

### Admin Endpoints

```
POST   /api/v1/admin/posts                    - Create new post
GET    /api/v1/admin/posts                    - List all posts (with filters)
GET    /api/v1/admin/posts/{uuid}             - Get post detail
PUT    /api/v1/admin/posts/{uuid}             - Update post
DELETE /api/v1/admin/posts/{uuid}             - Delete post (soft delete)
POST   /api/v1/admin/posts/{uuid}/publish     - Publish draft post
POST   /api/v1/admin/posts/{uuid}/unpublish   - Unpublish post
POST   /api/v1/admin/posts/{uuid}/schedule    - Schedule post for later
POST   /api/v1/admin/posts/{uuid}/cancel-schedule - Cancel scheduled post
POST   /api/v1/admin/posts/{uuid}/toggle-pin  - Toggle pin status
POST   /api/v1/admin/posts/upload-image       - Upload image for rich text editor
GET    /api/v1/admin/posts/trash              - List trashed posts
POST   /api/v1/admin/posts/{uuid}/restore     - Restore trashed post
DELETE /api/v1/admin/posts/{uuid}/force       - Permanently delete post
POST   /api/v1/admin/posts/bulk-delete        - Bulk delete posts (queued)
POST   /api/v1/admin/posts/bulk-publish       - Bulk publish drafts (queued)
GET    /api/v1/admin/posts/drafts             - List draft posts
GET    /api/v1/admin/posts/scheduled          - List scheduled posts
```

### Shared Endpoints (All Users)

```
GET    /api/v1/posts                          - List published posts (filtered by role)
GET    /api/v1/posts/{uuid}                   - Get post detail
POST   /api/v1/posts/{uuid}/view              - Mark post as viewed
GET    /api/v1/posts/pinned                   - Get pinned posts
```

### Routes File (`routes/api.php`)
```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\app\Http\Controllers\PostController;

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Image upload
    Route::post('posts/upload-image', [PostController::class, 'uploadImage']);
    
    // Trash management
    Route::get('posts/trash', [PostController::class, 'trash']);
    Route::post('posts/{uuid}/restore', [PostController::class, 'restore']);
    Route::delete('posts/{uuid}/force', [PostController::class, 'forceDelete']);
    
    // Bulk operations
    Route::post('posts/bulk-delete', [PostController::class, 'bulkDelete']);
    Route::post('posts/bulk-publish', [PostController::class, 'bulkPublish']);
    
    // Scheduled posts
    Route::get('posts/scheduled', [PostController::class, 'scheduled']);
    Route::post('posts/{uuid}/schedule', [PostController::class, 'schedule']);
    Route::post('posts/{uuid}/cancel-schedule', [PostController::class, 'cancelSchedule']);
    
    // Drafts
    Route::get('posts/drafts', [PostController::class, 'drafts']);
    
    // Standard CRUD
    Route::apiResource('posts', PostController::class);
    
    // Post actions
    Route::post('posts/{uuid}/publish', [PostController::class, 'publish']);
    Route::post('posts/{uuid}/unpublish', [PostController::class, 'unpublish']);
    Route::post('posts/{uuid}/toggle-pin', [PostController::class, 'togglePin']);
});

// Shared routes (all authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{uuid}', [PostController::class, 'show']);
    Route::post('posts/{uuid}/view', [PostController::class, 'markViewed']);
    Route::get('posts/pinned', [PostController::class, 'pinned']);
});
```

---

## 📅 TIMELINE & PRIORITAS

### Phase 1: Backend Foundation (Week 1)
- ✅ Database migrations (dengan scheduled_at field)
- ✅ Models & relationships
- ✅ Enums (termasuk SCHEDULED status)
- ✅ DTOs (dengan scheduledAt parameter)
- ✅ Repositories

### Phase 2: Backend Business Logic (Week 2)
- ✅ Services (termasuk schedule methods)
- ✅ Controllers (termasuk schedule endpoints)
- ✅ Form Requests (dengan scheduled_at validation)
- ✅ Resources (dengan scheduled_at field)
- ✅ Jobs & Notifications
- ✅ Routes

### Phase 3: Scheduled Posts Feature (Week 3)
- ✅ Console Command (PublishScheduledPostsCommand)
- ✅ Task Scheduler configuration
- ✅ Events untuk scheduled posts
- ✅ Logging system
- ✅ Testing scheduled publishing

### Phase 4: Testing & Polish (Week 4)
- ✅ Unit tests
- ✅ Integration tests
- ✅ Scheduled posts tests
- ✅ Bug fixes
- ✅ Performance optimization

---

## 🧪 TESTING STRATEGY

### Backend Tests

#### Unit Tests
```php
// tests/Unit/PostServiceTest.php
- testCreatePost()
- testCreateScheduledPost()
- testUpdatePost()
- testDeletePost()
- testPublishPost()
- testUnpublishPost()
- testSchedulePost()
- testPublishScheduledPost()
- testCancelSchedule()
- testTogglePin()
```

#### Feature Tests
```php
// tests/Feature/PostControllerTest.php
- testAdminCanCreatePost()
- testAdminCanCreateScheduledPost()
- testAdminCanUpdatePost()
- testAdminCanDeletePost()
- testAdminCanPublishPost()
- testAdminCanSchedulePost()
- testAdminCanCancelSchedule()
- testScheduledPostCannotBePublishedBeforeTime()
- testStudentCannotCreatePost()
- testUserCanViewPublishedPosts()
- testUserCannotViewScheduledPosts()
```

#### Console Command Tests
```php
// tests/Feature/PublishScheduledPostsCommandTest.php
- testPublishesScheduledPostsWhenDue()
- testDoesNotPublishFutureScheduledPosts()
- testHandlesMultipleScheduledPosts()
- testHandlesErrorsGracefully()
- testLogsPublishingActivity()
```

---

## 📝 CATATAN IMPLEMENTASI

### Dependencies & Installation

#### 1. Spatie Media Library
```bash
# Install package
composer require spatie/laravel-medialibrary

# Publish migration
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"

# Run migration
php artisan migrate

# Publish config (optional)
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
```

#### 2. Redis Configuration
Pastikan Redis sudah terinstall dan dikonfigurasi di `.env`:
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### 3. Queue Configuration
Untuk bulk operations, pastikan queue worker berjalan:
```bash
# Development
php artisan queue:work

# Production (dengan supervisor)
php artisan queue:work --queue=default --tries=3 --timeout=90
```

### Media Management Configuration

#### Storage Disk Configuration (`config/filesystems.php`)
```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

#### Media Library Configuration (`config/media-library.php`)
```php
return [
    'disk_name' => 'public',
    
    'max_file_size' => 1024 * 1024 * 5, // 5MB
    
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,
    
    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '--max=85',
            '--strip-all',
            '--all-progressive',
        ],
        Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--force',
        ],
    ],
    
    'image_generators' => [
        Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
    ],
];
```

#### Allowed Image Types
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)

#### Image Optimization
Spatie Media Library akan otomatis mengoptimasi gambar yang diupload menggunakan:
- jpegoptim untuk JPEG
- pngquant untuk PNG
- optipng untuk PNG
- gifsicle untuk GIF

### Redis Caching Strategy

#### Cache Keys Pattern
```
posts:list:{status}:{category}:{role}:page:{page_number}
posts:pinned:{role}
```

#### Cache TTL
- List cache: 1 hour (3600 seconds)
- Pinned posts cache: 1 hour (3600 seconds)

#### Cache Invalidation
Cache akan di-clear otomatis saat:
- Post dibuat
- Post diupdate
- Post didelete
- Post dipublish/unpublish
- Post di-pin/unpin

### Kebutuhan Tambahan
1. Task scheduler (Laravel Scheduler) - sudah built-in
2. Queue system untuk async notifications (Redis/Database)
3. Logging system untuk monitoring scheduled posts
4. Email templates untuk notifications
5. Soft delete management (Trash module integration)
6. Timezone handling untuk scheduled_at

### Pertimbangan Keamanan
1. Authorization policies untuk CRUD operations
2. XSS protection untuk rich text content
3. Rate limiting untuk API endpoints
4. Input sanitization
5. Validation untuk scheduled_at (must be future date)
6. Permission check untuk schedule/cancel operations
7. File upload validation (type, size, mime)
8. Image upload rate limiting

### Optimasi Performa
1. Database indexing (termasuk scheduled_at, last_editor_id)
2. Query optimization dengan eager loading
3. Redis caching untuk frequently accessed posts
4. Pagination untuk large datasets
5. Queue untuk notification sending
6. Efficient scheduled posts query (only pending)
7. Bulk operations dengan chunking
8. Image optimization dengan Spatie Media Library

### Monitoring & Logging
1. Log setiap scheduled post yang dipublish
2. Alert jika scheduled publishing gagal
3. Dashboard untuk monitoring scheduled posts
4. Metrics untuk success/failure rate
5. Log bulk operations progress
6. Monitor cache hit/miss ratio

### Timezone Considerations
1. Store scheduled_at in UTC
2. Display in user's timezone
3. Validate timezone in requests
4. Handle daylight saving time

### Integration dengan Existing Notification System
Lihat dokumen `INTEGRATION_GUIDE.md` dan `QUICK_INTEGRATION_GUIDE.md` untuk detail integrasi dengan sistem notifikasi yang sudah ada.

Key points:
- Gunakan `NotificationSender` service sebagai bridge
- Jangan modifikasi `NotificationService` yang sudah ada
- Post notifications bersifat manual (admin-created)
- Existing notifications tetap otomatis (system-generated)

---

**Dokumen ini akan diupdate seiring progress implementasi.**
