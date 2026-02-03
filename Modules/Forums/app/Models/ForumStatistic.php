<?php

namespace Modules\Forums\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Schemes\Models\Course;

class ForumStatistic extends Model
{
    use HasFactory;

     
    protected static function newFactory()
    {
        return \Modules\Forums\Database\Factories\ForumStatisticFactory::new();
    }

    protected $fillable = [
        'scheme_id',
        'user_id',
        'threads_count',
        'replies_count',
        'views_count',
        'avg_response_time_minutes',
        'response_rate',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'threads_count' => 'integer',
        'replies_count' => 'integer',
        'views_count' => 'integer',
        'avg_response_time_minutes' => 'integer',
        'response_rate' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

     
    public function scheme(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'scheme_id');
    }

     
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }

     
    public function scopeForScheme($query, int $schemeId)
    {
        return $query->where('scheme_id', $schemeId);
    }

     
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

     
    public function scopeSchemeWide($query)
    {
        return $query->whereNull('user_id');
    }

     
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate);
    }
}
