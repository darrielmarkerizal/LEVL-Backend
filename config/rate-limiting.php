<?php

return [

    

    'api' => [
        
        'default' => [
            'max' => (int) env('RATE_LIMIT_API_DEFAULT_MAX', 60),
            'decay' => (int) env('RATE_LIMIT_API_DEFAULT_DECAY', 1),
        ],

        
        'auth' => [
            'max' => (int) env('RATE_LIMIT_AUTH_MAX', 10),
            'decay' => (int) env('RATE_LIMIT_AUTH_DECAY', 1),
        ],

        
        'enrollment' => [
            'max' => (int) env('RATE_LIMIT_ENROLLMENT_MAX', 5),
            'decay' => (int) env('RATE_LIMIT_ENROLLMENT_DECAY', 1),
        ],
    ],

];
