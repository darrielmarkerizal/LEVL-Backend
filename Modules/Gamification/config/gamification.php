<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Global Daily XP Cap
    |--------------------------------------------------------------------------
    |
    | Maximum XP a user can earn per day across all sources.
    | This prevents users from leveling up too quickly.
    | Set to null for unlimited.
    |
    */
    'global_daily_xp_cap' => env('GAMIFICATION_GLOBAL_DAILY_XP_CAP', 10000),

    /*
    |--------------------------------------------------------------------------
    | Level Formula
    |--------------------------------------------------------------------------
    |
    | Formula used to calculate XP required for each level.
    | Current: XP(level) = 100 × level^1.6
    |
    */
    'level_formula' => [
        'base' => 100,
        'exponent' => 1.6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Logging
    |--------------------------------------------------------------------------
    |
    | Enable detailed transaction logging for audit and analytics.
    |
    */
    'transaction_logging' => [
        'enabled' => env('GAMIFICATION_TRANSACTION_LOGGING', true),
        'log_ip_address' => env('GAMIFICATION_LOG_IP', true),
        'log_user_agent' => env('GAMIFICATION_LOG_USER_AGENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-Abuse Settings
    |--------------------------------------------------------------------------
    |
    | Default anti-abuse settings if not configured in xp_sources table.
    |
    */
    'anti_abuse' => [
        'default_cooldown_seconds' => 10,
        'default_daily_limit' => null,
        'default_daily_xp_cap' => 5000,
    ],
];
