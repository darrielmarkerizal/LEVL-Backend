<?php

declare(strict_types=1);

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeVersion extends Model
{
    protected $table = 'badge_versions';

    protected $fillable = [
        'badge_id',
        'version',
        'threshold',
        'rules',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'badge_id' => 'integer',
        'version' => 'integer',
        'threshold' => 'integer',
        'rules' => 'array',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>', now());
            });
    }

    public function scopeForBadge($query, int $badgeId)
    {
        return $query->where('badge_id', $badgeId);
    }
}
