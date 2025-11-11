<?php

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'audit_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event',
        'target_type',
        'target_id',
        'actor_type',
        'actor_id',
        'user_id',
        'properties',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'properties' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * Get the target entity that was affected.
     */
    public function target(): MorphTo
    {
        return $this->morphTo('target');
    }

    /**
     * Get the actor that performed the action.
     */
    public function actor(): MorphTo
    {
        return $this->morphTo('actor');
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }

    /**
     * Scope a query to only include logs for a specific event.
     */
    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope a query to only include logs for a specific target.
     */
    public function scopeForTarget($query, $model)
    {
        return $query->where('target_type', get_class($model))
            ->where('target_id', $model->getKey());
    }

    /**
     * Scope a query to only include logs for a specific actor.
     */
    public function scopeForActor($query, $model)
    {
        return $query->where('actor_type', get_class($model))
            ->where('actor_id', $model->getKey());
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser($query, $user)
    {
        $userId = is_object($user) ? $user->id : $user;
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('logged_at', [$startDate, $endDate]);
    }

    /**
     * Log an event.
     */
    public static function log(
        string $event,
        $target = null,
        $actor = null,
        array $properties = []
    ): self {
        return static::create([
            'event' => $event,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target ? $target->getKey() : null,
            'actor_type' => $actor ? get_class($actor) : null,
            'actor_id' => $actor ? $actor->getKey() : null,
            'user_id' => Auth::check() ? Auth::id() : null,
            'properties' => $properties,
            'logged_at' => now(),
        ]);
    }
}

