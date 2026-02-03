<?php

return [
    'name' => 'Forums',

     
    'cache' => [
        'enabled' => env('FORUM_CACHE_ENABLED', true),
        'ttl' => env('FORUM_CACHE_TTL', 300), 
        'thread_list_ttl' => env('FORUM_THREAD_LIST_CACHE_TTL', 300),
        'statistics_ttl' => env('FORUM_STATISTICS_CACHE_TTL', 3600), 
    ],

     
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],

     
    'reply' => [
        'max_depth' => 5,
    ],

     
    'search' => [
        'min_query_length' => 3,
    ],
];
