<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    
    'api_path' => 'api',

    
    'api_domain' => null,

    
    'export_path' => 'api.json',

    'info' => [
        
        'version' => env('API_VERSION', '1.0.0'),

        
        'description' => 'API Documentation untuk TA Prep LSP - Platform Pembelajaran dan Sertifikasi LSP',
    ],

    
    'ui' => [
        
        'title' => null,

        
        'theme' => 'light',

        
        'hide_try_it' => false,

        
        'hide_schemas' => false,

        
        'logo' => '',

        
        'try_it_credentials_policy' => 'include',

        
        'layout' => 'responsive',
    ],

    
    'servers' => null,

    
    'rate_limits' => [
        'default' => '60 requests per minute',
        'auth' => '10 requests per minute',
        'enrollment' => '5 requests per minute',
    ],

    
    'enum_cases_description_strategy' => 'description',

    
    'enum_cases_names_strategy' => 'names',

    
    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        
    ],

    'extensions' => [],
];
