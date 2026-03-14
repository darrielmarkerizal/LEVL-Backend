<?php

declare(strict_types=1);

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpDailyCap extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'total_xp_earned',
        'global_daily_cap',
        'cap_reached',
        'cap_reached_at',
        'xp_by_source',
    ];

    protected $casts = [
        'date' => 'date',
        'total_xp_earned' => 'integer',
        'global_daily_cap' => 'integer',
        'cap_reached' => 'boolean',
        'cap_reached_at' => 'datetime',
        'xp_by_source' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function hasReachedCap(): bool
    {
        return $this->total_xp_earned >= $this->global_daily_cap;
    }

    public function getRemainingXp(): int
    {
        return max(0, $this->global_daily_cap - $this->total_xp_earned);
    }

    public function incrementXp(int $xp, string $source): void
    {
        $this->total_xp_earned += $xp;
        
        // Track XP by source
        $xpBySource = $this->xp_by_source ?? [];
        $xpBySource[$source] = ($xpBySource[$source] ?? 0) + $xp;
        $this->xp_by_source = $xpBySource;
        
        // Check if cap reached
        if ($this->hasReachedCap() && !$this->cap_reached) {
            $this->cap_reached = true;
            $this->cap_reached_at = now();
        }
        
        $this->save();
    }
}
