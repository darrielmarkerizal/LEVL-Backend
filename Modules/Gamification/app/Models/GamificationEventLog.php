<?php

declare(strict_types=1);

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class GamificationEventLog extends Model
{
    public $timestamps = false;

    protected $table = 'gamification_event_logs';

    protected $fillable = [
        'user_id',
        'event_type',
        'source_type',
        'source_id',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'source_id' => 'integer',
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
