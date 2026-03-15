<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\Gamification\Enums\PointReason;
use Modules\Gamification\Enums\PointSourceType;

class Point extends Model
{
    protected $table = 'points';

    protected $fillable = [
        'user_id',
        'source_type',
        'source_id',
        'points',
        'reason',
        'description',
        'xp_source_code',
        'old_level',
        'new_level',
        'triggered_level_up',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'source_id' => 'integer',
        'points' => 'integer',
        'old_level' => 'integer',
        'new_level' => 'integer',
        'triggered_level_up' => 'boolean',
        'metadata' => 'array',
        'source_type' => PointSourceType::class,
        'reason' => PointReason::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }
}
