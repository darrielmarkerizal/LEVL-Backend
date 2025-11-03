<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'login_activities';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'status',
        'logged_in_at',
        'logged_out_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'user_id' => 'integer',
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    /**
     * Get the user that owns the login activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include successful logins.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed logins.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include active sessions (logged in but not logged out).
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('logged_in_at')
            ->whereNull('logged_out_at');
    }

    /**
     * Check if the login activity is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if the login activity is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the session is active.
     */
    public function isActive(): bool
    {
        return $this->logged_in_at !== null && $this->logged_out_at === null;
    }

    /**
     * Mark as logged out.
     */
    public function markAsLoggedOut(): void
    {
        $this->update(['logged_out_at' => now()]);
    }
}

