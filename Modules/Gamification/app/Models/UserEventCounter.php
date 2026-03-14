<?php

declare(strict_types=1);

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class UserEventCounter extends Model
{
    protected $table = 'user_event_counters';

    protected $fillable = [
        'user_id',
        'event_type',
        'scope_type',
        'scope_id',
        'counter',
        'window',
        'window_start',
        'window_end',
        'last_increment_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'scope_id' => 'integer',
        'counter' => 'integer',
        'window_start' => 'date',
        'window_end' => 'date',
        'last_increment_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->window_end && now()->greaterThan($this->window_end);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeForScope($query, ?string $scopeType, ?int $scopeId)
    {
        return $query->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId);
    }

    public function scopeForWindow($query, string $window)
    {
        return $query->where('window', $window);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('window_end')
                ->orWhere('window_end', '>=', now());
        });
    }
}
