<?php

return [

    'workers' => [

        'grading' => [
            'queue' => 'grading',
            'processes' => env('QUEUE_GRADING_PROCESSES', 2),
            'tries' => 3,
            'timeout' => 300,
            'memory' => 256,
            'sleep' => 3,
            'max_jobs' => 1000,
            'max_time' => 3600,
        ],

        'notifications' => [
            'queue' => 'notifications',
            'processes' => env('QUEUE_NOTIFICATIONS_PROCESSES', 2),
            'tries' => 3,
            'timeout' => 60,
            'memory' => 128,
            'sleep' => 3,
            'max_jobs' => 1000,
            'max_time' => 3600,
        ],

        'file-processing' => [
            'queue' => 'file-processing',
            'processes' => env('QUEUE_FILE_PROCESSING_PROCESSES', 1),
            'tries' => 3,
            'timeout' => 120,
            'memory' => 512,
            'sleep' => 5,
            'max_jobs' => 500,
            'max_time' => 3600,
        ],

        'default' => [
            'queue' => 'default',
            'processes' => env('QUEUE_DEFAULT_PROCESSES', 1),
            'tries' => 3,
            'timeout' => 90,
            'memory' => 128,
            'sleep' => 3,
            'max_jobs' => 1000,
            'max_time' => 3600,
        ],

    ],

    'combined_queue_order' => 'grading,notifications,file-processing,default',

];
