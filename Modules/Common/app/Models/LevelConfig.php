<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Common\Traits\PgSearchable;

class LevelConfig extends Model
{
    use PgSearchable;

    protected array $searchable_columns = [
        'name',
        'description',
    ];

    protected $table = 'level_configs';

    protected $fillable = [
        'level',
        'name',
        'xp_required',
        'rewards',
        'milestone_badge_id',
        'bonus_xp',
    ];

    protected $casts = [
        'level' => 'integer',
        'xp_required' => 'integer',
        'rewards' => 'array',
        'milestone_badge_id' => 'integer',
        'bonus_xp' => 'integer',
    ];

    /**
     * Get the milestone badge for this level
     */
    public function milestoneBadge(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Gamification\Models\Badge::class, 'milestone_badge_id');
    }
}
