<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'channel',
        'enabled',
        'frequency',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    // Categories
    const CATEGORY_COURSE_UPDATES = 'course_updates';

    const CATEGORY_ASSIGNMENTS = 'assignments';

    const CATEGORY_ASSESSMENTS = 'assessments';

    const CATEGORY_FORUM = 'forum';

    const CATEGORY_ACHIEVEMENTS = 'achievements';

    const CATEGORY_SYSTEM = 'system';

    // Channels
    const CHANNEL_EMAIL = 'email';

    const CHANNEL_IN_APP = 'in_app';

    const CHANNEL_PUSH = 'push';

    // Frequency
    const FREQUENCY_IMMEDIATE = 'immediate';

    const FREQUENCY_DAILY = 'daily';

    const FREQUENCY_WEEKLY = 'weekly';

    /**
     * Get all available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_COURSE_UPDATES,
            self::CATEGORY_ASSIGNMENTS,
            self::CATEGORY_ASSESSMENTS,
            self::CATEGORY_FORUM,
            self::CATEGORY_ACHIEVEMENTS,
            self::CATEGORY_SYSTEM,
        ];
    }

    /**
     * Get all available channels.
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_EMAIL,
            self::CHANNEL_IN_APP,
            self::CHANNEL_PUSH,
        ];
    }

    /**
     * Get all available frequencies.
     */
    public static function getFrequencies(): array
    {
        return [
            self::FREQUENCY_IMMEDIATE,
            self::FREQUENCY_DAILY,
            self::FREQUENCY_WEEKLY,
        ];
    }

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
