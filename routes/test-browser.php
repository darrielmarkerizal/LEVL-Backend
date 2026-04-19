<?php

use App\Support\BrowserLogger;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test-browser-detect', function () {
    $deviceInfo = BrowserLogger::getDeviceInfo();

    
    $debugInfo = [
        'device_info' => $deviceInfo,
        'debug' => [
            'user_agent_header' => Request::header('User-Agent'),
            'x_forwarded_for' => Request::header('X-Forwarded-For'),
            'x_real_ip' => Request::header('X-Real-IP'),
            'request_ip' => Request::ip(),
            'all_headers' => Request::header(),
        ],
    ];

    return response()->json($debugInfo);
});
