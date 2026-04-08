<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Common\Traits\PgSearchable;

class Milestone extends Model
{
    use HasFactory, PgSearchable;

    protected $table = 'gamification_milestones';

    protected array $searchable_columns = [
        'name',
        'description',
        'code',
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'xp_required',
        'level_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'xp_required' => 'integer',
        'level_required' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
