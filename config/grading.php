<?php

declare(strict_types=1);

return [
    

    'file_retention' => [
        
        'retention_days' => env('GRADING_FILE_RETENTION_DAYS', 365),

        
        'cleanup_batch_size' => env('GRADING_FILE_CLEANUP_BATCH_SIZE', 100),
    ],
];
