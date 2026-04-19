<?php

return [
    
    'default_serializer' => '',

    
    'default_paginator' => '',

    
    'base_url' => null,

    
    'fractal_class' => Spatie\Fractal\Fractal::class,

    'auto_includes' => [

        
        'enabled' => true,

        
        'request_key' => 'include',
    ],

    'auto_excludes' => [

        
        'enabled' => true,

        
        'request_key' => 'exclude',
    ],

    'auto_fieldsets' => [

        
        'enabled' => false,

        
        'request_key' => 'fields',
    ],
];
