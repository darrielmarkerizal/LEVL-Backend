<?php

declare(strict_types=1);

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;

class XpSource extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'xp_amount',
        'cooldown_seconds',
        'daily_limit',
        'daily_xp_cap',
        'allow_multiple',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'xp_amount' => 'integer',
        'cooldown_seconds' => 'integer',
        'daily_limit' => 'integer',
        'daily_xp_cap' => 'integer',
        'allow_multiple' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->whereRaw('"is_active" = true');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}
