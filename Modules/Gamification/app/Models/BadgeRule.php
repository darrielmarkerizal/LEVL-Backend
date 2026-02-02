<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeRule extends Model
{
    protected $table = 'badge_rules';

    protected $fillable = [
        'badge_id',
        'criterion',
        'operator',
        'value',
    ];

    protected $casts = [
        'badge_id' => 'integer',
        'value' => 'integer',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
