<?php

return [

    

    'name' => env('APP_NAME', 'Laravel'),

    

    'env' => env('APP_ENV', 'production'),

    

    'debug' => (bool) env('APP_DEBUG', false),

    

    'url' => env('APP_URL', 'http://localhost'),

    

    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    

    'timezone' => 'UTC',

    

    'locale' => env('APP_LOCALE', 'id'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'id'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'id_ID'),

    

    'supported_locales' => ['en', 'id'],

    

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    

    'lesson_block_max_upload_mb' => (int) env('LESSON_BLOCK_MAX_UPLOAD_MB', 50),

    

    'superadmin' => [
        'email' => env('SUPERADMIN_EMAIL', 'superadmin@example.com'),
        'name' => env('SUPERADMIN_NAME', 'Super Admin'),
        'username' => env('SUPERADMIN_USERNAME', 'superadmin'),
        'password' => env('SUPERADMIN_PASSWORD', 'supersecret'),
    ],

];
