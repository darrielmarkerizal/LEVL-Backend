<?php

declare(strict_types=1);

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class BadgeRuleCooldown extends Model
{
    protected $table = 'badge_rule_cooldowns';

    protected $fillable = [
        'user_id',
        'badge_rule_id',
        'last_evaluated_at',
        'can_evaluate_after',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'badge_rule_id' => 'integer',
        'last_evaluated_at' => 'datetime',
        'can_evaluate_after' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badgeRule(): BelongsTo
    {
        return $this->belongsTo(BadgeRule::class);
    }

    public function canEvaluate(): bool
    {
        return now()->greaterThanOrEqualTo($this->can_evaluate_after);
    }

    public function scopeExpired($query)
    {
        return $query->where('can_evaluate_after', '<=', now());
    }
}
