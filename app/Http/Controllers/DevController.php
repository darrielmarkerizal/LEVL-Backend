<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DevController extends Controller
{
    public function checkOctane(Request $request)
    {
        $isOctane = isset($_SERVER['LARAVEL_OCTANE']) || App::bound('octane');
        
        $data = [
            'is_octane' => $isOctane,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => phpversion(),
            'octane_server' => $_SERVER['LARAVEL_OCTANE'] ?? 'N/A',
            'pid' => getmypid(),
            'memory_usage' => memory_get_usage(true),
            'environment' => app()->environment(),
        ];

        return view('dev.octane-check', $data);
    }
}
