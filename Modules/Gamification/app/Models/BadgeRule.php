<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeRule extends Model
{
    protected $table = 'badge_rules';

    protected $fillable = [
        'badge_id',
        'event_trigger',
        'conditions',
        'priority',
        'cooldown_seconds',
        'progress_window',
        'rule_enabled',
    ];

    protected $casts = [
        'badge_id' => 'integer',
        'conditions' => 'array',
        'priority' => 'integer',
        'cooldown_seconds' => 'integer',
        'rule_enabled' => 'boolean',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
