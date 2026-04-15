<?php

namespace Modules\Notifications\Models;

use App\Models\Concerns\TracksTrashBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Auth\Models\User;
use Modules\Common\Traits\PgSearchable;
use Modules\Notifications\Enums\PostCategory;
use Modules\Notifications\Enums\PostStatus;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, PgSearchable, SoftDeletes, TracksTrashBin;

    protected array $searchable_columns = ['title', 'content'];

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
        'category' => PostCategory::class,
        'status' => PostStatus::class,
        'is_pinned' => 'boolean',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_editor_id');
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(PostAudience::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PostNotification::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    // Scopes

    public function scopePublished($query)
    {
        return $query->where('status', PostStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', PostStatus::DRAFT);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', PostStatus::SCHEDULED);
    }

    public function scopePendingPublish($query)
    {
        return $query->where('status', PostStatus::SCHEDULED)
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

    // Media Collections

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public');
    }
}
