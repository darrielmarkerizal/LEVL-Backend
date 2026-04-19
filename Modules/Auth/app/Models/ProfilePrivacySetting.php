<?php

declare(strict_types=1);

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilePrivacySetting extends Model
{
    const VISIBILITY_PUBLIC = 'public';

    const VISIBILITY_PRIVATE = 'private';

    const VISIBILITY_FRIENDS = 'friends_only';

    protected $fillable = [
        'user_id',
        'profile_visibility',
        'show_email',
        'show_phone',
        'show_activity_history',
        'show_achievements',
        'show_statistics',
    ];

    protected $casts = [
        'show_email' => 'boolean',
        'show_phone' => 'boolean',
        'show_activity_history' => 'boolean',
        'show_achievements' => 'boolean',
        'show_statistics' => 'boolean',
        'profile_visibility' => \Modules\Auth\Enums\ProfileVisibility::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPublic(): bool
    {
        return $this->profile_visibility === self::VISIBILITY_PUBLIC;
    }

    public function canShowField(string $field, User $viewer): bool
    {
        
        if ($viewer->hasRole('Admin') || $viewer->hasRole('Superadmin')) {
            return true;
        }

        
        if ($this->user_id === $viewer->id) {
            return true;
        }

        
        if ($this->profile_visibility === self::VISIBILITY_PRIVATE) {
            return false;
        }

        
        $fieldMap = [
            'email' => 'show_email',
            'phone' => 'show_phone',
            'activity_history' => 'show_activity_history',
            'achievements' => 'show_achievements',
            'statistics' => 'show_statistics',
        ];

        if (isset($fieldMap[$field])) {
            return (bool) $this->{$fieldMap[$field]};
        }

        
        return true;
    }
}
