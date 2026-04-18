<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;

if (! function_exists('notifications_content_type_label')) {
    /**
     * Localized label for content workflow models (Announcement, News, …).
     */
    function notifications_content_type_label(object|string $content): string
    {
        $basename = is_string($content) ? $content : class_basename($content);
        $key = 'notifications.content.types.'.strtolower($basename);

        return Lang::has($key) ? trans($key) : trans('notifications.content.types.default');
    }
}
