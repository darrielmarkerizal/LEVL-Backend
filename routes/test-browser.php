<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Support\BrowserLogger;

Route::get("/test-browser-detect", function () {
  $deviceInfo = BrowserLogger::getDeviceInfo();

  // Add debug info to see what headers are received
  $debugInfo = [
    "device_info" => $deviceInfo,
    "debug" => [
      "user_agent_header" => Request::header("User-Agent"),
      "x_forwarded_for" => Request::header("X-Forwarded-For"),
      "x_real_ip" => Request::header("X-Real-IP"),
      "request_ip" => Request::ip(),
      "all_headers" => Request::header(),
    ],
  ];

  return response()->json($debugInfo);
});
