<?php

return [
    
    'enabled' => env('QUERY_DETECTOR_ENABLED', null),

    
    'threshold' => (int) env('QUERY_DETECTOR_THRESHOLD', 1),

    
    'except' => [
        
        
        
        
    ],

    
    'log_channel' => env('QUERY_DETECTOR_LOG_CHANNEL', 'daily'),

    
    'output' => [
        \BeyondCode\QueryDetector\Outputs\Json::class,       
        \BeyondCode\QueryDetector\Outputs\Log::class,        
    ],
];
