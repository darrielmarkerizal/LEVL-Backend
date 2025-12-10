<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Default pagination values used throughout the application
    |
    */

    'default' => env('PAGINATION_DEFAULT', 15),
    'max_per_page' => env('PAGINATION_MAX_PER_PAGE', 100),
    'leaderboard_default' => env('PAGINATION_LEADERBOARD_DEFAULT', 10),
    'forum_default' => env('PAGINATION_FORUM_DEFAULT', 20),
    'notifications_default' => env('PAGINATION_NOTIFICATIONS_DEFAULT', 15),
];
