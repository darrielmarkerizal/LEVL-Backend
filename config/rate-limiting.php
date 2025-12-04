<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the rate limiting configuration for different types
    | of API endpoints. Each limiter defines the maximum number of requests
    | allowed within a decay period (in minutes).
    |
    */

    'api' => [
        /*
        |--------------------------------------------------------------------------
        | Default API Rate Limit
        |--------------------------------------------------------------------------
        |
        | Applied to all general API endpoints.
        | Default: 60 requests per minute
        |
        */
        'default' => [
            'max' => (int) env('RATE_LIMIT_API_DEFAULT_MAX', 60),
            'decay' => (int) env('RATE_LIMIT_API_DEFAULT_DECAY', 1),
        ],

        /*
        |--------------------------------------------------------------------------
        | Authentication Rate Limit
        |--------------------------------------------------------------------------
        |
        | Applied to authentication endpoints (login, register, password reset).
        | More restrictive to prevent brute force attacks.
        | Default: 10 requests per minute
        |
        */
        'auth' => [
            'max' => (int) env('RATE_LIMIT_AUTH_MAX', 10),
            'decay' => (int) env('RATE_LIMIT_AUTH_DECAY', 1),
        ],

        /*
        |--------------------------------------------------------------------------
        | Enrollment Rate Limit
        |--------------------------------------------------------------------------
        |
        | Applied to enrollment-related endpoints.
        | Restrictive to prevent enrollment abuse.
        | Default: 5 requests per minute
        |
        */
        'enrollment' => [
            'max' => (int) env('RATE_LIMIT_ENROLLMENT_MAX', 5),
            'decay' => (int) env('RATE_LIMIT_ENROLLMENT_DECAY', 1),
        ],
    ],

];
