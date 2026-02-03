<?php

namespace Modules\Forums\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reply extends Model
{
    use HasFactory, SoftDeletes;

     
    const MAX_DEPTH = 5;

     
    protected static function newFactory()
    {
        return \Modules\Forums\Database\Factories\ReplyFactory::new();
    }

    protected $fillable = [
        'thread_id',
        'parent_id',
        'author_id',
        'content',
        'depth',
        'is_accepted_answer',
        'edited_at',
        'deleted_by',
    ];

    protected $casts = [
        'depth' => 'integer',
        'is_accepted_answer' => 'boolean',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

     
    protected static function boot()
    {
        parent::boot();

        
        static::creating(function ($reply) {
            if ($reply->parent_id) {
                $parent = static::find($reply->parent_id);
                $reply->depth = $parent ? $parent->depth + 1 : 0;
            } else {
                $reply->depth = 0;
            }
        });
    }

     
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

     
    public function author(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'author_id');
    }

     
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Reply::class, 'parent_id');
    }

     
    public function children(): HasMany
    {
        return $this->hasMany(Reply::class, 'parent_id');
    }

     
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'deleted_by');
    }

     
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

     
    public function isAcceptedAnswer(): bool
    {
        return $this->is_accepted_answer;
    }

     
    public function getDepth(): int
    {
        return $this->depth;
    }

     
    public function canHaveChildren(): bool
    {
        return $this->depth < self::MAX_DEPTH;
    }

     
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

     
    public function scopeAccepted($query)
    {
        return $query->where('is_accepted_answer', true);
    }
}
