<?php

return [
    
    'global_daily_xp_cap' => env('GAMIFICATION_GLOBAL_DAILY_XP_CAP', 10000),

    
    'level_formula' => [
        'base' => 100,
        'exponent' => 1.6,
    ],

    
    'transaction_logging' => [
        'enabled' => env('GAMIFICATION_TRANSACTION_LOGGING', true),
        'log_ip_address' => env('GAMIFICATION_LOG_IP', true),
        'log_user_agent' => env('GAMIFICATION_LOG_USER_AGENT', true),
    ],

    
    'anti_abuse' => [
        'default_cooldown_seconds' => 10,
        'default_daily_limit' => null,
        'default_daily_xp_cap' => 5000,
    ],
];
