<?php

declare(strict_types=1);

namespace Modules\Schemes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UnitContent extends Model
{
    protected $table = 'unit_contents';

    protected $fillable = [
        'unit_id',
        'contentable_type',
        'contentable_id',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeBeforeOrder($query, int $order)
    {
        return $query->where('order', '<', $order);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('contentable_type', $type);
    }
}
