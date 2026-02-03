<?php

declare(strict_types=1);

namespace Modules\Forums\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Thread extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected static function newFactory()
    {
        return \Modules\Forums\Database\Factories\ThreadFactory::new();
    }

    protected $fillable = [
        'forumable_type',
        'forumable_id',
        'author_id',
        'title',
        'content',
        'is_pinned',
        'is_closed',
        'is_resolved',
        'views_count',
        'replies_count',
        'last_activity_at',
        'edited_at',
        'deleted_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_closed' => 'boolean',
        'is_resolved' => 'boolean',
        'views_count' => 'integer',
        'replies_count' => 'integer',
        'last_activity_at' => 'datetime',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function forumable(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'author_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'deleted_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function scopeForumable($query, string $type, int $id)
    {
        return $query->where('forumable_type', $type)->where('forumable_id', $id);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $this->scopeForumable($query, \Modules\Schemes\Models\Course::class, $courseId);
    }

    public function scopeForScheme($query, int $schemeId)
    {
        return $this->scopeForCourse($query, $schemeId);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function isPinned(): bool
    {
        return $this->is_pinned;
    }

    public function isClosed(): bool
    {
        return $this->is_closed;
    }

    public function isResolved(): bool
    {
        return $this->is_resolved;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'forumable_type' => $this->forumable_type,
            'forumable_id' => $this->forumable_id,
            'author_id' => $this->author_id,
        ];
    }
