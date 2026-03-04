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
    ];

    protected $casts = [
        'badge_id' => 'integer',
        'conditions' => 'array',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
